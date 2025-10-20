<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Department;
use App\Models\TimesheetApproval;
use App\Models\TimesheetRevision;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class TimesheetController extends Controller
{
    /**
     * Display a listing of timesheets
     */
    public function index(Request $request): Response
    {
        $query = Timesheet::with(['employee', 'project', 'department', 'enteredBy']);

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('work_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('month')) {
            $date = Carbon::parse($request->month . '-01');
            $query->whereMonth('work_date', $date->month)
                  ->whereYear('work_date', $date->year);
        } else {
            // Default to current month
            $query->whereMonth('work_date', now()->month)
                  ->whereYear('work_date', now()->year);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by approval status
        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        // Filter by attendance type
        if ($request->filled('attendance_type')) {
            $query->where('attendance_type', $request->attendance_type);
        }

        // Special filters
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'pending_approval':
                    $query->where('approval_status', 'pending');
                    break;
                case 'overtime':
                    $query->where('overtime_minutes', '>', 0);
                    break;
                case 'late':
                    $query->where('attendance_type', 'late');
                    break;
                case 'weekend':
                    $query->where('shift_type', 'weekend');
                    break;
                case 'revised':
                    $query->where('is_revised', true);
                    break;
            }
        }

        // Role-based filtering
        $user = Auth::user();
        if ($user->hasRole('foreman')) {
            // Foreman can only see their team's timesheets
            $teamIds = Employee::where('manager_id', $user->employee_id)->pluck('id');
            $query->whereIn('employee_id', $teamIds);
        } elseif ($user->hasRole('employee')) {
            // Employees can only see their own timesheets
            $query->where('employee_id', $user->employee_id);
        }

        // Sorting
        $sortField = $request->get('sort', 'work_date');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $timesheets = $query->paginate(20)->withQueryString();

        // Get filter options
        $employees = Employee::active()->select('id', 'first_name', 'last_name')->get();
        $projects = Project::active()->select('id', 'name')->get();
        $departments = Department::select('id', 'name', 'project_id')->get();

        // Calculate summary statistics
        $stats = $this->getTimesheetStats($request);

        return Inertia::render('Timesheets/Index', [
            'timesheets' => $timesheets,
            'employees' => $employees,
            'projects' => $projects,
            'departments' => $departments,
            'filters' => $request->only([
                'start_date', 'end_date', 'month', 'employee_id', 'project_id',
                'department_id', 'approval_status', 'attendance_type', 'filter'
            ]),
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new timesheet
     */
    public function create(Request $request): Response
    {
        $employees = Employee::active()->select('id', 'first_name', 'last_name', 'employee_code')->get();
        $projects = Project::active()->select('id', 'name')->get();
        $departments = Department::select('id', 'name', 'project_id')->get();

        // Pre-fill with URL parameters
        $defaults = [
            'employee_id' => $request->employee_id,
            'project_id' => $request->project_id,
            'work_date' => $request->work_date ?? today()->toDateString(),
        ];

        return Inertia::render('Timesheets/Create', [
            'employees' => $employees,
            'projects' => $projects,
            'departments' => $departments,
            'defaults' => $defaults,
        ]);
    }

    /**
     * Store a newly created timesheet
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'project_id' => 'required|exists:projects,id',
            'department_id' => 'nullable|exists:departments,id',
            'work_date' => 'required|date|before_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'attendance_type' => 'required|in:present,absent,late,early_leave,sick_leave,annual_leave,excuse_leave,unpaid_leave',
            'entry_method' => 'required|in:manual,qr_code,biometric',
            'notes' => 'nullable|string|max:1000',
            'absence_reason' => 'nullable|string|max:500',
            'late_reason' => 'nullable|string|max:500',
        ]);

        // Check for duplicate timesheet
        $existing = Timesheet::where('employee_id', $validated['employee_id'])
            ->where('work_date', $validated['work_date'])
            ->first();

        if ($existing) {
            return back()->withErrors(['work_date' => 'Bu çalışan için bu tarihte zaten puantaj kaydı bulunmaktadır.']);
        }

        // Get employee wage information
        $employee = Employee::find($validated['employee_id']);
        $validated['daily_rate'] = $employee->daily_wage;
        $validated['hourly_rate'] = $employee->hourly_wage;

        // Set entry information
        $validated['entered_by'] = Auth::id();
        $validated['entered_at'] = now();
        $validated['approval_status'] = 'draft';

        // Create timesheet
        $timesheet = Timesheet::create($validated);

        // Calculate working time and wage
        $timesheet->calculateWorkingTime();
        $timesheet->calculateWage();
        $timesheet->save();

        return redirect()->route('timesheets.show', $timesheet)
            ->with('success', 'Puantaj kaydı başarıyla oluşturuldu.');
    }

    /**
     * Display the specified timesheet
     */
    public function show(Timesheet $timesheet): Response
    {
        $timesheet->load([
            'employee',
            'project',
            'department',
            'enteredBy',
            'approvals.approver',
            'revisions.revisedBy'
        ]);

        // Check if user can view this timesheet
        $this->authorizeViewTimesheet($timesheet);

        return Inertia::render('Timesheets/Show', [
            'timesheet' => $timesheet,
            'can_edit' => $this->canEditTimesheet($timesheet),
            'can_approve' => $this->canApproveTimesheet($timesheet),
        ]);
    }

    /**
     * Show the form for editing the specified timesheet
     */
    public function edit(Timesheet $timesheet): Response
    {
        // Check if timesheet can be edited
        if (!$timesheet->is_editable) {
            return redirect()->route('timesheets.show', $timesheet)
                ->with('error', 'Bu puantaj kaydı düzenlenemez.');
        }

        $this->authorizeEditTimesheet($timesheet);

        $timesheet->load(['employee', 'project', 'department']);
        
        $employees = Employee::active()->select('id', 'first_name', 'last_name', 'employee_code')->get();
        $projects = Project::active()->select('id', 'name')->get();
        $departments = Department::select('id', 'name', 'project_id')->get();

        return Inertia::render('Timesheets/Edit', [
            'timesheet' => $timesheet,
            'employees' => $employees,
            'projects' => $projects,
            'departments' => $departments,
        ]);
    }

    /**
     * Update the specified timesheet
     */
    public function update(Request $request, Timesheet $timesheet): RedirectResponse
    {
        if (!$timesheet->is_editable) {
            return back()->with('error', 'Bu puantaj kaydı düzenlenemez.');
        }

        $this->authorizeEditTimesheet($timesheet);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'department_id' => 'nullable|exists:departments,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'attendance_type' => 'required|in:present,absent,late,early_leave,sick_leave,annual_leave,excuse_leave,unpaid_leave',
            'notes' => 'nullable|string|max:1000',
            'absence_reason' => 'nullable|string|max:500',
            'late_reason' => 'nullable|string|max:500',
        ]);

        // Store old values for revision tracking
        $oldValues = $timesheet->getOriginal();

        $timesheet->update($validated);

        // Recalculate working time and wage
        $timesheet->calculateWorkingTime();
        $timesheet->calculateWage();
        $timesheet->save();

        // Create revision record if significant changes were made
        if ($this->hasSignificantChanges($oldValues, $timesheet->getAttributes())) {
            $timesheet->revisions()->create([
                'revised_by' => Auth::user()->employee_id,
                'revision_number' => $timesheet->revision_count + 1,
                'revision_type' => 'modification',
                'revision_reason' => 'Manuel güncelleme',
                'reason_category' => 'data_correction',
                'changes_made' => $this->getChangedFields($oldValues, $timesheet->getAttributes()),
                'old_values' => $oldValues,
                'new_values' => $timesheet->getAttributes(),
                'requested_at' => now(),
                'status' => 'approved',
                'approved_by' => Auth::user()->employee_id,
                'approved_at' => now(),
            ]);

            $timesheet->increment('revision_count');
            $timesheet->update(['is_revised' => true]);
        }

        // Reset approval status if timesheet was previously approved
        if ($timesheet->approval_status === 'approved') {
            $timesheet->update(['approval_status' => 'pending']);
        }

        return redirect()->route('timesheets.show', $timesheet)
            ->with('success', 'Puantaj kaydı başarıyla güncellendi.');
    }

    /**
     * Remove the specified timesheet
     */
    public function destroy(Timesheet $timesheet): RedirectResponse
    {
        // Only allow deletion of draft timesheets
        if ($timesheet->approval_status !== 'draft') {
            return back()->with('error', 'Sadece taslak durumundaki puantaj kayıtları silinebilir.');
        }

        $this->authorizeEditTimesheet($timesheet);

        $timesheet->delete();

        return redirect()->route('timesheets.index')
            ->with('success', 'Puantaj kaydı başarıyla silindi.');
    }

    /**
     * Submit timesheet for approval
     */
    public function submitForApproval(Timesheet $timesheet): RedirectResponse
    {
        if (!in_array($timesheet->approval_status, ['draft', 'rejected'])) {
            return back()->with('error', 'Bu puantaj kaydı zaten onaya gönderilmiş.');
        }

        $timesheet->submitForApproval();

        return back()->with('success', 'Puantaj kaydı onaya gönderildi.');
    }

    /**
     * Approve timesheet
     */
    public function approve(Request $request, Timesheet $timesheet): RedirectResponse
    {
        if (!$this->canApproveTimesheet($timesheet)) {
            return back()->with('error', 'Bu puantaj kaydını onaylama yetkiniz bulunmamaktadır.');
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:500',
        ]);

        $approval = $timesheet->approvals()
            ->where('approver_id', Auth::user()->employee_id)
            ->where('status', 'pending')
            ->first();

        if (!$approval) {
            return back()->with('error', 'Onay kaydı bulunamadı.');
        }

        $approval->approve($validated['approval_notes']);

        return back()->with('success', 'Puantaj kaydı başarıyla onaylandı.');
    }

    /**
     * Reject timesheet
     */
    public function reject(Request $request, Timesheet $timesheet): RedirectResponse
    {
        if (!$this->canApproveTimesheet($timesheet)) {
            return back()->with('error', 'Bu puantaj kaydını reddetme yetkiniz bulunmamaktadır.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $approval = $timesheet->approvals()
            ->where('approver_id', Auth::user()->employee_id)
            ->where('status', 'pending')
            ->first();

        if (!$approval) {
            return back()->with('error', 'Onay kaydı bulunamadı.');
        }

        $approval->reject($validated['rejection_reason']);

        return back()->with('success', 'Puantaj kaydı reddedildi.');
    }

    /**
     * Bulk approve timesheets
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'timesheet_ids' => 'required|array',
            'timesheet_ids.*' => 'exists:timesheets,id',
            'approval_notes' => 'nullable|string|max:500',
        ]);

        $approvedCount = 0;
        $user = Auth::user();

        foreach ($validated['timesheet_ids'] as $timesheetId) {
            $timesheet = Timesheet::find($timesheetId);
            
            if ($this->canApproveTimesheet($timesheet)) {
                $approval = $timesheet->approvals()
                    ->where('approver_id', $user->employee_id)
                    ->where('status', 'pending')
                    ->first();

                if ($approval) {
                    $approval->approve($validated['approval_notes']);
                    $approvedCount++;
                }
            }
        }

        return back()->with('success', "{$approvedCount} puantaj kaydı onaylandı.");
    }

    /**
     * Create QR code entry
     */
    public function qrEntry(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'qr_data' => 'required|string',
            'entry_type' => 'required|in:check_in,check_out',
            'location' => 'nullable|string',
        ]);

        // Decode QR data
        $qrData = json_decode($validated['qr_data'], true);
        
        if (!$qrData || !isset($qrData['employee_id'])) {
            return back()->with('error', 'Geçersiz QR kod.');
        }

        $employee = Employee::find($qrData['employee_id']);
        if (!$employee) {
            return back()->with('error', 'Çalışan bulunamadı.');
        }

        $today = today();
        $timesheet = Timesheet::where('employee_id', $employee->id)
            ->where('work_date', $today)
            ->first();

        if ($validated['entry_type'] === 'check_in') {
            // Create new timesheet for check-in
            if ($timesheet) {
                return back()->with('error', 'Bu çalışan için bugün zaten giriş kaydı bulunmaktadır.');
            }

            $timesheet = Timesheet::create([
                'employee_id' => $employee->id,
                'project_id' => $employee->current_project_id,
                'work_date' => $today,
                'start_time' => now()->format('H:i:s'),
                'attendance_type' => 'present',
                'entry_method' => 'qr_code',
                'entry_location' => $validated['location'],
                'entered_by' => Auth::id(),
                'entered_at' => now(),
                'daily_rate' => $employee->daily_wage,
                'hourly_rate' => $employee->hourly_wage,
                'approval_status' => 'draft',
            ]);

            return back()->with('success', "{$employee->full_name} başarıyla giriş yaptı.");

        } else {
            // Update timesheet for check-out
            if (!$timesheet) {
                return back()->with('error', 'Bu çalışan için giriş kaydı bulunamadı.');
            }

            if ($timesheet->end_time) {
                return back()->with('error', 'Bu çalışan için çıkış kaydı zaten bulunmaktadır.');
            }

            $timesheet->update([
                'end_time' => now()->format('H:i:s'),
                'exit_location' => $validated['location'],
            ]);

            // Calculate working time and wage
            $timesheet->calculateWorkingTime();
            $timesheet->calculateWage();
            $timesheet->save();

            return back()->with('success', "{$employee->full_name} başarıyla çıkış yaptı.");
        }
    }

    /**
     * Get timesheet statistics
     */
    private function getTimesheetStats(Request $request): array
    {
        $query = Timesheet::query();

        // Apply same filters as main query
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('work_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('month')) {
            $date = Carbon::parse($request->month . '-01');
            $query->whereMonth('work_date', $date->month)
                  ->whereYear('work_date', $date->year);
        } else {
            $query->whereMonth('work_date', now()->month)
                  ->whereYear('work_date', now()->year);
        }

        return [
            'total_timesheets' => $query->count(),
            'pending_approval' => $query->where('approval_status', 'pending')->count(),
            'approved' => $query->where('approval_status', 'approved')->count(),
            'rejected' => $query->where('approval_status', 'rejected')->count(),
            'total_hours' => round($query->sum('total_minutes') / 60, 1),
            'total_overtime' => round($query->sum('overtime_minutes') / 60, 1),
            'total_wages' => $query->where('approval_status', 'approved')->sum('calculated_wage'),
            'avg_daily_hours' => round($query->avg('total_minutes') / 60, 1),
        ];
    }

    /**
     * Generate timesheet report
     */
    public function report(Request $request): Response
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'employee_id' => 'nullable|exists:employees,id',
            'project_id' => 'nullable|exists:projects,id',
            'department_id' => 'nullable|exists:departments,id',
            'report_type' => 'required|in:summary,detailed,attendance,overtime',
        ]);

        $query = Timesheet::with(['employee', 'project', 'department'])
            ->whereBetween('work_date', [$validated['start_date'], $validated['end_date']]);

        if ($validated['employee_id']) {
            $query->where('employee_id', $validated['employee_id']);
        }

        if ($validated['project_id']) {
            $query->where('project_id', $validated['project_id']);
        }

        if ($validated['department_id']) {
            $query->where('department_id', $validated['department_id']);
        }

        $timesheets = $query->get();

        $reportData = $this->generateReportData($timesheets, $validated['report_type']);

        return Inertia::render('Timesheets/Report', [
            'report_data' => $reportData,
            'filters' => $validated,
            'employees' => Employee::active()->select('id', 'first_name', 'last_name')->get(),
            'projects' => Project::active()->select('id', 'name')->get(),
            'departments' => Department::select('id', 'name')->get(),
        ]);
    }

    /**
     * Show bulk entry page for monthly timesheet entry
     */
    public function bulkEntry(Request $request): Response
    {
        $projects = Project::active()->select('id', 'name')->get();
        $departments = Department::select('id', 'name', 'project_id')->get();

        $employees = [];
        $existingTimesheets = [];

        if ($request->filled('month') && $request->filled('project_id')) {
            // Get employees assigned to this project
            $projectId = $request->project_id;
            $month = $request->month;
            $date = Carbon::parse($month . '-01');

            $employeeQuery = Employee::active()
                ->whereHas('projectAssignments', function ($q) use ($projectId, $date) {
                    $q->where('project_id', $projectId)
                      ->where('status', 'active')
                      ->where('start_date', '<=', $date->copy()->endOfMonth())
                      ->where(function ($subQ) use ($date) {
                          $subQ->whereNull('end_date')
                               ->orWhere('end_date', '>=', $date->copy()->startOfMonth());
                      });
                });

            if ($request->filled('department_id')) {
                $employeeQuery->where('department_id', $request->department_id);
            }

            $employees = $employeeQuery
                ->with(['projectAssignments' => function ($q) use ($projectId) {
                    $q->where('project_id', $projectId)
                      ->where('status', 'active')
                      ->select('employee_id', 'project_id', 'start_date', 'end_date');
                }])
                ->select('id', 'first_name', 'last_name', 'employee_code')
                ->get()
                ->map(function ($employee) {
                    // Get assignment dates for this project
                    $assignment = $employee->projectAssignments->first();
                    return [
                        'id' => $employee->id,
                        'first_name' => $employee->first_name,
                        'last_name' => $employee->last_name,
                        'employee_code' => $employee->employee_code,
                        'assignment_start_date' => $assignment ? $assignment->start_date : null,
                        'assignment_end_date' => $assignment ? $assignment->end_date : null,
                    ];
                });

            // Get existing timesheets for this month/project (only draft/pending, not approved)
            $existingTimesheets = Timesheet::where('project_id', $projectId)
                ->whereMonth('work_date', $date->month)
                ->whereYear('work_date', $date->year)
                ->whereIn('employee_id', $employees->pluck('id'))
                ->whereIn('approval_status', ['draft', 'pending'])
                ->select('employee_id', 'work_date', 'attendance_type', 'overtime_hours', 'overtime_type')
                ->get();

            // Get approved timesheets separately (read-only, to show which dates are locked)
            $approvedTimesheets = Timesheet::where('project_id', $projectId)
                ->whereMonth('work_date', $date->month)
                ->whereYear('work_date', $date->year)
                ->whereIn('employee_id', $employees->pluck('id'))
                ->where('approval_status', 'approved')
                ->select('employee_id', 'work_date', 'attendance_type')
                ->get();
        }

        return Inertia::render('Timesheets/BulkEntry', [
            'projects' => $projects,
            'departments' => $departments,
            'employees' => $employees,
            'existingTimesheets' => $existingTimesheets,
            'approvedTimesheets' => $approvedTimesheets ?? [],
            'month' => $request->month,
            'projectId' => $request->project_id ? (int) $request->project_id : null,
            'departmentId' => $request->department_id ? (int) $request->department_id : null,
        ]);
    }

    /**
     * Store bulk timesheets
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        \Log::info('🔵 BulkStore started', [
            'total_timesheets' => count($request->timesheets ?? []),
            'month' => $request->month,
            'project_id' => $request->project_id,
        ]);

        $validated = $request->validate([
            'timesheets' => 'required|array',
            'timesheets.*.employee_id' => 'required|exists:employees,id',
            'timesheets.*.project_id' => 'required|exists:projects,id',
            'timesheets.*.department_id' => 'nullable|exists:departments,id',
            'timesheets.*.work_date' => 'required|date',
            'timesheets.*.attendance_type' => 'required|in:present,absent,late,early_leave,sick,leave',
            'timesheets.*.entry_method' => 'required|in:manual,qr_code,biometric',
            'timesheets.*.overtime_hours' => 'nullable|numeric|min:0|max:24',
            'timesheets.*.overtime_type' => 'nullable|in:weekday,weekend,holiday',
            'month' => 'required|string',
            'project_id' => 'required|exists:projects,id',
        ]);

        $successCount = 0;
        $updateCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();

        try {
            foreach ($validated['timesheets'] as $index => $timesheetData) {
                // Get employee name for logging
                $employee = Employee::find($timesheetData['employee_id']);
                $employeeName = $employee ? $employee->first_name . ' ' . $employee->last_name : 'Unknown';

                // Check if timesheet already exists
                $existing = Timesheet::where('employee_id', $timesheetData['employee_id'])
                    ->where('work_date', $timesheetData['work_date'])
                    ->first();

                if ($existing) {
                    // Update existing timesheet if not approved
                    if ($existing->approval_status !== 'approved') {
                        \Log::info("🟡 Updating existing timesheet #{$index}", [
                            'id' => $existing->id,
                            'employee' => $employeeName,
                            'employee_id' => $timesheetData['employee_id'],
                            'work_date' => $timesheetData['work_date'],
                            'old_attendance_type' => $existing->attendance_type,
                            'new_attendance_type' => $timesheetData['attendance_type'],
                            'old_overtime' => $existing->overtime_hours,
                            'new_overtime' => $timesheetData['overtime_hours'] ?? null,
                        ]);

                        // Use fill() and save() to ensure changes persist properly
                        $existing->fill([
                            'attendance_type' => $timesheetData['attendance_type'],
                            'project_id' => $timesheetData['project_id'],
                            'department_id' => $timesheetData['department_id'] ?? null,
                            'overtime_hours' => $timesheetData['overtime_hours'] ?? null,
                            'overtime_type' => $timesheetData['overtime_type'] ?? null,
                            'entry_method' => $timesheetData['entry_method'],
                            'entered_by' => Auth::id(),
                            'entered_at' => now(),
                        ]);

                        // Save and verify the changes were persisted
                        $saveResult = $existing->save();

                        if ($saveResult) {
                            // Refresh the model to verify database changes
                            $existing->refresh();

                            \Log::info("✅ Successfully updated timesheet #{$index}", [
                                'id' => $existing->id,
                                'verified_attendance_type' => $existing->attendance_type,
                                'verified_overtime' => $existing->overtime_hours,
                            ]);

                            $updateCount++;
                        } else {
                            \Log::error("❌ Failed to update timesheet #{$index}", [
                                'id' => $existing->id,
                                'employee' => $employeeName,
                            ]);
                        }
                    } else {
                        \Log::warning("⚠️ Skipping approved timesheet #{$index}", [
                            'id' => $existing->id,
                            'employee' => $employeeName,
                            'employee_id' => $timesheetData['employee_id'],
                            'work_date' => $timesheetData['work_date'],
                            'approval_status' => $existing->approval_status,
                        ]);
                        $skippedCount++;
                    }
                } else {
                    // Create new timesheet
                    \Log::info("🟢 Creating new timesheet #{$index}", [
                        'employee' => $employeeName,
                        'employee_id' => $timesheetData['employee_id'],
                        'work_date' => $timesheetData['work_date'],
                        'attendance_type' => $timesheetData['attendance_type'],
                        'overtime_hours' => $timesheetData['overtime_hours'] ?? null,
                        'overtime_type' => $timesheetData['overtime_type'] ?? null,
                    ]);

                    $timesheet = Timesheet::create([
                        'employee_id' => $timesheetData['employee_id'],
                        'project_id' => $timesheetData['project_id'],
                        'department_id' => $timesheetData['department_id'] ?? null,
                        'work_date' => $timesheetData['work_date'],
                        'attendance_type' => $timesheetData['attendance_type'],
                        'entry_method' => $timesheetData['entry_method'],
                        'overtime_hours' => $timesheetData['overtime_hours'] ?? null,
                        'overtime_type' => $timesheetData['overtime_type'] ?? null,
                        'entered_by' => Auth::id(),
                        'entered_at' => now(),
                        'daily_rate' => $employee->daily_wage,
                        'hourly_rate' => $employee->hourly_wage,
                        'approval_status' => 'draft',
                        // Set default working hours for 'present' attendance
                        'start_time' => $timesheetData['attendance_type'] === 'present' ? '08:00:00' : null,
                        'end_time' => $timesheetData['attendance_type'] === 'present' ? '17:00:00' : null,
                    ]);

                    \Log::info("✅ Created timesheet with ID: {$timesheet->id}");

                    // Calculate working time and wage if present
                    if ($timesheetData['attendance_type'] === 'present') {
                        $timesheet->calculateWorkingTime();
                        $timesheet->calculateWage();
                        $timesheet->save();
                    }

                    $successCount++;
                }
            }

            DB::commit();

            \Log::info('✅ BulkStore completed successfully', [
                'new_records' => $successCount,
                'updated_records' => $updateCount,
                'skipped_records' => $skippedCount,
                'total_processed' => count($validated['timesheets']),
            ]);

            $message = "Toplam {$successCount} yeni kayıt oluşturuldu";
            if ($updateCount > 0) {
                $message .= ", {$updateCount} kayıt güncellendi";
            }
            $message .= ".";

            return redirect()->route('timesheets.bulk-entry', [
                'month' => $validated['month'],
                'project_id' => $validated['project_id']
            ])->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Toplu kayıt sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Export timesheets to Excel/CSV
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'employee_id' => 'nullable|exists:employees,id',
            'project_id' => 'nullable|exists:projects,id',
            'format' => 'required|in:csv,xlsx',
        ]);

        $query = Timesheet::with(['employee', 'project', 'department'])
            ->whereBetween('work_date', [$validated['start_date'], $validated['end_date']]);

        if ($validated['employee_id']) {
            $query->where('employee_id', $validated['employee_id']);
        }

        if ($validated['project_id']) {
            $query->where('project_id', $validated['project_id']);
        }

        $timesheets = $query->orderBy('work_date')->get();

        if ($validated['format'] === 'csv') {
            return $this->exportToCsv($timesheets);
        }

        // For XLSX, you would typically use Laravel Excel package
        return back()->with('error', 'Excel export şu anda desteklenmiyor.');
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($timesheets)
    {
        $csvData = [];
        $csvData[] = [
            'Tarih', 'Çalışan', 'Proje', 'Departman', 'Giriş', 'Çıkış',
            'Toplam Saat', 'Fazla Mesai', 'Devam Durumu', 'Onay Durumu', 'Ücret'
        ];

        foreach ($timesheets as $timesheet) {
            $csvData[] = [
                $timesheet->work_date->format('d.m.Y'),
                $timesheet->employee->full_name,
                $timesheet->project?->name,
                $timesheet->department?->name,
                $timesheet->start_time?->format('H:i'),
                $timesheet->end_time?->format('H:i'),
                $timesheet->total_hours,
                $timesheet->overtime_hours,
                $timesheet->attendance_type_display,
                $timesheet->approval_status_display,
                number_format($timesheet->calculated_wage, 2),
            ];
        }

        $filename = 'puantaj_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper Excel display
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            foreach ($csvData as $row) {
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate report data based on type
     */
    private function generateReportData($timesheets, $reportType): array
    {
        switch ($reportType) {
            case 'summary':
                return $this->generateSummaryReport($timesheets);
            case 'detailed':
                return $this->generateDetailedReport($timesheets);
            case 'attendance':
                return $this->generateAttendanceReport($timesheets);
            case 'overtime':
                return $this->generateOvertimeReport($timesheets);
            default:
                return [];
        }
    }

    /**
     * Generate summary report
     */
    private function generateSummaryReport($timesheets): array
    {
        $summary = $timesheets->groupBy('employee_id')->map(function ($employeeTimesheets) {
            $employee = $employeeTimesheets->first()->employee;
            return [
                'employee_name' => $employee->full_name,
                'employee_code' => $employee->employee_code,
                'total_days' => $employeeTimesheets->count(),
                'present_days' => $employeeTimesheets->where('attendance_type', 'present')->count(),
                'total_hours' => round($employeeTimesheets->sum('total_minutes') / 60, 1),
                'overtime_hours' => round($employeeTimesheets->sum('overtime_minutes') / 60, 1),
                'total_wages' => $employeeTimesheets->where('approval_status', 'approved')->sum('calculated_wage'),
                'attendance_rate' => round(($employeeTimesheets->where('attendance_type', 'present')->count() / $employeeTimesheets->count()) * 100, 1),
            ];
        })->values();

        return [
            'type' => 'summary',
            'data' => $summary,
            'totals' => [
                'total_employees' => $summary->count(),
                'total_hours' => $summary->sum('total_hours'),
                'total_overtime' => $summary->sum('overtime_hours'),
                'total_wages' => $summary->sum('total_wages'),
            ]
        ];
    }

    /**
     * Generate detailed report
     */
    private function generateDetailedReport($timesheets): array
    {
        return [
            'type' => 'detailed',
            'data' => $timesheets->map(function ($timesheet) {
                return [
                    'work_date' => $timesheet->work_date->format('d.m.Y'),
                    'employee_name' => $timesheet->employee->full_name,
                    'project_name' => $timesheet->project?->name,
                    'department_name' => $timesheet->department?->name,
                    'start_time' => $timesheet->start_time?->format('H:i'),
                    'end_time' => $timesheet->end_time?->format('H:i'),
                    'total_hours' => $timesheet->total_hours,
                    'overtime_hours' => $timesheet->overtime_hours,
                    'attendance_type' => $timesheet->attendance_type_display,
                    'approval_status' => $timesheet->approval_status_display,
                    'calculated_wage' => $timesheet->calculated_wage,
                    'notes' => $timesheet->notes,
                ];
            })
        ];
    }

    /**
     * Generate attendance report
     */
    private function generateAttendanceReport($timesheets): array
    {
        $attendanceStats = $timesheets->groupBy('attendance_type')->map(function ($group, $type) {
            return [
                'type' => $type,
                'count' => $group->count(),
                'percentage' => 0, // Will be calculated below
            ];
        });

        $total = $timesheets->count();
        if ($total > 0) {
            $attendanceStats = $attendanceStats->map(function ($stat) use ($total) {
                $stat['percentage'] = round(($stat['count'] / $total) * 100, 1);
                return $stat;
            });
        }

        return [
            'type' => 'attendance',
            'data' => $attendanceStats->values(),
            'total_records' => $total,
        ];
    }

    /**
     * Generate overtime report
     */
    private function generateOvertimeReport($timesheets): array
    {
        $overtimeData = $timesheets->filter(function ($timesheet) {
            return $timesheet->overtime_minutes > 0;
        })->groupBy('employee_id')->map(function ($employeeTimesheets) {
            $employee = $employeeTimesheets->first()->employee;
            return [
                'employee_name' => $employee->full_name,
                'total_overtime_hours' => round($employeeTimesheets->sum('overtime_minutes') / 60, 1),
                'overtime_days' => $employeeTimesheets->count(),
                'avg_overtime_per_day' => round($employeeTimesheets->avg('overtime_minutes') / 60, 1),
                'overtime_wages' => $employeeTimesheets->sum(function ($timesheet) {
                    return ($timesheet->overtime_minutes / 60) * $timesheet->hourly_rate * 1.5;
                }),
            ];
        })->values();

        return [
            'type' => 'overtime',
            'data' => $overtimeData,
            'totals' => [
                'total_overtime_hours' => $overtimeData->sum('total_overtime_hours'),
                'total_overtime_wages' => $overtimeData->sum('overtime_wages'),
                'employees_with_overtime' => $overtimeData->count(),
            ]
        ];
    }

    /**
     * Authorization and helper methods
     */
    private function authorizeViewTimesheet(Timesheet $timesheet): void
    {
        $user = Auth::user();
        
        if ($user->hasRole(['admin', 'hr'])) {
            return; // Admin and HR can view all timesheets
        }
        
        if ($user->hasRole(['project_manager', 'site_manager'])) {
            // Managers can view timesheets from their projects
            $managedProjects = Project::where('project_manager_id', $user->employee_id)
                ->orWhere('site_manager_id', $user->employee_id)
                ->pluck('id');
            
            if ($managedProjects->contains($timesheet->project_id)) {
                return;
            }
        }
        
        if ($user->hasRole('foreman')) {
            // Foremen can view their team's timesheets
            $teamIds = Employee::where('manager_id', $user->employee_id)->pluck('id');
            if ($teamIds->contains($timesheet->employee_id)) {
                return;
            }
        }
        
        if ($user->employee_id === $timesheet->employee_id) {
            return; // Employees can view their own timesheets
        }
        
        abort(403, 'Bu puantaj kaydını görüntüleme yetkiniz bulunmamaktadır.');
    }

    private function authorizeEditTimesheet(Timesheet $timesheet): void
    {
        $user = Auth::user();
        
        if ($user->hasRole(['admin', 'hr'])) {
            return;
        }
        
        if ($user->employee_id === $timesheet->employee_id) {
            return;
        }
        
        if ($user->hasRole('foreman')) {
            $teamIds = Employee::where('manager_id', $user->employee_id)->pluck('id');
            if ($teamIds->contains($timesheet->employee_id)) {
                return;
            }
        }
        
        abort(403, 'Bu puantaj kaydını düzenleme yetkiniz bulunmamaktadır.');
    }

    private function canEditTimesheet(Timesheet $timesheet): bool
    {
        $user = Auth::user();
        
        if (!$timesheet->is_editable) {
            return false;
        }
        
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }
        
        if ($user->employee_id === $timesheet->employee_id) {
            return true;
        }
        
        if ($user->hasRole('foreman')) {
            $teamIds = Employee::where('manager_id', $user->employee_id)->pluck('id');
            return $teamIds->contains($timesheet->employee_id);
        }
        
        return false;
    }

    private function canApproveTimesheet(Timesheet $timesheet): bool
    {
        $user = Auth::user();
        
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }
        
        if ($timesheet->approval_status !== 'pending') {
            return false;
        }
        
        // Check if user has pending approval for this timesheet
        return $timesheet->approvals()
            ->where('approver_id', $user->employee_id)
            ->where('status', 'pending')
            ->exists();
    }

    private function hasSignificantChanges(array $oldValues, array $newValues): bool
    {
        $significantFields = [
            'start_time', 'end_time', 'break_start', 'break_end',
            'attendance_type', 'project_id', 'department_id'
        ];
        
        foreach ($significantFields as $field) {
            if (isset($oldValues[$field]) && isset($newValues[$field])) {
                if ($oldValues[$field] !== $newValues[$field]) {
                    return true;
                }
            }
        }
        
        return false;
    }

    private function getChangedFields(array $oldValues, array $newValues): array
    {
        $changes = [];
        
        foreach ($newValues as $field => $newValue) {
            if (isset($oldValues[$field]) && $oldValues[$field] !== $newValue) {
                $changes[$field] = [
                    'old' => $oldValues[$field],
                    'new' => $newValue,
                ];
            }
        }
        
        return $changes;
    }
}