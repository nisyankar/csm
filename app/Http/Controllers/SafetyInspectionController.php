<?php

namespace App\Http\Controllers;

use App\Models\SafetyInspection;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SafetyInspectionController extends Controller
{
    public function index(Request $request)
    {
        $query = SafetyInspection::with(['project', 'inspector', 'reviewedBy'])
            ->orderBy('inspection_date', 'desc');

        // Filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('inspection_type')) {
            $query->where('inspection_type', $request->inspection_type);
        }

        if ($request->filled('overall_status')) {
            $query->where('overall_status', $request->overall_status);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('inspection_title', 'like', "%{$request->search}%")
                  ->orWhere('location', 'like', "%{$request->search}%");
            });
        }

        $inspections = $query->paginate(15)->withQueryString();

        return Inertia::render('SafetyInspections/Index', [
            'inspections' => $inspections,
            'projects' => Project::select('id', 'name')->get(),
            'filters' => $request->only(['project_id', 'inspection_type', 'overall_status', 'status', 'search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('SafetyInspections/Create', [
            'projects' => Project::select('id', 'name')->get(),
            'users' => User::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'inspection_title' => 'required|string|max:255',
            'inspection_type' => 'required|in:daily,weekly,monthly,quarterly,pre_operation,post_incident,special,audit',
            'inspection_date' => 'required|date',
            'inspection_time' => 'nullable',
            'location' => 'required|string|max:255',
            'area_inspected' => 'nullable|string|max:255',
            'checklist' => 'nullable|array',
            'findings' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'photos' => 'nullable|array',
            'overall_status' => 'required|in:passed,passed_with_notes,requires_action,failed',
            'score' => 'nullable|numeric|min:0|max:100',
            'items_checked' => 'integer|min:0',
            'items_passed' => 'integer|min:0',
            'items_failed' => 'integer|min:0',
            'action_items' => 'nullable|array',
            'next_inspection_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $validated['inspector_id'] = Auth::id();
            $validated['status'] = 'scheduled';

            SafetyInspection::create($validated);
        });

        return redirect()->route('safety-inspections.index')
            ->with('success', 'Denetim kaydı başarıyla oluşturuldu.');
    }

    public function show(SafetyInspection $safetyInspection)
    {
        $safetyInspection->load(['project', 'inspector', 'reviewedBy']);

        return Inertia::render('SafetyInspections/Show', [
            'inspection' => $safetyInspection
        ]);
    }

    public function edit(SafetyInspection $safetyInspection)
    {
        $safetyInspection->load(['project', 'inspector']);

        return Inertia::render('SafetyInspections/Edit', [
            'inspection' => $safetyInspection,
            'projects' => Project::select('id', 'name')->get(),
            'users' => User::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, SafetyInspection $safetyInspection)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'inspection_title' => 'required|string|max:255',
            'inspection_type' => 'required|in:daily,weekly,monthly,quarterly,pre_operation,post_incident,special,audit',
            'inspection_date' => 'required|date',
            'inspection_time' => 'nullable',
            'location' => 'required|string|max:255',
            'area_inspected' => 'nullable|string|max:255',
            'checklist' => 'nullable|array',
            'findings' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'photos' => 'nullable|array',
            'overall_status' => 'required|in:passed,passed_with_notes,requires_action,failed',
            'score' => 'nullable|numeric|min:0|max:100',
            'items_checked' => 'integer|min:0',
            'items_passed' => 'integer|min:0',
            'items_failed' => 'integer|min:0',
            'action_items' => 'nullable|array',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'next_inspection_date' => 'nullable|date',
            'reviewed_by' => 'nullable|exists:users,id',
            'reviewed_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($safetyInspection, $validated) {
            $safetyInspection->update($validated);
        });

        return redirect()->route('safety-inspections.index')
            ->with('success', 'Denetim kaydı başarıyla güncellendi.');
    }

    public function destroy(SafetyInspection $safetyInspection)
    {
        $safetyInspection->delete();

        return redirect()->route('safety-inspections.index')
            ->with('success', 'Denetim kaydı başarıyla silindi.');
    }
}
