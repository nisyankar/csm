<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Project;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments
     */
    public function index(Request $request): Response
    {
        $query = Department::with(['project', 'supervisor', 'parentDepartment']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by supervisor
        if ($request->filled('supervisor_id')) {
            $query->where('supervisor_id', $request->supervisor_id);
        }

        // Special filters
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'main_departments':
                    $query->whereNull('parent_department_id');
                    break;
                case 'sub_departments':
                    $query->whereNotNull('parent_department_id');
                    break;
                case 'no_supervisor':
                    $query->whereNull('supervisor_id');
                    break;
                case 'over_budget':
                    $query->whereRaw('spent_amount > budget');
                    break;
                case 'delayed':
                    $query->where('planned_end_date', '<', now())
                          ->where('status', '!=', 'completed');
                    break;
            }
        }

        // Role-based filtering
        $user = Auth::user();
        if ($user->hasRole('foreman')) {
            // Foreman can only see departments they supervise
            $query->where('supervisor_id', $user->employee_id);
        } elseif ($user->hasRole(['project_manager', 'site_manager'])) {
            // Managers can see departments from their projects
            $managedProjects = Project::where('project_manager_id', $user->employee_id)
                ->orWhere('site_manager_id', $user->employee_id)
                ->pluck('id');
            $query->whereIn('project_id', $managedProjects);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $departments = $query->paginate(20)->withQueryString();

        // Get filter options
        $projects = $this->getUserProjects();
        $supervisors = Employee::whereIn('category', ['foreman', 'engineer', 'manager'])
            ->select('id', 'first_name', 'last_name')
            ->get();

        return Inertia::render('Departments/Index', [
            'departments' => $departments,
            'projects' => $projects,
            'supervisors' => $supervisors,
            'filters' => $request->only([
                'search', 'project_id', 'status', 'type', 'supervisor_id', 'filter'
            ]),
            'stats' => $this->getDepartmentStats(),
        ]);
    }

    /**
     * Show the form for creating a new department
     */
    public function create(Request $request): Response
    {
        $this->authorizeRole(['admin', 'hr', 'project_manager', 'site_manager']);

        $projects = $this->getUserProjects();
        $supervisors = Employee::whereIn('category', ['foreman', 'engineer', 'manager'])
            ->select('id', 'first_name', 'last_name', 'position')
            ->get();

        // Get parent departments if project is selected
        $parentDepartments = collect();
        if ($request->filled('project_id')) {
            $parentDepartments = Department::where('project_id', $request->project_id)
                ->whereNull('parent_department_id')
                ->select('id', 'name', 'code')
                ->get();
        }

        return Inertia::render('Departments/Create', [
            'projects' => $projects,
            'supervisors' => $supervisors,
            'parent_departments' => $parentDepartments,
            'defaults' => [
                'project_id' => $request->project_id,
            ],
        ]);
    }

    /**
     * Store a newly created department
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeRole(['admin', 'hr', 'project_manager', 'site_manager']);

        $validated = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'parent_department_id' => 'nullable|exists:departments,id',
            'supervisor_id' => 'nullable|exists:employees,id',
            'type' => 'required|in:structural,mechanical,electrical,finishing,landscaping,safety,quality,logistics,administration,other',
            'budget' => 'nullable|numeric|min:0',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after:planned_start_date',
            'estimated_employees' => 'nullable|integer|min:1',
            'location_description' => 'nullable|string|max:255',
            'priority' => 'required|in:low,medium,high,critical',
            'notes' => 'nullable|string',
        ]);

        // Check if code is unique in this project
        $existingDepartment = Department::where('project_id', $validated['project_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($existingDepartment) {
            return back()->withErrors(['code' => 'Bu proje için bu departman kodu zaten kullanılıyor.']);
        }

        // Validate parent department belongs to same project
        if ($validated['parent_department_id']) {
            $parentDepartment = Department::find($validated['parent_department_id']);
            if ($parentDepartment->project_id !== $validated['project_id']) {
                return back()->withErrors(['parent_department_id' => 'Ana departman aynı projeye ait olmalıdır.']);
            }
        }

        $validated['status'] = 'not_started';
        $validated['spent_amount'] = 0;

        $department = Department::create($validated);

        return redirect()->route('departments.show', $department)
            ->with('success', 'Departman başarıyla oluşturuldu.');
    }

    /**
     * Display the specified department
     */
    public function show(Department $department): Response
    {
        $department->load([
            'project',
            'supervisor',
            'parentDepartment',
            'subDepartments.supervisor',
            'timesheets' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);

        // Check authorization
        $this->authorizeDepartmentAccess($department);

        // Calculate department statistics
        $stats = [
            'total_timesheets' => $department->timesheets()->count(),
            'active_employees' => $department->getActiveEmployeeCount(),
            'total_hours_worked' => $department->timesheets()
                ->where('approval_status', 'approved')
                ->sum('total_minutes') / 60,
            'this_month_expenses' => $department->timesheets()
                ->where('approval_status', 'approved')
                ->whereMonth('work_date', now()->month)
                ->sum('calculated_wage'),
            'budget_utilization' => $department->budget_usage_percentage,
            'completion_percentage' => $department->completion_percentage,
            'days_elapsed' => $department->actual_duration,
            'estimated_days_remaining' => $department->planned_duration ? 
                max(0, $department->planned_duration - ($department->actual_duration ?? 0)) : null,
        ];

        // Get recent activities
        $recentActivities = $this->getDepartmentActivities($department);

        return Inertia::render('Departments/Show', [
            'department' => $department,
            'stats' => $stats,
            'recent_activities' => $recentActivities,
            'can_edit' => $this->canEditDepartment($department),
        ]);
    }

    /**
     * Show the form for editing the specified department
     */
    public function edit(Department $department): Response
    {
        $this->authorizeDepartmentEdit($department);

        $department->load(['project', 'supervisor', 'parentDepartment']);

        $projects = $this->getUserProjects();
        $supervisors = Employee::whereIn('category', ['foreman', 'engineer', 'manager'])
            ->select('id', 'first_name', 'last_name', 'position')
            ->get();

        $parentDepartments = Department::where('project_id', $department->project_id)
            ->whereNull('parent_department_id')
            ->where('id', '!=', $department->id)
            ->select('id', 'name', 'code')
            ->get();

        return Inertia::render('Departments/Edit', [
            'department' => $department,
            'projects' => $projects,
            'supervisors' => $supervisors,
            'parent_departments' => $parentDepartments,
        ]);
    }

    /**
     * Update the specified department
     */
    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->authorizeDepartmentEdit($department);

        $validated = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_department_id' => 'nullable|exists:departments,id',
            'supervisor_id' => 'nullable|exists:employees,id',
            'type' => 'required|in:structural,mechanical,electrical,finishing,landscaping,safety,quality,logistics,administration,other',
            'budget' => 'nullable|numeric|min:0',
            'spent_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:not_started,in_progress,completed,on_hold,cancelled',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after:planned_start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'estimated_employees' => 'nullable|integer|min:1',
            'location_description' => 'nullable|string|max:255',
            'priority' => 'required|in:low,medium,high,critical',
            'notes' => 'nullable|string',
        ]);

        // Check if code is unique in this project (excluding current department)
        $existingDepartment = Department::where('project_id', $department->project_id)
            ->where('code', $validated['code'])
            ->where('id', '!=', $department->id)
            ->exists();

        if ($existingDepartment) {
            return back()->withErrors(['code' => 'Bu proje için bu departman kodu zaten kullanılıyor.']);
        }

        // Validate parent department
        if ($validated['parent_department_id']) {
            $parentDepartment = Department::find($validated['parent_department_id']);
            if ($parentDepartment->project_id !== $department->project_id) {
                return back()->withErrors(['parent_department_id' => 'Ana departman aynı projeye ait olmalıdır.']);
            }
            
            // Prevent circular reference
            if ($validated['parent_department_id'] === $department->id) {
                return back()->withErrors(['parent_department_id' => 'Departman kendisinin ana departmanı olamaz.']);
            }
        }

        // Auto-set actual dates based on status
        if ($validated['status'] === 'in_progress' && !$department->actual_start_date && !$validated['actual_start_date']) {
            $validated['actual_start_date'] = now();
        }

        if ($validated['status'] === 'completed' && !$department->actual_end_date && !$validated['actual_end_date']) {
            $validated['actual_end_date'] = now();
        }

        $department->update($validated);

        return redirect()->route('departments.show', $department)
            ->with('success', 'Departman bilgileri başarıyla güncellendi.');
    }

    /**
     * Remove the specified department
     */
    public function destroy(Department $department): RedirectResponse
    {
        $this->authorizeDepartmentEdit($department);

        // Check if department has timesheets or sub-departments
        if ($department->timesheets()->exists()) {
            return back()->with('error', 'Bu departmanın puantaj kayıtları bulunduğu için silinemez.');
        }

        if ($department->subDepartments()->exists()) {
            return back()->with('error', 'Bu departmanın alt departmanları bulunduğu için silinemez.');
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Departman başarıyla silindi.');
    }

    /**
     * Update department status
     */
    public function updateStatus(Request $request, Department $department): RedirectResponse
    {
        $this->authorizeDepartmentEdit($department);

        $validated = $request->validate([
            'status' => 'required|in:not_started,in_progress,completed,on_hold,cancelled',
            'status_reason' => 'nullable|string|max:500',
        ]);

        $oldStatus = $department->status;

        // Auto-set actual dates
        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'in_progress' && !$department->actual_start_date) {
            $updates['actual_start_date'] = now();
        }

        if ($validated['status'] === 'completed' && !$department->actual_end_date) {
            $updates['actual_end_date'] = now();
        }

        // Add status change to notes
        if ($validated['status_reason']) {
            $updates['notes'] = ($department->notes ? $department->notes . "\n\n" : '') . 
                              "Durum değişikliği: {$oldStatus} → {$validated['status']} " .
                              "(" . now()->format('d.m.Y H:i') . ")\n" .
                              "Sebep: " . $validated['status_reason'];
        }

        $department->update($updates);

        return back()->with('success', 'Departman durumu güncellendi.');
    }

    /**
     * Assign supervisor to department
     */
    public function assignSupervisor(Request $request, Department $department): RedirectResponse
    {
        $this->authorizeDepartmentEdit($department);

        $validated = $request->validate([
            'supervisor_id' => 'required|exists:employees,id',
            'assignment_reason' => 'nullable|string|max:500',
        ]);

        $supervisor = Employee::find($validated['supervisor_id']);

        // Check if supervisor is suitable
        if (!in_array($supervisor->category, ['foreman', 'engineer', 'manager'])) {
            return back()->with('error', 'Seçilen çalışan supervisor olarak atanamaz.');
        }

        $oldSupervisor = $department->supervisor;

        $department->update(['supervisor_id' => $validated['supervisor_id']]);

        // Add assignment to notes
        $assignmentNote = "Supervisor atama: ";
        if ($oldSupervisor) {
            $assignmentNote .= "{$oldSupervisor->full_name} → {$supervisor->full_name}";
        } else {
            $assignmentNote .= "{$supervisor->full_name} atandı";
        }
        $assignmentNote .= " (" . now()->format('d.m.Y H:i') . ")";

        if ($validated['assignment_reason']) {
            $assignmentNote .= "\nSebep: " . $validated['assignment_reason'];
        }

        $department->update([
            'notes' => ($department->notes ? $department->notes . "\n\n" : '') . $assignmentNote
        ]);

        return back()->with('success', 'Supervisor başarıyla atandı.');
    }

    /**
     * Get departments by project (AJAX)
     */
    public function getByProject(Project $project)
    {
        $this->authorizeRole(['admin', 'hr', 'project_manager', 'site_manager', 'foreman']);

        $departments = $project->departments()
            ->select('id', 'name', 'code', 'type', 'status')
            ->orderBy('name')
            ->get();

        return response()->json($departments);
    }

    /**
     * Helper methods
     */
    private function getDepartmentStats(): array
    {
        $query = Department::query();

        // Apply role-based filtering
        $user = Auth::user();
        if ($user->hasRole('foreman')) {
            $query->where('supervisor_id', $user->employee_id);
        } elseif ($user->hasRole(['project_manager', 'site_manager'])) {
            $managedProjects = Project::where('project_manager_id', $user->employee_id)
                ->orWhere('site_manager_id', $user->employee_id)
                ->pluck('id');
            $query->whereIn('project_id', $managedProjects);
        }

        return [
            'total' => $query->count(),
            'not_started' => $query->where('status', 'not_started')->count(),
            'in_progress' => $query->where('status', 'in_progress')->count(),
            'completed' => $query->where('status', 'completed')->count(),
            'on_hold' => $query->where('status', 'on_hold')->count(),
            'over_budget' => $query->whereRaw('spent_amount > budget')->count(),
            'no_supervisor' => $query->whereNull('supervisor_id')->count(),
            'main_departments' => $query->whereNull('parent_department_id')->count(),
            'sub_departments' => $query->whereNotNull('parent_department_id')->count(),
        ];
    }

    private function getDepartmentActivities(Department $department): array
    {
        $activities = collect();

        // Recent timesheets
        $recentTimesheets = $department->timesheets()
            ->with('employee')
            ->latest()
            ->limit(10)
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

        // Status changes (from notes or custom log)
        if ($department->notes) {
            $activities->push([
                'type' => 'status',
                'message' => 'Departman güncellendi',
                'details' => 'Durum: ' . $department->status_display,
                'timestamp' => $department->updated_at,
                'icon' => 'info',
            ]);
        }

        return $activities->concat($recentTimesheets)
                         ->sortByDesc('timestamp')
                         ->take(15)
                         ->values()
                         ->toArray();
    }

    private function authorizeDepartmentAccess(Department $department): void
    {
        $user = Auth::user();

        if ($user->hasRole(['admin', 'hr'])) {
            return; // Admin and HR can access all departments
        }

        if ($user->hasRole(['project_manager', 'site_manager'])) {
            // Check if user manages this department's project
            $project = $department->project;
            if ($project->project_manager_id === $user->employee_id || 
                $project->site_manager_id === $user->employee_id) {
                return;
            }
        }

        if ($user->hasRole('foreman')) {
            // Check if user supervises this department
            if ($department->supervisor_id === $user->employee_id) {
                return;
            }
        }

        // Check if employee works in this department
        if ($user->employee && $user->employee->current_project_id === $department->project_id) {
            return;
        }

        abort(403, 'Bu departmana erişim yetkiniz bulunmamaktadır.');
    }

    private function authorizeDepartmentEdit(Department $department): void
    {
        $user = Auth::user();

        if ($user->hasRole(['admin', 'hr'])) {
            return;
        }

        if ($user->hasRole(['project_manager', 'site_manager'])) {
            $project = $department->project;
            if ($project->project_manager_id === $user->employee_id || 
                $project->site_manager_id === $user->employee_id) {
                return;
            }
        }

        abort(403, 'Bu departmanı düzenleme yetkiniz bulunmamaktadır.');
    }

    private function canEditDepartment(Department $department): bool
    {
        $user = Auth::user();

        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }

        if ($user->hasRole(['project_manager', 'site_manager'])) {
            $project = $department->project;
            return $project->project_manager_id === $user->employee_id || 
                   $project->site_manager_id === $user->employee_id;
        }

        return false;
    }
}