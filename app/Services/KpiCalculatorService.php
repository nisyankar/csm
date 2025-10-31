<?php

namespace App\Services;

use App\Models\KpiDefinition;
use App\Models\Project;
use App\Models\ProgressPayment;
use App\Models\FinancialTransaction;
use App\Models\Timesheet;
use App\Models\Quantity;
use Illuminate\Support\Facades\DB;

/**
 * KPI Hesaplama Servisi
 */
class KpiCalculatorService
{
    /**
     * KPI hesapla
     */
    public function calculate(KpiDefinition $kpi, ?int $projectId = null): array
    {
        $value = $this->calculateValue($kpi, $projectId);
        $status = $this->getKpiStatus($value, $kpi->target_value, $kpi->warning_threshold);

        return [
            'kpi_id' => $kpi->id,
            'kpi_name' => $kpi->name,
            'value' => $value,
            'target_value' => $kpi->target_value,
            'warning_threshold' => $kpi->warning_threshold,
            'unit' => $kpi->unit,
            'status' => $status,
            'calculated_at' => now(),
        ];
    }

    /**
     * KPI değerini hesapla
     */
    protected function calculateValue(KpiDefinition $kpi, ?int $projectId): float
    {
        // Formül tipine göre hesaplama
        return match($kpi->formula) {
            'project_completion_percentage' => $this->calculateProjectCompletion($projectId),
            'cost_variance' => $this->calculateCostVariance($projectId),
            'labor_productivity' => $this->calculateLaborProductivity($projectId),
            'safety_incident_rate' => $this->calculateSafetyIncidentRate($projectId),
            'equipment_utilization' => $this->calculateEquipmentUtilization($projectId),
            'on_time_delivery' => $this->calculateOnTimeDelivery($projectId),
            default => $this->parseCustomFormula($kpi->formula, $projectId)
        };
    }

    /**
     * Proje tamamlanma yüzdesi
     */
    protected function calculateProjectCompletion(?int $projectId): float
    {
        if (!$projectId) {
            return 0;
        }

        $totalQuantities = Quantity::where('project_id', $projectId)->sum('planned_quantity');
        $completedQuantities = Quantity::where('project_id', $projectId)->sum('completed_quantity');

        if ($totalQuantities == 0) {
            return 0;
        }

        return round(($completedQuantities / $totalQuantities) * 100, 2);
    }

    /**
     * Maliyet varyansı
     */
    protected function calculateCostVariance(?int $projectId): float
    {
        if (!$projectId) {
            return 0;
        }

        $project = Project::find($projectId);
        if (!$project) {
            return 0;
        }

        $plannedBudget = $project->budget ?? 0;
        $actualCost = FinancialTransaction::where('project_id', $projectId)
            ->where('type', 'expense')
            ->sum('amount');

        if ($plannedBudget == 0) {
            return 0;
        }

        return round((($actualCost - $plannedBudget) / $plannedBudget) * 100, 2);
    }

    /**
     * İşgücü verimliliği
     */
    protected function calculateLaborProductivity(?int $projectId): float
    {
        if (!$projectId) {
            return 0;
        }

        $totalHours = Timesheet::where('project_id', $projectId)->sum('total_hours');
        $completedWork = Quantity::where('project_id', $projectId)->sum('completed_quantity');

        if ($totalHours == 0) {
            return 0;
        }

        return round($completedWork / $totalHours, 2);
    }

    /**
     * İSG kaza oranı
     */
    protected function calculateSafetyIncidentRate(?int $projectId): float
    {
        if (!$projectId) {
            return 0;
        }

        $totalWorkers = Timesheet::where('project_id', $projectId)
            ->distinct('employee_id')
            ->count('employee_id');

        $incidents = DB::table('safety_incidents')
            ->where('project_id', $projectId)
            ->whereIn('incident_type', ['injury', 'fatality'])
            ->count();

        if ($totalWorkers == 0) {
            return 0;
        }

        return round(($incidents / $totalWorkers) * 100, 2);
    }

    /**
     * Ekipman kullanım oranı
     */
    protected function calculateEquipmentUtilization(?int $projectId): float
    {
        if (!$projectId) {
            return 0;
        }

        $totalEquipment = DB::table('equipments')
            ->where('current_project_id', $projectId)
            ->count();

        $usedEquipment = DB::table('equipments')
            ->where('current_project_id', $projectId)
            ->where('status', 'in_use')
            ->count();

        if ($totalEquipment == 0) {
            return 0;
        }

        return round(($usedEquipment / $totalEquipment) * 100, 2);
    }

    /**
     * Zamanında teslim oranı
     */
    protected function calculateOnTimeDelivery(?int $projectId): float
    {
        if (!$projectId) {
            return 0;
        }

        $totalPayments = ProgressPayment::where('project_id', $projectId)
            ->whereIn('status', ['completed', 'approved', 'paid'])
            ->count();

        $onTimePayments = ProgressPayment::where('project_id', $projectId)
            ->whereIn('status', ['completed', 'approved', 'paid'])
            ->whereRaw('completed_at <= planned_end_date')
            ->count();

        if ($totalPayments == 0) {
            return 0;
        }

        return round(($onTimePayments / $totalPayments) * 100, 2);
    }

    /**
     * Özel formül parse et
     */
    protected function parseCustomFormula(string $formula, ?int $projectId): float
    {
        // Basit formül parser
        // Örnek: "total_income - total_expense"
        // Daha karmaşık formüller için expression evaluator kullanılabilir

        return 0;
    }

    /**
     * KPI durumu belirle
     */
    protected function getKpiStatus(float $value, ?float $target, ?float $warning): string
    {
        if (!$target) {
            return 'unknown';
        }

        if ($value >= $target) {
            return 'success';
        }

        if ($warning && $value >= $warning) {
            return 'warning';
        }

        return 'danger';
    }

    /**
     * Tüm KPI'ları hesapla
     */
    public function calculateAll(?int $projectId = null): array
    {
        $kpis = KpiDefinition::active()->get();
        $results = [];

        foreach ($kpis as $kpi) {
            $results[] = $this->calculate($kpi, $projectId);
        }

        return $results;
    }

    /**
     * Modül bazlı KPI'ları hesapla
     */
    public function calculateByModule(string $module, ?int $projectId = null): array
    {
        $kpis = KpiDefinition::active()->byModule($module)->get();
        $results = [];

        foreach ($kpis as $kpi) {
            $results[] = $this->calculate($kpi, $projectId);
        }

        return $results;
    }

    /**
     * Dashboard için KPI özeti
     */
    public function getDashboardSummary(?int $projectId = null): array
    {
        $allKpis = $this->calculateAll($projectId);

        return [
            'total_kpis' => count($allKpis),
            'success_count' => count(array_filter($allKpis, fn($k) => $k['status'] === 'success')),
            'warning_count' => count(array_filter($allKpis, fn($k) => $k['status'] === 'warning')),
            'danger_count' => count(array_filter($allKpis, fn($k) => $k['status'] === 'danger')),
            'kpis' => $allKpis,
        ];
    }
}
