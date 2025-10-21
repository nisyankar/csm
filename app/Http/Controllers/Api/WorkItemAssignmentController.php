<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkItemAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkItemAssignmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WorkItemAssignment::with([
            'project',
            'structure',
            'floor',
            'unit',
            'workItem.category',
            'subcontractor',
            'supervisor'
        ]);

        if ($projectId = $request->query('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($structureId = $request->query('structure_id')) {
            $query->where('structure_id', $structureId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($subcontractorId = $request->query('subcontractor_id')) {
            $query->where('subcontractor_id', $subcontractorId);
        }

        $assignments = $query->get();

        return response()->json(['success' => true, 'data' => $assignments]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'structure_id' => 'nullable|exists:project_structures,id',
            'floor_id' => 'nullable|exists:project_floors,id',
            'unit_id' => 'nullable|exists:project_units,id',
            'work_item_id' => 'required|exists:work_items,id',
            'assignment_type' => 'required|in:subcontractor,internal_team',
            'subcontractor_id' => 'required_if:assignment_type,subcontractor|nullable|exists:subcontractors,id',
            'supervisor_id' => 'nullable|exists:employees,id',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'unit_price' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'priority' => 'nullable|in:low,medium,high,critical',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after_or_equal:planned_start_date',
            'notes' => 'nullable|string',
        ]);

        // Auto-calculate total_price if not provided
        if (!isset($validated['total_price']) && isset($validated['unit_price'])) {
            $validated['total_price'] = $validated['quantity'] * $validated['unit_price'];
        }

        // Set remaining_quantity
        $validated['remaining_quantity'] = $validated['quantity'];

        $assignment = WorkItemAssignment::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'İş ataması başarıyla oluşturuldu.',
            'data' => $assignment->load([
                'project',
                'structure',
                'workItem.category',
                'subcontractor'
            ]),
        ], 201);
    }

    public function show(WorkItemAssignment $assignment): JsonResponse
    {
        $assignment->load([
            'project',
            'structure',
            'floor',
            'unit',
            'workItem.category',
            'subcontractor',
            'supervisor',
            'progressReports'
        ]);

        return response()->json(['success' => true, 'data' => $assignment]);
    }

    public function update(Request $request, WorkItemAssignment $assignment): JsonResponse
    {
        $validated = $request->validate([
            'subcontractor_id' => 'nullable|exists:subcontractors,id',
            'supervisor_id' => 'nullable|exists:employees,id',
            'quantity' => 'sometimes|numeric|min:0',
            'unit' => 'sometimes|string|max:20',
            'unit_price' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:not_started,in_progress,completed,on_hold,cancelled',
            'priority' => 'nullable|in:low,medium,high,critical',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Recalculate total_price if quantity or unit_price changed
        if (isset($validated['quantity']) || isset($validated['unit_price'])) {
            $quantity = $validated['quantity'] ?? $assignment->quantity;
            $unitPrice = $validated['unit_price'] ?? $assignment->unit_price;
            $validated['total_price'] = $quantity * $unitPrice;
            $validated['remaining_quantity'] = $quantity - $assignment->completed_quantity;
        }

        $assignment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'İş ataması başarıyla güncellendi.',
            'data' => $assignment->load([
                'project',
                'structure',
                'workItem.category',
                'subcontractor'
            ]),
        ]);
    }

    public function destroy(WorkItemAssignment $assignment): JsonResponse
    {
        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'İş ataması başarıyla silindi.',
        ]);
    }
}
