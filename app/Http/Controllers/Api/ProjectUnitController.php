<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectUnit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjectUnitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProjectUnit::with(['structure', 'floor']);

        if ($structureId = $request->query('structure_id')) {
            $query->where('structure_id', $structureId);
        }

        if ($floorId = $request->query('floor_id')) {
            $query->where('floor_id', $floorId);
        }

        if ($unitType = $request->query('unit_type')) {
            $query->where('unit_type', $unitType);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $units = $query->get();

        return response()->json(['success' => true, 'data' => $units]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'structure_id' => 'required|exists:project_structures,id',
            'floor_id' => 'nullable|exists:project_floors,id',
            'unit_code' => 'required|string|max:50',
            'unit_type' => 'required|in:apartment,office,shop,warehouse,parking_space,storage,technical_room,common_area,other',
            'room_configuration' => 'nullable|string|max:50',
            'gross_area' => 'nullable|numeric|min:0',
            'net_area' => 'nullable|numeric|min:0',
            'balcony_area' => 'nullable|numeric|min:0',
            'terrace_area' => 'nullable|numeric|min:0',
            'garden_area' => 'nullable|numeric|min:0',
            'direction' => 'nullable|in:north,south,east,west,northeast,northwest,southeast,southwest,multiple',
            'planned_completion_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        // Check unique unit_code per structure
        $exists = ProjectUnit::where('structure_id', $validated['structure_id'])
            ->where('unit_code', $validated['unit_code'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Bu yapıda bu birim kodu zaten kullanılıyor.',
            ], 422);
        }

        $unit = ProjectUnit::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Birim başarıyla oluşturuldu.',
            'data' => $unit->load(['structure', 'floor']),
        ], 201);
    }

    public function show(ProjectUnit $unit): JsonResponse
    {
        $unit->load(['structure', 'floor']);

        return response()->json(['success' => true, 'data' => $unit]);
    }

    public function update(Request $request, ProjectUnit $unit): JsonResponse
    {
        $validated = $request->validate([
            'unit_code' => 'sometimes|string|max:50',
            'unit_type' => 'sometimes|in:apartment,office,shop,warehouse,parking_space,storage,technical_room,common_area,other',
            'room_configuration' => 'nullable|string|max:50',
            'gross_area' => 'nullable|numeric|min:0',
            'net_area' => 'nullable|numeric|min:0',
            'balcony_area' => 'nullable|numeric|min:0',
            'terrace_area' => 'nullable|numeric|min:0',
            'garden_area' => 'nullable|numeric|min:0',
            'direction' => 'nullable|in:north,south,east,west,northeast,northwest,southeast,southwest,multiple',
            'status' => 'sometimes|in:not_started,in_progress,completed,delivered,sold',
            'planned_completion_date' => 'nullable|date',
            'actual_completion_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'owner_name' => 'nullable|string|max:255',
            'owner_contact' => 'nullable|string|max:255',
            'is_sold' => 'sometimes|boolean',
            'description' => 'nullable|string',
        ]);

        // Check unique if unit_code changed
        if (isset($validated['unit_code']) && $validated['unit_code'] !== $unit->unit_code) {
            $exists = ProjectUnit::where('structure_id', $unit->structure_id)
                ->where('unit_code', $validated['unit_code'])
                ->where('id', '!=', $unit->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu yapıda bu birim kodu zaten kullanılıyor.',
                ], 422);
            }
        }

        $unit->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Birim başarıyla güncellendi.',
            'data' => $unit->load(['structure', 'floor']),
        ]);
    }

    public function destroy(ProjectUnit $unit): JsonResponse
    {
        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Birim başarıyla silindi.',
        ]);
    }
}
