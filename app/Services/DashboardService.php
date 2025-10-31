<?php

namespace App\Services;

use App\Models\UserDashboard;
use App\Models\Project;
use App\Models\ProgressPayment;
use App\Models\Timesheet;
use App\Models\FinancialTransaction;
use App\Models\SafetyIncident;
use App\Models\Equipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard Yönetim Servisi
 */
class DashboardService
{
    protected KpiCalculatorService $kpiCalculator;

    public function __construct(KpiCalculatorService $kpiCalculator)
    {
        $this->kpiCalculator = $kpiCalculator;
    }

    /**
     * Kullanıcı dashboard'unu al veya oluştur
     */
    public function getUserDashboard(?int $userId = null): UserDashboard
    {
        $userId = $userId ?? Auth::id();

        return UserDashboard::firstOrCreate(
            ['user_id' => $userId],
            [
                'layout_json' => $this->getDefaultLayout(),
                'widgets_json' => $this->getDefaultWidgets(),
            ]
        );
    }

    /**
     * Dashboard layout güncelle
     */
    public function updateLayout(array $layout, ?int $userId = null): bool
    {
        $dashboard = $this->getUserDashboard($userId);
        $dashboard->layout_json = $layout;

        return $dashboard->save();
    }

    /**
     * Dashboard widget'ları güncelle
     */
    public function updateWidgets(array $widgets, ?int $userId = null): bool
    {
        $dashboard = $this->getUserDashboard($userId);
        $dashboard->widgets_json = $widgets;

        return $dashboard->save();
    }

    /**
     * Widget verisi al
     */
    public function getWidgetData(string $widgetType, array $params = []): array
    {
        return match($widgetType) {
            'projects_summary' => $this->getProjectsSummaryWidget($params),
            'financial_summary' => $this->getFinancialSummaryWidget($params),
            'progress_payments' => $this->getProgressPaymentsWidget($params),
            'timesheets_summary' => $this->getTimesheetsSummaryWidget($params),
            'safety_incidents' => $this->getSafetyIncidentsWidget($params),
            'equipment_status' => $this->getEquipmentStatusWidget($params),
            'kpi_overview' => $this->getKpiOverviewWidget($params),
            'recent_activities' => $this->getRecentActivitiesWidget($params),
            default => []
        };
    }

    /**
     * Proje özeti widget'ı
     */
    protected function getProjectsSummaryWidget(array $params): array
    {
        $projects = Project::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return [
            'total' => Project::count(),
            'by_status' => $projects,
            'active' => Project::where('status', 'active')->count(),
            'completed' => Project::where('status', 'completed')->count(),
        ];
    }

    /**
     * Finansal özet widget'ı
     */
    protected function getFinancialSummaryWidget(array $params): array
    {
        $income = FinancialTransaction::where('type', 'income')->sum('amount');
        $expense = FinancialTransaction::where('type', 'expense')->sum('amount');

        return [
            'total_income' => $income,
            'total_expense' => $expense,
            'net_profit' => $income - $expense,
            'monthly_income' => FinancialTransaction::where('type', 'income')
                ->whereMonth('transaction_date', now()->month)
                ->sum('amount'),
            'monthly_expense' => FinancialTransaction::where('type', 'expense')
                ->whereMonth('transaction_date', now()->month)
                ->sum('amount'),
        ];
    }

    /**
     * Hakediş widget'ı
     */
    protected function getProgressPaymentsWidget(array $params): array
    {
        return [
            'total' => ProgressPayment::count(),
            'pending' => ProgressPayment::where('status', 'in_progress')->count(),
            'approved' => ProgressPayment::where('status', 'approved')->count(),
            'paid' => ProgressPayment::where('status', 'paid')->count(),
            'total_amount' => ProgressPayment::sum('total_amount'),
            'recent' => ProgressPayment::with(['project', 'subcontractor'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Puantaj özeti widget'ı
     */
    protected function getTimesheetsSummaryWidget(array $params): array
    {
        $today = now()->format('Y-m-d');
        $currentMonth = now()->month;

        $totalToday = Timesheet::whereDate('work_date', $today)->count();
        $totalHoursToday = Timesheet::whereDate('work_date', $today)->sum('total_hours');
        $totalMonth = Timesheet::whereMonth('work_date', $currentMonth)->count();
        $totalHoursMonth = Timesheet::whereMonth('work_date', $currentMonth)->sum('total_hours');

        return [
            'total_today' => $totalToday,
            'total_hours_today' => $totalHoursToday,
            'total_month' => $totalMonth,
            'total_hours_month' => $totalHoursMonth,
        ];
    }

    /**
     * İSG widget'ı
     */
    protected function getSafetyIncidentsWidget(array $params): array
    {
        return [
            'total' => SafetyIncident::count(),
            'this_month' => SafetyIncident::whereMonth('incident_date', now()->month)->count(),
            'by_severity' => SafetyIncident::select('severity', DB::raw('count(*) as count'))
                ->groupBy('severity')
                ->get(),
            'recent' => SafetyIncident::with(['project'])
                ->orderBy('incident_date', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Ekipman durumu widget'ı
     */
    protected function getEquipmentStatusWidget(array $params): array
    {
        return [
            'total' => Equipment::count(),
            'available' => Equipment::where('status', 'available')->count(),
            'in_use' => Equipment::where('status', 'in_use')->count(),
            'maintenance' => Equipment::where('status', 'maintenance')->count(),
            'maintenance_due' => Equipment::maintenanceDue()->count(),
        ];
    }

    /**
     * KPI genel bakış widget'ı
     */
    protected function getKpiOverviewWidget(array $params): array
    {
        $projectId = $params['project_id'] ?? null;

        return $this->kpiCalculator->getDashboardSummary($projectId);
    }

    /**
     * Son aktiviteler widget'ı
     */
    protected function getRecentActivitiesWidget(array $params): array
    {
        // Activity Log modelinden son aktiviteleri al
        $activities = DB::table('activity_logs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'activities' => $activities,
        ];
    }

    /**
     * Varsayılan layout
     */
    protected function getDefaultLayout(): array
    {
        return [
            [
                'id' => 'projects_summary',
                'x' => 0,
                'y' => 0,
                'w' => 3,
                'h' => 2,
            ],
            [
                'id' => 'financial_summary',
                'x' => 3,
                'y' => 0,
                'w' => 3,
                'h' => 2,
            ],
            [
                'id' => 'kpi_overview',
                'x' => 6,
                'y' => 0,
                'w' => 3,
                'h' => 2,
            ],
            [
                'id' => 'progress_payments',
                'x' => 0,
                'y' => 2,
                'w' => 4,
                'h' => 3,
            ],
            [
                'id' => 'recent_activities',
                'x' => 4,
                'y' => 2,
                'w' => 5,
                'h' => 3,
            ],
        ];
    }

    /**
     * Varsayılan widget'lar
     */
    protected function getDefaultWidgets(): array
    {
        return [
            'projects_summary',
            'financial_summary',
            'kpi_overview',
            'progress_payments',
            'recent_activities',
        ];
    }

    /**
     * Tüm mevcut widget'ları listele
     */
    public function getAvailableWidgets(): array
    {
        return [
            [
                'id' => 'projects_summary',
                'name' => 'Proje Özeti',
                'description' => 'Toplam proje sayısı ve durumları',
                'icon' => 'folder',
            ],
            [
                'id' => 'financial_summary',
                'name' => 'Finansal Özet',
                'description' => 'Gelir, gider ve kar/zarar özeti',
                'icon' => 'currency-dollar',
            ],
            [
                'id' => 'kpi_overview',
                'name' => 'KPI Genel Bakış',
                'description' => 'Tanımlı KPI\'ların durumu',
                'icon' => 'chart-bar',
            ],
            [
                'id' => 'progress_payments',
                'name' => 'Hakediş Takibi',
                'description' => 'Hakediş durumları ve son hakediş kayıtları',
                'icon' => 'document-text',
            ],
            [
                'id' => 'timesheets_summary',
                'name' => 'Puantaj Özeti',
                'description' => 'Günlük ve aylık puantaj özeti',
                'icon' => 'clock',
            ],
            [
                'id' => 'safety_incidents',
                'name' => 'İSG Olayları',
                'description' => 'İş sağlığı ve güvenliği olayları',
                'icon' => 'shield-check',
            ],
            [
                'id' => 'equipment_status',
                'name' => 'Ekipman Durumu',
                'description' => 'Ekipman kullanım ve bakım durumları',
                'icon' => 'cog',
            ],
            [
                'id' => 'recent_activities',
                'name' => 'Son Aktiviteler',
                'description' => 'Sistem aktivite kayıtları',
                'icon' => 'bell',
            ],
        ];
    }
}
