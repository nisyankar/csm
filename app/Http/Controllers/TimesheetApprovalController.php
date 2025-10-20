<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use App\Models\TimesheetApproval;
use App\Models\TimesheetRevision;
use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class TimesheetApprovalController extends Controller
{
    /**
     * Display pending approvals dashboard
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        
        // Get pending approvals for current user
        $query = TimesheetApproval::with([
            'timesheet.employee',
            'timesheet.project',
            'timesheet.department',
            'approver',
            'rejector'
        ])->where('approver_id', $user->employee->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereHas('timesheet', function ($q) use ($request) {
                $q->whereBetween('date', [$request->date_from, $request->date_to]);
            });
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->whereHas('timesheet', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->whereHas('timesheet', function ($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            });
        }

        $approvals = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get summary statistics
        $summary = $this->getApprovalSummary($user);

        // Get filter options
        $filterOptions = $this->getFilterOptions($user);

        return Inertia::render('TimesheetApproval/Index', [
            'approvals' => $approvals,
            'summary' => $summary,
            'filters' => $request->only(['status', 'date_from', 'date_to', 'project_id', 'employee_id']),
            'filterOptions' => $filterOptions,
        ]);
    }

    /**
     * Show specific approval details
     */
    public function show(TimesheetApproval $approval): Response
    {
        $this->authorizeApproval($approval);

        $approval->load([
            'timesheet.employee.user',
            'timesheet.project',
            'timesheet.department',
            'timesheet.revisions.revisor',
            'approver.user',
            'rejector.user'
        ]);

        // Get approval history for this timesheet
        $approvalHistory = TimesheetApproval::where('timesheet_id', $approval->timesheet_id)
            ->with(['approver.user', 'rejector.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get revision history
        $revisionHistory = TimesheetRevision::where('timesheet_id', $approval->timesheet_id)
            ->with(['revisor.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('TimesheetApproval/Show', [
            'approval' => $approval,
            'approvalHistory' => $approvalHistory,
            'revisionHistory' => $revisionHistory,
            'canApprove' => $this->canApprove($approval),
            'canReject' => $this->canReject($approval),
            'canRequestRevision' => $this->canRequestRevision($approval),
        ]);
    }

    /**
     * Approve timesheet
     */
    public function approve(Request $request, TimesheetApproval $approval): JsonResponse
    {
        $this->authorizeApproval($approval);

        if ($approval->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Bu onay zaten işlenmiş.',
            ], 422);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
            'approve_overtime' => 'boolean',
            'adjusted_hours' => 'nullable|numeric|min:0|max:24',
            'adjusted_overtime' => 'nullable|numeric|min:0|max:12',
        ]);

        DB::transaction(function () use ($approval, $validated) {
            $timesheet = $approval->timesheet;
            $originalData = $timesheet->toArray();

            // Apply adjustments if provided
            $updates = [];
            if (isset($validated['adjusted_hours'])) {
                $updates['regular_hours'] = $validated['adjusted_hours'];
                $updates['total_hours'] = $validated['adjusted_hours'] + ($validated['adjusted_overtime'] ?? $timesheet->overtime_hours);
            }

            if (isset($validated['adjusted_overtime'])) {
                $updates['overtime_hours'] = $validated['adjusted_overtime'];
                $updates['total_hours'] = ($validated['adjusted_hours'] ?? $timesheet->regular_hours) + $validated['adjusted_overtime'];
            }

            if (!empty($updates)) {
                $timesheet->update($updates);
                
                // Log revision if changes were made
                TimesheetRevision::create([
                    'timesheet_id' => $timesheet->id,
                    'revisor_id' => Auth::user()->employee->id,
                    'revision_type' => 'approval_adjustment',
                    'original_data' => $originalData,
                    'revised_data' => $timesheet->fresh()->toArray(),
                    'reason' => 'Onay sırasında düzeltme yapıldı',
                    'notes' => $validated['notes'],
                ]);
            }

            // Update approval
            $approval->update([
                'status' => 'approved',
                'approved_at' => now(),
                'notes' => $validated['notes'],
                'overtime_approved' => $validated['approve_overtime'] ?? true,
            ]);

            // Update timesheet status
            $timesheet->update([
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::user()->employee->id,
            ]);

            // Check if this completes the approval workflow
            $this->checkApprovalWorkflow($timesheet);
        });

        return response()->json([
            'success' => true,
            'message' => 'Puantaj başarıyla onaylandı.',
            'approval' => $approval->fresh()->load(['timesheet']),
        ]);
    }

    /**
     * Reject timesheet
     */
    public function reject(Request $request, TimesheetApproval $approval): JsonResponse
    {
        $this->authorizeApproval($approval);

        if ($approval->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Bu onay zaten işlenmiş.',
            ], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'require_resubmission' => 'boolean',
        ]);

        DB::transaction(function () use ($approval, $validated) {
            $timesheet = $approval->timesheet;

            // Update approval
            $approval->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
                'rejector_id' => Auth::user()->employee->id,
            ]);

            // Update timesheet status
            $statusUpdate = [
                'approval_status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => Auth::user()->employee->id,
            ];

            if ($validated['require_resubmission']) {
                $statusUpdate['status'] = 'revision_required';
            }

            $timesheet->update($statusUpdate);
        });

        return response()->json([
            'success' => true,
            'message' => 'Puantaj reddedildi.',
            'approval' => $approval->fresh()->load(['timesheet']),
        ]);
    }

    /**
     * Request revision for timesheet
     */
    public function requestRevision(Request $request, TimesheetApproval $approval): JsonResponse
    {
        $this->authorizeApproval($approval);

        if ($approval->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Bu onay zaten işlenmiş.',
            ], 422);
        }

        $validated = $request->validate([
            'revision_reason' => 'required|string|max:1000',
            'revision_notes' => 'nullable|string|max:1000',
            'revision_fields' => 'nullable|array',
            'revision_fields.*' => 'string|in:check_in,check_out,break_start,break_end,overtime_reason,notes',
        ]);

        DB::transaction(function () use ($approval, $validated) {
            $timesheet = $approval->timesheet;

            // Create revision request
            TimesheetRevision::create([
                'timesheet_id' => $timesheet->id,
                'revisor_id' => Auth::user()->employee->id,
                'revision_type' => 'revision_request',
                'reason' => $validated['revision_reason'],
                'notes' => $validated['revision_notes'],
                'requested_fields' => $validated['revision_fields'] ?? [],
                'status' => 'pending',
            ]);

            // Update approval status
            $approval->update([
                'status' => 'revision_requested',
                'revision_requested_at' => now(),
                'revision_reason' => $validated['revision_reason'],
            ]);

            // Update timesheet status
            $timesheet->update([
                'status' => 'revision_required',
                'approval_status' => 'revision_requested',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Revizyon talep edildi.',
            'approval' => $approval->fresh()->load(['timesheet']),
        ]);
    }

    /**
     * Bulk approve multiple timesheets
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'approval_ids' => 'required|array|min:1',
            'approval_ids.*' => 'required|exists:timesheet_approvals,id',
            'notes' => 'nullable|string|max:1000',
            'approve_all_overtime' => 'boolean',
        ]);

        $user = Auth::user();
        $approvals = TimesheetApproval::whereIn('id', $validated['approval_ids'])
            ->where('approver_id', $user->employee->id)
            ->where('status', 'pending')
            ->get();

        if ($approvals->count() !== count($validated['approval_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bazı onaylar bulunamadı veya işlenemez durumda.',
            ], 422);
        }

        $successful = 0;
        $failed = 0;
        $errors = [];

        DB::transaction(function () use ($approvals, $validated, &$successful, &$failed, &$errors) {
            foreach ($approvals as $approval) {
                try {
                    $timesheet = $approval->timesheet;

                    // Update approval
                    $approval->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'notes' => $validated['notes'],
                        'overtime_approved' => $validated['approve_all_overtime'] ?? true,
                    ]);

                    // Update timesheet
                    $timesheet->update([
                        'approval_status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => Auth::user()->employee->id,
                    ]);

                    $this->checkApprovalWorkflow($timesheet);
                    $successful++;

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'approval_id' => $approval->id,
                        'employee' => $approval->timesheet->employee->full_name,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "{$successful} puantaj onaylandı" . ($failed > 0 ? ", {$failed} başarısız." : "."),
            'successful' => $successful,
            'failed' => $failed,
            'errors' => $errors,
        ]);
    }

    /**
     * Bulk reject multiple timesheets
     */
    public function bulkReject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'approval_ids' => 'required|array|min:1',
            'approval_ids.*' => 'required|exists:timesheet_approvals,id',
            'rejection_reason' => 'required|string|max:1000',
            'require_resubmission' => 'boolean',
        ]);

        $user = Auth::user();
        $approvals = TimesheetApproval::whereIn('id', $validated['approval_ids'])
            ->where('approver_id', $user->employee->id)
            ->where('status', 'pending')
            ->get();

        if ($approvals->count() !== count($validated['approval_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bazı onaylar bulunamadı veya işlenemez durumda.',
            ], 422);
        }

        $successful = 0;
        $failed = 0;
        $errors = [];

        DB::transaction(function () use ($approvals, $validated, &$successful, &$failed, &$errors) {
            foreach ($approvals as $approval) {
                try {
                    $timesheet = $approval->timesheet;

                    // Update approval
                    $approval->update([
                        'status' => 'rejected',
                        'rejected_at' => now(),
                        'rejection_reason' => $validated['rejection_reason'],
                        'rejector_id' => Auth::user()->employee->id,
                    ]);

                    // Update timesheet
                    $statusUpdate = [
                        'approval_status' => 'rejected',
                        'rejected_at' => now(),
                        'rejected_by' => Auth::user()->employee->id,
                    ];

                    if ($validated['require_resubmission']) {
                        $statusUpdate['status'] = 'revision_required';
                    }

                    $timesheet->update($statusUpdate);
                    $successful++;

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'approval_id' => $approval->id,
                        'employee' => $approval->timesheet->employee->full_name,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "{$successful} puantaj reddedildi" . ($failed > 0 ? ", {$failed} başarısız." : "."),
            'successful' => $successful,
            'failed' => $failed,
            'errors' => $errors,
        ]);
    }

    /**
     * Get approval queue for mobile app
     */
    public function queue(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $approvals = TimesheetApproval::with([
            'timesheet.employee:id,first_name,last_name,employee_code',
            'timesheet.project:id,name,project_code',
            'timesheet.department:id,name,code'
        ])
        ->where('approver_id', $user->employee->id)
        ->where('status', 'pending')
        ->orderBy('created_at', 'asc')
        ->limit(50)
        ->get();

        return response()->json([
            'success' => true,
            'approvals' => $approvals,
            'count' => $approvals->count(),
        ]);
    }

    /**
     * Get approval statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'nullable|in:week,month,quarter,year',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $user = Auth::user();
        $period = $validated['period'] ?? 'month';

        // Calculate date range
        $dateRange = $this->getDateRange($period);

        $query = TimesheetApproval::where('approver_id', $user->employee->id)
            ->whereBetween('created_at', $dateRange);

        if (!empty($validated['project_id'])) {
            $query->whereHas('timesheet', function ($q) use ($validated) {
                $q->where('project_id', $validated['project_id']);
            });
        }

        $approvals = $query->get();

        $statistics = [
            'total_approvals' => $approvals->count(),
            'approved' => $approvals->where('status', 'approved')->count(),
            'rejected' => $approvals->where('status', 'rejected')->count(),
            'pending' => $approvals->where('status', 'pending')->count(),
            'revision_requested' => $approvals->where('status', 'revision_requested')->count(),
            'average_approval_time' => $this->calculateAverageApprovalTime($approvals),
            'approval_rate' => $approvals->count() > 0 ? 
                round(($approvals->where('status', 'approved')->count() / $approvals->count()) * 100, 2) : 0,
            'daily_breakdown' => $this->getDailyBreakdown($approvals, $dateRange),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
            'period' => $period,
            'date_range' => $dateRange,
        ]);
    }

    /**
     * Export approvals to Excel/CSV
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,excel',
            'status' => 'nullable|in:pending,approved,rejected,revision_requested',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $user = Auth::user();
        
        $query = TimesheetApproval::with([
            'timesheet.employee',
            'timesheet.project',
            'timesheet.department'
        ])->where('approver_id', $user->employee->id);

        // Apply filters
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['date_from']) && !empty($validated['date_to'])) {
            $query->whereHas('timesheet', function ($q) use ($validated) {
                $q->whereBetween('date', [$validated['date_from'], $validated['date_to']]);
            });
        }

        if (!empty($validated['project_id'])) {
            $query->whereHas('timesheet', function ($q) use ($validated) {
                $q->where('project_id', $validated['project_id']);
            });
        }

        $approvals = $query->orderBy('created_at', 'desc')->get();

        $filename = 'timesheet_approvals_' . now()->format('Y_m_d_H_i_s');

        if ($validated['format'] === 'csv') {
            return $this->exportCsv($approvals, $filename);
        } else {
            return $this->exportExcel($approvals, $filename);
        }
    }

    // Private helper methods

    private function authorizeApproval(TimesheetApproval $approval): void
    {
        $user = Auth::user();
        
        if ($approval->approver_id !== $user->employee->id) {
            abort(403, 'Bu onayı işleme yetkiniz yok.');
        }
    }

    private function canApprove(TimesheetApproval $approval): bool
    {
        return $approval->status === 'pending' && 
               $approval->approver_id === Auth::user()->employee->id;
    }

    private function canReject(TimesheetApproval $approval): bool
    {
        return $approval->status === 'pending' && 
               $approval->approver_id === Auth::user()->employee->id;
    }

    private function canRequestRevision(TimesheetApproval $approval): bool
    {
        return $approval->status === 'pending' && 
               $approval->approver_id === Auth::user()->employee->id;
    }

    private function getApprovalSummary(User $user): array
    {
        $approvals = TimesheetApproval::where('approver_id', $user->employee->id);

        return [
            'pending_count' => $approvals->clone()->where('status', 'pending')->count(),
            'approved_today' => $approvals->clone()
                ->where('status', 'approved')
                ->whereDate('approved_at', today())
                ->count(),
            'rejected_today' => $approvals->clone()
                ->where('status', 'rejected')
                ->whereDate('rejected_at', today())
                ->count(),
            'total_this_week' => $approvals->clone()
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];
    }

    private function getFilterOptions(User $user): array
    {
        // Get projects and employees that this user can approve
        $managedTimesheets = Timesheet::whereHas('approvals', function ($q) use ($user) {
            $q->where('approver_id', $user->employee->id);
        });

        $projects = Project::whereIn('id', $managedTimesheets->clone()->pluck('project_id')->unique())
            ->select('id', 'name', 'project_code')
            ->get();

        $employees = Employee::whereIn('id', $managedTimesheets->clone()->pluck('employee_id')->unique())
            ->select('id', 'first_name', 'last_name', 'employee_code')
            ->get();

        return [
            'projects' => $projects,
            'employees' => $employees,
            'statuses' => [
                ['value' => 'pending', 'label' => 'Beklemede'],
                ['value' => 'approved', 'label' => 'Onaylandı'],
                ['value' => 'rejected', 'label' => 'Reddedildi'],
                ['value' => 'revision_requested', 'label' => 'Revizyon Talep Edildi'],
            ],
        ];
    }

    private function checkApprovalWorkflow(Timesheet $timesheet): void
    {
        // Check if all required approvals are completed
        $pendingApprovals = $timesheet->approvals()->where('status', 'pending')->count();
        
        if ($pendingApprovals === 0) {
            $timesheet->update([
                'approval_status' => 'fully_approved',
                'final_approval_at' => now(),
            ]);
        }
    }

    private function getDateRange(string $period): array
    {
        switch ($period) {
            case 'week':
                return [now()->startOfWeek(), now()->endOfWeek()];
            case 'month':
                return [now()->startOfMonth(), now()->endOfMonth()];
            case 'quarter':
                return [now()->startOfQuarter(), now()->endOfQuarter()];
            case 'year':
                return [now()->startOfYear(), now()->endOfYear()];
            default:
                return [now()->startOfMonth(), now()->endOfMonth()];
        }
    }

    private function calculateAverageApprovalTime($approvals): float
    {
        $approvedItems = $approvals->whereIn('status', ['approved', 'rejected']);
        
        if ($approvedItems->isEmpty()) {
            return 0;
        }

        $totalMinutes = $approvedItems->sum(function ($approval) {
            $processedAt = $approval->approved_at ?? $approval->rejected_at;
            return $approval->created_at->diffInMinutes($processedAt);
        });

        return round($totalMinutes / $approvedItems->count(), 2);
    }

    private function getDailyBreakdown($approvals, array $dateRange): array
    {
        $breakdown = [];
        $current = Carbon::parse($dateRange[0]);
        $end = Carbon::parse($dateRange[1]);

        while ($current <= $end) {
            $dayApprovals = $approvals->filter(function ($approval) use ($current) {
                return $approval->created_at->isSameDay($current);
            });

            $breakdown[] = [
                'date' => $current->format('Y-m-d'),
                'total' => $dayApprovals->count(),
                'approved' => $dayApprovals->where('status', 'approved')->count(),
                'rejected' => $dayApprovals->where('status', 'rejected')->count(),
                'pending' => $dayApprovals->where('status', 'pending')->count(),
            ];

            $current->addDay();
        }

        return $breakdown;
    }

    private function exportCsv($approvals, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($approvals) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Tarih', 'Çalışan', 'Proje', 'Departman', 'Giriş', 'Çıkış', 
                'Toplam Saat', 'Fazla Mesai', 'Durum', 'Onay Tarihi', 'Notlar'
            ]);

            // Data
            foreach ($approvals as $approval) {
                $timesheet = $approval->timesheet;
                fputcsv($file, [
                    $timesheet->date,
                    $timesheet->employee->full_name,
                    $timesheet->project->name ?? '',
                    $timesheet->department->name ?? '',
                    $timesheet->check_in ? $timesheet->check_in->format('H:i') : '',
                    $timesheet->check_out ? $timesheet->check_out->format('H:i') : '',
                    $timesheet->total_hours,
                    $timesheet->overtime_hours,
                    $approval->status,
                    $approval->approved_at ? $approval->approved_at->format('d.m.Y H:i') : '',
                    $approval->notes ?? '',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportExcel($approvals, string $filename)
    {
        // This would typically use Laravel Excel package
        // For now, fall back to CSV
        return $this->exportCsv($approvals, $filename);
    }
}