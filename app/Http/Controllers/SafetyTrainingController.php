<?php

namespace App\Http\Controllers;

use App\Models\SafetyTraining;
use App\Models\Project;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SafetyTrainingController extends Controller
{
    public function index(Request $request)
    {
        $query = SafetyTraining::with(['project', 'createdBy', 'approvedBy'])
            ->orderBy('training_date', 'desc');

        // Filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('training_type')) {
            $query->where('training_type', $request->training_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('training_title', 'like', "%{$request->search}%")
                  ->orWhere('trainer_name', 'like', "%{$request->search}%");
            });
        }

        $trainings = $query->paginate(15)->withQueryString();

        return Inertia::render('SafetyTrainings/Index', [
            'trainings' => $trainings,
            'projects' => Project::select('id', 'name')->get(),
            'filters' => $request->only(['project_id', 'training_type', 'status', 'search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('SafetyTrainings/Create', [
            'projects' => Project::select('id', 'name')->get(),
            'employees' => Employee::select('id', 'first_name', 'last_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'training_title' => 'required|string|max:255',
            'training_type' => 'required|in:orientation,isg_basic,fire_safety,first_aid,height_work,confined_space,crane_operation,electrical_safety,chemical_handling,emergency_response,excavation,scaffolding,fall_protection,ppe_usage,other',
            'trainer_name' => 'nullable|string|max:255',
            'trainer_company' => 'nullable|string|max:255',
            'training_date' => 'required|date',
            'start_time' => 'nullable',
            'duration_hours' => 'nullable|numeric|min:0',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'objectives' => 'nullable|string',
            'topics' => 'nullable|array',
            'attendees' => 'nullable|array',
            'materials' => 'nullable|array',
            'certificate_issued' => 'boolean',
            'certificate_expiry_date' => 'nullable|date',
            'certificate_number' => 'nullable|string',
            'test_conducted' => 'boolean',
            'pass_score' => 'nullable|numeric|min:0|max:100',
            'test_results' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated) {
            $validated['created_by'] = Auth::id();
            $validated['status'] = 'planned';

            SafetyTraining::create($validated);
        });

        return redirect()->route('safety-trainings.index')
            ->with('success', 'Eğitim kaydı başarıyla oluşturuldu.');
    }

    public function show(SafetyTraining $safetyTraining)
    {
        $safetyTraining->load(['project', 'createdBy', 'approvedBy']);

        return Inertia::render('SafetyTrainings/Show', [
            'training' => $safetyTraining
        ]);
    }

    public function edit(SafetyTraining $safetyTraining)
    {
        $safetyTraining->load(['project']);

        return Inertia::render('SafetyTrainings/Edit', [
            'training' => $safetyTraining,
            'projects' => Project::select('id', 'name')->get(),
            'employees' => Employee::select('id', 'first_name', 'last_name')->get(),
            'users' => User::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, SafetyTraining $safetyTraining)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'training_title' => 'required|string|max:255',
            'training_type' => 'required|in:orientation,isg_basic,fire_safety,first_aid,height_work,confined_space,crane_operation,electrical_safety,chemical_handling,emergency_response,excavation,scaffolding,fall_protection,ppe_usage,other',
            'trainer_name' => 'nullable|string|max:255',
            'trainer_company' => 'nullable|string|max:255',
            'training_date' => 'required|date',
            'start_time' => 'nullable',
            'duration_hours' => 'nullable|numeric|min:0',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'objectives' => 'nullable|string',
            'topics' => 'nullable|array',
            'attendees' => 'nullable|array',
            'materials' => 'nullable|array',
            'certificate_issued' => 'boolean',
            'certificate_expiry_date' => 'nullable|date',
            'certificate_number' => 'nullable|string',
            'test_conducted' => 'boolean',
            'pass_score' => 'nullable|numeric|min:0|max:100',
            'test_results' => 'nullable|array',
            'status' => 'required|in:planned,completed,cancelled',
            'approved_by' => 'nullable|exists:users,id',
        ]);

        DB::transaction(function () use ($safetyTraining, $validated) {
            $safetyTraining->update($validated);
        });

        return redirect()->route('safety-trainings.index')
            ->with('success', 'Eğitim kaydı başarıyla güncellendi.');
    }

    public function destroy(SafetyTraining $safetyTraining)
    {
        $safetyTraining->delete();

        return redirect()->route('safety-trainings.index')
            ->with('success', 'Eğitim kaydı başarıyla silindi.');
    }
}
