<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of leave requests
     */
    public function index(Request $request): Response
    {
        $query = LeaveRequest::with(['employee', 'approver', 'substituteEmployee']);

        // Date filtering
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('month')) {
            $date = Carbon::parse($request->month . '-01');
            $query->whereMonth('start_date', $date->month)
                  ->whereYear('start_date', $date->year);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by leave type
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Special filters
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'pending_approval':
                    $query->where('status', 'pending');
                    break;
                case 'current_leaves':
                    $query->where('status', 'approved')
                          ->where('start_date', '<=', now())
                          ->where('end_date', '>=', now());
                    break;
                case 'upcoming_leaves':
                    $query->where('status', 'approved')
                          ->where('start_date', '>', now());
                    break;
                case 'urgent':
                    $query->where('priority', 'urgent');
                    break;
                case 'long_leaves':
                    $query->where('working_days', '>', 5);
                    break;
                case 'requires_document':
                    $query->where('requires_document', true)
                          ->where('document_status', '!=', 'verified');
                    break;
            }
        }

        // Role-based filtering
        $user = Auth::user();
        if ($user->hasRole('employee')) {
            // Employees can only see their own leave requests
            $query->where('employee_id', $user->employee_id);
        } elseif ($user->hasRole(['project_manager', 'site_manager', 'foreman'])) {
            // Managers can see their team's leave requests
            $teamIds = $this->getTeamEmployeeIds();
            $query->whereIn('employee_id', $teamIds);
        }

        // Sorting
        $sortField = $request->get('sort', 'submitted_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $leaveRequests = $query->paginate(20)->withQueryString();

        // Get filter options
        $employees = $this->getAccessibleEmployees();

        return Inertia::render('LeaveRequests/Index', [
            'leave_requests' => $leaveRequests,
            'employees' => $employees,
            'filters' => $request->only([
                'search', 'start_date', 'end_date', 'month', 'employee_id', 
                'leave_type', 'status', 'priority', 'filter'
            ]),
            'stats' => $this->getLeaveRequestStats(),
            'leave_types' => $this->getLeaveTypes(),
        ]);
    }

    /**
     * Show the form for creating a new leave request
     */
    public function create(Request $request): Response
    {
        $employees = $this->getAccessibleEmployees();
        $substituteEmployees = Employee::active()
            ->select('id', 'first_name', 'last_name', 'position')
            ->get();

        // Pre-fill for specific employee
        $defaults = [
            'employee_id' => $request->employee_id ?? Auth::user()->employee_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'leave_type' => $request->leave_type ?? 'annual',
        ];

        return Inertia::render('LeaveRequests/Create', [
            'employees' => $employees,
            'substitute_employees' => $substituteEmployees,
            'leave_types' => $this->getLeaveTypes(),
            'defaults' => $defaults,
        ]);
    }

    /**
     * Store a newly created leave request
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:annual,sick,maternity,paternity,marriage,funeral,military,unpaid,emergency,study,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'boolean',
            'half_day_period' => 'nullable|in:morning,afternoon',
            'reason' => 'required|string|max:1000',
            'description' => 'nullable|string|max:2000',
            'emergency_contact' => 'nullable|string|max:255',
            'handover_notes' => 'nullable|string|max:1000',
            'substitute_employee_id' => 'nullable|exists:employees,id',
            'substitute_notes' => 'nullable|string|max:500',
            'priority' => 'required|in:low,medium,high,urgent',
            'urgency_reason' => 'nullable|in:medical,family,legal,personal,other',
            'attached_documents' => 'nullable|array',
            'attached_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        // Check authorization
        $this->authorizeLeaveRequestCreation($validated['employee_id']);

        $employee = Employee::find($validated['employee_id']);

        // Calculate leave duration
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $workingDays = $this->calculateWorkingDays($startDate, $endDate);

        if ($validated['is_half_day']) {
            $workingDays = 0.5;
            $totalDays = 0.5;
        }

        // Check leave balance for annual leave
        if ($validated['leave_type'] === 'annual') {
            if (!$employee->hasLeaveBalance($workingDays)) {
                return back()->with('error', 'Yeterli izin bakiyeniz bulunmamaktadır.');
            }
        }

        // Check for conflicts
        $conflicts = $this->checkLeaveConflicts($employee->id, $startDate, $endDate);

        // Handle file uploads
        $attachedDocuments = [];
        if ($request->hasFile('attached_documents')) {
            foreach ($request->file('attached_documents') as $file) {
                $path = $file->store('leave_documents', 'public');
                $attachedDocuments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
        }

        // Determine if document is required
        $requiresDocument = in_array($validated['leave_type'], ['sick', 'maternity', 'military']);
        $documentStatus = $requiresDocument ? 'pending' : 'not_required';

        // Create leave request
        $leaveRequest = LeaveRequest::create([
            'employee_id' => $validated['employee_id'],
            'leave_type' => $validated['leave_type'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'is_half_day' => $validated['is_half_day'] ?? false,
            'half_day_period' => $validated['half_day_period'],
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'emergency_contact' => $validated['emergency_contact'],
            'handover_notes' => $validated['handover_notes'],
            'substitute_employee_id' => $validated['substitute_employee_id'],
            'substitute_notes' => $validated['substitute_notes'],
            'priority' => $validated['priority'],
            'urgency_reason' => $validated['urgency_reason'],
            'attached_documents' => $attachedDocuments,
            'requires_document' => $requiresDocument,
            'document_status' => $documentStatus,
            'conflicting_leaves' => $conflicts,
            'conflict_resolved' => empty($conflicts),
            'balance_impact' => $validated['leave_type'] === 'annual' ? $workingDays : 0,
            'is_paid' => $this->isLeaveTypePaid($validated['leave_type']),
            'status' => 'pending',
            'submitted_at' => now(),
            'approver_id' => $this->getApproverForEmployee($employee),
        ]);

        // Auto-submit if no conflicts
        if (empty($conflicts)) {
            $leaveRequest->submit();
        }

        return redirect()->route('leave-requests.show', $leaveRequest)
            ->with('success', 'İzin talebi başarıyla oluşturuldu.');
    }

    /**
     * Display the specified leave request
     */
    public function show(LeaveRequest $leaveRequest): Response
    {
        $leaveRequest->load([
            'employee',
            'approver',
            'substituteEmployee',
            'parentRequest',
            'childRequests'
        ]);

        // Check authorization
        $this->authorizeLeaveRequestAccess($leaveRequest);

        return Inertia::render('LeaveRequests/Show', [
            'leave_request' => $leaveRequest,
            'can_edit' => $this->canEditLeaveRequest($leaveRequest),
            'can_approve' => $this->canApproveLeaveRequest($leaveRequest),
            'can_cancel' => $this->canCancelLeaveRequest($leaveRequest),
        ]);
    }

    /**
     * Show the form for editing the specified leave request
     */
    public function edit(LeaveRequest $leaveRequest): Response
    {
        if (!$this->canEditLeaveRequest($leaveRequest)) {
            return redirect()->route('leave-requests.show', $leaveRequest)
                ->with('error', 'Bu izin talebi düzenlenemez.');
        }

        $leaveRequest->load(['employee', 'substituteEmployee']);

        $employees = $this->getAccessibleEmployees();
        $substituteEmployees = Employee::active()
            ->where('id', '!=', $leaveRequest->employee_id)
            ->select('id', 'first_name', 'last_name', 'position')
            ->get();

        return Inertia::render('LeaveRequests/Edit', [
            'leave_request' => $leaveRequest,
            'employees' => $employees,
            'substitute_employees' => $substituteEmployees,
            'leave_types' => $this->getLeaveTypes(),
        ]);
    }

    /**
     * Update the specified leave request
     */
    public function update(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        if (!$this->canEditLeaveRequest($leaveRequest)) {
            return back()->with('error', 'Bu izin talebi düzenlenemez.');
        }

        $validated = $request->validate([
            'leave_type' => 'required|in:annual,sick,maternity,paternity,marriage,funeral,military,unpaid,emergency,study,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'boolean',
            'half_day_period' => 'nullable|in:morning,afternoon',
            'reason' => 'required|string|max:1000',
            'description' => 'nullable|string|max:2000',
            'emergency_contact' => 'nullable|string|max:255',
            'handover_notes' => 'nullable|string|max:1000',
            'substitute_employee_id' => 'nullable|exists:employees,id',
            'substitute_notes' => 'nullable|string|max:500',
            'priority' => 'required|in:low,medium,high,urgent',
            'urgency_reason' => 'nullable|in:medical,family,legal,personal,other',
        ]);

        // Recalculate duration
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $workingDays = $this->calculateWorkingDays($startDate, $endDate);

        if ($validated['is_half_day']) {
            $workingDays = 0.5;
            $totalDays = 0.5;
        }

        // Check conflicts again
        $conflicts = $this->checkLeaveConflicts(
            $leaveRequest->employee_id, 
            $startDate, 
            $endDate, 
            $leaveRequest->id
        );

        $leaveRequest->update([
            'leave_type' => $validated['leave_type'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'is_half_day' => $validated['is_half_day'] ?? false,
            'half_day_period' => $validated['half_day_period'],
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'emergency_contact' => $validated['emergency_contact'],
            'handover_notes' => $validated['handover_notes'],
            'substitute_employee_id' => $validated['substitute_employee_id'],
            'substitute_notes' => $validated['substitute_notes'],
            'priority' => $validated['priority'],
            'urgency_reason' => $validated['urgency_reason'],
            'conflicting_leaves' => $conflicts,
            'conflict_resolved' => empty($conflicts),
            'balance_impact' => $validated['leave_type'] === 'annual' ? $workingDays : 0,
            'is_paid' => $this->isLeaveTypePaid($validated['leave_type']),
        ]);

        return redirect()->route('leave-requests.show', $leaveRequest)
            ->with('success', 'İzin talebi başarıyla güncellendi.');
    }

    /**
     * Remove the specified leave request
     */
    public function destroy(LeaveRequest $leaveRequest): RedirectResponse
    {
        if (!$this->canEditLeaveRequest($leaveRequest)) {
            return back()->with('error', 'Bu izin talebi silinemez.');
        }

        // Delete attached files
        if ($leaveRequest->attached_documents) {
            foreach ($leaveRequest->attached_documents as $document) {
                if (isset($document['path']) && Storage::disk('public')->exists($document['path'])) {
                    Storage::disk('public')->delete($document['path']);
                }
            }
        }

        $leaveRequest->delete();

        return redirect()->route('leave-requests.index')
            ->with('success', 'İzin talebi başarıyla silindi.');
    }

    /**
     * Submit leave request for approval
     */
    public function submit(LeaveRequest $leaveRequest): RedirectResponse
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Bu izin talebi zaten onaya gönderilmiş.');
        }

        $this->authorizeLeaveRequestAction($leaveRequest);

        $leaveRequest->submit();

        return back()->with('success', 'İzin talebi onaya gönderildi.');
    }

    /**
     * Approve leave request
     */
    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        if (!$this->canApproveLeaveRequest($leaveRequest)) {
            return back()->with('error', 'Bu izin talebini onaylama yetkiniz bulunmamaktadır.');
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:500',
        ]);

        $approver = Auth::user()->employee;
        $leaveRequest->approve($approver, $validated['approval_notes']);

        return back()->with('success', 'İzin talebi başarıyla onaylandı.');
    }

    /**
     * Reject leave request
     */
    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        if (!$this->canApproveLeaveRequest($leaveRequest)) {
            return back()->with('error', 'Bu izin talebini reddetme yetkiniz bulunmamaktadır.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $approver = Auth::user()->employee;
        $leaveRequest->reject($approver, $validated['rejection_reason']);

        return back()->with('success', 'İzin talebi reddedildi.');
    }

    /**
     * Cancel leave request
     */
    public function cancel(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        if (!$this->canCancelLeaveRequest($leaveRequest)) {
            return back()->with('error', 'Bu izin talebi iptal edilemez.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $leaveRequest->cancel($validated['cancellation_reason']);

        return back()->with('success', 'İzin talebi iptal edildi.');
    }

    /**
     * Show leave calendar
     */
    public function calendar(Request $request): Response
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get approved leaves for the month
        $leaves = LeaveRequest::with(['employee'])
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->get();

        // Apply role-based filtering
        $user = Auth::user();
        if ($user->hasRole('employee')) {
            $leaves = $leaves->where('employee_id', $user->employee_id);
        } elseif ($user->hasRole(['project_manager', 'site_manager', 'foreman'])) {
            $teamIds = $this->getTeamEmployeeIds();
            $leaves = $leaves->whereIn('employee_id', $teamIds);
        }

        // Format for calendar
        $calendarEvents = $leaves->map(function ($leave) {
            return [
                'id' => $leave->id,
                'title' => $leave->employee->full_name . ' - ' . $leave->leave_type_display,
                'start' => $leave->start_date->toDateString(),
                'end' => $leave->end_date->addDay()->toDateString(), // Full calendar needs end+1
                'color' => $this->getLeaveTypeColor($leave->leave_type),
                'extendedProps' => [
                    'employee' => $leave->employee->full_name,
                    'type' => $leave->leave_type_display,
                    'days' => $leave->working_days,
                    'reason' => $leave->reason,
                ],
            ];
        });

        return Inertia::render('LeaveRequests/Calendar', [
            'events' => $calendarEvents,
            'year' => $year,
            'month' => $month,
            'employees' => $this->getAccessibleEmployees(),
        ]);
    }

    /**
     * Bulk approve leave requests
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $this->authorizeRole(['admin', 'hr', 'project_manager', 'site_manager']);

        $validated = $request->validate([
            'leave_request_ids' => 'required|array',
            'leave_request_ids.*' => 'exists:leave_requests,id',
            'approval_notes' => 'nullable|string|max:500',
        ]);

        $approvedCount = 0;
        $approver = Auth::user()->employee;

        foreach ($validated['leave_request_ids'] as $leaveRequestId) {
            $leaveRequest = LeaveRequest::find($leaveRequestId);
            
            if ($this->canApproveLeaveRequest($leaveRequest)) {
                $leaveRequest->approve($approver, $validated['approval_notes']);
                $approvedCount++;
            }
        }

        return back()->with('success', "{$approvedCount} izin talebi onaylandı.");
    }

    /**
     * Helper methods
     */
    private function getLeaveRequestStats(): array
    {
        $query = LeaveRequest::query();

        // Apply role-based filtering
        $user = Auth::user();
        if ($user->hasRole('employee')) {
            $query->where('employee_id', $user->employee_id);
        } elseif ($user->hasRole(['project_manager', 'site_manager', 'foreman'])) {
            $teamIds = $this->getTeamEmployeeIds();
            $query->whereIn('employee_id', $teamIds);
        }

        return [
            'total' => $query->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'approved' => $query->where('status', 'approved')->count(),
            'rejected' => $query->where('status', 'rejected')->count(),
            'current_leaves' => $query->where('status', 'approved')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->count(),
            'upcoming_leaves' => $query->where('status', 'approved')
                ->where('start_date', '>', now())
                ->count(),
            'urgent_requests' => $query->where('priority', 'urgent')
                ->where('status', 'pending')
                ->count(),
        ];
    }

    private function getLeaveTypes()
    {
        return \App\Models\LeaveType::active()
            ->select('id', 'name', 'code', 'description')
            ->orderBy('name')
            ->get();
    }

    private function calculateWorkingDays(Carbon $startDate, Carbon $endDate): int
    {
        $workingDays = 0;
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            if (!$current->isWeekend()) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        return $workingDays;
    }

    private function checkLeaveConflicts(int $employeeId, Carbon $startDate, Carbon $endDate, int $excludeId = null): array
    {
        $query = LeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($sq) use ($startDate, $endDate) {
                      $sq->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get()->map(function ($leave) {
            return [
                'id' => $leave->id,
                'type' => $leave->leave_type,
                'start_date' => $leave->start_date,
                'end_date' => $leave->end_date,
            ];
        })->toArray();
    }

    private function isLeaveTypePaid(string $leaveType): bool
    {
        $paidTypes = ['annual', 'sick', 'maternity', 'paternity', 'marriage', 'funeral'];
        return in_array($leaveType, $paidTypes);
    }

    private function getApproverForEmployee(Employee $employee): ?int
    {
        return $employee->manager?->id;
    }

    private function getLeaveTypeColor(string $leaveType): string
    {
        $colors = [
            'annual' => '#3B82F6',      // Blue
            'sick' => '#EF4444',        // Red
            'maternity' => '#EC4899',   // Pink
            'paternity' => '#8B5CF6',   // Purple
            'marriage' => '#F59E0B',    // Amber
            'funeral' => '#6B7280',     // Gray
            'military' => '#059669',    // Emerald
            'unpaid' => '#DC2626',      // Red
            'emergency' => '#DC2626',   // Red
            'study' => '#0891B2',       // Cyan
            'other' => '#6B7280',       // Gray
        ];

        return $colors[$leaveType] ?? '#6B7280';
    }

    private function getAccessibleEmployees()
    {
        $user = Auth::user();

        if ($user->hasRole(['admin', 'hr'])) {
            return Employee::active()->select('id', 'first_name', 'last_name', 'employee_code')->get();
        }

        if ($user->hasRole(['project_manager', 'site_manager', 'foreman'])) {
            $teamIds = $this->getTeamEmployeeIds();
            return Employee::whereIn('id', $teamIds)
                ->select('id', 'first_name', 'last_name', 'employee_code')
                ->get();
        }

        // Employees can only create for themselves
        return Employee::where('id', $user->employee_id)
            ->select('id', 'first_name', 'last_name', 'employee_code')
            ->get();
    }

    private function getTeamEmployeeIds(): array
    {
        $user = Auth::user();
        $teamIds = [$user->employee_id]; // Include self

        if ($user->hasRole(['project_manager', 'site_manager'])) {
            // Get employees from managed projects
            $managedProjects = \App\Models\Project::where('project_manager_id', $user->employee_id)
                ->orWhere('site_manager_id', $user->employee_id)
                ->pluck('id');
            
            $projectEmployees = Employee::whereIn('current_project_id', $managedProjects)->pluck('id');
            $teamIds = array_merge($teamIds, $projectEmployees->toArray());
        }

        if ($user->hasRole('foreman')) {
            // Get direct reports
            $directReports = Employee::where('manager_id', $user->employee_id)->pluck('id');
            $teamIds = array_merge($teamIds, $directReports->toArray());
        }

        return array_unique($teamIds);
    }

    private function authorizeLeaveRequestCreation(int $employeeId): void
    {
        $user = Auth::user();

        // Admin and HR can create for anyone
        if ($user->hasRole(['admin', 'hr'])) {
            return;
        }

        // Employees can only create for themselves
        if ($user->employee_id === $employeeId) {
            return;
        }

        // Managers can create for their team
        if ($user->hasRole(['project_manager', 'site_manager', 'foreman'])) {
            $teamIds = $this->getTeamEmployeeIds();
            if (in_array($employeeId, $teamIds)) {
                return;
            }
        }

        abort(403, 'Bu çalışan için izin talebi oluşturma yetkiniz bulunmamaktadır.');
    }

    private function authorizeLeaveRequestAccess(LeaveRequest $leaveRequest): void
    {
        $user = Auth::user();

        // Admin and HR can access all
        if ($user->hasRole(['admin', 'hr'])) {
            return;
        }

        // Employee can access their own
        if ($user->employee_id === $leaveRequest->employee_id) {
            return;
        }

        // Managers can access their team's requests
        if ($user->hasRole(['project_manager', 'site_manager', 'foreman'])) {
            $teamIds = $this->getTeamEmployeeIds();
            if (in_array($leaveRequest->employee_id, $teamIds)) {
                return;
            }
        }

        abort(403, 'Bu izin talebini görüntüleme yetkiniz bulunmamaktadır.');
    }

    private function authorizeLeaveRequestAction(LeaveRequest $leaveRequest): void
    {
        $user = Auth::user();

        // Admin and HR can perform actions on all
        if ($user->hasRole(['admin', 'hr'])) {
            return;
        }

        // Employee can act on their own pending requests
        if ($user->employee_id === $leaveRequest->employee_id && $leaveRequest->status === 'pending') {
            return;
        }

        abort(403, 'Bu izin talebi üzerinde işlem yapma yetkiniz bulunmamaktadır.');
    }

    private function canEditLeaveRequest(LeaveRequest $leaveRequest): bool
    {
        $user = Auth::user();

        // Can't edit approved or completed requests
        if (in_array($leaveRequest->status, ['approved', 'cancelled'])) {
            return false;
        }

        // Admin and HR can edit all
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }

        // Employee can edit their own pending requests
        return $user->employee_id === $leaveRequest->employee_id && $leaveRequest->status === 'pending';
    }

    private function canApproveLeaveRequest(LeaveRequest $leaveRequest): bool
    {
        $user = Auth::user();

        if ($leaveRequest->status !== 'pending') {
            return false;
        }

        // Admin and HR can approve all
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }

        // Check if user is the designated approver
        return $leaveRequest->approver_id === $user->employee_id;
    }

    private function canCancelLeaveRequest(LeaveRequest $leaveRequest): bool
    {
        $user = Auth::user();

        // Can't cancel rejected requests
        if ($leaveRequest->status === 'rejected') {
            return false;
        }

        // Admin and HR can cancel all
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }

        // Employee can cancel their own pending requests
        if ($user->employee_id === $leaveRequest->employee_id && $leaveRequest->status === 'pending') {
            return true;
        }

        // Managers can cancel approved requests if leave hasn't started
        if ($user->hasRole(['project_manager', 'site_manager']) && 
            $leaveRequest->status === 'approved' && 
            $leaveRequest->start_date > now()) {
            return $leaveRequest->approver_id === $user->employee_id;
        }

        return false;
    }
}