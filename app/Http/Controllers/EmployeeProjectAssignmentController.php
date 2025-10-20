<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeProjectAssignment;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeProjectAssignmentController extends Controller
{
    /**
     * Display a listing of employee project assignments.
     */
    public function index(Request $request): Response
    {
        $query = EmployeeProjectAssignment::with(['employee', 'project'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_primary')) {
            $query->where('is_primary', $request->is_primary);
        }

        // Quick filters
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'active':
                    $query->where('status', 'active');
                    break;
                case 'primary':
                    $query->where('is_primary', true)->where('status', 'active');
                    break;
                case 'expiring_soon':
                    $query->where('status', 'active')
                        ->whereNotNull('end_date')
                        ->where('end_date', '<=', now()->addDays(30))
                        ->where('end_date', '>', now());
                    break;
                case 'without_end_date':
                    $query->where('status', 'active')
                        ->whereNull('end_date');
                    break;
            }
        }

        $assignments = $query->paginate(20)->withQueryString();

        // Calculate statistics
        $stats = [
            'total_assignments' => EmployeeProjectAssignment::count(),
            'active_assignments' => EmployeeProjectAssignment::where('status', 'active')->count(),
            'active_projects' => EmployeeProjectAssignment::where('status', 'active')
                ->distinct('project_id')
                ->count('project_id'),
            'expiring_soon' => EmployeeProjectAssignment::where('status', 'active')
                ->whereNotNull('end_date')
                ->where('end_date', '<=', now()->addDays(30))
                ->where('end_date', '>', now())
                ->count(),
        ];

        return Inertia::render('EmployeeAssignments/Index', [
            'assignments' => $assignments,
            'employees' => Employee::active()
                ->select('id', 'first_name', 'last_name', 'employee_code')
                ->orderBy('first_name')
                ->get(),
            'projects' => Project::active()
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
            'filters' => $request->only(['employee_id', 'project_id', 'status', 'is_primary', 'filter']),
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create(): Response
    {
        return Inertia::render('EmployeeAssignments/Create', [
            'employees' => Employee::active()
                ->select('id', 'first_name', 'last_name', 'employee_code')
                ->orderBy('first_name')
                ->get(),
            'projects' => Project::active()
                ->select('id', 'name', 'status')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store a newly created assignment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'project_id' => 'required|exists:projects,id',
            'is_primary' => 'required|boolean',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'role_in_project' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // If this is a primary project, unset other primary projects for this employee
            if ($validated['is_primary']) {
                EmployeeProjectAssignment::where('employee_id', $validated['employee_id'])
                    ->where('is_primary', true)
                    ->where('status', 'active')
                    ->update(['is_primary' => false]);
            }

            $assignment = EmployeeProjectAssignment::create(array_merge($validated, [
                'status' => 'active',
            ]));

            DB::commit();

            return redirect()->route('employee-assignments.index')
                ->with('success', 'Personel proje ataması başarıyla oluşturuldu.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Atama oluşturulurken bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified assignment.
     */
    public function show(EmployeeProjectAssignment $employeeAssignment): Response
    {
        $employeeAssignment->load(['employee', 'project']);

        return Inertia::render('EmployeeAssignments/Show', [
            'assignment' => $employeeAssignment,
        ]);
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit(EmployeeProjectAssignment $employeeAssignment): Response
    {
        $employeeAssignment->load(['employee', 'project']);

        return Inertia::render('EmployeeAssignments/Edit', [
            'assignment' => $employeeAssignment,
            'employees' => Employee::active()
                ->select('id', 'first_name', 'last_name', 'employee_code')
                ->orderBy('first_name')
                ->get(),
            'projects' => Project::active()
                ->select('id', 'name', 'status')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Update the specified assignment.
     */
    public function update(Request $request, EmployeeProjectAssignment $employeeAssignment): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'project_id' => 'required|exists:projects,id',
            'is_primary' => 'required|boolean',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'role_in_project' => 'nullable|string|max:255',
            'status' => 'required|in:active,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // If this is a primary project, unset other primary projects for this employee
            if ($validated['is_primary'] && $validated['employee_id'] == $employeeAssignment->employee_id) {
                EmployeeProjectAssignment::where('employee_id', $validated['employee_id'])
                    ->where('id', '!=', $employeeAssignment->id)
                    ->where('is_primary', true)
                    ->where('status', 'active')
                    ->update(['is_primary' => false]);
            }

            $employeeAssignment->update($validated);

            DB::commit();

            return redirect()->route('employee-assignments.index')
                ->with('success', 'Personel proje ataması başarıyla güncellendi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Atama güncellenirken bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark assignment as completed.
     */
    public function complete(EmployeeProjectAssignment $employeeAssignment): RedirectResponse
    {
        if ($employeeAssignment->status !== 'active') {
            return back()->withErrors(['error' => 'Sadece aktif atamalar tamamlanabilir.']);
        }

        $employeeAssignment->update([
            'status' => 'completed',
            'end_date' => $employeeAssignment->end_date ?? now()->toDateString(),
        ]);

        return back()->with('success', 'Atama başarıyla tamamlandı.');
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy(EmployeeProjectAssignment $employeeAssignment): RedirectResponse
    {
        try {
            $employeeAssignment->delete();

            return redirect()->route('employee-assignments.index')
                ->with('success', 'Personel proje ataması başarıyla silindi.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Atama silinirken bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * Get employee's active assignments
     */
    public function employeeAssignments(Employee $employee): Response
    {
        $assignments = $employee->projectAssignments()
            ->with('project')
            ->where('status', 'active')
            ->orderBy('is_primary', 'desc')
            ->orderBy('start_date', 'desc')
            ->get();

        return Inertia::render('EmployeeAssignments/EmployeeView', [
            'employee' => $employee,
            'assignments' => $assignments,
        ]);
    }
}
