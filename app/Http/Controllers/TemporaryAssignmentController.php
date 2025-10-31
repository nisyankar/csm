<?php

namespace App\Http\Controllers;

use App\Models\TemporaryAssignment;
use App\Models\Employee;
use App\Models\Project;
use App\Services\TemporaryAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class TemporaryAssignmentController extends Controller
{
    protected TemporaryAssignmentService $service;

    public function __construct(TemporaryAssignmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of temporary assignments
     */
    public function index(Request $request)
    {
        $query = TemporaryAssignment::with([
            'employee',
            'fromProject',
            'toProject',
            'requestedBy',
            'approvedBy'
        ]);

        // Filters
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('from_project_id')) {
            $query->where('from_project_id', $request->from_project_id);
        }

        if ($request->filled('to_project_id')) {
            $query->where('to_project_id', $request->to_project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $assignments = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics
        $stats = $this->service->getStatistics();

        return Inertia::render('TemporaryAssignments/Index', [
            'assignments' => $assignments,
            'stats' => $stats,
            'filters' => $request->only(['employee_id', 'from_project_id', 'to_project_id', 'status', 'date_from', 'date_to', 'search']),
            'employees' => Employee::active()->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'employee_code']),
            'projects' => Project::active()->orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }

    /**
     * Show the form for creating a new assignment
     */
    public function create()
    {
        return Inertia::render('TemporaryAssignments/Create', [
            'employees' => Employee::active()
                ->with('currentProject')
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name', 'employee_code', 'current_project_id']),
            'projects' => Project::active()->orderBy('name')->get(['id', 'name', 'project_code']),
            'shifts' => \App\Models\Shift::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'shift_type', 'daily_hours']),
        ]);
    }

    /**
     * Store a newly created assignment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'from_project_id' => 'required|exists:projects,id',
            'to_project_id' => 'required|exists:projects,id|different:from_project_id',
            'preferred_shift_id' => 'required|exists:shifts,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ], [
            'to_project_id.different' => 'Görevlendirilecek proje, ana projeden farklı olmalıdır.',
            'start_date.after_or_equal' => 'Başlangıç tarihi bugünden önce olamaz.',
            'end_date.after' => 'Bitiş tarihi başlangıç tarihinden sonra olmalıdır.',
            'preferred_shift_id.required' => 'Vardiya seçimi zorunludur.',
        ]);

        try {
            $validated['requested_by'] = auth()->id();
            $validated['status'] = 'pending';

            $assignment = $this->service->createAssignment($validated);

            return redirect()
                ->route('temporary-assignments.show', $assignment)
                ->with('success', 'Geçici görevlendirme talebi oluşturuldu ve onay bekliyor.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified assignment
     */
    public function show(TemporaryAssignment $temporaryAssignment)
    {
        $temporaryAssignment->load([
            'employee',
            'fromProject',
            'toProject',
            'preferredShift',
            'requestedBy',
            'approvedBy',
            'timesheets.shift'
        ]);

        // Calculate timesheet statistics
        $timesheetStats = [
            'total_hours' => $temporaryAssignment->timesheets->sum('hours_worked'),
            'total_days' => $temporaryAssignment->timesheets->count(),
            'total_amount' => $temporaryAssignment->timesheets->sum('calculated_wage'),
        ];

        return Inertia::render('TemporaryAssignments/Show', [
            'assignment' => $temporaryAssignment,
            'timesheetStats' => $timesheetStats,
            'progressPercentage' => $temporaryAssignment->getProgressPercentage(),
            'remainingDays' => $temporaryAssignment->getRemainingDays(),
        ]);
    }

    /**
     * Show the form for editing the specified assignment
     */
    public function edit(TemporaryAssignment $temporaryAssignment)
    {
        // Only pending or active assignments can be edited
        if (!in_array($temporaryAssignment->status, ['pending', 'active'])) {
            return redirect()
                ->route('temporary-assignments.show', $temporaryAssignment)
                ->with('error', 'Bu görevlendirme düzenlenemez.');
        }

        $temporaryAssignment->load(['employee', 'fromProject', 'toProject', 'preferredShift']);

        return Inertia::render('TemporaryAssignments/Edit', [
            'assignment' => $temporaryAssignment,
            'employees' => Employee::active()
                ->with('currentProject')
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name', 'employee_code', 'current_project_id']),
            'projects' => Project::active()->orderBy('name')->get(['id', 'name', 'project_code']),
            'shifts' => \App\Models\Shift::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'shift_type', 'daily_hours']),
        ]);
    }

    /**
     * Update the specified assignment
     */
    public function update(Request $request, TemporaryAssignment $temporaryAssignment)
    {
        if (!in_array($temporaryAssignment->status, ['pending', 'active'])) {
            return back()->with('error', 'Bu görevlendirme düzenlenemez.');
        }

        $rules = [
            'end_date' => 'required|date|after:start_date',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ];

        // If pending, allow more fields to be edited
        if ($temporaryAssignment->status === 'pending') {
            $rules = array_merge($rules, [
                'employee_id' => 'required|exists:employees,id',
                'from_project_id' => 'required|exists:projects,id',
                'to_project_id' => 'required|exists:projects,id|different:from_project_id',
                'preferred_shift_id' => 'required|exists:shifts,id',
                'start_date' => 'required|date|after_or_equal:today',
            ]);
        }

        $validated = $request->validate($rules);

        try {
            // If active and changing end_date, check for conflicts
            if ($temporaryAssignment->status === 'active' && $validated['end_date'] != $temporaryAssignment->end_date) {
                $newEndDate = Carbon::parse($validated['end_date']);
                if ($newEndDate < $temporaryAssignment->end_date) {
                    return back()->with('error', 'Aktif görevlendirmenin bitiş tarihi kısaltılamaz.');
                }

                $this->service->extendAssignment(
                    $temporaryAssignment,
                    $newEndDate,
                    $request->input('notes', 'Bitiş tarihi güncellendi')
                );
            } else {
                $temporaryAssignment->update($validated);
            }

            return redirect()
                ->route('temporary-assignments.show', $temporaryAssignment)
                ->with('success', 'Görevlendirme güncellendi.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified assignment
     */
    public function destroy(TemporaryAssignment $temporaryAssignment)
    {
        // Only pending assignments can be deleted
        if ($temporaryAssignment->status !== 'pending') {
            return back()->with('error', 'Sadece onay bekleyen görevlendirmeler silinebilir.');
        }

        $temporaryAssignment->delete();

        return redirect()
            ->route('temporary-assignments.index')
            ->with('success', 'Görevlendirme silindi.');
    }

    /**
     * Approve an assignment
     */
    public function approve(TemporaryAssignment $temporaryAssignment)
    {
        try {
            $this->service->approveAssignment($temporaryAssignment, auth()->user());

            return back()->with('success', 'Görevlendirme onaylandı ve aktif hale getirildi.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject an assignment
     */
    public function reject(Request $request, TemporaryAssignment $temporaryAssignment)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->service->rejectAssignment($temporaryAssignment, auth()->user(), $request->reason);

            return back()->with('success', 'Görevlendirme reddedildi.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Complete an assignment
     */
    public function complete(TemporaryAssignment $temporaryAssignment)
    {
        if ($temporaryAssignment->status !== 'active') {
            return back()->with('error', 'Sadece aktif görevlendirmeler tamamlanabilir.');
        }

        $temporaryAssignment->complete();

        return back()->with('success', 'Görevlendirme tamamlandı.');
    }

    /**
     * Get assignments by employee
     */
    public function byEmployee(int $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $assignments = $this->service->getAssignmentHistory($employee);

        return response()->json([
            'employee' => $employee,
            'assignments' => $assignments,
        ]);
    }

    /**
     * Get assignments by project
     */
    public function byProject(Request $request, int $projectId)
    {
        $project = Project::findOrFail($projectId);
        $status = $request->input('status');

        $assignments = $this->service->getProjectAssignments($projectId, $status);

        return response()->json([
            'project' => $project,
            'assignments' => $assignments,
        ]);
    }

    /**
     * Check for conflicts (AJAX endpoint)
     */
    public function checkConflicts(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'exclude_id' => 'nullable|exists:temporary_assignments,id',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $conflicts = $this->service->checkConflicts(
            $employee,
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date)
        );

        // Exclude current assignment if editing
        if ($request->filled('exclude_id')) {
            $conflicts = $conflicts->where('id', '!=', $request->exclude_id);
        }

        return response()->json([
            'hasConflicts' => $conflicts->isNotEmpty(),
            'conflicts' => $conflicts,
        ]);
    }
}
