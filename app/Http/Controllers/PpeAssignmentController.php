<?php

namespace App\Http\Controllers;

use App\Models\PpeAssignment;
use App\Models\Project;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PpeAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = PpeAssignment::with(['project', 'employee', 'assignedBy', 'returnedTo'])
            ->orderBy('assigned_date', 'desc');

        // Filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('ppe_type')) {
            $query->where('ppe_type', $request->ppe_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('serial_number', 'like', "%{$request->search}%")
                  ->orWhere('brand', 'like', "%{$request->search}%");
            });
        }

        $assignments = $query->paginate(15)->withQueryString();

        return Inertia::render('PpeAssignments/Index', [
            'assignments' => $assignments,
            'projects' => Project::select('id', 'name')->get(),
            'employees' => Employee::select('id', 'first_name', 'last_name')->get(),
            'filters' => $request->only(['project_id', 'employee_id', 'ppe_type', 'status', 'search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('PpeAssignments/Create', [
            'projects' => Project::select('id', 'name')->get(),
            'employees' => Employee::select('id', 'first_name', 'last_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'employee_id' => 'required|exists:employees,id',
            'ppe_type' => 'required|in:helmet,safety_boots,gloves,goggles,vest,harness,respirator,ear_protection,face_shield,coverall,knee_pads,dust_mask,welding_mask,fall_arrest,other',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:50',
            'serial_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'assigned_date' => 'required|date',
            'quantity' => 'integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'inspection_required' => 'boolean',
            'last_inspection_date' => 'nullable|date',
            'next_inspection_date' => 'nullable|date',
            'inspection_notes' => 'nullable|string',
            'certificate_number' => 'nullable|string',
            'certificate_expiry' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $validated['assigned_by'] = Auth::id();
            $validated['status'] = 'assigned';

            PpeAssignment::create($validated);
        });

        return redirect()->route('ppe-assignments.index')
            ->with('success', 'KKD ataması başarıyla oluşturuldu.');
    }

    public function show(PpeAssignment $ppeAssignment)
    {
        $ppeAssignment->load(['project', 'employee', 'assignedBy', 'returnedTo']);

        return Inertia::render('PpeAssignments/Show', [
            'assignment' => $ppeAssignment
        ]);
    }

    public function edit(PpeAssignment $ppeAssignment)
    {
        $ppeAssignment->load(['project', 'employee']);

        return Inertia::render('PpeAssignments/Edit', [
            'assignment' => $ppeAssignment,
            'projects' => Project::select('id', 'name')->get(),
            'employees' => Employee::select('id', 'first_name', 'last_name')->get(),
            'users' => User::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, PpeAssignment $ppeAssignment)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'employee_id' => 'required|exists:employees,id',
            'ppe_type' => 'required|in:helmet,safety_boots,gloves,goggles,vest,harness,respirator,ear_protection,face_shield,coverall,knee_pads,dust_mask,welding_mask,fall_arrest,other',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:50',
            'serial_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'assigned_date' => 'required|date',
            'return_date' => 'nullable|date',
            'returned_to' => 'nullable|exists:users,id',
            'return_condition' => 'nullable|in:good,fair,poor,damaged,lost',
            'status' => 'required|in:assigned,returned,lost,damaged,expired,replaced',
            'quantity' => 'integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'inspection_required' => 'boolean',
            'last_inspection_date' => 'nullable|date',
            'next_inspection_date' => 'nullable|date',
            'inspection_notes' => 'nullable|string',
            'certificate_number' => 'nullable|string',
            'certificate_expiry' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($ppeAssignment, $validated) {
            $ppeAssignment->update($validated);
        });

        return redirect()->route('ppe-assignments.index')
            ->with('success', 'KKD ataması başarıyla güncellendi.');
    }

    public function destroy(PpeAssignment $ppeAssignment)
    {
        $ppeAssignment->delete();

        return redirect()->route('ppe-assignments.index')
            ->with('success', 'KKD ataması başarıyla silindi.');
    }

    public function returnPpe(PpeAssignment $ppeAssignment, Request $request)
    {
        $validated = $request->validate([
            'return_date' => 'required|date',
            'return_condition' => 'required|in:good,fair,poor,damaged,lost',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($ppeAssignment, $validated) {
            $ppeAssignment->update([
                'return_date' => $validated['return_date'],
                'return_condition' => $validated['return_condition'],
                'returned_to' => Auth::id(),
                'status' => 'returned',
                'notes' => $validated['notes'] ?? $ppeAssignment->notes,
            ]);
        });

        return redirect()->back()->with('success', 'KKD iadesi başarıyla kaydedildi.');
    }
}
