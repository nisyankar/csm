<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Project::with(['projectManager', 'siteManager', 'departments', 'currentEmployees']);

        // Search functionality
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($city = $request->query('city')) {
            $query->where('city', $city);
        }

        if ($projectManagerId = $request->query('project_manager_id')) {
            $query->where('project_manager_id', $projectManagerId);
        }

        if ($priority = $request->query('priority')) {
            $query->where('priority', $priority);
        }

        // Special filters
        if ($filter = $request->query('filter')) {
            switch ($filter) {
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

        // Sorting
        $sortField = $request->query('sort', 'created_at');
        $sortDirection = $request->query('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->query('per_page', 20);
        $projects = $query->paginate($perPage);

        return response()->json(['success' => true, 'data' => $projects]);
    }

    public function store(Request $request): JsonResponse
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

        return response()->json([
            'success' => true,
            'message' => 'Proje başarıyla oluşturuldu.',
            'data' => $project->load(['projectManager', 'siteManager']),
        ], 201);
    }

    public function show(Project $project): JsonResponse
    {
        $project->load([
            'projectManager',
            'siteManager',
            'departments.supervisor',
            'currentEmployees',
            'subcontractors.category',
            'structures.floors',
            'structures.units',
        ]);

        // Calculate statistics
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
            'budget_usage_percentage' => $project->budget_usage_percentage,
            'is_delayed' => $project->is_delayed,
        ];

        return response()->json([
            'success' => true,
            'data' => $project,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'location' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'district' => 'nullable|string|max:100',
            'full_address' => 'nullable|string',
            'coordinates' => 'nullable|string',
            'start_date' => 'sometimes|date',
            'planned_end_date' => 'sometimes|date|after:start_date',
            'actual_end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'labor_budget' => 'nullable|numeric|min:0',
            'spent_amount' => 'nullable|numeric|min:0',
            'project_manager_id' => 'nullable|exists:employees,id',
            'site_manager_id' => 'nullable|exists:employees,id',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email',
            'status' => 'sometimes|in:planning,active,on_hold,completed,cancelled',
            'type' => 'sometimes|in:residential,commercial,infrastructure,industrial,other',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'client_name' => 'nullable|string|max:255',
            'client_contact' => 'nullable|string|max:255',
            'estimated_employees' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $project->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Proje başarıyla güncellendi.',
            'data' => $project->load(['projectManager', 'siteManager']),
        ]);
    }

    public function destroy(Project $project): JsonResponse
    {
        // Check if project has related records
        if ($project->timesheets()->exists() || $project->currentEmployees()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projenin puantaj kayıtları veya atanmış çalışanları bulunduğu için silinemez.',
            ], 422);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proje başarıyla silindi.',
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
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

        return response()->json(['success' => true, 'data' => $stats]);
    }

    public function assignEmployee(Request $request, Project $project): JsonResponse
    {
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
            'set_as_current' => 'nullable|boolean',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        // Check if already assigned
        if ($project->employees()->where('employee_id', $employee->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Bu çalışan zaten projeye atanmış.',
            ], 422);
        }

        $project->employees()->attach($employee->id, [
            'role_in_project' => $validated['role_in_project'],
            'assignment_type' => $validated['assignment_type'],
            'work_percentage' => $validated['work_percentage'],
            'assigned_date' => now(),
            'start_date' => $validated['start_date'],
            'planned_end_date' => $validated['planned_end_date'] ?? null,
            'project_daily_rate' => $validated['project_daily_rate'] ?? null,
            'project_hourly_rate' => $validated['project_hourly_rate'] ?? null,
            'responsibilities' => $validated['responsibilities'] ?? null,
            'status' => 'assigned',
            'assigned_by' => Auth::id(),
        ]);

        // Update employee's current project if requested
        if ($request->boolean('set_as_current')) {
            $employee->update(['current_project_id' => $project->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Çalışan projeye başarıyla atandı.',
        ]);
    }

    public function removeEmployee(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'end_reason' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $project->employees()->updateExistingPivot($employee->id, [
            'status' => 'completed',
            'end_date' => now(),
            'performance_notes' => $validated['end_reason'] ?? null,
        ]);

        // Clear current project if this was it
        if ($employee->current_project_id == $project->id) {
            $employee->update(['current_project_id' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Çalışan projeden başarıyla çıkarıldı.',
        ]);
    }

    public function assignSubcontractor(Request $request, Project $project): JsonResponse
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
            'assigned_by' => Auth::id(),
            'status' => 'active',
            'contract_amount' => $validated['contract_amount'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        $project->subcontractors()->attach($validated['subcontractor_id'], $pivotData);

        return response()->json([
            'success' => true,
            'message' => 'Taşeron projeye başarıyla atandı.',
        ]);
    }

    public function removeSubcontractor(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'subcontractor_id' => 'required|exists:subcontractors,id',
        ]);

        $project->subcontractors()->updateExistingPivot($validated['subcontractor_id'], [
            'status' => 'completed',
            'end_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Taşeron projeden çıkarıldı.',
        ]);
    }

    public function updateStatus(Request $request, Project $project): JsonResponse
    {
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

        return response()->json([
            'success' => true,
            'message' => 'Proje durumu güncellendi.',
            'data' => $project,
        ]);
    }

    public function dashboard(Project $project): JsonResponse
    {
        $project->load([
            'projectManager',
            'siteManager',
            'departments',
            'currentEmployees',
        ]);

        $stats = [
            'overview' => $this->getProjectOverviewStats($project),
            'financial' => $this->getProjectFinancialStats($project),
            'progress' => $this->getProjectProgressStats($project),
            'workforce' => $this->getProjectWorkforceStats($project),
        ];

        $charts = [
            'expense_trend' => $this->getExpenseTrendChart($project),
            'department_status' => $this->getDepartmentStatusChart($project),
            'workforce_distribution' => $this->getWorkforceDistributionChart($project),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'project' => $project,
                'stats' => $stats,
                'charts' => $charts,
            ],
        ]);
    }

    // Helper methods
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

    /**
     * Get project units for dropdowns
     */
    public function units(Project $project): JsonResponse
    {
        $units = $project->structures()
            ->with(['floors.units' => function ($query) {
                $query->select('id', 'floor_id', 'unit_code', 'unit_type', 'gross_area', 'net_area')
                    ->orderBy('unit_code');
            }])
            ->get()
            ->flatMap(function ($structure) {
                return $structure->floors->flatMap(function ($floor) {
                    return $floor->units;
                });
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $units
        ]);
    }
}
