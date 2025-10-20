<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Timesheet;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use PDF;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard
     */
    public function index(): Response
    {
        $user = Auth::user();
        
        // Get available report types based on user role
        $availableReports = $this->getAvailableReports($user);
        
        // Get recent reports
        $recentReports = $this->getRecentReports($user);
        
        // Get quick stats for dashboard
        $quickStats = $this->getQuickStats($user);
        
        return Inertia::render('Reports/Index', [
            'availableReports' => $availableReports,
            'recentReports' => $recentReports,
            'quickStats' => $quickStats,
        ]);
    }

    /**
     * Employee attendance report
     */
    public function employeeAttendance(Request $request): Response
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'project_id' => 'nullable|exists:projects,id',
            'department_id' => 'nullable|exists:departments,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'nullable|in:table,chart,summary',
        ]);

        $dateFrom = Carbon::parse($validated['date_from']);
        $dateTo = Carbon::parse($validated['date_to']);
        $format = $validated['format'] ?? 'table';

        // Build query
        $query = Timesheet::with(['employee', 'project', 'department'])
            ->whereBetween('date', [$dateFrom, $dateTo]);

        // Apply filters
        if (!empty($validated['employee_id'])) {
            $query->where('employee_id', $validated['employee_id']);
        }

        if (!empty($validated['project_id'])) {
            $query->where('project_id', $validated['project_id']);
        }

        if (!empty($validated['department_id'])) {
            $query->where('department_id', $validated['department_id']);
        }

        // Authorize based on user role
        $user = Auth::user();
        if (!$user->hasRole(['admin', 'project_manager'])) {
            // Site managers can only see their projects
            if ($user->hasRole('site_manager')) {
                $managedProjects = Project::where('site_manager_id', $user->employee->id)->pluck('id');
                $query->whereIn('project_id', $managedProjects);
            }
            // Employees can only see their own records
            elseif ($user->hasRole('employee')) {
                $query->where('employee_id', $user->employee->id);
            }
        }

        $timesheets = $query->orderBy('date', 'desc')->get();

        // Calculate summary data
        $summary = [
            'total_days' => $timesheets->groupBy('employee_id')->count(),
            'total_hours' => $timesheets->sum('total_hours'),
            'total_overtime' => $timesheets->sum('overtime_hours'),
            'average_daily_hours' => $timesheets->avg('total_hours'),
            'employee_count' => $timesheets->groupBy('employee_id')->count(),
        ];

        // Get filter options
        $employees = Employee::select('id', 'first_name', 'last_name', 'employee_code')->get();
        $projects = Project::select('id', 'name', 'project_code')->get();
        $departments = Department::select('id', 'name', 'code')->get();

        return Inertia::render('Reports/EmployeeAttendance', [
            'timesheets' => $timesheets,
            'summary' => $summary,
            'filters' => $validated,
            'format' => $format,
            'employees' => $employees,
            'projects' => $projects,
            'departments' => $departments,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ]);
    }

    /**
     * Project progress report
     */
    public function projectProgress(Request $request): Response
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'status' => 'nullable|in:planning,active,on_hold,completed,cancelled',
            'type' => 'nullable|in:residential,commercial,infrastructure,industrial',
            'format' => 'nullable|in:table,chart,summary',
        ]);

        $format = $validated['format'] ?? 'table';

        // Build query
        $query = Project::with(['projectManager', 'siteManager', 'departments', 'currentEmployees']);

        // Apply filters
        if (!empty($validated['project_id'])) {
            $query->where('id', $validated['project_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        // Authorize based on user role
        $user = Auth::user();
        if (!$user->hasRole(['admin', 'project_manager'])) {
            if ($user->hasRole('site_manager')) {
                $query->where('site_manager_id', $user->employee->id);
            }
        }

        $projects = $query->get();

        // Calculate progress data
        $projectsData = $projects->map(function ($project) {
            $departments = $project->departments;
            $totalDepartments = $departments->count();
            $completedDepartments = $departments->where('status', 'completed')->count();
            $progressPercentage = $totalDepartments > 0 ? round(($completedDepartments / $totalDepartments) * 100, 2) : 0;

            // Calculate budget usage
            $budgetUsage = $project->budget > 0 ? round(($project->spent_amount / $project->budget) * 100, 2) : 0;

            // Calculate timeline progress
            $timelineProgress = 0;
            if ($project->planned_start_date && $project->planned_end_date) {
                $totalDays = Carbon::parse($project->planned_start_date)->diffInDays(Carbon::parse($project->planned_end_date));
                $passedDays = Carbon::parse($project->planned_start_date)->diffInDays(now());
                $timelineProgress = $totalDays > 0 ? round(($passedDays / $totalDays) * 100, 2) : 0;
            }

            return [
                'project' => $project,
                'progress_percentage' => $progressPercentage,
                'budget_usage' => $budgetUsage,
                'timeline_progress' => $timelineProgress,
                'is_on_schedule' => $timelineProgress <= $progressPercentage + 10, // 10% tolerance
                'is_over_budget' => $budgetUsage > 100,
                'total_departments' => $totalDepartments,
                'completed_departments' => $completedDepartments,
                'active_employees' => $project->currentEmployees->count(),
            ];
        });

        // Calculate summary
        $summary = [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'active')->count(),
            'completed_projects' => $projects->where('status', 'completed')->count(),
            'total_budget' => $projects->sum('budget'),
            'total_spent' => $projects->sum('spent_amount'),
            'average_progress' => $projectsData->avg('progress_percentage'),
        ];

        // Get filter options
        $allProjects = Project::select('id', 'name', 'project_code')->get();

        return Inertia::render('Reports/ProjectProgress', [
            'projectsData' => $projectsData,
            'summary' => $summary,
            'filters' => $validated,
            'format' => $format,
            'allProjects' => $allProjects,
        ]);
    }

    /**
     * Employee performance report
     */
    public function employeePerformance(Request $request): Response
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'project_id' => 'nullable|exists:projects,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'metric' => 'nullable|in:attendance,productivity,overtime,punctuality',
        ]);

        $dateFrom = Carbon::parse($validated['date_from']);
        $dateTo = Carbon::parse($validated['date_to']);
        $metric = $validated['metric'] ?? 'attendance';

        // Get employees based on filters
        $employeeQuery = Employee::with(['user', 'timesheets']);

        if (!empty($validated['employee_id'])) {
            $employeeQuery->where('id', $validated['employee_id']);
        }

        // Authorize based on user role
        $user = Auth::user();
        if (!$user->hasRole(['admin', 'project_manager'])) {
            if ($user->hasRole('site_manager')) {
                $managedProjects = Project::where('site_manager_id', $user->employee->id)->pluck('id');
                $employeeQuery->whereHas('projects', function ($q) use ($managedProjects) {
                    $q->whereIn('projects.id', $managedProjects);
                });
            } elseif ($user->hasRole('employee')) {
                $employeeQuery->where('id', $user->employee->id);
            }
        }

        $employees = $employeeQuery->get();

        // Calculate performance metrics
        $performanceData = $employees->map(function ($employee) use ($dateFrom, $dateTo, $validated) {
            $timesheets = $employee->timesheets()
                ->whereBetween('date', [$dateFrom, $dateTo]);

            if (!empty($validated['project_id'])) {
                $timesheets->where('project_id', $validated['project_id']);
            }

            $timesheets = $timesheets->get();

            // Calculate metrics
            $totalDays = $dateFrom->diffInDays($dateTo) + 1;
            $workingDays = $this->getWorkingDays($dateFrom, $dateTo);
            $attendedDays = $timesheets->count();
            $totalHours = $timesheets->sum('total_hours');
            $overtimeHours = $timesheets->sum('overtime_hours');
            $lateArrivals = $timesheets->where('is_late', true)->count();

            // Performance calculations
            $attendanceRate = $workingDays > 0 ? round(($attendedDays / $workingDays) * 100, 2) : 0;
            $averageDailyHours = $attendedDays > 0 ? round($totalHours / $attendedDays, 2) : 0;
            $overtimeRate = $totalHours > 0 ? round(($overtimeHours / $totalHours) * 100, 2) : 0;
            $punctualityRate = $attendedDays > 0 ? round((($attendedDays - $lateArrivals) / $attendedDays) * 100, 2) : 100;

            // Performance score (weighted average)
            $performanceScore = round(
                ($attendanceRate * 0.3) + 
                (min($averageDailyHours / 8, 1) * 100 * 0.25) + 
                ($punctualityRate * 0.25) + 
                (max(0, 100 - $overtimeRate) * 0.2), 2
            );

            return [
                'employee' => $employee,
                'attendance_rate' => $attendanceRate,
                'average_daily_hours' => $averageDailyHours,
                'overtime_rate' => $overtimeRate,
                'punctuality_rate' => $punctualityRate,
                'performance_score' => $performanceScore,
                'total_hours' => $totalHours,
                'overtime_hours' => $overtimeHours,
                'attended_days' => $attendedDays,
                'late_arrivals' => $lateArrivals,
                'working_days' => $workingDays,
            ];
        });

        // Sort by selected metric
        $performanceData = $performanceData->sortByDesc(function ($data) use ($metric) {
            switch ($metric) {
                case 'attendance':
                    return $data['attendance_rate'];
                case 'productivity':
                    return $data['performance_score'];
                case 'overtime':
                    return $data['overtime_hours'];
                case 'punctuality':
                    return $data['punctuality_rate'];
                default:
                    return $data['performance_score'];
            }
        })->values();

        // Calculate summary
        $summary = [
            'average_attendance' => $performanceData->avg('attendance_rate'),
            'average_performance' => $performanceData->avg('performance_score'),
            'total_overtime' => $performanceData->sum('overtime_hours'),
            'average_punctuality' => $performanceData->avg('punctuality_rate'),
            'top_performer' => $performanceData->first(),
        ];

        // Get filter options
        $allEmployees = Employee::select('id', 'first_name', 'last_name', 'employee_code')->get();
        $projects = Project::select('id', 'name', 'project_code')->get();

        return Inertia::render('Reports/EmployeePerformance', [
            'performanceData' => $performanceData,
            'summary' => $summary,
            'filters' => $validated,
            'metric' => $metric,
            'employees' => $allEmployees,
            'projects' => $projects,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ]);
    }

    /**
     * Leave report
     */
    public function leaveReport(Request $request): Response
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'leave_type' => 'nullable|in:annual,sick,maternity,paternity,emergency,personal,religious,military,unpaid',
            'status' => 'nullable|in:draft,pending,approved,rejected,cancelled',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $dateFrom = Carbon::parse($validated['date_from']);
        $dateTo = Carbon::parse($validated['date_to']);

        // Build query
        $query = LeaveRequest::with(['employee', 'approver', 'substitute'])
            ->where(function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('start_date', [$dateFrom, $dateTo])
                  ->orWhereBetween('end_date', [$dateFrom, $dateTo])
                  ->orWhere(function ($q2) use ($dateFrom, $dateTo) {
                      $q2->where('start_date', '<=', $dateFrom)
                         ->where('end_date', '>=', $dateTo);
                  });
            });

        // Apply filters
        if (!empty($validated['employee_id'])) {
            $query->where('employee_id', $validated['employee_id']);
        }

        if (!empty($validated['leave_type'])) {
            $query->where('leave_type', $validated['leave_type']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // Authorize based on user role
        $user = Auth::user();
        if (!$user->hasRole(['admin', 'project_manager'])) {
            if ($user->hasRole('site_manager')) {
                $managedEmployees = Employee::whereHas('projects', function ($q) use ($user) {
                    $q->where('site_manager_id', $user->employee->id);
                })->pluck('id');
                $query->whereIn('employee_id', $managedEmployees);
            } elseif ($user->hasRole('employee')) {
                $query->where('employee_id', $user->employee->id);
            }
        }

        $leaveRequests = $query->orderBy('start_date', 'desc')->get();

        // Calculate summary
        $summary = [
            'total_requests' => $leaveRequests->count(),
            'approved_requests' => $leaveRequests->where('status', 'approved')->count(),
            'pending_requests' => $leaveRequests->where('status', 'pending')->count(),
            'total_days' => $leaveRequests->sum('working_days'),
            'by_type' => $leaveRequests->groupBy('leave_type')->map->count(),
            'by_status' => $leaveRequests->groupBy('status')->map->count(),
        ];

        // Get filter options
        $employees = Employee::select('id', 'first_name', 'last_name', 'employee_code')->get();

        return Inertia::render('Reports/LeaveReport', [
            'leaveRequests' => $leaveRequests,
            'summary' => $summary,
            'filters' => $validated,
            'employees' => $employees,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ]);
    }

    /**
     * Financial summary report
     */
    public function financialSummary(Request $request): Response
    {
        $this->authorize('viewFinancialReports', User::class);

        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $dateFrom = Carbon::parse($validated['date_from']);
        $dateTo = Carbon::parse($validated['date_to']);

        // Get projects
        $projectQuery = Project::with(['departments']);

        if (!empty($validated['project_id'])) {
            $projectQuery->where('id', $validated['project_id']);
        }

        $projects = $projectQuery->get();

        // Calculate financial data
        $financialData = $projects->map(function ($project) use ($dateFrom, $dateTo) {
            // Get timesheets for the period
            $timesheets = Timesheet::where('project_id', $project->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->with('employee')
                ->get();

            // Calculate labor costs
            $laborCost = $timesheets->sum(function ($timesheet) {
                $regularCost = $timesheet->regular_hours * $timesheet->employee->hourly_rate;
                $overtimeCost = $timesheet->overtime_hours * ($timesheet->employee->hourly_rate * 1.5);
                return $regularCost + $overtimeCost;
            });

            // Department budgets
            $departmentBudgets = $project->departments->sum('budget');
            $departmentSpent = $project->departments->sum('spent_amount');

            return [
                'project' => $project,
                'labor_cost' => $laborCost,
                'department_budgets' => $departmentBudgets,
                'department_spent' => $departmentSpent,
                'total_budget_usage' => $project->budget > 0 ? round((($project->spent_amount + $laborCost) / $project->budget) * 100, 2) : 0,
                'remaining_budget' => $project->budget - $project->spent_amount - $laborCost,
                'total_hours' => $timesheets->sum('total_hours'),
                'total_overtime' => $timesheets->sum('overtime_hours'),
            ];
        });

        // Calculate overall summary
        $summary = [
            'total_budget' => $financialData->sum('project.budget'),
            'total_spent' => $financialData->sum('department_spent'),
            'total_labor_cost' => $financialData->sum('labor_cost'),
            'total_remaining' => $financialData->sum('remaining_budget'),
            'overall_usage' => $financialData->sum('project.budget') > 0 ? 
                round((($financialData->sum('department_spent') + $financialData->sum('labor_cost')) / $financialData->sum('project.budget')) * 100, 2) : 0,
        ];

        // Get filter options
        $allProjects = Project::select('id', 'name', 'project_code')->get();

        return Inertia::render('Reports/FinancialSummary', [
            'financialData' => $financialData,
            'summary' => $summary,
            'filters' => $validated,
            'projects' => $allProjects,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ]);
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request): HttpResponse
    {
        $validated = $request->validate([
            'report_type' => 'required|in:attendance,project_progress,employee_performance,leave,financial',
            'params' => 'required|array',
        ]);

        // Generate report data based on type
        $reportData = $this->generateReportData($validated['report_type'], $validated['params']);

        // Generate PDF
        $pdf = PDF::loadView("reports.pdf.{$validated['report_type']}", $reportData);

        $filename = $validated['report_type'] . '_report_' . now()->format('Y_m_d_H_i_s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export report to Excel
     */
    public function exportExcel(Request $request): HttpResponse
    {
        $validated = $request->validate([
            'report_type' => 'required|in:attendance,project_progress,employee_performance,leave,financial',
            'params' => 'required|array',
        ]);

        // This would typically use Laravel Excel or similar
        // For now, we'll return a CSV response
        $reportData = $this->generateReportData($validated['report_type'], $validated['params']);

        $filename = $validated['report_type'] . '_report_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($reportData, $validated) {
            $file = fopen('php://output', 'w');
            
            // Write CSV headers and data based on report type
            $this->writeCsvData($file, $validated['report_type'], $reportData);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Private helper methods

    private function getAvailableReports(User $user): array
    {
        $reports = [
            'attendance' => [
                'name' => 'Puantaj Raporu',
                'description' => 'Çalışan devam durumu ve çalışma saatleri',
                'icon' => 'clock',
                'roles' => ['admin', 'project_manager', 'site_manager'],
            ],
            'project_progress' => [
                'name' => 'Proje İlerleme Raporu',
                'description' => 'Proje durumu ve ilerleme yüzdesi',
                'icon' => 'trending-up',
                'roles' => ['admin', 'project_manager', 'site_manager'],
            ],
            'employee_performance' => [
                'name' => 'Çalışan Performans Raporu',
                'description' => 'Çalışan verimlilik ve performans analizi',
                'icon' => 'users',
                'roles' => ['admin', 'project_manager'],
            ],
            'leave' => [
                'name' => 'İzin Raporu',
                'description' => 'İzin talepleri ve kullanım durumu',
                'icon' => 'calendar',
                'roles' => ['admin', 'project_manager', 'site_manager'],
            ],
            'financial' => [
                'name' => 'Mali Özet Raporu',
                'description' => 'Proje bütçesi ve maliyet analizi',
                'icon' => 'dollar-sign',
                'roles' => ['admin', 'project_manager'],
            ],
        ];

        // Filter by user roles
        return collect($reports)->filter(function ($report) use ($user) {
            return $user->hasAnyRole($report['roles']);
        })->toArray();
    }

    private function getRecentReports(User $user): array
    {
        // This would typically come from a reports_log table
        // For now, return empty array
        return [];
    }

    private function getQuickStats(User $user): array
    {
        $stats = [];

        if ($user->hasAnyRole(['admin', 'project_manager'])) {
            $stats['total_employees'] = Employee::count();
            $stats['active_projects'] = Project::where('status', 'active')->count();
            $stats['pending_leaves'] = LeaveRequest::where('status', 'pending')->count();
            $stats['today_attendance'] = Timesheet::where('date', now()->toDateString())->count();
        }

        return $stats;
    }

    private function getWorkingDays(Carbon $from, Carbon $to): int
    {
        $workingDays = 0;
        $current = $from->copy();
        
        while ($current <= $to) {
            if ($current->isWeekday()) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        return $workingDays;
    }

    private function generateReportData(string $reportType, array $params): array
    {
        // This would generate the appropriate report data
        // Implementation depends on the specific report type
        return [];
    }

    private function writeCsvData($file, string $reportType, array $data): void
    {
        // This would write CSV data based on report type
        // Implementation depends on the specific report structure
    }
}