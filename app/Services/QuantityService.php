<?php

namespace App\Services;

use App\Models\Quantity;
use App\Models\Project;
use App\Models\WorkItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Keşif & Metraj Yönetim Servisi
 */
class QuantityService
{
    /**
     * Proje için toplam metraj özeti
     */
    public function getProjectSummary(int $projectId): array
    {
        $quantities = Quantity::where('project_id', $projectId)
            ->with(['workItem', 'projectStructure', 'projectFloor'])
            ->get();

        return [
            'total_items' => $quantities->count(),
            'total_planned_quantity' => $quantities->sum('planned_quantity'),
            'total_completed_quantity' => $quantities->sum('completed_quantity'),
            'total_remaining_quantity' => $quantities->sum(function ($q) {
                return $q->remaining_quantity;
            }),
            'completion_percentage' => $this->calculateCompletionPercentage(
                $quantities->sum('completed_quantity'),
                $quantities->sum('planned_quantity')
            ),
            'verified_count' => $quantities->filter->isVerified()->count(),
            'approved_count' => $quantities->filter->isApproved()->count(),
            'pending_count' => $quantities->filter(fn($q) => !$q->isVerified())->count(),
        ];
    }

    /**
     * İş kalemi bazında metraj raporu
     */
    public function getWorkItemReport(int $projectId): Collection
    {
        return Quantity::where('project_id', $projectId)
            ->select([
                'work_item_id',
                DB::raw('SUM(planned_quantity) as total_planned'),
                DB::raw('SUM(completed_quantity) as total_completed'),
                DB::raw('COUNT(*) as measurement_count'),
            ])
            ->with('workItem')
            ->groupBy('work_item_id')
            ->get()
            ->map(function ($item) {
                return [
                    'work_item_id' => $item->work_item_id,
                    'work_item_name' => $item->workItem->name ?? 'N/A',
                    'unit' => $item->workItem->unit ?? 'N/A',
                    'total_planned' => (float) $item->total_planned,
                    'total_completed' => (float) $item->total_completed,
                    'remaining' => (float) ($item->total_planned - $item->total_completed),
                    'completion_percentage' => $this->calculateCompletionPercentage(
                        $item->total_completed,
                        $item->total_planned
                    ),
                    'measurement_count' => $item->measurement_count,
                ];
            });
    }

    /**
     * Lokasyon bazında metraj raporu (Blok/Kat/Birim)
     */
    public function getLocationReport(int $projectId, string $locationType = 'structure'): Collection
    {
        $locationColumn = match($locationType) {
            'structure' => 'project_structure_id',
            'floor' => 'project_floor_id',
            'unit' => 'project_unit_id',
            default => 'project_structure_id',
        };

        $relationName = match($locationType) {
            'structure' => 'projectStructure',
            'floor' => 'projectFloor',
            'unit' => 'projectUnit',
            default => 'projectStructure',
        };

        return Quantity::where('project_id', $projectId)
            ->whereNotNull($locationColumn)
            ->select([
                $locationColumn,
                DB::raw('SUM(planned_quantity) as total_planned'),
                DB::raw('SUM(completed_quantity) as total_completed'),
                DB::raw('COUNT(*) as measurement_count'),
            ])
            ->with($relationName)
            ->groupBy($locationColumn)
            ->get()
            ->map(function ($item) use ($relationName, $locationColumn) {
                $location = $item->{$relationName};

                return [
                    'location_id' => $item->{$locationColumn},
                    'location_name' => $location->name ?? 'N/A',
                    'total_planned' => (float) $item->total_planned,
                    'total_completed' => (float) $item->total_completed,
                    'remaining' => (float) ($item->total_planned - $item->total_completed),
                    'completion_percentage' => $this->calculateCompletionPercentage(
                        $item->total_completed,
                        $item->total_planned
                    ),
                    'measurement_count' => $item->measurement_count,
                ];
            });
    }

    /**
     * Keşif vs Gerçekleşen karşılaştırma raporu
     */
    public function getComparisonReport(int $projectId): array
    {
        $quantities = Quantity::where('project_id', $projectId)
            ->with(['workItem'])
            ->get();

        $grouped = $quantities->groupBy('work_item_id');

        $items = $grouped->map(function ($group, $workItemId) {
            $workItem = $group->first()->workItem;
            $totalPlanned = $group->sum('planned_quantity');
            $totalCompleted = $group->sum('completed_quantity');
            $variance = $totalCompleted - $totalPlanned;
            $variancePercentage = $totalPlanned > 0
                ? round(($variance / $totalPlanned) * 100, 2)
                : 0;

            return [
                'work_item_id' => $workItemId,
                'work_item_name' => $workItem->name ?? 'N/A',
                'work_item_code' => $workItem->code ?? 'N/A',
                'unit' => $workItem->unit ?? 'N/A',
                'planned_quantity' => (float) $totalPlanned,
                'completed_quantity' => (float) $totalCompleted,
                'variance' => (float) $variance,
                'variance_percentage' => $variancePercentage,
                'status' => $this->getVarianceStatus($variancePercentage),
            ];
        })->values();

        return [
            'items' => $items,
            'summary' => [
                'total_planned' => $quantities->sum('planned_quantity'),
                'total_completed' => $quantities->sum('completed_quantity'),
                'total_variance' => $quantities->sum('completed_quantity') - $quantities->sum('planned_quantity'),
                'overall_completion' => $this->calculateCompletionPercentage(
                    $quantities->sum('completed_quantity'),
                    $quantities->sum('planned_quantity')
                ),
            ],
        ];
    }

    /**
     * Aylık metraj ilerleme raporu
     */
    public function getMonthlyProgressReport(int $projectId, int $year, int $month): array
    {
        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $quantities = Quantity::where('project_id', $projectId)
            ->whereBetween('measurement_date', [$startDate, $endDate])
            ->with(['workItem', 'verifiedBy', 'approvedBy'])
            ->orderBy('measurement_date')
            ->get();

        $dailyProgress = $quantities->groupBy(function ($quantity) {
            return $quantity->measurement_date->format('Y-m-d');
        })->map(function ($dayQuantities) {
            return [
                'date' => $dayQuantities->first()->measurement_date->format('Y-m-d'),
                'completed_quantity' => $dayQuantities->sum('completed_quantity'),
                'measurement_count' => $dayQuantities->count(),
                'verified_count' => $dayQuantities->filter->isVerified()->count(),
            ];
        })->values();

        return [
            'period' => [
                'year' => $year,
                'month' => $month,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_measurements' => $quantities->count(),
                'total_completed_quantity' => $quantities->sum('completed_quantity'),
                'verified_count' => $quantities->filter->isVerified()->count(),
                'approved_count' => $quantities->filter->isApproved()->count(),
            ],
            'daily_progress' => $dailyProgress,
        ];
    }

    /**
     * İş kalemi için mevcut metrajı al
     */
    public function getCurrentQuantity(int $workItemId, ?int $structureId = null, ?int $floorId = null, ?int $unitId = null): ?Quantity
    {
        $query = Quantity::where('work_item_id', $workItemId);

        if ($structureId) {
            $query->where('project_structure_id', $structureId);
        }

        if ($floorId) {
            $query->where('project_floor_id', $floorId);
        }

        if ($unitId) {
            $query->where('project_unit_id', $unitId);
        }

        return $query->first();
    }

    /**
     * Metraj verilerini toplu import et
     */
    public function importQuantities(int $projectId, array $quantitiesData): array
    {
        $imported = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($quantitiesData as $index => $data) {
                try {
                    // Validasyon
                    if (!isset($data['work_item_id'])) {
                        $errors[] = "Satır " . ($index + 1) . ": İş kalemi belirtilmemiş";
                        continue;
                    }

                    // Metraj oluştur
                    Quantity::create([
                        'project_id' => $projectId,
                        'work_item_id' => $data['work_item_id'],
                        'project_structure_id' => $data['project_structure_id'] ?? null,
                        'project_floor_id' => $data['project_floor_id'] ?? null,
                        'project_unit_id' => $data['project_unit_id'] ?? null,
                        'planned_quantity' => $data['planned_quantity'] ?? 0,
                        'completed_quantity' => $data['completed_quantity'] ?? 0,
                        'unit' => $data['unit'] ?? 'm²',
                        'measurement_date' => $data['measurement_date'] ?? now(),
                        'measurement_method' => $data['measurement_method'] ?? null,
                        'notes' => $data['notes'] ?? null,
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Satır " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            return [
                'success' => true,
                'imported' => $imported,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'imported' => 0,
                'errors' => ['Genel hata: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Metraj verilerini Excel formatına dönüştür
     */
    public function exportToArray(int $projectId): array
    {
        return Quantity::where('project_id', $projectId)
            ->with(['workItem', 'projectStructure', 'projectFloor', 'projectUnit', 'verifiedBy', 'approvedBy'])
            ->orderBy('created_at')
            ->get()
            ->map(function ($quantity) {
                return [
                    'ID' => $quantity->id,
                    'İş Kalemi' => $quantity->workItem->name ?? 'N/A',
                    'İş Kalemi Kodu' => $quantity->workItem->code ?? 'N/A',
                    'Blok' => $quantity->projectStructure->name ?? '-',
                    'Kat' => $quantity->projectFloor->name ?? '-',
                    'Birim' => $quantity->projectUnit->name ?? '-',
                    'Planlanan Metraj' => $quantity->planned_quantity,
                    'Gerçekleşen Metraj' => $quantity->completed_quantity,
                    'Kalan Metraj' => $quantity->remaining_quantity,
                    'Birim' => $quantity->unit,
                    'Tamamlanma %' => $quantity->completion_percentage,
                    'Ölçüm Tarihi' => $quantity->measurement_date?->format('d.m.Y'),
                    'Ölçüm Yöntemi' => $quantity->measurement_method ?? '-',
                    'Doğrulayan' => $quantity->verifiedBy->name ?? '-',
                    'Onaylayan' => $quantity->approvedBy->name ?? '-',
                    'Notlar' => $quantity->notes ?? '-',
                ];
            })
            ->toArray();
    }

    /**
     * Tamamlanma yüzdesi hesapla
     */
    private function calculateCompletionPercentage(float $completed, float $planned): float
    {
        if ($planned == 0) {
            return 0;
        }

        return round(($completed / $planned) * 100, 2);
    }

    /**
     * Varyans durumu belirle
     */
    private function getVarianceStatus(float $variancePercentage): string
    {
        if ($variancePercentage > 10) {
            return 'over'; // Keşfi aşan
        } elseif ($variancePercentage < -10) {
            return 'under'; // Keşiften az
        } else {
            return 'normal'; // Normal aralık
        }
    }

    /**
     * Dashboard istatistikleri
     */
    public function getDashboardStats(int $projectId): array
    {
        $quantities = Quantity::where('project_id', $projectId)->get();

        $totalPlanned = $quantities->sum('planned_quantity');
        $totalCompleted = $quantities->sum('completed_quantity');

        return [
            'total_items' => $quantities->count(),
            'total_planned_quantity' => $totalPlanned,
            'total_completed_quantity' => $totalCompleted,
            'total_remaining_quantity' => $totalPlanned - $totalCompleted,
            'completion_percentage' => $this->calculateCompletionPercentage($totalCompleted, $totalPlanned),
            'verified_count' => $quantities->filter->isVerified()->count(),
            'approved_count' => $quantities->filter->isApproved()->count(),
            'pending_verification' => $quantities->filter(fn($q) => !$q->isVerified())->count(),
            'recent_measurements' => Quantity::where('project_id', $projectId)
                ->with(['workItem', 'verifiedBy'])
                ->orderBy('measurement_date', 'desc')
                ->limit(5)
                ->get(),
        ];
    }
}
