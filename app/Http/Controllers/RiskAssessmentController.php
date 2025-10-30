<?php

namespace App\Http\Controllers;

use App\Models\RiskAssessment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RiskAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $query = RiskAssessment::with(['project', 'assessedBy', 'reviewedBy', 'approvedBy'])
            ->orderBy('assessment_date', 'desc');

        // Filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('overall_risk_level')) {
            $query->where('overall_risk_level', $request->overall_risk_level);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('assessment_title', 'like', "%{$request->search}%")
                  ->orWhere('work_activity', 'like', "%{$request->search}%");
            });
        }

        $assessments = $query->paginate(15)->withQueryString();

        return Inertia::render('RiskAssessments/Index', [
            'assessments' => $assessments,
            'projects' => Project::select('id', 'name')->get(),
            'filters' => $request->only(['project_id', 'overall_risk_level', 'status', 'search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('RiskAssessments/Create', [
            'projects' => Project::select('id', 'name')->get(),
            'users' => User::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'assessment_title' => 'required|string|max:255',
            'work_activity' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'assessment_date' => 'required|date',
            'description' => 'nullable|string',
            'risk_items' => 'nullable|array',
            'overall_risk_level' => 'required|in:low,medium,high,critical',
            'control_measures' => 'nullable|string',
            'emergency_procedures' => 'nullable|string',
            'training_requirements' => 'nullable|string',
            'required_ppe' => 'nullable|array',
            'required_equipment' => 'nullable|array',
            'responsible_persons' => 'nullable|array',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'review_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $validated['assessed_by'] = Auth::id();
            $validated['status'] = 'draft';
            $validated['revision_number'] = 1;

            RiskAssessment::create($validated);
        });

        return redirect()->route('risk-assessments.index')
            ->with('success', 'Risk değerlendirmesi başarıyla oluşturuldu.');
    }

    public function show(RiskAssessment $riskAssessment)
    {
        $riskAssessment->load(['project', 'assessedBy', 'reviewedBy', 'approvedBy', 'previousVersion']);

        return Inertia::render('RiskAssessments/Show', [
            'assessment' => $riskAssessment
        ]);
    }

    public function edit(RiskAssessment $riskAssessment)
    {
        $riskAssessment->load(['project']);

        return Inertia::render('RiskAssessments/Edit', [
            'assessment' => $riskAssessment,
            'projects' => Project::select('id', 'name')->get(),
            'users' => User::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, RiskAssessment $riskAssessment)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'assessment_title' => 'required|string|max:255',
            'work_activity' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'assessment_date' => 'required|date',
            'description' => 'nullable|string',
            'risk_items' => 'nullable|array',
            'overall_risk_level' => 'required|in:low,medium,high,critical',
            'control_measures' => 'nullable|string',
            'emergency_procedures' => 'nullable|string',
            'training_requirements' => 'nullable|string',
            'required_ppe' => 'nullable|array',
            'required_equipment' => 'nullable|array',
            'responsible_persons' => 'nullable|array',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'review_date' => 'nullable|date',
            'status' => 'required|in:draft,submitted,approved,active,expired,archived',
            'reviewed_by' => 'nullable|exists:users,id',
            'reviewed_at' => 'nullable|date',
            'approved_by' => 'nullable|exists:users,id',
            'approved_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($riskAssessment, $validated) {
            $riskAssessment->update($validated);
        });

        return redirect()->route('risk-assessments.index')
            ->with('success', 'Risk değerlendirmesi başarıyla güncellendi.');
    }

    public function destroy(RiskAssessment $riskAssessment)
    {
        $riskAssessment->delete();

        return redirect()->route('risk-assessments.index')
            ->with('success', 'Risk değerlendirmesi başarıyla silindi.');
    }

    public function approve(RiskAssessment $riskAssessment)
    {
        DB::transaction(function () use ($riskAssessment) {
            $riskAssessment->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Risk değerlendirmesi onaylandı.');
    }
}
