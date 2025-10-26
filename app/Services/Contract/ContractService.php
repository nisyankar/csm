<?php

namespace App\Services\Contract;

use App\Models\Contract;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContractService
{
    /**
     * Yeni sözleşme oluştur
     */
    public function create(array $data): Contract
    {
        try {
            DB::beginTransaction();

            // Sözleşme numarası otomatik oluştur
            if (empty($data['contract_number'])) {
                $data['contract_number'] = $this->generateContractNumber($data['project_id'], $data['contract_type']);
            }

            $contract = Contract::create($data);

            DB::commit();

            Log::info('Sözleşme oluşturuldu', [
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
            ]);

            return $contract;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sözleşme oluşturma hatası: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sözleşme güncelle
     */
    public function update(Contract $contract, array $data): Contract
    {
        try {
            DB::beginTransaction();

            $contract->update($data);

            DB::commit();

            Log::info('Sözleşme güncellendi', [
                'contract_id' => $contract->id,
            ]);

            return $contract->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sözleşme güncelleme hatası: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sözleşme aktive et
     */
    public function activate(Contract $contract): Contract
    {
        if ($contract->status !== 'draft') {
            throw new \Exception('Sadece taslak sözleşmeler aktif hale getirilebilir.');
        }

        $contract->update(['status' => 'active']);

        Log::info('Sözleşme aktif hale getirildi', [
            'contract_id' => $contract->id,
        ]);

        return $contract->fresh();
    }

    /**
     * Sözleşme feshet
     */
    public function terminate(Contract $contract, string $reason): Contract
    {
        if (!$contract->canBeTerminated()) {
            throw new \Exception('Bu sözleşme feshedilemez.');
        }

        $contract->update([
            'status' => 'terminated',
            'termination_date' => now(),
            'termination_reason' => $reason,
        ]);

        Log::warning('Sözleşme feshedildi', [
            'contract_id' => $contract->id,
            'reason' => $reason,
        ]);

        return $contract->fresh();
    }

    /**
     * Sözleşme tamamla
     */
    public function complete(Contract $contract): Contract
    {
        if ($contract->status !== 'active') {
            throw new \Exception('Sadece aktif sözleşmeler tamamlanabilir.');
        }

        $contract->update(['status' => 'completed']);

        Log::info('Sözleşme tamamlandı', [
            'contract_id' => $contract->id,
        ]);

        return $contract->fresh();
    }

    /**
     * Süresi dolan sözleşmeleri güncelle
     */
    public function updateExpiredContracts(): int
    {
        $count = Contract::where('status', 'active')
            ->whereDate('end_date', '<', now())
            ->update(['status' => 'expired']);

        if ($count > 0) {
            Log::info("Süresi dolan {$count} sözleşme güncellendi.");
        }

        return $count;
    }

    /**
     * Sözleşme numarası oluştur
     */
    private function generateContractNumber(int $projectId, string $contractType): string
    {
        $project = Project::findOrFail($projectId);

        // Proje kodu
        $projectCode = $project->project_code ?? 'PRJ';

        // Sözleşme tipi kısaltması
        $typeCode = match($contractType) {
            'subcontractor' => 'TS',
            'supplier' => 'TD',
            default => 'SZ',
        };

        // Yıl
        $year = date('Y');

        // Benzersiz sıra numarası bul (tüm sözleşmeler arasında)
        // Bu projenin bu yıldaki sözleşme sayısına göre
        $count = Contract::where('project_id', $projectId)
            ->where('contract_type', $contractType)
            ->whereYear('created_at', $year)
            ->count();

        $sequence = $count + 1;

        // Eğer aynı numara varsa devam et
        $attempts = 0;
        do {
            $contractNumber = sprintf('%s-%s-%s-%04d', $projectCode, $typeCode, $year, $sequence);
            $exists = Contract::where('contract_number', $contractNumber)->exists();

            if ($exists) {
                $sequence++;
                $attempts++;

                // Sonsuz döngüyü önle
                if ($attempts > 100) {
                    // Timestamp ekle benzersizlik için
                    $contractNumber = sprintf('%s-%s-%s-%04d-%s', $projectCode, $typeCode, $year, $sequence, substr(microtime(true) * 10000, -6));
                    break;
                }
            }
        } while ($exists);

        return $contractNumber;
    }

    /**
     * Sözleşme özeti
     */
    public function getContractSummary(Contract $contract): array
    {
        $totalPaid = $contract->progressPayments()
            ->where('status', 'paid')
            ->sum('total_amount') ?? 0;

        $totalPending = $contract->progressPayments()
            ->whereIn('status', ['pending', 'approved'])
            ->sum('total_amount') ?? 0;

        $paymentCount = $contract->progressPayments()->count();

        return [
            'contract_value' => $contract->contract_value,
            'total_paid' => $totalPaid,
            'total_pending' => $totalPending,
            'remaining' => $contract->contract_value - $totalPaid - $totalPending,
            'payment_count' => $paymentCount,
            'completion_percentage' => $contract->contract_value > 0
                ? round(($totalPaid / $contract->contract_value) * 100, 2)
                : 0,
            'days_until_expiry' => $contract->days_until_expiry,
            'is_expiring_soon' => $contract->is_expiring_soon,
            'is_expired' => $contract->is_expired,
        ];
    }

    /**
     * Dashboard istatistikleri
     */
    public function getDashboardStats(?int $projectId = null): array
    {
        $query = Contract::query();

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $totalContracts = $query->count();
        $activeContracts = (clone $query)->where('status', 'active')->count();
        $expiringSoon = (clone $query)->expiringSoon(30)->count();
        $totalValue = (clone $query)->where('status', 'active')->sum('contract_value');

        // Taşeron sözleşmeleri
        $subcontractorContracts = (clone $query)
            ->where('contract_type', 'subcontractor')
            ->where('status', 'active')
            ->count();

        // Tedarikçi anlaşmaları
        $supplierContracts = (clone $query)
            ->where('contract_type', 'supplier')
            ->where('status', 'active')
            ->count();

        return [
            'total_contracts' => $totalContracts,
            'active_contracts' => $activeContracts,
            'expiring_soon' => $expiringSoon,
            'total_value' => $totalValue,
            'subcontractor_contracts' => $subcontractorContracts,
            'supplier_contracts' => $supplierContracts,
        ];
    }

    /**
     * Yaklaşan bitiş tarihleri
     */
    public function getExpiringSoonContracts(int $days = 30, ?int $projectId = null)
    {
        $query = Contract::with(['project', 'subcontractor'])
            ->expiringSoon($days);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->orderBy('end_date', 'asc')->get();
    }

    /**
     * Süresi dolan sözleşmeler
     */
    public function getExpiredContracts(?int $projectId = null)
    {
        $query = Contract::with(['project', 'subcontractor'])
            ->expired();

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->orderBy('end_date', 'desc')->get();
    }

    /**
     * Sözleşme için hakediş özetleri
     */
    public function getContractProgressPayments(Contract $contract)
    {
        return $contract->progressPayments()
            ->with(['workItem', 'projectStructure', 'projectFloor', 'projectUnit'])
            ->orderBy('payment_date', 'desc')
            ->get();
    }
}
