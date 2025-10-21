<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectFloor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjectFloorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $structureId = $request->query('structure_id');

        $query = ProjectFloor::with(['structure', 'units']);

        if ($structureId) {
            $query->where('structure_id', $structureId);
        }

        $floors = $query->orderBy('floor_number')->get();

        return response()->json(['success' => true, 'data' => $floors]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'structure_id' => 'required|exists:project_structures,id',
            'floor_number' => 'required|integer',
            'floor_name' => 'required|string|max:255',
            'floor_type' => 'required|in:basement,ground,standard,roof,penthouse,technical,mezzanine',
            'total_units' => 'nullable|integer|min:0',
            'floor_area' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after_or_equal:planned_start_date',
            'description' => 'nullable|string',
        ]);

        // Check unique floor_number per structure
        $exists = ProjectFloor::where('structure_id', $validated['structure_id'])
            ->where('floor_number', $validated['floor_number'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Bu yapıda bu kat numarası zaten mevcut.',
            ], 422);
        }

        $floor = ProjectFloor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kat başarıyla oluşturuldu.',
            'data' => $floor->load('structure'),
        ], 201);
    }

    public function show(ProjectFloor $floor): JsonResponse
    {
        $floor->load(['structure', 'units']);

        return response()->json(['success' => true, 'data' => $floor]);
    }

    public function update(Request $request, ProjectFloor $floor): JsonResponse
    {
        $validated = $request->validate([
            'floor_number' => 'sometimes|integer',
            'floor_name' => 'sometimes|string|max:255',
            'floor_type' => 'sometimes|in:basement,ground,standard,roof,penthouse,technical,mezzanine',
            'total_units' => 'nullable|integer|min:0',
            'floor_area' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:not_started,in_progress,completed,on_hold',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        // Check unique if floor_number changed
        if (isset($validated['floor_number']) && $validated['floor_number'] !== $floor->floor_number) {
            $exists = ProjectFloor::where('structure_id', $floor->structure_id)
                ->where('floor_number', $validated['floor_number'])
                ->where('id', '!=', $floor->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu yapıda bu kat numarası zaten mevcut.',
                ], 422);
            }
        }

        $floor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kat başarıyla güncellendi.',
            'data' => $floor->load('structure'),
        ]);
    }

    public function destroy(ProjectFloor $floor): JsonResponse
    {
        $floor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kat başarıyla silindi.',
        ]);
    }
}
