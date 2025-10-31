<?php

namespace App\Http\Controllers;

use App\Models\ProjectSchedule;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = ProjectSchedule::with(['project', 'assignedTo', 'department', 'parentTask'])
            ->latest('start_date');

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('task_name', 'like', "%{$search}%")
                  ->orWhere('task_code', 'like', "%{$search}%")
                  ->orWhere('task_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('task_type')) {
            $query->where('task_type', $request->task_type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $tasks = $query->paginate(20)->withQueryString();

        // Statistics
        $statistics = [
            'total_tasks' => ProjectSchedule::count(),
            'in_progress' => ProjectSchedule::inProgress()->count(),
            'completed' => ProjectSchedule::completed()->count(),
            'delayed' => ProjectSchedule::delayed()->count(),
            'critical' => ProjectSchedule::critical()->count(),
        ];

        return Inertia::render('ProjectSchedules/Index', [
            'tasks' => $tasks,
            'statistics' => $statistics,
            'projects' => Project::select('id', 'name', 'project_code')->get(),
            'users' => Employee::select('id', 'first_name', 'last_name', 'position')
                ->get()
                ->map(fn($emp) => [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'position' => $emp->position ?? '-'
                ]),
            'filters' => $request->only(['search', 'project_id', 'status', 'task_type', 'priority', 'assigned_to']),
        ]);
    }

    public function gantt(Request $request, Project $project)
    {
        $tasks = ProjectSchedule::with(['assignedTo', 'department', 'parentTask', 'subTasks'])
            ->where('project_id', $project->id)
            ->orderBy('start_date')
            ->get();

        // Format tasks for Gantt chart
        $ganttData = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'name' => $task->task_name,
                'start' => $task->start_date->format('Y-m-d'),
                'end' => $task->end_date->format('Y-m-d'),
                'progress' => $task->completion_percentage,
                'dependencies' => $this->formatDependencies($task->predecessors),
                'type' => $task->task_type,
                'status' => $task->status,
                'priority' => $task->priority,
                'color' => $task->color ?? $this->getDefaultColor($task->status),
                'assignee' => $task->assignedTo ? $task->assignedTo->full_name : null,
                'parent' => $task->parent_task_id,
            ];
        });

        // Statistics
        $statistics = [
            'total_tasks' => $tasks->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'delayed' => $tasks->filter(fn($t) => $t->is_delayed)->count(),
            'completion_rate' => $tasks->count() > 0 ? round($tasks->where('status', 'completed')->count() / $tasks->count() * 100, 1) : 0,
        ];

        return Inertia::render('ProjectSchedules/Gantt', [
            'project' => $project,
            'ganttData' => $ganttData,
            'statistics' => $statistics,
        ]);
    }

    public function create(Request $request)
    {
        $projects = Project::select('id', 'name', 'project_code')->get();
        $users = Employee::select('id', 'first_name', 'last_name', 'position')
            ->get()
            ->map(fn($emp) => [
                'id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'position' => $emp->position ?? '-'
            ]);

        $departments = collect();
        if ($request->filled('project_id')) {
            $departments = Department::where('project_id', $request->project_id)
                ->select('id', 'name', 'code')
                ->get();
        }

        $parentTasks = collect();
        if ($request->filled('project_id')) {
            $parentTasks = ProjectSchedule::where('project_id', $request->project_id)
                ->whereNull('parent_task_id')
                ->select('id', 'task_code', 'task_name')
                ->get();
        }

        $predecessorTasks = collect();
        if ($request->filled('project_id')) {
            $predecessorTasks = ProjectSchedule::where('project_id', $request->project_id)
                ->select('id', 'task_code', 'task_name', 'start_date', 'end_date')
                ->get();
        }

        return Inertia::render('ProjectSchedules/Create', [
            'projects' => $projects,
            'users' => $users,
            'departments' => $departments,
            'parentTasks' => $parentTasks,
            'predecessorTasks' => $predecessorTasks,
            'defaults' => [
                'project_id' => $request->project_id,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_name' => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'task_type' => 'required|in:phase,milestone,activity,deliverable,meeting',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:not_started,in_progress,completed,delayed,on_hold,cancelled',
            'priority' => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'parent_task_id' => 'nullable|exists:project_schedules,id',
            'predecessors' => 'nullable|array',
            'predecessors.*.task_id' => 'required|exists:project_schedules,id',
            'predecessors.*.type' => 'required|in:FS,SS,FF,SF',
            'estimated_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ]);

        // Generate task code
        $validated['task_code'] = ProjectSchedule::generateTaskCode();

        // Calculate duration
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $validated['duration'] = $startDate->diffInDays($endDate) + 1;

        $task = ProjectSchedule::create($validated);

        return redirect()->route('project-schedules.index')
            ->with('success', 'Görev başarıyla oluşturuldu.');
    }

    public function show(ProjectSchedule $projectSchedule)
    {
        $projectSchedule->load(['project', 'assignedTo', 'department', 'parentTask', 'subTasks']);

        return Inertia::render('ProjectSchedules/Show', [
            'task' => $projectSchedule,
            'subTasks' => $projectSchedule->subTasks,
            'predecessorTasks' => $projectSchedule->getPredecessorTasks(),
            'successorTasks' => $projectSchedule->getSuccessorTasks(),
        ]);
    }

    public function edit(ProjectSchedule $projectSchedule)
    {
        $projectSchedule->load(['project', 'assignedTo', 'department']);

        $projects = Project::select('id', 'name', 'project_code')->get();
        $users = Employee::select('id', 'first_name', 'last_name', 'position')
            ->get()
            ->map(fn($emp) => [
                'id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'position' => $emp->position ?? '-'
            ]);
        $departments = Department::where('project_id', $projectSchedule->project_id)
            ->select('id', 'name', 'code')
            ->get();

        $parentTasks = ProjectSchedule::where('project_id', $projectSchedule->project_id)
            ->whereNull('parent_task_id')
            ->where('id', '!=', $projectSchedule->id)
            ->select('id', 'task_code', 'task_name')
            ->get();

        $predecessorTasks = ProjectSchedule::where('project_id', $projectSchedule->project_id)
            ->where('id', '!=', $projectSchedule->id)
            ->select('id', 'task_code', 'task_name', 'start_date', 'end_date')
            ->get();

        return Inertia::render('ProjectSchedules/Edit', [
            'task' => $projectSchedule,
            'projects' => $projects,
            'users' => $users,
            'departments' => $departments,
            'parentTasks' => $parentTasks,
            'predecessorTasks' => $predecessorTasks,
        ]);
    }

    public function update(Request $request, ProjectSchedule $projectSchedule)
    {
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'task_type' => 'required|in:phase,milestone,activity,deliverable,meeting',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:not_started,in_progress,completed,delayed,on_hold,cancelled',
            'priority' => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'parent_task_id' => 'nullable|exists:project_schedules,id',
            'predecessors' => 'nullable|array',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ]);

        // Recalculate duration
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $validated['duration'] = $startDate->diffInDays($endDate) + 1;

        $projectSchedule->update($validated);

        return redirect()->route('project-schedules.show', $projectSchedule)
            ->with('success', 'Görev başarıyla güncellendi.');
    }

    public function destroy(ProjectSchedule $projectSchedule)
    {
        $projectSchedule->delete();

        return redirect()->route('project-schedules.index')
            ->with('success', 'Görev başarıyla silindi.');
    }

    public function updateProgress(Request $request, ProjectSchedule $projectSchedule)
    {
        $validated = $request->validate([
            'completion_percentage' => 'required|integer|min:0|max:100',
        ]);

        $projectSchedule->updateProgress($validated['completion_percentage']);

        return back()->with('success', 'İlerleme başarıyla güncellendi.');
    }

    private function formatDependencies($predecessors)
    {
        if (!$predecessors || empty($predecessors)) {
            return '';
        }

        return collect($predecessors)
            ->map(fn($p) => $p['task_id'])
            ->implode(',');
    }

    private function getDefaultColor($status)
    {
        return match($status) {
            'not_started' => '#9CA3AF',
            'in_progress' => '#3B82F6',
            'completed' => '#10B981',
            'delayed' => '#EF4444',
            'on_hold' => '#F59E0B',
            'cancelled' => '#6B7280',
            default => '#3B82F6',
        };
    }
}
