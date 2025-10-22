<?php

namespace App\Services\Leave;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * İzin bakiyesi yönetim servisi
 * LeaveRequest onaylandığında/iptal edildiğinde bakiyeleri günceller
 */
class LeaveBalanceService
{
    public function __construct(
        private LeaveEntitlementService $entitlementService
    ) {}

    /**
     * İzin talebini onaylayıp bakiyeden düş
     */
    public function deductLeaveFromBalance(LeaveRequest $leaveRequest, ?User $approver = null): array
    {
        DB::beginTransaction();

        try {
            $employee = $leaveRequest->employee;
            $year = Carbon::parse($leaveRequest->start_date)->year;
            $leaveType = $leaveRequest->leave_type;

            // Bakiye yoksa oluştur
            $balance = $this->entitlementService->ensureLeaveBalance($employee, $leaveType, $year);

            // Yeterli bakiye var mı kontrol et
            if (!$balance->hasSufficientBalance($leaveRequest->working_days)) {
                throw new \Exception(
                    "Yetersiz izin bakiyesi. Kalan: {$balance->remaining_days} gün, İstenen: {$leaveRequest->working_days} gün"
                );
            }

            // Bakiyeden düş
            $balance->deductDays($leaveRequest->working_days, $leaveRequest, $approver);

            DB::commit();

            return [
                'success' => true,
                'balance' => $balance,
                'remaining_days' => $balance->remaining_days,
                'used_days' => $balance->used_days,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * İptal edilen izni bakiyeye iade et
     */
    public function restoreLeaveToBalance(LeaveRequest $leaveRequest, ?User $user = null): array
    {
        DB::beginTransaction();

        try {
            $employee = $leaveRequest->employee;
            $year = Carbon::parse($leaveRequest->start_date)->year;
            $leaveType = $leaveRequest->leave_type;

            $balance = LeaveBalance::forEmployee($employee->id)
                ->forYear($year)
                ->byLeaveType($leaveType)
                ->first();

            if (!$balance) {
                throw new \Exception("İzin bakiyesi bulunamadı.");
            }

            // Bakiyeye iade et
            $balance->restoreDays($leaveRequest->working_days, $leaveRequest, $user);

            DB::commit();

            return [
                'success' => true,
                'balance' => $balance,
                'remaining_days' => $balance->remaining_days,
                'used_days' => $balance->used_days,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Bekleyen izin talebini planlı günlere ekle
     */
    public function markAsPlanned(LeaveRequest $leaveRequest): void
    {
        $employee = $leaveRequest->employee;
        $year = Carbon::parse($leaveRequest->start_date)->year;
        $leaveType = $leaveRequest->leave_type;

        $balance = $this->entitlementService->ensureLeaveBalance($employee, $leaveType, $year);

        // Planlı günlere ekle (remaining_days'den düşer)
        $balance->addPlannedDays($leaveRequest->working_days, $leaveRequest);
    }

    /**
     * Reddedilen izin talebini planlı günlerden çıkar
     */
    public function unmarkAsPlanned(LeaveRequest $leaveRequest): void
    {
        $employee = $leaveRequest->employee;
        $year = Carbon::parse($leaveRequest->start_date)->year;
        $leaveType = $leaveRequest->leave_type;

        $balance = LeaveBalance::forEmployee($employee->id)
            ->forYear($year)
            ->byLeaveType($leaveType)
            ->first();

        if ($balance) {
            // Planlı günlerden çıkar (remaining_days'e geri ekler)
            $balance->removePlannedDays($leaveRequest->working_days, $leaveRequest);
        }
    }

    /**
     * Çalışanın tüm izin bakiyelerini getir
     */
    public function getEmployeeBalances(Employee $employee, int $year = null): array
    {
        $year = $year ?? now()->year;

        $balances = LeaveBalance::forEmployee($employee->id)
            ->forYear($year)
            ->active()
            ->get();

        // Eksik bakiyeler varsa oluştur (annual)
        if ($balances->where('leave_type', 'annual')->isEmpty()) {
            $annualBalance = $this->entitlementService->ensureLeaveBalance($employee, 'annual', $year);
            $balances->push($annualBalance);
        }

        return $balances->map(function ($balance) {
            return [
                'id' => $balance->id,
                'leave_type' => $balance->leave_type,
                'leave_type_display' => $balance->leave_type_display,
                'entitled_days' => $balance->entitled_days,
                'carried_forward_days' => $balance->carried_forward_days,
                'total_days' => $balance->total_days,
                'used_days' => $balance->used_days,
                'planned_days' => $balance->planned_days,
                'remaining_days' => $balance->remaining_days,
                'usage_percentage' => $balance->usage_percentage,
                'seniority_years' => $balance->seniority_years,
                'is_expired' => $balance->is_expired,
            ];
        })->toArray();
    }

    /**
     * Çalışanın belirli izin türünde kalan günü getir
     */
    public function getRemainingDays(Employee $employee, string $leaveType = 'annual', int $year = null): float
    {
        $year = $year ?? now()->year;

        $balance = LeaveBalance::forEmployee($employee->id)
            ->forYear($year)
            ->byLeaveType($leaveType)
            ->first();

        if (!$balance) {
            // Bakiye yoksa oluştur ve dön
            $balance = $this->entitlementService->ensureLeaveBalance($employee, $leaveType, $year);
        }

        return $balance->remaining_days;
    }

    /**
     * Manuel bakiye düzeltmesi yap
     */
    public function adjustBalance(
        Employee $employee,
        string $leaveType,
        float $amount,
        string $reason,
        User $user,
        int $year = null
    ): LeaveBalance {
        $year = $year ?? now()->year;

        DB::beginTransaction();

        try {
            $balance = $this->entitlementService->ensureLeaveBalance($employee, $leaveType, $year);
            $balance->adjustBalance($amount, $reason, $user);

            DB::commit();

            return $balance;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Yıl sonu devir işlemi
     * Kullanılmayan izinleri yeni yıla aktar (max_carry_forward'a göre)
     */
    public function performYearEndCarryForward(int $fromYear, int $toYear = null): array
    {
        $toYear = $toYear ?? $fromYear + 1;

        DB::beginTransaction();

        try {
            $results = [
                'processed' => 0,
                'carried_forward' => 0,
                'expired' => 0,
                'details' => [],
            ];

            // Önceki yılın bakiyelerini getir
            $balances = LeaveBalance::forYear($fromYear)
                ->where('leave_type', 'annual')
                ->where('status', 'active')
                ->get();

            foreach ($balances as $balance) {
                $employee = $balance->employee;

                // Kalan izin varsa devir et
                if ($balance->remaining_days > 0) {
                    $carryForwardAmount = min($balance->remaining_days, $balance->max_carry_forward ?? 10);
                    $expiredAmount = $balance->remaining_days - $carryForwardAmount;

                    // Yeni yıl bakiyesi oluştur veya güncelle
                    $newBalance = $this->entitlementService->ensureLeaveBalance($employee, 'annual', $toYear);

                    // Devir ekle
                    if ($carryForwardAmount > 0) {
                        $newBalance->carried_forward_days = $carryForwardAmount;
                        $newBalance->total_days += $carryForwardAmount;
                        $newBalance->remaining_days += $carryForwardAmount;
                        $newBalance->save();

                        $results['carried_forward'] += $carryForwardAmount;
                    }

                    // Eski bakiyeyi expired yap
                    $balance->status = 'expired';
                    $balance->save();

                    if ($expiredAmount > 0) {
                        $results['expired'] += $expiredAmount;
                    }

                    $results['details'][] = [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->full_name,
                        'remaining' => $balance->remaining_days,
                        'carried_forward' => $carryForwardAmount,
                        'expired' => $expiredAmount,
                    ];
                }

                $results['processed']++;
            }

            DB::commit();

            return $results;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Toplu bakiye hesaplama - tüm çalışanlar için
     */
    public function calculateBulkBalances(int $year = null): array
    {
        return $this->entitlementService->calculateForAllEmployees($year);
    }

    /**
     * Bakiye özetini getir (dashboard için)
     */
    public function getBalanceSummary(Employee $employee): array
    {
        $currentYear = now()->year;
        $balances = $this->getEmployeeBalances($employee, $currentYear);

        $annualBalance = collect($balances)->firstWhere('leave_type', 'annual');

        return [
            'current_year' => $currentYear,
            'annual_leave' => [
                'total' => $annualBalance['total_days'] ?? 0,
                'used' => $annualBalance['used_days'] ?? 0,
                'planned' => $annualBalance['planned_days'] ?? 0,
                'remaining' => $annualBalance['remaining_days'] ?? 0,
                'percentage_used' => $annualBalance['usage_percentage'] ?? 0,
            ],
            'all_balances' => $balances,
            'seniority_years' => $annualBalance['seniority_years'] ?? 0,
            'warnings' => $this->getBalanceWarnings($employee),
        ];
    }

    /**
     * Bakiye uyarılarını getir
     */
    private function getBalanceWarnings(Employee $employee): array
    {
        $warnings = [];
        $balances = $this->getEmployeeBalances($employee);

        foreach ($balances as $balance) {
            // Kalan gün azsa uyar
            if ($balance['remaining_days'] < 3 && $balance['remaining_days'] > 0) {
                $warnings[] = [
                    'type' => 'low_balance',
                    'message' => "{$balance['leave_type_display']} bakiyeniz azalıyor: {$balance['remaining_days']} gün kaldı",
                    'severity' => 'warning',
                ];
            }

            // Kullanım yüzdesi yüksekse uyar
            if ($balance['usage_percentage'] > 80) {
                $warnings[] = [
                    'type' => 'high_usage',
                    'message' => "{$balance['leave_type_display']} kullanımınız %{$balance['usage_percentage']} seviyesinde",
                    'severity' => 'info',
                ];
            }

            // Süresi doluyorsa uyar
            if ($balance['is_expired']) {
                $warnings[] = [
                    'type' => 'expired',
                    'message' => "{$balance['leave_type_display']} süreniz doldu",
                    'severity' => 'danger',
                ];
            }
        }

        return $warnings;
    }

    /**
     * İzin talebi yapılabilir mi kontrol et
     */
    public function canRequestLeave(Employee $employee, string $leaveType, float $requestedDays, int $year = null): array
    {
        $year = $year ?? now()->year;

        // Bakiyeyi al veya oluştur
        $balance = $this->entitlementService->ensureLeaveBalance($employee, $leaveType, $year);

        $canRequest = $balance->hasSufficientBalance($requestedDays);

        return [
            'can_request' => $canRequest,
            'remaining_days' => $balance->remaining_days,
            'requested_days' => $requestedDays,
            'deficit' => $canRequest ? 0 : $requestedDays - $balance->remaining_days,
            'message' => $canRequest
                ? "İzin talebi yapılabilir. Kalan: {$balance->remaining_days} gün"
                : "Yetersiz bakiye. Kalan: {$balance->remaining_days} gün, İstenen: {$requestedDays} gün",
        ];
    }
}
