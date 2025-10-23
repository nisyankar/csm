<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Timesheet;
use App\Models\LeaveRequest;
use App\Models\Department;
use App\Models\TimesheetApproval;

class DashboardController extends Controller
{
    /**
     * Display main dashboard based on user role
     */
    public function index(): Response
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return $this->adminDashboard();
        }

        if ($user->hasRole('hr')) {
            return $this->hrDashboard();
        }

        if ($user->hasRole(['project_manager', 'site_manager'])) {
            return $this->managerDashboard();
        }

        if ($user->hasRole('foreman')) {
            return $this->foremanDashboard();
        }

        return $this->employeeDashboard();
    }

    /**
     * Admin dashboard with system-wide statistics
     */
    private function adminDashboard(): Response
    {
        $stats = $this->getAdminStats();
        $charts = $this->getAdminChartData();
        $recentActivities = $this->getRecentActivities();
        $alerts = $this->getSystemAlerts();

        return Inertia::render('Dashboard/Admin', [
            'stats' => $stats,
            'charts' => $charts,
            'recent_activities' => $recentActivities,
            'alerts' => $alerts,
        ]);
    }

    /**
     * HR dashboard with employee and leave management focus
     */
    private function hrDashboard(): Response
    {
        $stats = $this->getHRStats();
        $charts = $this->getHRChartData();
        $pendingLeaves = $this->getPendingLeaveRequests();
        $birthdaysThisMonth = $this->getUpcomingBirthdays();
        $newEmployees = $this->getNewEmployees();

        return Inertia::render('Dashboard/HR', [
            'stats' => $stats,
            'charts' => $charts,
            'pending_leaves' => $pendingLeaves,
            'birthdays' => $birthdaysThisMonth,
            'new_employees' => $newEmployees,
        ]);
    }

    /**
     * Manager dashboard with project and team focus
     */
    private function managerDashboard(): Response
    {
        $user = Auth::user();
        $employee = $user->employee;

        $stats = $this->getManagerStats($employee);
        $projects = $this->getManagedProjects($employee);
        $teamTimesheets = $this->getTeamTimesheets($employee);
        $projectProgress = $this->getProjectProgress($employee);

        return Inertia::render('Dashboard/Manager', [
            'stats' => $stats,
            'projects' => $projects,
            'team_timesheets' => $teamTimesheets,
            'project_progress' => $projectProgress,
        ]);
    }

    /**
     * Foreman dashboard with department and crew focus
     */
    private function foremanDashboard(): Response
    {
        $user = Auth::user();
        $employee = $user->employee;

        $stats = $this->getForemanStats($employee);
        $departments = $this->getSupervisedDepartments($employee);
        $crewMembers = $this->getCrewMembers($employee);
        $todayAttendance = $this->getTodayAttendance($employee);

        return Inertia::render('Dashboard/Foreman', [
            'stats' => $stats,
            'departments' => $departments,
            'crew_members' => $crewMembers,
            'today_attendance' => $todayAttendance,
        ]);
    }

    /**
     * Employee dashboard with personal stats
     */
    private function employeeDashboard(): Response
    {
        $user = Auth::user();
        $employee = $user->employee;

        $stats = $this->getEmployeeStats($employee);
        $recentTimesheets = $this->getRecentTimesheets($employee);
        $leaveBalance = $this->getLeaveBalance($employee);
        $currentProject = $employee?->currentProject;

        return Inertia::render('Dashboard/Employee', [
            'stats' => $stats,
            'recent_timesheets' => $recentTimesheets,
            'leave_balance' => $leaveBalance,
            'current_project' => $current_project,
        ]);
    }

    /**
     * Get admin statistics
     */
    private function getAdminStats(): array
    {
        return [
            'total_employees' => Employee::count(),
            'active_employees' => Employee::active()->count(),
            'total_projects' => Project::count(),
            'active_projects' => Project::active()->count(),
            'pending_timesheets' => Timesheet::where('approval_status', 'pending')->count(),
            'pending_leaves' => LeaveRequest::where('status', 'pending')->count(),
            'this_month_expenses' => Timesheet::with('employee')
                ->whereMonth('work_date', now()->month)
                ->where('approval_status', 'approved')
                ->get()
                ->sum('calculated_wage'),
            'departments_count' => Department::count(),
        ];
    }

    /**
     * Get admin chart data
     */
    private function getAdminChartData(): array
    {
        // Monthly expenses for the last 12 months
        $monthlyExpenses = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $amount = Timesheet::with('employee')
                ->whereYear('work_date', $date->year)
                ->whereMonth('work_date', $date->month)
                ->where('approval_status', 'approved')
                ->get()
                ->sum('calculated_wage');

            $monthlyExpenses[] = [
                'month' => $date->format('M Y'),
                'amount' => $amount,
            ];
        }

        // Employee distribution by category
        $employeesByCategory = Employee::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => ucfirst($item->category),
                    'count' => $item->count,
                ];
            });

        // Project status distribution
        $projectsByStatus = Project::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => ucfirst($item->status),
                    'count' => $item->count,
                ];
            });

        return [
            'monthly_expenses' => $monthlyExpenses,
            'employees_by_category' => $employeesByCategory,
            'projects_by_status' => $projectsByStatus,
        ];
    }

    /**
     * Get HR statistics
     */
    private function getHRStats(): array
    {
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        return [
            'total_employees' => Employee::count(),
            'new_this_month' => Employee::where('start_date', '>=', $thisMonth)->count(),
            'terminated_this_month' => Employee::where('end_date', '>=', $thisMonth)->count(),
            'pending_leave_requests' => LeaveRequest::where('status', 'pending')->count(),
            'employees_on_leave_today' => LeaveRequest::where('status', 'approved')
                ->where('start_date', '<=', today())
                ->where('end_date', '>=', today())
                ->count(),
            'avg_monthly_leave_days' => LeaveRequest::where('status', 'approved')
                ->whereMonth('start_date', now()->month)
                ->avg('working_days') ?? 0,
        ];
    }

    /**
     * Get HR chart data
     */
    private function getHRChartData(): array
    {
        // Leave requests by type this year
        $leavesByType = LeaveRequest::whereYear('start_date', now()->year)
            ->select('leave_type', DB::raw('count(*) as count'))
            ->groupBy('leave_type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => ucfirst($item->leave_type),
                    'count' => $item->count,
                ];
            });

        // Employee turnover by month
        $turnoverData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $hired = Employee::whereYear('start_date', $date->year)
                ->whereMonth('start_date', $date->month)
                ->count();
            $terminated = Employee::whereYear('end_date', $date->year)
                ->whereMonth('end_date', $date->month)
                ->count();

            $turnoverData[] = [
                'month' => $date->format('M Y'),
                'hired' => $hired,
                'terminated' => $terminated,
            ];
        }

        return [
            'leaves_by_type' => $leavesByType,
            'turnover_data' => $turnoverData,
        ];
    }

    /**
     * Get manager statistics
     */
    private function getManagerStats(Employee $employee): array
    {
        $managedProjects = $this->getManagedProjectIds($employee);

        return [
            'managed_projects' => count($managedProjects),
            'team_size' => Employee::whereIn('current_project_id', $managedProjects)->count(),
            'pending_approvals' => TimesheetApproval::where('approver_id', $employee->id)
                ->where('status', 'pending')
                ->count(),
            'active_departments' => Department::whereIn('project_id', $managedProjects)
                ->where('status', 'in_progress')
                ->count(),
            'this_week_hours' => Timesheet::whereIn('project_id', $managedProjects)
                ->whereBetween('work_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('hours_worked'),
            'budget_utilized' => Project::whereIn('id', $managedProjects)
                ->sum('spent_amount'),
        ];
    }

    /**
     * Get foreman statistics
     */
    private function getForemanStats(Employee $employee): array
    {
        $supervisedDepts = Department::where('supervisor_id', $employee->id)->pluck('id');

        return [
            'supervised_departments' => $supervisedDepts->count(),
            'crew_size' => Employee::where('manager_id', $employee->id)->count(),
            'today_attendance' => Timesheet::whereIn('department_id', $supervisedDepts)
                ->where('work_date', today())
                ->where('attendance_type', 'present')
                ->count(),
            'this_week_productivity' => $this->calculateProductivity($supervisedDepts),
        ];
    }

    /**
     * Get employee statistics
     */
    private function getEmployeeStats(Employee $employee): array
    {
        if (!$employee) {
            return [];
        }

        $thisMonth = now()->startOfMonth();

        return [
            'remaining_leave_days' => $employee->remaining_leave_days,
            'this_month_hours' => $employee->timesheets()
                ->where('work_date', '>=', $thisMonth)
                ->where('approval_status', 'approved')
                ->sum('hours_worked'),
            'this_month_overtime' => $employee->timesheets()
                ->where('work_date', '>=', $thisMonth)
                ->where('approval_status', 'approved')
                ->sum('overtime_hours'),
            'pending_timesheets' => $employee->timesheets()
                ->where('approval_status', 'pending')
                ->count(),
            'this_month_earnings' => $employee->timesheets()
                ->with('employee')
                ->where('work_date', '>=', $thisMonth)
                ->where('approval_status', 'approved')
                ->get()
                ->sum('calculated_wage'),
            'attendance_rate' => $this->calculateAttendanceRate($employee),
        ];
    }

    /**
     * Get recent activities for admin dashboard
     */
    private function getRecentActivities(): array
    {
        $activities = [];

        // Recent timesheets
        $recentTimesheets = Timesheet::with(['employee', 'project'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($timesheet) {
                return [
                    'type' => 'timesheet',
                    'message' => "{$timesheet->employee->full_name} puantaj girdi",
                    'time' => $timesheet->created_at->diffForHumans(),
                    'status' => $timesheet->approval_status,
                ];
            });

        // Recent leave requests
        $recentLeaves = LeaveRequest::with('employee')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($leave) {
                return [
                    'type' => 'leave',
                    'message' => "{$leave->employee->full_name} izin talep etti",
                    'time' => $leave->created_at->diffForHumans(),
                    'status' => $leave->status,
                ];
            });

        return $recentTimesheets->concat($recentLeaves)
            ->sortByDesc('created_at')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts(): array
    {
        $alerts = [];

        // Projects over budget
        $overBudgetProjects = Project::whereRaw('spent_amount > budget')->count();
        if ($overBudgetProjects > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$overBudgetProjects} proje bütçe aştı",
                'action' => 'Projeleri İncele',
                'url' => '/admin/projects?filter=over_budget',
            ];
        }

        // Pending approvals
        $pendingApprovals = TimesheetApproval::where('status', 'pending')->count();
        if ($pendingApprovals > 10) {
            $alerts[] = [
                'type' => 'info',
                'message' => "{$pendingApprovals} onay bekleyen puantaj var",
                'action' => 'Onayları İncele',
                'url' => '/admin/timesheets/approvals',
            ];
        }

        // Employees without QR codes
        $noQrEmployees = Employee::whereNull('qr_code')->count();
        if ($noQrEmployees > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$noQrEmployees} çalışanın QR kodu eksik",
                'action' => 'QR Kodları Oluştur',
                'url' => '/admin/employees?filter=no_qr',
            ];
        }

        return $alerts;
    }

    /**
     * Get pending leave requests for HR
     */
    private function getPendingLeaveRequests(): array
    {
        return LeaveRequest::with(['employee', 'approver'])
            ->where('status', 'pending')
            ->orderBy('submitted_at', 'asc')
            ->take(10)
            ->get()
            ->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'employee_name' => $leave->employee->full_name,
                    'leave_type' => $leave->leave_type_display,
                    'start_date' => $leave->start_date->format('d.m.Y'),
                    'end_date' => $leave->end_date->format('d.m.Y'),
                    'total_days' => $leave->total_days,
                    'submitted_at' => $leave->submitted_at->diffForHumans(),
                    'priority' => $leave->priority,
                ];
            })
            ->toArray();
    }

    /**
     * Get upcoming birthdays
     */
    private function getUpcomingBirthdays(): array
    {
        return Employee::whereRaw('MONTH(birth_date) = ? AND DAY(birth_date) >= ?', [
            now()->month,
            now()->day
        ])
            ->orWhereRaw('MONTH(birth_date) = ? AND YEAR(birth_date) != ?', [
                now()->addMonth()->month,
                now()->year
            ])
            ->orderByRaw('MONTH(birth_date), DAY(birth_date)')
            ->take(5)
            ->get()
            ->map(function ($employee) {
                $birthday = Carbon::createFromFormat('Y-m-d', now()->year . '-' . $employee->birth_date->format('m-d'));
                if ($birthday->isPast()) {
                    $birthday->addYear();
                }

                return [
                    'name' => $employee->full_name,
                    'birthday' => $birthday->format('d.m.Y'),
                    'days_until' => now()->diffInDays($birthday),
                    'age' => $birthday->year - $employee->birth_date->year,
                ];
            })
            ->toArray();
    }

    /**
     * Get new employees
     */
    private function getNewEmployees(): array
    {
        return Employee::where('start_date', '>=', now()->subDays(30))
            ->orderBy('start_date', 'desc')
            ->take(5)
            ->get()
            ->map(function ($employee) {
                return [
                    'name' => $employee->full_name,
                    'position' => $employee->position,
                    'start_date' => $employee->start_date->format('d.m.Y'),
                    'days_since_start' => $employee->start_date->diffInDays(now()),
                ];
            })
            ->toArray();
    }

    /**
     * Get managed projects for a manager
     */
    private function getManagedProjects(Employee $employee): array
    {
        $projects = Project::where('project_manager_id', $employee->id)
            ->orWhere('site_manager_id', $employee->id)
            ->with(['departments', 'employees'])
            ->get();

        return $projects->map(function ($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'status' => $project->status,
                'completion_percentage' => $project->completion_percentage,
                'budget_usage' => $project->budget_usage_percentage,
                'employee_count' => $project->employees->count(),
                'start_date' => $project->start_date->format('d.m.Y'),
                'planned_end_date' => $project->planned_end_date->format('d.m.Y'),
                'is_delayed' => $project->is_delayed,
            ];
        })->toArray();
    }

    /**
     * Get team timesheets for manager
     */
    private function getTeamTimesheets(Employee $employee): array
    {
        $projectIds = $this->getManagedProjectIds($employee);

        return Timesheet::with(['employee', 'project'])
            ->whereIn('project_id', $projectIds)
            ->where('approval_status', 'pending')
            ->orderBy('work_date', 'desc')
            ->take(10)
            ->get()
            ->map(function ($timesheet) {
                return [
                    'id' => $timesheet->id,
                    'employee_name' => $timesheet->employee->full_name,
                    'project_name' => $timesheet->project->name,
                    'work_date' => $timesheet->work_date->format('d.m.Y'),
                    'total_hours' => $timesheet->total_hours,
                    'overtime_hours' => $timesheet->overtime_hours,
                    'calculated_wage' => $timesheet->calculated_wage,
                ];
            })
            ->toArray();
    }

    /**
     * Get project progress for manager
     */
    private function getProjectProgress(Employee $employee): array
    {
        $projectIds = $this->getManagedProjectIds($employee);

        return Department::whereIn('project_id', $projectIds)
            ->with('project')
            ->get()
            ->groupBy('project_id')
            ->map(function ($departments, $projectId) {
                $project = $departments->first()->project;
                $totalDepartments = $departments->count();
                $completedDepartments = $departments->where('status', 'completed')->count();

                return [
                    'project_name' => $project->name,
                    'total_departments' => $totalDepartments,
                    'completed_departments' => $completedDepartments,
                    'completion_percentage' => $totalDepartments > 0 ?
                        round(($completedDepartments / $totalDepartments) * 100, 1) : 0,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get supervised departments for foreman
     */
    private function getSupervisedDepartments(Employee $employee): array
    {
        return Department::where('supervisor_id', $employee->id)
            ->with('project')
            ->get()
            ->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'project_name' => $department->project->name,
                    'status' => $department->status_display,
                    'completion_percentage' => $department->completion_percentage,
                    'estimated_employees' => $department->estimated_employees,
                    'budget_usage' => $department->budget_usage_percentage,
                ];
            })
            ->toArray();
    }

    /**
     * Get crew members for foreman
     */
    private function getCrewMembers(Employee $employee): array
    {
        return Employee::where('manager_id', $employee->id)
            ->with('currentProject')
            ->get()
            ->map(function ($crewMember) {
                return [
                    'id' => $crewMember->id,
                    'name' => $crewMember->full_name,
                    'position' => $crewMember->position,
                    'category' => $crewMember->category,
                    'project_name' => $crewMember->currentProject?->name,
                    'status' => $crewMember->status,
                ];
            })
            ->toArray();
    }

    /**
     * Get today's attendance for foreman
     */
    private function getTodayAttendance(Employee $employee): array
    {
        $crewIds = Employee::where('manager_id', $employee->id)->pluck('id');

        return Timesheet::with('employee')
            ->whereIn('employee_id', $crewIds)
            ->where('work_date', today())
            ->get()
            ->map(function ($timesheet) {
                return [
                    'employee_name' => $timesheet->employee->full_name,
                    'attendance_type' => $timesheet->attendance_type_display,
                    'start_time' => $timesheet->start_time?->format('H:i'),
                    'status' => $timesheet->attendance_type,
                ];
            })
            ->toArray();
    }

    /**
     * Get recent timesheets for employee
     */
    private function getRecentTimesheets(Employee $employee): array
    {
        if (!$employee) {
            return [];
        }

        return $employee->timesheets()
            ->with(['project', 'department'])
            ->orderBy('work_date', 'desc')
            ->take(10)
            ->get()
            ->map(function ($timesheet) {
                return [
                    'id' => $timesheet->id,
                    'work_date' => $timesheet->work_date->format('d.m.Y'),
                    'project_name' => $timesheet->project?->name,
                    'department_name' => $timesheet->department?->name,
                    'total_hours' => $timesheet->total_hours,
                    'overtime_hours' => $timesheet->overtime_hours,
                    'approval_status' => $timesheet->approval_status_display,
                    'calculated_wage' => $timesheet->calculated_wage,
                ];
            })
            ->toArray();
    }

    /**
     * Get leave balance for employee
     */
    private function getLeaveBalance(Employee $employee): array
    {
        if (!$employee) {
            return [];
        }

        return [
            'annual_leave_days' => $employee->annual_leave_days,
            'used_leave_days' => $employee->used_leave_days,
            'remaining_leave_days' => $employee->remaining_leave_days,
            'pending_requests' => $employee->leaveRequests()
                ->where('status', 'pending')
                ->count(),
            'approved_future_leaves' => $employee->leaveRequests()
                ->where('status', 'approved')
                ->where('start_date', '>', now())
                ->count(),
        ];
    }

    /**
     * Helper: Get managed project IDs
     */
    private function getManagedProjectIds(Employee $employee): array
    {
        return Project::where('project_manager_id', $employee->id)
            ->orWhere('site_manager_id', $employee->id)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Helper: Calculate productivity
     */
    private function calculateProductivity(array $departmentIds): float
    {
        // Simple productivity calculation based on completed tasks vs planned
        // This is a simplified version - in real app you'd have more complex metrics

        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $plannedHours = count($departmentIds) * 40 * 8; // Assuming 40 hours per department per week
        $actualHours = Timesheet::whereIn('department_id', $departmentIds)
            ->whereBetween('work_date', [$weekStart, $weekEnd])
            ->sum('hours_worked');

        return $plannedHours > 0 ? round(($actualHours / $plannedHours) * 100, 1) : 0;
    }

    /**
     * Helper: Calculate attendance rate
     */
    private function calculateAttendanceRate(Employee $employee): float
    {
        $thisMonth = now()->startOfMonth();
        $workDaysThisMonth = now()->diffInDaysFiltered(function (Carbon $date) {
            return $date->isWeekday();
        }, $thisMonth);

        $attendedDays = $employee->timesheets()
            ->where('work_date', '>=', $thisMonth)
            ->where('attendance_type', 'present')
            ->count();

        return $workDaysThisMonth > 0 ? round(($attendedDays / $workDaysThisMonth) * 100, 1) : 0;
    }

    /**
     * Get dashboard data via API (for mobile app)
     */
    public function getDashboardData(): array
    {
        $user = Auth::user();

        return [
            'user' => $user,
            'employee' => $user->employee,
            'stats' => $this->getStatsForUser($user),
            'recent_activities' => $this->getRecentActivitiesForUser($user),
        ];
    }

    /**
     * Get stats based on user role
     */
    private function getStatsForUser($user): array
    {
        if ($user->hasRole('admin')) {
            return $this->getAdminStats();
        }

        if ($user->hasRole('hr')) {
            return $this->getHRStats();
        }

        if ($user->hasRole(['project_manager', 'site_manager'])) {
            return $this->getManagerStats($user->employee);
        }

        if ($user->hasRole('foreman')) {
            return $this->getForemanStats($user->employee);
        }

        return $this->getEmployeeStats($user->employee);
    }

    /**
     * Get recent activities based on user role
     */
    private function getRecentActivitiesForUser($user): array
    {
        if ($user->hasRole(['admin', 'hr'])) {
            return $this->getRecentActivities();
        }

        if ($user->hasRole(['project_manager', 'site_manager'])) {
            return $this->getTeamTimesheets($user->employee);
        }

        if ($user->hasRole('foreman')) {
            return $this->getTodayAttendance($user->employee);
        }

        return $this->getRecentTimesheets($user->employee);
    }
}
