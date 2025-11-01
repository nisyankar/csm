<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuantityResource;
use App\Models\Quantity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class QuantityController extends Controller
{
    /**
     * Get quantities list with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Quantity::with([
            'project:id,name,project_code',
            'workItem:id,name,code,unit',
            'projectStructure:id,code,name',
            'projectFloor:id,name,floor_number',
            'projectUnit:id,unit_code,unit_type',
        ]);

        // Search
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('workItem', function ($wq) use ($search) {
                    $wq->where('name', 'like', "%{$search}%")
                       ->orWhere('code', 'like', "%{$search}%");
                });
            });
        }

        // Filter by project
        if ($projectId = $request->query('project_id')) {
            $query->where('project_id', $projectId);
        }

        // Filter by work item
        if ($workItemId = $request->query('work_item_id')) {
            $query->where('work_item_id', $workItemId);
        }

        // Filter by structure
        if ($structureId = $request->query('structure_id')) {
            $query->where('project_structure_id', $structureId);
        }

        // Filter by floor
        if ($floorId = $request->query('floor_id')) {
            $query->where('project_floor_id', $floorId);
        }

        // Filter by unit
        if ($unitId = $request->query('unit_id')) {
            $query->where('project_unit_id', $unitId);
        }

        // Filter by verification status
        if ($verified = $request->query('verified')) {
            if ($verified === 'true') {
                $query->whereNotNull('verified_at');
            } elseif ($verified === 'false') {
                $query->whereNull('verified_at');
            }
        }

        // Filter by approval status
        if ($approved = $request->query('approved')) {
            if ($approved === 'true') {
                $query->whereNotNull('approved_at');
            } elseif ($approved === 'false') {
                $query->whereNull('approved_at');
            }
        }

        // Sorting
        $sortField = $request->query('sort', 'measurement_date');
        $sortDirection = $request->query('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->query('per_page', 20);
        $quantities = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'current_page' => $quantities->currentPage(),
                'data' => QuantityResource::collection($quantities->items()),
                'first_page_url' => $quantities->url(1),
                'from' => $quantities->firstItem(),
                'last_page' => $quantities->lastPage(),
                'last_page_url' => $quantities->url($quantities->lastPage()),
                'next_page_url' => $quantities->nextPageUrl(),
                'path' => $quantities->path(),
                'per_page' => $quantities->perPage(),
                'prev_page_url' => $quantities->previousPageUrl(),
                'to' => $quantities->lastItem(),
                'total' => $quantities->total(),
            ],
        ]);
    }

    /**
     * Get single quantity details
     */
    public function show(Quantity $quantity): JsonResponse
    {
        $quantity->load([
            'project:id,name,project_code',
            'workItem:id,name,code,unit,default_unit_price',
            'projectStructure:id,code,name',
            'projectFloor:id,name,floor_number',
            'projectUnit:id,unit_code,unit_type,gross_area,net_area',
            'verifiedBy:id,name',
            'approvedBy:id,name',
        ]);

        return response()->json([
            'success' => true,
            'data' => new QuantityResource($quantity),
        ]);
    }

    /**
     * Create new quantity
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'work_item_id' => 'required|exists:work_items,id',
            'project_structure_id' => 'nullable|exists:project_structures,id',
            'project_floor_id' => 'nullable|exists:project_floors,id',
            'project_unit_id' => 'nullable|exists:project_units,id',
            'planned_quantity' => 'required|numeric|min:0',
            'completed_quantity' => 'nullable|numeric|min:0',
            'unit' => 'required|string|max:20',
            'measurement_date' => 'required|date',
            'measurement_method' => 'nullable|in:manual,calculated,imported',
            'notes' => 'nullable|string',
        ]);

        $quantity = Quantity::create($validated);

        $quantity->load([
            'project',
            'workItem',
            'projectStructure',
            'projectFloor',
            'projectUnit',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Metraj kaydı oluşturuldu.',
            'data' => $quantity,
        ], 201);
    }

    /**
     * Update quantity
     */
    public function update(Request $request, Quantity $quantity): JsonResponse
    {
        $validated = $request->validate([
            'planned_quantity' => 'sometimes|numeric|min:0',
            'completed_quantity' => 'sometimes|numeric|min:0',
            'unit' => 'sometimes|string|max:20',
            'measurement_date' => 'sometimes|date',
            'measurement_method' => 'nullable|in:manual,calculated,imported',
            'notes' => 'nullable|string',
        ]);

        $quantity->update($validated);

        $quantity->load([
            'project',
            'workItem',
            'projectStructure',
            'projectFloor',
            'projectUnit',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Metraj güncellendi.',
            'data' => $quantity,
        ]);
    }

    /**
     * Delete quantity
     */
    public function destroy(Quantity $quantity): JsonResponse
    {
        $quantity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Metraj silindi.',
        ]);
    }

    /**
     * Verify quantity
     */
    public function verify(Request $request, Quantity $quantity): JsonResponse
    {
        if ($quantity->verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Bu metraj zaten doğrulanmış.',
            ], 422);
        }

        $quantity->update([
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Metraj doğrulandı.',
            'data' => $quantity->fresh(['verifiedBy']),
        ]);
    }

    /**
     * Approve quantity
     */
    public function approve(Request $request, Quantity $quantity): JsonResponse
    {
        if (!$quantity->verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Metraj önce doğrulanmalıdır.',
            ], 422);
        }

        if ($quantity->approved_at) {
            return response()->json([
                'success' => false,
                'message' => 'Bu metraj zaten onaylanmış.',
            ], 422);
        }

        $quantity->update([
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Metraj onaylandı.',
            'data' => $quantity->fresh(['approvedBy']),
        ]);
    }

    /**
     * Get quantities by project
     */
    public function byProject(int $projectId): JsonResponse
    {
        $quantities = Quantity::with([
            'workItem:id,name,code,unit',
            'projectStructure:id,code,name',
            'projectFloor:id,name,floor_number',
            'projectUnit:id,unit_code,unit_type',
        ])
            ->where('project_id', $projectId)
            ->orderBy('measurement_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $quantities,
        ]);
    }

    /**
     * Get quantities statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $query = Quantity::query();

        // Filter by project if provided
        if ($projectId = $request->query('project_id')) {
            $query->where('project_id', $projectId);
        }

        $quantities = $query->get();

        $stats = [
            'total_count' => $quantities->count(),
            'total_planned' => $quantities->sum('planned_quantity'),
            'total_completed' => $quantities->sum('completed_quantity'),
            'total_remaining' => $quantities->sum('remaining_quantity'),
            'verified_count' => $quantities->filter(fn($q) => $q->verified_at)->count(),
            'approved_count' => $quantities->filter(fn($q) => $q->approved_at)->count(),
            'pending_verification' => $quantities->filter(fn($q) => !$q->verified_at)->count(),
            'pending_approval' => $quantities->filter(fn($q) => $q->verified_at && !$q->approved_at)->count(),
            'completion_percentage' => $quantities->sum('planned_quantity') > 0
                ? round(($quantities->sum('completed_quantity') / $quantities->sum('planned_quantity')) * 100, 2)
                : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
