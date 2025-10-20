<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects
     */
    public function index(Request $request): Response
    {
        $query = Project::with(['projectManager', 'siteManager', 'departments', 'currentEmployees']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // Filter by project manager
        if ($request->filled('project_manager_id')) {
            $query->where('project_manager_id', $request->project_manager_id);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Special filters
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'delayed':
                    $query->where('planned_end_date', '<', now())
                          ->whereNotIn('status', ['completed', 'cancelled']);
                    break;
                case 'over_budget':
                    $query->whereRaw('spent_amount > budget');
                    break;
                case 'no_manager':
                    $query->whereNull('project_manager_id');
                    break;
                case 'ending_soon':
                    $query->where('planned_end_date', '<=', now()->addDays(30))
                          ->whereNotIn('status', ['completed', 'cancelled']);
                    break;
            }
        }

        // Role-based filtering
        $user = Auth::user();
        // TEMPORARILY DISABLED - Allow all users to see all projects for testing
        // if ($user->hasRole(['project_manager', 'site_manager'])) {
        //     // Managers can only see their own projects
        //     $query->where(function ($q) use ($user) {
        //         $q->where('project_manager_id', $user->employee_id)
        //           ->orWhere('site_manager_id', $user->employee_id);
        //     });
        // }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $projects = $query->paginate(20)->withQueryString();

        // Get filter options
        $managers = Employee::whereIn('category', ['manager', 'engineer'])
            ->select('id', 'first_name', 'last_name')
            ->get();
        
        $cities = Project::distinct()->pluck('city')->filter()->sort()->values();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'managers' => $managers,
            'cities' => $cities,
            'filters' => $request->only([
                'search', 'status', 'type', 'city', 'project_manager_id', 'priority', 'filter'
            ]),
            'stats' => $this->getProjectStats(),
        ]);
    }

    /**
     * Show the form for creating a new project
     */
    public function create(): Response
    {
        $managers = Employee::whereIn('category', ['manager', 'engineer'])
            ->select('id', 'first_name', 'last_name', 'position')
            ->get();

        return Inertia::render('Projects/Create', [
            'managers' => $managers,
        ]);
    }

    /**
     * Store a newly created project
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'full_address' => 'nullable|string',
            'coordinates' => 'nullable|string',
            'start_date' => 'required|date',
            'planned_end_date' => 'required|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
            'labor_budget' => 'nullable|numeric|min:0',
            'project_manager_id' => 'nullable|exists:employees,id',
            'site_manager_id' => 'nullable|exists:employees,id',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email',
            'type' => 'required|in:residential,commercial,infrastructure,industrial,other',
            'priority' => 'required|in:low,medium,high,critical',
            'client_name' => 'nullable|string|max:255',
            'client_contact' => 'nullable|string|max:255',
            'estimated_employees' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        // Generate project code
        $validated['project_code'] = $this->generateProjectCode($validated['type']);
        $validated['status'] = 'planning';
        $validated['spent_amount'] = 0;

        $project = Project::create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Proje başarıyla oluşturuldu.');
    }

    /**
     * Display the specified project
     */
    public function show(Project $project): Response
    {
        $project->load([
            'projectManager',
            'siteManager',
            'departments.supervisor',
            'currentEmployees',
            'timesheets' => function ($query) {
                $query->latest()->limit(10);
            },
            'subcontractors' => function ($query) {
                $query->with('category')
                    ->orderBy('project_subcontractor.status')
                    ->orderBy('project_subcontractor.assigned_date', 'desc');
            }
        ]);

        // Check authorization
        $this->authorizeViewProject($project);

        // Calculate project statistics
        $stats = [
            'total_employees' => $project->currentEmployees->count(),
            'total_departments' => $project->departments->count(),
            'completed_departments' => $project->departments->where('status', 'completed')->count(),
            'total_hours_worked' => $project->timesheets()
                ->where('approval_status', 'approved')
                ->sum('total_minutes') / 60,
            'this_month_expenses' => $project->timesheets()
                ->where('approval_status', 'approved')
                ->whereMonth('work_date', now()->month)
                ->sum('calculated_wage'),
            'completion_percentage' => $this->calculateProjectCompletion($project),
            'days_remaining' => $project->planned_end_date->diffInDays(now(), false),
        ];

        // Get recent activities
        $recentActivities = $this->getProjectActivities($project);

        // Taşeron atama için onaylanmış aktif taşeronlar
        $availableSubcontractors = \App\Models\Subcontractor::active()
            ->approved()
            ->with('category')
            ->orderBy('company_name')
            ->get();

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'stats' => $stats,
            'recent_activities' => $recentActivities,
            'can_edit' => $this->canEditProject($project),
            'available_subcontractors' => $availableSubcontractors,
        ]);
    }

    /**
     * Show the form for editing the specified project
     */
    public function edit(Project $project): Response
    {
        $this->authorizeEditProject($project);

        $managers = Employee::whereIn('category', ['manager', 'engineer'])
            ->select('id', 'first_name', 'last_name', 'position')
            ->get();

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'managers' => $managers,
        ]);
    }

    /**
     * Update the specified project
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeEditProject($project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'full_address' => 'nullable|string',
            'coordinates' => 'nullable|string',
            'start_date' => 'required|date',
            'planned_end_date' => 'required|date|after:start_date',
            'actual_end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'labor_budget' => 'nullable|numeric|min:0',
            'spent_amount' => 'nullable|numeric|min:0',
            'project_manager_id' => 'nullable|exists:employees,id',
            'site_manager_id' => 'nullable|exists:employees,id',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'type' => 'required|in:residential,commercial,infrastructure,industrial,other',
            'priority' => 'required|in:low,medium,high,critical',
            'client_name' => 'nullable|string|max:255',
            'client_contact' => 'nullable|string|max:255',
            'estimated_employees' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Proje bilgileri başarıyla güncellendi.');
    }

    /**
     * Remove the specified project
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorizeEditProject($project);

        // Check if project has related records
        if ($project->timesheets()->exists() || $project->currentEmployees()->exists()) {
            return back()->with('error', 'Bu projenin puantaj kayıtları veya atanmış çalışanları bulunduğu için silinemez.');
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Proje başarıyla silindi.');
    }

    /**
     * Assign employee to project
     */
    public function assignEmployee(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeEditProject($project);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'role_in_project' => 'required|string|max:255',
            'assignment_type' => 'required|in:full_time,part_time,temporary,consultant',
            'work_percentage' => 'required|integer|min:1|max:100',
            'start_date' => 'required|date',
            'planned_end_date' => 'nullable|date|after:start_date',
            'project_daily_rate' => 'nullable|numeric|min:0',
            'project_hourly_rate' => 'nullable|numeric|min:0',
            'responsibilities' => 'nullable|string',
        ]);

        $employee = Employee::find($validated['employee_id']);

        // Check if already assigned
        if ($project->employees()->where('employee_id', $employee->id)->exists()) {
            return back()->with('error', 'Bu çalışan zaten projeye atanmış.');
        }

        $project->employees()->attach($employee->id, [
            'role_in_project' => $validated['role_in_project'],
            'assignment_type' => $validated['assignment_type'],
            'work_percentage' => $validated['work_percentage'],
            'assigned_date' => now(),
            'start_date' => $validated['start_date'],
            'planned_end_date' => $validated['planned_end_date'],
            'project_daily_rate' => $validated['project_daily_rate'],
            'project_hourly_rate' => $validated['project_hourly_rate'],
            'responsibilities' => $validated['responsibilities'],
            'status' => 'assigned',
            'assigned_by' => Auth::id(),
        ]);

        // Update employee's current project if this is the primary assignment
        if ($request->boolean('set_as_current')) {
            $employee->update(['current_project_id' => $project->id]);
        }

        return back()->with('success', 'Çalışan projeye başarıyla atandı.');
    }

    /**
     * Remove employee from project
     */
    public function removeEmployee(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeEditProject($project);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'end_reason' => 'nullable|string',
        ]);

        $employee = Employee::find($validated['employee_id']);

        $project->employees()->updateExistingPivot($employee->id, [
            'status' => 'completed',
            'end_date' => now(),
            'performance_notes' => $validated['end_reason'],
        ]);

        // Clear current project if this was it
        if ($employee->current_project_id == $project->id) {
            $employee->update(['current_project_id' => null]);
        }

        return back()->with('success', 'Çalışan projeden başarıyla çıkarıldı.');
    }

    /**
     * Create department for project
     */
    public function createDepartment(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeEditProject($project);

        $validated = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:structural,mechanical,electrical,finishing,landscaping,safety,quality,logistics,administration,other',
            'supervisor_id' => 'nullable|exists:employees,id',
            'parent_department_id' => 'nullable|exists:departments,id',
            'budget' => 'nullable|numeric|min:0',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after:planned_start_date',
            'estimated_employees' => 'nullable|integer|min:1',
            'location_description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,critical',
        ]);

        // Check if department code is unique in this project
        if ($project->departments()->where('code', $validated['code'])->exists()) {
            return back()->withErrors(['code' => 'Bu proje için bu departman kodu zaten kullanılıyor.']);
        }

        $validated['project_id'] = $project->id;
        $validated['status'] = 'not_started';
        $validated['spent_amount'] = 0;

        $department = Department::create($validated);

        return back()->with('success', 'Departman başarıyla oluşturuldu.');
    }

    /**
     * Update project status
     */
    public function updateStatus(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeEditProject($project);

        $validated = $request->validate([
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'status_reason' => 'nullable|string|max:500',
        ]);

        $oldStatus = $project->status;
        $project->update([
            'status' => $validated['status'],
            'notes' => ($project->notes ? $project->notes . "\n\n" : '') . 
                      "Durum değişikliği: {$oldStatus} → {$validated['status']} " .
                      "(" . now()->format('d.m.Y H:i') . ")" .
                      ($validated['status_reason'] ? "\nSebep: " . $validated['status_reason'] : "")
        ]);

        // Set actual end date if completed
        if ($validated['status'] === 'completed' && !$project->actual_end_date) {
            $project->update(['actual_end_date' => now()]);
        }

        return back()->with('success', 'Proje durumu güncellendi.');
    }

    /**
     * Get project dashboard with detailed analytics
     */
    public function dashboard(Project $project): Response
    {
        $this->authorizeViewProject($project);

        $project->load([
            'projectManager',
            'siteManager',
            'departments',
            'currentEmployees',
        ]);

        // Detailed statistics
        $stats = [
            'overview' => $this->getProjectOverviewStats($project),
            'financial' => $this->getProjectFinancialStats($project),
            'progress' => $this->getProjectProgressStats($project),
            'workforce' => $this->getProjectWorkforceStats($project),
        ];

        // Charts and analytics
        $charts = [
            'daily_progress' => $this->getDailyProgressChart($project),
            'expense_trend' => $this->getExpenseTrendChart($project),
            'department_status' => $this->getDepartmentStatusChart($project),
            'workforce_distribution' => $this->getWorkforceDistributionChart($project),
        ];

        // Recent activities and alerts
        $activities = $this->getProjectActivities($project, 20);
        $alerts = $this->getProjectAlerts($project);

        return Inertia::render('Projects/Dashboard', [
            'project' => $project,
            'stats' => $stats,
            'charts' => $charts,
            'activities' => $activities,
            'alerts' => $alerts,
        ]);
    }

    /**
     * Generate project report
     */
    public function report(Request $request, Project $project): Response
    {
        $this->authorizeViewProject($project);

        $validated = $request->validate([
            'report_type' => 'required|in:overview,financial,progress,workforce,timeline',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'] ? Carbon::parse($validated['start_date']) : $project->start_date;
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : now();

        $reportData = $this->generateProjectReport($project, $validated['report_type'], $startDate, $endDate);

        return Inertia::render('Projects/Report', [
            'project' => $project,
            'report_data' => $reportData,
            'report_type' => $validated['report_type'],
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Export project data
     */
    public function export(Request $request, Project $project)
    {
        $this->authorizeViewProject($project);

        $validated = $request->validate([
            'export_type' => 'required|in:timesheets,expenses,employees,departments',
            'format' => 'required|in:csv,xlsx',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        return $this->generateProjectExport($project, $validated);
    }

    /**
     * Helper methods
     */
    private function getProjectStats(): array
    {
        return [
            'total' => Project::count(),
            'active' => Project::where('status', 'active')->count(),
            'planning' => Project::where('status', 'planning')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'on_hold' => Project::where('status', 'on_hold')->count(),
            'delayed' => Project::where('planned_end_date', '<', now())
                              ->whereNotIn('status', ['completed', 'cancelled'])
                              ->count(),
            'over_budget' => Project::whereRaw('spent_amount > budget')->count(),
            'total_budget' => Project::sum('budget'),
            'total_spent' => Project::sum('spent_amount'),
        ];
    }

    private function generateProjectCode(string $type): string
    {
        $prefixes = [
            'residential' => 'KON',
            'commercial' => 'TIC',
            'infrastructure' => 'ALT',
            'industrial' => 'END',
            'other' => 'PRJ'
        ];
        
        $prefix = $prefixes[$type] ?? 'PRJ';
        $year = date('Y');
        
        do {
            $number = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
            $code = $prefix . '-' . $year . '-' . $number;
        } while (Project::where('project_code', $code)->exists());
        
        return $code;
    }

    private function calculateProjectCompletion(Project $project): float
    {
        $departments = $project->departments;
        
        if ($departments->isEmpty()) {
            // Calculate based on time if no departments
            if ($project->start_date && $project->planned_end_date) {
                $totalDays = $project->start_date->diffInDays($project->planned_end_date);
                $elapsedDays = $project->start_date->diffInDays(now());
                return min(round(($elapsedDays / $totalDays) * 100, 1), 100);
            }
            return 0;
        }
        
        $totalDepartments = $departments->count();
        $completedDepartments = $departments->where('status', 'completed')->count();
        
        return round(($completedDepartments / $totalDepartments) * 100, 1);
    }

    private function getProjectActivities(Project $project, int $limit = 10): array
    {
        $activities = collect();

        // Recent timesheets
        $recentTimesheets = $project->timesheets()
            ->with('employee')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($timesheet) {
                return [
                    'type' => 'timesheet',
                    'message' => "{$timesheet->employee->full_name} puantaj girdi",
                    'details' => "{$timesheet->total_hours} saat çalıştı",
                    'timestamp' => $timesheet->created_at,
                    'icon' => 'clock',
                ];
            });

        // Department updates
        $departmentUpdates = $project->departments()
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(function ($department) {
                return [
                    'type' => 'department',
                    'message' => "{$department->name} departmanında güncelleme",
                    'details' => "Durum: {$department->status_display}",
                    'timestamp' => $department->updated_at,
                    'icon' => 'building',
                ];
            });

        return $activities->concat($recentTimesheets)
                         ->concat($departmentUpdates)
                         ->sortByDesc('timestamp')
                         ->take($limit)
                         ->values()
                         ->toArray();
    }

    private function getProjectOverviewStats(Project $project): array
    {
        return [
            'duration_days' => $project->start_date->diffInDays($project->planned_end_date),
            'elapsed_days' => $project->start_date->diffInDays(now()),
            'remaining_days' => now()->diffInDays($project->planned_end_date, false),
            'completion_percentage' => $this->calculateProjectCompletion($project),
            'is_delayed' => $project->is_delayed,
            'total_departments' => $project->departments->count(),
            'completed_departments' => $project->departments->where('status', 'completed')->count(),
        ];
    }

    private function getProjectFinancialStats(Project $project): array
    {
        return [
            'total_budget' => $project->budget,
            'labor_budget' => $project->labor_budget,
            'spent_amount' => $project->spent_amount,
            'remaining_budget' => $project->remaining_budget,
            'budget_usage_percentage' => $project->budget_usage_percentage,
            'this_month_expenses' => $project->timesheets()
                ->where('approval_status', 'approved')
                ->whereMonth('work_date', now()->month)
                ->sum('calculated_wage'),
            'labor_cost' => $project->calculateTotalLaborCost(),
        ];
    }

    private function getProjectProgressStats(Project $project): array
    {
        $departments = $project->departments;
        
        return [
            'not_started' => $departments->where('status', 'not_started')->count(),
            'in_progress' => $departments->where('status', 'in_progress')->count(),
            'completed' => $departments->where('status', 'completed')->count(),
            'on_hold' => $departments->where('status', 'on_hold')->count(),
            'cancelled' => $departments->where('status', 'cancelled')->count(),
        ];
    }

    private function getProjectWorkforceStats(Project $project): array
    {
        $employees = $project->currentEmployees;
        
        return [
            'total_employees' => $employees->count(),
            'by_category' => $employees->groupBy('category')
                                     ->map(fn($group) => $group->count())
                                     ->toArray(),
            'active_today' => $project->timesheets()
                                    ->where('work_date', today())
                                    ->where('attendance_type', 'present')
                                    ->distinct('employee_id')
                                    ->count(),
        ];
    }

    private function getDailyProgressChart(Project $project): array
    {
        // Implementation for daily progress chart
        return [];
    }

    private function getExpenseTrendChart(Project $project): array
    {
        $data = [];
        $startDate = max($project->start_date, now()->subDays(30));
        
        for ($date = $startDate->copy(); $date <= now(); $date->addDay()) {
            $dailyExpense = $project->timesheets()
                ->where('work_date', $date->toDateString())
                ->where('approval_status', 'approved')
                ->sum('calculated_wage');
                
            $data[] = [
                'date' => $date->format('M d'),
                'amount' => $dailyExpense,
            ];
        }
        
        return $data;
    }

    private function getDepartmentStatusChart(Project $project): array
    {
        return $project->departments
            ->groupBy('status')
            ->map(fn($group) => $group->count())
            ->map(fn($count, $status) => [
                'status' => ucfirst($status),
                'count' => $count,
            ])
            ->values()
            ->toArray();
    }

    private function getWorkforceDistributionChart(Project $project): array
    {
        return $project->currentEmployees
            ->groupBy('category')
            ->map(fn($group) => $group->count())
            ->map(fn($count, $category) => [
                'category' => ucfirst($category),
                'count' => $count,
            ])
            ->values()
            ->toArray();
    }

    private function getProjectAlerts(Project $project): array
    {
        $alerts = [];

        // Budget alerts
        if ($project->budget_usage_percentage > 90) {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'Proje bütçesi %90\'ını aştı',
                'action' => 'Bütçe kontrolü yap',
            ];
        }

        // Deadline alerts
        if ($project->is_delayed) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Proje planlanan tarihini geçti',
                'action' => 'Zaman planını gözden geçir',
            ];
        }

        // Department alerts
        $delayedDepartments = $project->departments()
            ->where('planned_end_date', '<', now())
            ->where('status', '!=', 'completed')
            ->count();
            
        if ($delayedDepartments > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$delayedDepartments} departman gecikmiş",
                'action' => 'Departman durumlarını kontrol et',
            ];
        }

        return $alerts;
    }

    /**
     * Authorization methods
     */
    private function authorizeViewProject(Project $project): void
    {
        $user = Auth::user();
        
        if ($user->hasRole(['admin', 'hr'])) {
            return;
        }
        
        if ($user->hasRole(['project_manager', 'site_manager'])) {
            if ($project->project_manager_id === $user->employee_id || 
                $project->site_manager_id === $user->employee_id) {
                return;
            }
        }
        
        // Check if user is assigned to this project
        if ($user->employee && $user->employee->current_project_id === $project->id) {
            return;
        }
        
        abort(403, 'Bu projeyi görüntüleme yetkiniz bulunmamaktadır.');
    }

    private function authorizeEditProject(Project $project): void
    {
        $user = Auth::user();
        
        if ($user->hasRole(['admin', 'hr'])) {
            return;
        }
        
        if ($user->hasRole(['project_manager', 'site_manager'])) {
            if ($project->project_manager_id === $user->employee_id || 
                $project->site_manager_id === $user->employee_id) {
                return;
            }
        }
        
        abort(403, 'Bu projeyi düzenleme yetkiniz bulunmamaktadır.');
    }

    private function canEditProject(Project $project): bool
    {
        $user = Auth::user();
        
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }
        
        if ($user->hasRole(['project_manager', 'site_manager'])) {
            return $project->project_manager_id === $user->employee_id || 
                   $project->site_manager_id === $user->employee_id;
        }
        
        return false;
    }

    private function generateProjectReport(Project $project, string $type, Carbon $startDate, Carbon $endDate): array
    {
        // Implementation would depend on report type
        return [
            'type' => $type,
            'period' => [
                'start' => $startDate->format('d.m.Y'),
                'end' => $endDate->format('d.m.Y'),
            ],
            'data' => [], // Report-specific data
        ];
    }

    private function generateProjectExport(Project $project, array $params)
    {
        // Implementation for exporting project data
        return back()->with('info', 'Export özelliği geliştiriliyor.');
    }

    /**
     * Projeye taşeron ata
     */
    public function assignSubcontractor(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'work_type' => 'required|string|max:100',
            'scope_of_work' => 'required|string',
            'contract_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        $pivotData = [
            'work_type' => $validated['work_type'],
            'scope_of_work' => $validated['scope_of_work'],
            'assigned_date' => now(),
            'assigned_by' => auth()->id(),
            'status' => 'active',
            'contract_amount' => $validated['contract_amount'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        $project->subcontractors()->attach($validated['subcontractor_id'], $pivotData);

        return back()->with('success', 'Taşeron projeye başarıyla atandı.');
    }

    /**
     * Projeden taşeron çıkar
     */
    public function removeSubcontractor(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'pivot_id' => 'required|exists:project_subcontractor,id',
        ]);

        \DB::table('project_subcontractor')
            ->where('id', $validated['pivot_id'])
            ->update([
                'status' => 'completed',
                'end_date' => now(),
            ]);

        return back()->with('success', 'Taşeron projeden çıkarıldı.');
    }

    /**
     * Taşeron bilgilerini güncelle
     */
    public function updateSubcontractor(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'pivot_id' => 'required|exists:project_subcontractor,id',
            'work_type' => 'required|string|max:100',
            'scope_of_work' => 'required|string',
            'contract_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,completed,terminated,suspended',
            'notes' => 'nullable|string',
        ]);

        $pivotId = $validated['pivot_id'];
        unset($validated['pivot_id']);

        // Pivot tabloyu direkt güncelle
        \DB::table('project_subcontractor')
            ->where('id', $pivotId)
            ->update($validated);

        return back()->with('success', 'Taşeron bilgileri güncellendi.');
    }
}