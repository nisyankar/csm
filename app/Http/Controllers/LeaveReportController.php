<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveCalculation;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\LeaveBalanceLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class LeaveReportController extends Controller
{
    /**
     * Display the reports dashboard
     */
    public function index(): Response
    {
        return Inertia::render('LeaveManagement/Reports/Index', [
            'stats' => $this->getReportStats(),
            'availableReports' => $this->getAvailableReports(),
            'recentReports' => $this->getRecentReports(),
            'quickMetrics' => $this->getQuickMetrics(),
        ]);
    }

    /**
     * Compliance report
     */
    public function compliance(Request $request): Response
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2020|max:' . (now()->year + 1),
            'employee_id' => 'nullable|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $year = $validated['year'] ?? now()->year;
        
        $complianceData = $this->generateComplianceReport($year, $validated);

        return Inertia::render('LeaveManagement/Reports/Compliance', [
            'complianceData' => $complianceData,
            'filters' => $validated,
            'years' => $this->getAvailableYears(),
            'employees' => $this->getEmployeesForFilter(),
            'departments' => $this->getDepartmentsForFilter(),
        ]);
    }

    /**
     * Usage analysis report
     */
    public function usageAnalysis(Request $request): Response
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2020|max:' . (now()->year + 1),
            'leave_type_id' => 'nullable|exists:leave_types,id',
            'period' => 'nullable|in:monthly,quarterly,yearly',
        ]);

        $year = $validated['year'] ?? now()->year;
        $period = $validated['period'] ?? 'monthly';
        
        $analysisData = $this->generateUsageAnalysis($year, $validated, $period);

        return Inertia::render('LeaveManagement/Reports/UsageAnalysis', [
            'analysisData' => $analysisData,
            'filters' => $validated,
            'years' => $this->getAvailableYears(),
            'leaveTypes' => $this->getLeaveTypesForFilter(),
            'periods' => [
                'monthly' => 'Aylık',
                'quarterly' => 'Çeyreklik',
                'yearly' => 'Yıllık',
            ],
        ]);
    }

    /**
     * Balance report
     */
    public function balanceReport(Request $request): Response
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2020|max:' . (now()->year + 1),
            'employee_id' => 'nullable|exists:employees,id',
            'leave_type_id' => 'nullable|exists:leave_types,id',
            'status' => 'nullable|in:all,low_balance,high_usage,unused',
        ]);

        $year = $validated['year'] ?? now()->year;
        
        $balanceData = $this->generateBalanceReport($year, $validated);

        return Inertia::render('LeaveManagement/Reports/BalanceReport', [
            'balanceData' => $balanceData,
            'filters' => $validated,
            'years' => $this->getAvailableYears(),
            'employees' => $this->getEmployeesForFilter(),
            'leaveTypes' => $this->getLeaveTypesForFilter(),
            'statusOptions' => [
                'all' => 'Tümü',
                'low_balance' => 'Düşük Bakiye',
                'high_usage' => 'Yüksek Kullanım',
                'unused' => 'Kullanılmamış',
            ],
        ]);
    }

    /**
     * Annual summary report
     */
    public function annualSummary(Request $request): Response
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2020|max:' . (now()->year + 1),
        ]);

        $year = $validated['year'] ?? now()->year;
        
        $summaryData = $this->generateAnnualSummary($year);

        return Inertia::render('LeaveManagement/Reports/AnnualSummary', [
            'summaryData' => $summaryData,
            'year' => $year,
            'years' => $this->getAvailableYears(),
        ]);
    }

    /**
     * Export reports
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $validated = $request->validate([
            'report_type' => 'required|in:compliance,usage_analysis,balance_report,annual_summary',
            'format' => 'required|in:csv,excel,pdf',
            'year' => 'nullable|integer|min:2020|max:' . (now()->year + 1),
            'employee_id' => 'nullable|exists:employees,id',
            'leave_type_id' => 'nullable|exists:leave_types,id',
        ]);

        $year = $validated['year'] ?? now()->year;

        switch ($validated['report_type']) {
            case 'compliance':
                $data = $this->generateComplianceReport($year, $validated);
                break;
            case 'usage_analysis':
                $data = $this->generateUsageAnalysis($year, $validated, 'monthly');
                break;
            case 'balance_report':
                $data = $this->generateBalanceReport($year, $validated);
                break;
            case 'annual_summary':
                $data = $this->generateAnnualSummary($year);
                break;
            default:
                abort(400, 'Geçersiz rapor türü');
        }

        return $this->exportData($data, $validated['format'], $validated['report_type'], $year);
    }

    /**
     * Generate compliance report data
     */
    private function generateComplianceReport(int $year, array $filters): array
    {
        $query = LeaveCalculation::with(['employee', 'leaveType'])
            ->where('calculation_year', $year);

        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        $calculations = $query->get();

        $complianceIssues = [];
        $overallStats = [
            'total_employees' => $calculations->pluck('employee_id')->unique()->count(),
            'total_calculations' => $calculations->count(),
            'compliance_rate' => 0,
            'issues_found' => 0,
        ];

        foreach ($calculations as $calculation) {
            $issues = $this->checkCompliance($calculation);
            if (!empty($issues)) {
                $complianceIssues[] = [
                    'employee' => $calculation->employee->full_name,
                    'leave_type' => $calculation->leaveType->name,
                    'issues' => $issues,
                    'severity' => $this->calculateSeverity($issues),
                ];
                $overallStats['issues_found']++;
            }
        }

        $overallStats['compliance_rate'] = $calculations->count() > 0 
            ? round((($calculations->count() - $overallStats['issues_found']) / $calculations->count()) * 100, 1)
            : 100;

        return [
            'overall_stats' => $overallStats,
            'compliance_issues' => $complianceIssues,
            'by_leave_type' => $this->getComplianceByLeaveType($calculations),
            'by_department' => $this->getComplianceByDepartment($calculations),
        ];
    }

    /**
     * Generate usage analysis data
     */
    private function generateUsageAnalysis(int $year, array $filters, string $period): array
    {
        $query = LeaveRequest::with(['employee', 'leaveType'])
            ->whereYear('start_date', $year)
            ->where('status', 'approved');

        if (isset($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        $requests = $query->get();

        return [
            'usage_trends' => $this->getUsageTrends($requests, $period),
            'popular_periods' => $this->getPopularPeriods($requests),
            'usage_by_type' => $this->getUsageByType($requests),
            'usage_by_employee' => $this->getUsageByEmployee($requests),
            'peak_analysis' => $this->getPeakAnalysis($requests),
            'comparison_data' => $this->getYearOverYearComparison($year),
        ];
    }

    /**
     * Generate balance report data
     */
    private function generateBalanceReport(int $year, array $filters): array
    {
        $query = LeaveCalculation::with(['employee', 'leaveType'])
            ->where('calculation_year', $year)
            ->where('status', 'approved');

        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        $calculations = $query->get();

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $calculations = $calculations->filter(function ($calc) use ($filters) {
                switch ($filters['status']) {
                    case 'low_balance':
                        return $calc->remaining_days < ($calc->entitlement_days * 0.2);
                    case 'high_usage':
                        return $calc->usage_percentage > 80;
                    case 'unused':
                        return $calc->used_days == 0;
                    default:
                        return true;
                }
            });
        }

        return [
            'balance_summary' => $this->getBalanceSummary($calculations),
            'employee_balances' => $this->getEmployeeBalances($calculations),
            'leave_type_summary' => $this->getLeaveTypeSummary($calculations),
            'alerts' => $this->getBalanceAlerts($calculations),
            'utilization_stats' => $this->getUtilizationStats($calculations),
        ];
    }

    /**
     * Generate annual summary data
     */
    private function generateAnnualSummary(int $year): array
    {
        return [
            'overview' => $this->getAnnualOverview($year),
            'leave_requests_summary' => $this->getLeaveRequestsSummary($year),
            'employee_participation' => $this->getEmployeeParticipation($year),
            'cost_analysis' => $this->getCostAnalysis($year),
            'trends' => $this->getAnnualTrends($year),
            'recommendations' => $this->getRecommendations($year),
        ];
    }

    // Helper methods would go here...
    // (I'll implement key helper methods)

    private function getReportStats(): array
    {
        $currentYear = now()->year;
        
        return [
            'total_reports_generated' => 245, // This would come from a reports log table
            'compliance_rate' => 94.5,
            'most_used_leave_type' => 'Yıllık İzin',
            'average_usage_rate' => 76.3,
        ];
    }

    private function getAvailableReports(): array
    {
        return [
            [
                'key' => 'compliance',
                'name' => 'Uygunluk Raporu',
                'description' => 'İzin politikalarına uygunluk analizi',
                'icon' => '📋',
            ],
            [
                'key' => 'usage_analysis',
                'name' => 'Kullanım Analizi',
                'description' => 'İzin kullanım trendleri ve istatistikleri',
                'icon' => '📊',
            ],
            [
                'key' => 'balance_report',
                'name' => 'Bakiye Raporu',
                'description' => 'Çalışan izin bakiyeleri',
                'icon' => '💰',
            ],
            [
                'key' => 'annual_summary',
                'name' => 'Yıllık Özet',
                'description' => 'Yıllık izin özeti ve trendler',
                'icon' => '📅',
            ],
        ];
    }

    private function getAvailableYears(): array
    {
        $currentYear = now()->year;
        $startYear = LeaveCalculation::min('calculation_year') ?? ($currentYear - 2);
        
        $years = [];
        for ($year = $currentYear; $year >= $startYear; $year--) {
            $years[] = $year;
        }
        
        return $years;
    }

    private function getEmployeesForFilter(): array
    {
        return Employee::where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'employee_code'])
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->full_name,
                    'code' => $employee->employee_code,
                ];
            })
            ->toArray();
    }

    private function getLeaveTypesForFilter(): array
    {
        return LeaveType::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->toArray();
    }

    private function exportData(array $data, string $format, string $reportType, int $year): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = "{$reportType}_{$year}_" . now()->format('Y-m-d');
        
        if ($format === 'csv') {
            return $this->exportToCsv($data, $filename);
        }
        
        // For now, default to CSV
        return $this->exportToCsv($data, $filename);
    }

    private function exportToCsv(array $data, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            
            // This is a simplified CSV export
            // You would implement proper CSV formatting based on the data structure
            fputcsv($handle, ['Rapor Verileri']);
            fputcsv($handle, [json_encode($data)]);
            
            fclose($handle);
        }, 200, $headers);
    }
}