<?php

namespace App\Http\Controllers;

use App\Models\SafetyIncident;
use App\Models\Project;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SafetyIncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SafetyIncident::with(['project', 'employee', 'reportedBy', 'investigatedBy'])
            ->orderBy('incident_date', 'desc');

        // Filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('incident_type')) {
            $query->where('incident_type', $request->incident_type);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('location', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $incidents = $query->paginate(15)->withQueryString();

        return Inertia::render('SafetyIncidents/Index', [
            'incidents' => $incidents,
            'projects' => Project::select('id', 'name')->get(),
            'filters' => $request->only(['project_id', 'incident_type', 'severity', 'status', 'search']) ?: []
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('SafetyIncidents/Create', [
            'projects' => Project::select('id', 'name')->get(),
            'employees' => Employee::select('id', 'first_name', 'last_name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'employee_id' => 'nullable|exists:employees,id',
            'incident_date' => 'required|date',
            'incident_time' => 'nullable',
            'location' => 'required|string|max:255',
            'incident_type' => 'required|in:minor_injury,major_injury,near_miss,property_damage,environmental,fatal',
            'severity' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'immediate_actions' => 'nullable|string',
            'root_cause' => 'nullable|string',
            'corrective_actions' => 'nullable|string',
            'preventive_actions' => 'nullable|string',
            'witnesses' => 'nullable|array',
            'photos' => 'nullable|array',
            'injured_body_parts' => 'nullable|array',
            'investigated_by' => 'nullable|exists:users,id',
            'medical_treatment_required' => 'boolean',
            'work_stopped' => 'boolean',
            'days_lost' => 'integer|min:0',
            'cost_estimate' => 'nullable|numeric|min:0',
            'reported_to_authority' => 'boolean',
            'authority_report_date' => 'nullable|date',
            'authority_reference_number' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $validated['reported_by'] = Auth::id();
            $validated['reported_at'] = now();
            $validated['status'] = 'reported';

            SafetyIncident::create($validated);
        });

        return redirect()->route('safety-incidents.index')
            ->with('success', 'İş kazası kaydı başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SafetyIncident $safetyIncident)
    {
        $safetyIncident->load(['project', 'employee', 'reportedBy', 'investigatedBy']);

        return Inertia::render('SafetyIncidents/Show', [
            'incident' => $safetyIncident
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SafetyIncident $safetyIncident)
    {
        $safetyIncident->load(['project', 'employee']);

        return Inertia::render('SafetyIncidents/Edit', [
            'incident' => $safetyIncident,
            'projects' => Project::select('id', 'name')->get(),
            'employees' => Employee::select('id', 'first_name', 'last_name')->get(),
            'users' => User::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SafetyIncident $safetyIncident)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'employee_id' => 'nullable|exists:employees,id',
            'incident_date' => 'required|date',
            'incident_time' => 'nullable',
            'location' => 'required|string|max:255',
            'incident_type' => 'required|in:minor_injury,major_injury,near_miss,property_damage,environmental,fatal',
            'severity' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'immediate_actions' => 'nullable|string',
            'root_cause' => 'nullable|string',
            'corrective_actions' => 'nullable|string',
            'preventive_actions' => 'nullable|string',
            'witnesses' => 'nullable|array',
            'photos' => 'nullable|array',
            'injured_body_parts' => 'nullable|array',
            'investigated_by' => 'nullable|exists:users,id',
            'investigation_completed_at' => 'nullable|date',
            'status' => 'required|in:reported,investigating,resolved,closed',
            'medical_treatment_required' => 'boolean',
            'work_stopped' => 'boolean',
            'days_lost' => 'integer|min:0',
            'cost_estimate' => 'nullable|numeric|min:0',
            'reported_to_authority' => 'boolean',
            'authority_report_date' => 'nullable|date',
            'authority_reference_number' => 'nullable|string',
        ]);

        DB::transaction(function () use ($safetyIncident, $validated) {
            $safetyIncident->update($validated);
        });

        return redirect()->route('safety-incidents.index')
            ->with('success', 'İş kazası kaydı başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SafetyIncident $safetyIncident)
    {
        $safetyIncident->delete();

        return redirect()->route('safety-incidents.index')
            ->with('success', 'İş kazası kaydı başarıyla silindi.');
    }
}
