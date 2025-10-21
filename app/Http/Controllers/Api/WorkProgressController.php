<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkProgress;
use App\Models\WorkItemAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WorkProgressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WorkProgress::with([
            'assignment.workItem.category',
            'assignment.project',
            'assignment.structure',
            'assignment.floor',
            'assignment.unit',
            'assignment.subcontractor',
            'reporter'
        ]);

        if ($assignmentId = $request->query('assignment_id')) {
            $query->where('assignment_id', $assignmentId);
        }

        if ($reportDate = $request->query('report_date')) {
            $query->whereDate('report_date', $reportDate);
        }

        if ($approvalStatus = $request->query('approval_status')) {
            $query->where('approval_status', $approvalStatus);
        }

        if ($startDate = $request->query('start_date')) {
            $query->whereDate('report_date', '>=', $startDate);
        }

        if ($endDate = $request->query('end_date')) {
            $query->whereDate('report_date', '<=', $endDate);
        }

        $reports = $query->orderBy('report_date', 'desc')->get();

        return response()->json(['success' => true, 'data' => $reports]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'assignment_id' => 'required|exists:work_item_assignments,id',
            'report_date' => 'required|date',
            'completed_quantity' => 'required|numeric|min:0',
            'quality_rating' => 'nullable|integer|min:1|max:5',
            'work_description' => 'nullable|string',
            'issues' => 'nullable|array',
            'issues.*.type' => 'nullable|in:delay,quality,safety,material,equipment,weather,other',
            'issues.*.description' => 'nullable|string',
            'issues.*.severity' => 'nullable|in:low,medium,high,critical',
            'photos' => 'nullable|array',
            'photos.*.url' => 'nullable|string',
            'photos.*.caption' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Auto-assign reported_by to authenticated user
        $validated['reported_by'] = Auth::id();

        // Check if assignment exists and get current progress
        $assignment = WorkItemAssignment::findOrFail($validated['assignment_id']);

        // Validate completed_quantity doesn't exceed remaining
        $totalReported = WorkProgress::where('assignment_id', $assignment->id)
            ->where('approval_status', '!=', 'rejected')
            ->sum('completed_quantity');

        if (($totalReported + $validated['completed_quantity']) > $assignment->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Bildirilen miktar, kalan iş miktarını aşıyor.',
                'remaining' => $assignment->quantity - $totalReported,
            ], 422);
        }

        // Convert arrays to JSON
        if (isset($validated['issues'])) {
            $validated['issues'] = json_encode($validated['issues']);
        }
        if (isset($validated['photos'])) {
            $validated['photos'] = json_encode($validated['photos']);
        }

        $report = WorkProgress::create($validated);

        // Update assignment completed_quantity and progress_percentage
        $this->updateAssignmentProgress($assignment);

        return response()->json([
            'success' => true,
            'message' => 'İlerleme raporu başarıyla oluşturuldu.',
            'data' => $report->load([
                'assignment.workItem.category',
                'assignment.project',
                'reporter'
            ]),
        ], 201);
    }

    public function show(WorkProgress $report): JsonResponse
    {
        $report->load([
            'assignment.workItem.category',
            'assignment.project',
            'assignment.structure',
            'assignment.floor',
            'assignment.unit',
            'assignment.subcontractor',
            'reporter',
            'approver'
        ]);

        return response()->json(['success' => true, 'data' => $report]);
    }

    public function update(Request $request, WorkProgress $report): JsonResponse
    {
        // Only allow updates if not yet approved
        if ($report->approval_status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Onaylanmış rapor düzenlenemez.',
            ], 403);
        }

        $validated = $request->validate([
            'report_date' => 'sometimes|date',
            'completed_quantity' => 'sometimes|numeric|min:0',
            'quality_rating' => 'nullable|integer|min:1|max:5',
            'work_description' => 'nullable|string',
            'issues' => 'nullable|array',
            'issues.*.type' => 'nullable|in:delay,quality,safety,material,equipment,weather,other',
            'issues.*.description' => 'nullable|string',
            'issues.*.severity' => 'nullable|in:low,medium,high,critical',
            'photos' => 'nullable|array',
            'photos.*.url' => 'nullable|string',
            'photos.*.caption' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Validate completed_quantity if changed
        if (isset($validated['completed_quantity'])) {
            $assignment = $report->assignment;
            $totalReported = WorkProgress::where('assignment_id', $assignment->id)
                ->where('approval_status', '!=', 'rejected')
                ->where('id', '!=', $report->id)
                ->sum('completed_quantity');

            if (($totalReported + $validated['completed_quantity']) > $assignment->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bildirilen miktar, kalan iş miktarını aşıyor.',
                ], 422);
            }
        }

        // Convert arrays to JSON
        if (isset($validated['issues'])) {
            $validated['issues'] = json_encode($validated['issues']);
        }
        if (isset($validated['photos'])) {
            $validated['photos'] = json_encode($validated['photos']);
        }

        $report->update($validated);

        // Update assignment progress if quantity changed
        if (isset($validated['completed_quantity'])) {
            $this->updateAssignmentProgress($report->assignment);
        }

        return response()->json([
            'success' => true,
            'message' => 'İlerleme raporu başarıyla güncellendi.',
            'data' => $report->load(['assignment.workItem.category', 'reporter']),
        ]);
    }

    public function destroy(WorkProgress $report): JsonResponse
    {
        // Only allow deletion if not approved
        if ($report->approval_status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Onaylanmış rapor silinemez.',
            ], 403);
        }

        $assignment = $report->assignment;
        $report->delete();

        // Update assignment progress after deletion
        $this->updateAssignmentProgress($assignment);

        return response()->json([
            'success' => true,
            'message' => 'İlerleme raporu başarıyla silindi.',
        ]);
    }

    public function approve(WorkProgress $report): JsonResponse
    {
        if ($report->approval_status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Bu rapor zaten onaylanmış.',
            ], 422);
        }

        $report->update([
            'approval_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Update assignment progress
        $this->updateAssignmentProgress($report->assignment);

        return response()->json([
            'success' => true,
            'message' => 'İlerleme raporu onaylandı.',
            'data' => $report->load(['assignment', 'reporter', 'approver']),
        ]);
    }

    public function reject(Request $request, WorkProgress $report): JsonResponse
    {
        if ($report->approval_status === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Bu rapor zaten reddedilmiş.',
            ], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $report->update([
            'approval_status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Update assignment progress (rejected reports don't count)
        $this->updateAssignmentProgress($report->assignment);

        return response()->json([
            'success' => true,
            'message' => 'İlerleme raporu reddedildi.',
            'data' => $report->load(['assignment', 'reporter', 'approver']),
        ]);
    }

    /**
     * Helper method to update assignment progress based on approved reports
     */
    private function updateAssignmentProgress(WorkItemAssignment $assignment): void
    {
        $approvedQuantity = WorkProgress::where('assignment_id', $assignment->id)
            ->where('approval_status', 'approved')
            ->sum('completed_quantity');

        $assignment->update([
            'completed_quantity' => $approvedQuantity,
            'remaining_quantity' => $assignment->quantity - $approvedQuantity,
            'progress_percentage' => $assignment->quantity > 0
                ? round(($approvedQuantity / $assignment->quantity) * 100, 2)
                : 0,
        ]);

        // Auto-update status based on progress
        if ($assignment->progress_percentage >= 100 && $assignment->status !== 'completed') {
            $assignment->update([
                'status' => 'completed',
                'actual_end_date' => now(),
            ]);
        } elseif ($assignment->progress_percentage > 0 && $assignment->status === 'not_started') {
            $assignment->update([
                'status' => 'in_progress',
                'actual_start_date' => now(),
            ]);
        }
    }
}
