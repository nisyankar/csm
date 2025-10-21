<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectStructure;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjectStructureController extends Controller
{
    /**
     * Display a listing of structures for a project.
     */
    public function index(Request $request): JsonResponse
    {
        $projectId = $request->query('project_id');

        $query = ProjectStructure::with(['project', 'supervisor', 'floors', 'units']);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $structures = $query->get();

        return response()->json([
            'success' => true,
            'data' => $structures,
        ]);
    }

    /**
     * Store a newly created structure.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'structure_type' => 'required|in:residential_block,office_block,commercial,villa,infrastructure,mixed_use,other',
            'total_floors' => 'nullable|integer|min:0',
            'total_units' => 'nullable|integer|min:0',
            'total_area' => 'nullable|numeric|min:0',
            'built_area' => 'nullable|numeric|min:0',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after_or_equal:planned_start_date',
            'supervisor_id' => 'nullable|exists:employees,id',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Check unique code per project
        $exists = ProjectStructure::where('project_id', $validated['project_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Bu proje için bu kod zaten kullanılıyor.',
            ], 422);
        }

        $structure = ProjectStructure::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Yapı başarıyla oluşturuldu.',
            'data' => $structure->load(['project', 'supervisor']),
        ], 201);
    }

    /**
     * Display the specified structure.
     */
    public function show(ProjectStructure $structure): JsonResponse
    {
        $structure->load([
            'project',
            'supervisor',
            'floors.units',
            'units',
            'workAssignments.workItem.category',
            'workAssignments.subcontractor',
        ]);

        return response()->json([
            'success' => true,
            'data' => $structure,
        ]);
    }

    /**
     * Update the specified structure.
     */
    public function update(Request $request, ProjectStructure $structure): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:50',
            'name' => 'sometimes|string|max:255',
            'structure_type' => 'sometimes|in:residential_block,office_block,commercial,villa,infrastructure,mixed_use,other',
            'total_floors' => 'nullable|integer|min:0',
            'total_units' => 'nullable|integer|min:0',
            'total_area' => 'nullable|numeric|min:0',
            'built_area' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:not_started,in_progress,completed,on_hold,cancelled',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'supervisor_id' => 'nullable|exists:employees,id',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Check unique code if being changed
        if (isset($validated['code']) && $validated['code'] !== $structure->code) {
            $exists = ProjectStructure::where('project_id', $structure->project_id)
                ->where('code', $validated['code'])
                ->where('id', '!=', $structure->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu proje için bu kod zaten kullanılıyor.',
                ], 422);
            }
        }

        $structure->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Yapı başarıyla güncellendi.',
            'data' => $structure->load(['project', 'supervisor']),
        ]);
    }

    /**
     * Remove the specified structure.
     */
    public function destroy(ProjectStructure $structure): JsonResponse
    {
        $structure->delete();

        return response()->json([
            'success' => true,
            'message' => 'Yapı başarıyla silindi.',
        ]);
    }

    /**
     * Get progress summary for a structure.
     */
    public function progress(ProjectStructure $structure): JsonResponse
    {
        $structure->load([
            'workAssignments.workItem.category',
            'workAssignments.subcontractor',
        ]);

        $summary = [
            'structure' => $structure->only(['id', 'code', 'name', 'status']),
            'overall_progress' => $structure->progress_percentage,
            'work_summary' => [
                'total' => $structure->workAssignments->count(),
                'not_started' => $structure->workAssignments->where('status', 'not_started')->count(),
                'in_progress' => $structure->workAssignments->where('status', 'in_progress')->count(),
                'completed' => $structure->workAssignments->where('status', 'completed')->count(),
            ],
            'work_assignments' => $structure->workAssignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'work_item' => $assignment->workItem->name,
                    'category' => $assignment->workItem->category->name,
                    'progress' => $assignment->progress_percentage,
                    'status' => $assignment->status,
                    'subcontractor' => $assignment->subcontractor?->company_name,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
