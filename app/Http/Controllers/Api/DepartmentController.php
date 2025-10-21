<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Project;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Department::with(['project', 'supervisor', 'parentDepartment']);

        // Search
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($projectId = $request->query('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($supervisorId = $request->query('supervisor_id')) {
            $query->where('supervisor_id', $supervisorId);
        }

        // Special filters
        if ($filter = $request->query('filter')) {
            switch ($filter) {
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

        // Sorting
        $sortField = $request->query('sort', 'created_at');
        $sortDirection = $request->query('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->query('per_page', 20);
        $departments = $query->paginate($perPage);

        return response()->json(['success' => true, 'data' => $departments]);
    }

    public function store(Request $request): JsonResponse
    {
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
            return response()->json([
                'success' => false,
                'message' => 'Bu proje için bu departman kodu zaten kullanılıyor.',
            ], 422);
        }

        // Validate parent department belongs to same project
        if ($validated['parent_department_id'] ?? null) {
            $parentDepartment = Department::find($validated['parent_department_id']);
            if ($parentDepartment->project_id !== $validated['project_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ana departman aynı projeye ait olmalıdır.',
                ], 422);
            }
        }

        $validated['status'] = 'not_started';
        $validated['spent_amount'] = 0;

        $department = Department::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Departman başarıyla oluşturuldu.',
            'data' => $department->load(['project', 'supervisor', 'parentDepartment']),
        ], 201);
    }

    public function show(Department $department): JsonResponse
    {
        $department->load([
            'project',
            'supervisor',
            'parentDepartment',
            'subDepartments.supervisor',
        ]);

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
        ];

        return response()->json([
            'success' => true,
            'data' => $department,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:10',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'parent_department_id' => 'nullable|exists:departments,id',
            'supervisor_id' => 'nullable|exists:employees,id',
            'type' => 'sometimes|in:structural,mechanical,electrical,finishing,landscaping,safety,quality,logistics,administration,other',
            'budget' => 'nullable|numeric|min:0',
            'spent_amount' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:not_started,in_progress,completed,on_hold,cancelled',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after:planned_start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'estimated_employees' => 'nullable|integer|min:1',
            'location_description' => 'nullable|string|max:255',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'notes' => 'nullable|string',
        ]);

        // Check if code is unique in this project (excluding current department)
        if (isset($validated['code'])) {
            $existingDepartment = Department::where('project_id', $department->project_id)
                ->where('code', $validated['code'])
                ->where('id', '!=', $department->id)
                ->exists();

            if ($existingDepartment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu proje için bu departman kodu zaten kullanılıyor.',
                ], 422);
            }
        }

        // Validate parent department
        if (isset($validated['parent_department_id']) && $validated['parent_department_id']) {
            $parentDepartment = Department::find($validated['parent_department_id']);
            if ($parentDepartment->project_id !== $department->project_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ana departman aynı projeye ait olmalıdır.',
                ], 422);
            }
        }

        $department->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Departman başarıyla güncellendi.',
            'data' => $department->load(['project', 'supervisor', 'parentDepartment']),
        ]);
    }

    public function destroy(Department $department): JsonResponse
    {
        // Check for sub-departments
        if ($department->subDepartments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Bu departmanın alt departmanları bulunduğu için silinemez.',
            ], 422);
        }

        // Check for timesheets
        if ($department->timesheets()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Bu departmanın puantaj kayıtları bulunduğu için silinemez.',
            ], 422);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Departman başarıyla silindi.',
        ]);
    }

    public function byProject(Request $request, Project $project): JsonResponse
    {
        $query = $project->departments()->with(['supervisor', 'parentDepartment']);

        if ($request->boolean('main_only')) {
            $query->whereNull('parent_department_id');
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $departments = $query->orderBy('code')->get();

        return response()->json(['success' => true, 'data' => $departments]);
    }

    public function updateStatus(Request $request, Department $department): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:not_started,in_progress,completed,on_hold,cancelled',
            'status_reason' => 'nullable|string|max:500',
        ]);

        $oldStatus = $department->status;
        $department->update([
            'status' => $validated['status'],
            'notes' => ($department->notes ? $department->notes . "\n\n" : '') .
                      "Durum değişikliği: {$oldStatus} → {$validated['status']} " .
                      "(" . now()->format('d.m.Y H:i') . ")" .
                      ($validated['status_reason'] ? "\nSebep: " . $validated['status_reason'] : "")
        ]);

        // Auto-set dates
        if ($validated['status'] === 'in_progress' && !$department->actual_start_date) {
            $department->update(['actual_start_date' => now()]);
        } elseif ($validated['status'] === 'completed' && !$department->actual_end_date) {
            $department->update(['actual_end_date' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Departman durumu güncellendi.',
            'data' => $department,
        ]);
    }

    public function assignSupervisor(Request $request, Department $department): JsonResponse
    {
        $validated = $request->validate([
            'supervisor_id' => 'required|exists:employees,id',
        ]);

        $department->update(['supervisor_id' => $validated['supervisor_id']]);

        return response()->json([
            'success' => true,
            'message' => 'Sorumlu başarıyla atandı.',
            'data' => $department->load('supervisor'),
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Department::count(),
            'not_started' => Department::where('status', 'not_started')->count(),
            'in_progress' => Department::where('status', 'in_progress')->count(),
            'completed' => Department::where('status', 'completed')->count(),
            'on_hold' => Department::where('status', 'on_hold')->count(),
            'delayed' => Department::where('planned_end_date', '<', now())
                ->where('status', '!=', 'completed')
                ->count(),
            'over_budget' => Department::whereRaw('spent_amount > budget')->count(),
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }
}
