<?php

namespace App\Http\Controllers;

use App\Models\TimesheetV2;
use App\Models\Shift;
use App\Models\Employee;
use App\Models\Project;
use App\Models\TimesheetCarryover;
use App\Services\TimesheetCalculatorService;
use App\Services\Timesheet\TimesheetApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class TimesheetV2Controller extends Controller
{
    protected TimesheetCalculatorService $calculator;

    public function __construct(TimesheetCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Puantaj listesi
     */
    public function index(Request $request)
    {
        $query = TimesheetV2::with(['employee', 'project', 'shift']);

        // Filtreler
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('date_from')) {
            $query->where('work_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('work_date', '<=', $request->date_to);
        }

        $timesheets = $query->orderBy('work_date', 'desc')
            ->paginate(50);

        return Inertia::render('Timesheets/Index', [
            'timesheets' => $timesheets,
            'filters' => $request->only(['employee_id', 'project_id', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Yeni puantaj formu
     */
    public function create()
    {
        return Inertia::render('Timesheets/Create', [
            'employees' => Employee::active()->orderBy('name')->get(['id', 'name']),
            'projects' => Project::active()->orderBy('name')->get(['id', 'name']),
            'shifts' => Shift::active()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Puantaj kaydet
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'project_id' => 'required|exists:projects,id',
            'shift_id' => 'required|exists:shifts,id',
            'work_date' => 'required|date',
            'hours_worked' => 'required|numeric|min:0|max:24',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'break_duration' => 'nullable|numeric|min:0|max:8',
            'notes' => 'nullable|string|max:500',
        ]);

        TimesheetV2::create($validated);

        return redirect()
            ->route('timesheets-v2.index')
            ->with('success', 'Puantaj kaydı oluşturuldu.');
    }

    /**
     * Puantaj detayı
     */
    public function show(TimesheetV2 $timesheet)
    {
        $timesheet->load(['employee', 'project', 'shift', 'adjustments', 'approvedBy']);

        return Inertia::render('Timesheets/Show', [
            'timesheet' => $timesheet,
        ]);
    }

    /**
     * Düzenleme formu
     */
    public function edit(TimesheetV2 $timesheet)
    {
        if (!$timesheet->isEditable()) {
            return redirect()
                ->route('timesheets.show', $timesheet)
                ->with('error', 'Bu kayıt kilitli veya onaylanmış, düzenlenemez.');
        }

        return Inertia::render('Timesheets/Edit', [
            'timesheet' => $timesheet->load(['employee', 'project', 'shift']),
            'employees' => Employee::active()->orderBy('name')->get(['id', 'name']),
            'projects' => Project::active()->orderBy('name')->get(['id', 'name']),
            'shifts' => Shift::active()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Güncelle
     */
    public function update(Request $request, TimesheetV2 $timesheet)
    {
        if (!$timesheet->isEditable()) {
            return back()->with('error', 'Bu kayıt düzenlenemez.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'project_id' => 'required|exists:projects,id',
            'shift_id' => 'required|exists:shifts,id',
            'work_date' => 'required|date',
            'hours_worked' => 'required|numeric|min:0|max:24',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'break_duration' => 'nullable|numeric|min:0|max:8',
            'notes' => 'nullable|string|max:500',
        ]);

        $timesheet->update($validated);

        return redirect()
            ->route('timesheets.index')
            ->with('success', 'Puantaj kaydı güncellendi.');
    }

    /**
     * Sil
     */
    public function destroy(TimesheetV2 $timesheet)
    {
        if (!$timesheet->isEditable()) {
            return back()->with('error', 'Bu kayıt silinemez.');
        }

        $timesheet->delete();

        return redirect()
            ->route('timesheets.index')
            ->with('success', 'Puantaj kaydı silindi.');
    }

    /**
     * Haftalık rapor
     */
    public function weeklyReport(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $weekStart = $request->input('week_start')
            ? Carbon::parse($request->input('week_start'))
            : Carbon::now()->startOfWeek();

        // Devir kaydı varsa getir
        $carryover = TimesheetCarryover::forEmployee($employeeId)
            ->notApplied()
            ->latest()
            ->first();

        $calculation = $this->calculator->calculateWeekly($employeeId, $weekStart, $carryover);

        $timesheets = TimesheetV2::forEmployee($employeeId)
            ->forWeek($weekStart)
            ->with(['shift', 'project'])
            ->orderBy('work_date')
            ->get();

        return Inertia::render('Timesheets/WeeklyReport', [
            'employee' => Employee::find($employeeId),
            'weekStart' => $weekStart,
            'timesheets' => $timesheets,
            'calculation' => $calculation,
            'carryover' => $carryover,
        ]);
    }

    /**
     * Aylık rapor
     */
    public function monthlyReport(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        // Önceki aydan devir
        $carryover = TimesheetCarryover::forEmployee($employeeId)
            ->where('from_year', $month == 1 ? $year - 1 : $year)
            ->where('from_month', $month == 1 ? 12 : $month - 1)
            ->notApplied()
            ->first();

        $calculation = $this->calculator->calculateMonthly($employeeId, $year, $month, $carryover);

        $timesheets = TimesheetV2::forEmployee($employeeId)
            ->forMonth($year, $month)
            ->with(['shift', 'project'])
            ->orderBy('work_date')
            ->get();

        return Inertia::render('Timesheets/MonthlyReport', [
            'employee' => Employee::find($employeeId),
            'year' => $year,
            'month' => $month,
            'timesheets' => $timesheets,
            'calculation' => $calculation,
            'carryover' => $carryover,
        ]);
    }

    /**
     * Toplu onaylama
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'timesheet_ids' => 'required|array',
            'timesheet_ids.*' => 'exists:timesheets_v2,id',
        ]);

        $updated = TimesheetV2::whereIn('id', $validated['timesheet_ids'])
            ->unlocked()
            ->update([
                'is_approved' => true,
                'approved_by' => auth()->user()?->id,
                'approved_at' => now(),
            ]);

        return back()->with('success', "{$updated} kayıt onaylandı.");
    }

    /**
     * Toplu kilitleme
     */
    public function bulkLock(Request $request)
    {
        $validated = $request->validate([
            'timesheet_ids' => 'required|array',
            'timesheet_ids.*' => 'exists:timesheets_v2,id',
        ]);

        $updated = TimesheetV2::whereIn('id', $validated['timesheet_ids'])
            ->update(['is_locked' => true]);

        return back()->with('success', "{$updated} kayıt kilitlendi.");
    }

    /**
     * Toplu puantaj giriş ekranı
     */
    public function bulkEntry(Request $request)
    {
        $projects = Project::with('departments')
            ->orderBy('name')
            ->get();

        $shifts = Shift::active()
            ->orderBy('sort_order')
            ->get();

        $employees = collect();
        $existingTimesheets = collect();

        // Eğer proje ve ay seçilmişse, personel ve mevcut kayıtları getir
        if ($request->filled('project_id') && $request->filled('month')) {
            $projectId = $request->project_id;
            $month = $request->month; // Format: "2025-12"

            // Aydaki başlangıç ve bitiş tarihleri
            $startDate = Carbon::parse($month . '-01')->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            // Projedeki aktif personelleri getir
            $employeesQuery = Employee::whereHas('projectAssignments', function ($query) use ($projectId, $startDate, $endDate) {
                $query->where('project_id', $projectId)
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->where(function ($q2) use ($startDate) {
                            // Atama bu aydan önce başlamış
                            $q2->where('start_date', '<=', $startDate);
                        })
                        ->where(function ($q2) use ($endDate) {
                            // Atama hala devam ediyor veya bu aydan sonra bitiyor
                            $q2->whereNull('end_date')
                                ->orWhere('end_date', '>=', $endDate);
                        });
                    });
            })->with(['projectAssignments' => function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            }]);

            // Departman filtresi varsa
            if ($request->filled('department_id')) {
                $employeesQuery->whereHas('projectAssignments', function ($query) use ($projectId, $request) {
                    $query->where('project_id', $projectId)
                        ->where('department_id', $request->department_id);
                });
            }

            $employees = $employeesQuery->orderBy('first_name')
                ->get()
                ->map(function ($employee) use ($projectId) {
                    $assignment = $employee->projectAssignments->firstWhere('project_id', $projectId);
                    return [
                        'id' => $employee->id,
                        'employee_code' => $employee->employee_code,
                        'first_name' => $employee->first_name,
                        'last_name' => $employee->last_name,
                        'assignment_start_date' => $assignment?->start_date,
                        'assignment_end_date' => $assignment?->end_date,
                    ];
                });

            // Mevcut puantaj kayıtlarını getir
            $existingTimesheets = TimesheetV2::with('shift')
                ->where('project_id', $projectId)
                ->whereBetween('work_date', [$startDate, $endDate])
                ->whereIn('employee_id', $employees->pluck('id'))
                ->get();
        }

        return Inertia::render('TimesheetsV2/BulkEntry', [
            'projects' => $projects,
            'shifts' => $shifts,
            'employees' => $employees,
            'existingTimesheets' => $existingTimesheets,
            'month' => $request->month,
            'projectId' => $request->project_id ? (int) $request->project_id : null,
            'departmentId' => $request->department_id ? (int) $request->department_id : null,
        ]);
    }

    /**
     * Toplu puantaj kaydetme
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'timesheets' => 'required|array',
            'timesheets.*.employee_id' => 'required|exists:employees,id',
            'timesheets.*.project_id' => 'required|exists:projects,id',
            'timesheets.*.shift_id' => 'required|exists:shifts,id',
            'timesheets.*.work_date' => 'required|date',
            'timesheets.*.hours_worked' => 'required|numeric|min:0|max:24',
            'timesheets.*.overtime_hours' => 'nullable|numeric|min:0|max:24',
            'timesheets.*.overtime_type' => 'nullable|string|in:weekday,weekend,holiday',
            'timesheets.*.notes' => 'nullable|string',
        ]);

        $created = 0;
        $updated = 0;

        foreach ($validated['timesheets'] as $timesheetData) {
            // Mevcut kaydı kontrol et
            $existing = TimesheetV2::where('employee_id', $timesheetData['employee_id'])
                ->where('project_id', $timesheetData['project_id'])
                ->where('work_date', $timesheetData['work_date'])
                ->first();

            if ($existing) {
                // Kilitli veya onaylı kayıtları güncelleme
                if ($existing->is_locked || $existing->is_approved) {
                    continue;
                }

                $existing->update([
                    'shift_id' => $timesheetData['shift_id'],
                    'hours_worked' => $timesheetData['hours_worked'],
                    'notes' => $timesheetData['notes'] ?? null,
                ]);
                $updated++;
            } else {
                TimesheetV2::create([
                    'employee_id' => $timesheetData['employee_id'],
                    'project_id' => $timesheetData['project_id'],
                    'shift_id' => $timesheetData['shift_id'],
                    'work_date' => $timesheetData['work_date'],
                    'hours_worked' => $timesheetData['hours_worked'],
                    'notes' => $timesheetData['notes'] ?? null,
                    'is_approved' => false,
                    'is_locked' => false,
                ]);
                $created++;
            }
        }

        $message = [];
        if ($created > 0) $message[] = "{$created} yeni kayıt oluşturuldu";
        if ($updated > 0) $message[] = "{$updated} kayıt güncellendi";

        return back()->with('success', implode(', ', $message) ?: 'Hiçbir değişiklik yapılmadı.');
    }

    // ==========================================
    // ONAY SİSTEMİ ENDPOINT'LERİ
    // ==========================================

    /**
     * Aylık puantajları toplu onayla
     */
    public function approveMonthly(Request $request, TimesheetApprovalService $approvalService)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,id',
            'project_id' => 'nullable|exists:projects,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $result = $approvalService->approveMonthlyTimesheets(
                $validated['year'],
                $validated['month'],
                $validated['employee_ids'] ?? null,
                $validated['project_id'] ?? null,
                auth()->user(),
                $validated['notes'] ?? null
            );

            if ($result['success']) {
                return back()->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Onay işlemi başarısız: ' . $e->getMessage());
        }
    }

    /**
     * Tekil puantaj onayı
     */
    public function approve(TimesheetV2 $timesheet, TimesheetApprovalService $approvalService)
    {
        try {
            $result = $approvalService->approveSingle(
                $timesheet,
                auth()->user(),
                request('notes')
            );

            if ($result['success']) {
                return back()->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Onay işlemi başarısız: ' . $e->getMessage());
        }
    }

    /**
     * Puantajı reddet
     */
    public function reject(Request $request, TimesheetV2 $timesheet, TimesheetApprovalService $approvalService)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $result = $approvalService->reject(
                $timesheet,
                auth()->user(),
                $validated['reason']
            );

            if ($result['success']) {
                return back()->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Red işlemi başarısız: ' . $e->getMessage());
        }
    }

    /**
     * İK müdahalesi - onaylanmış puantajı düzelt
     */
    public function hrOverride(Request $request, TimesheetV2 $timesheet, TimesheetApprovalService $approvalService)
    {
        $validated = $request->validate([
            'changes' => 'required|array',
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $result = $approvalService->hrOverride(
                $timesheet,
                auth()->user(),
                $validated['changes'],
                $validated['reason']
            );

            if ($result['success']) {
                return back()->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'İK müdahalesi başarısız: ' . $e->getMessage());
        }
    }

    /**
     * Onay istatistikleri
     */
    public function approvalStats(Request $request, TimesheetApprovalService $approvalService)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $stats = $approvalService->getApprovalStats(
            $validated['year'],
            $validated['month'],
            $validated['project_id'] ?? null
        );

        return response()->json($stats);
    }

    /**
     * Onay bekleyen puantajlar
     */
    public function pendingApprovals(Request $request, TimesheetApprovalService $approvalService)
    {
        $projectId = $request->input('project_id');
        $employeeId = $request->input('employee_id');

        $pending = $approvalService->getPendingApprovals($projectId, $employeeId);

        return Inertia::render('Timesheets/PendingApprovals', [
            'pending' => $pending,
            'filters' => $request->only(['project_id', 'employee_id']),
        ]);
    }

    /**
     * Puantajı onaya gönder
     */
    public function submit(TimesheetV2 $timesheet)
    {
        try {
            if ($timesheet->leave_request_id) {
                return back()->with('error', 'İzin kayıtları onaya gönderilemez.');
            }

            if ($timesheet->isSubmitted() || $timesheet->isApprovedNew()) {
                return back()->with('error', 'Bu puantaj zaten onaya gönderilmiş veya onaylanmış.');
            }

            $timesheet->submitForApproval(request('notes'));

            return back()->with('success', 'Puantaj onaya gönderildi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Onaya gönderme başarısız: ' . $e->getMessage());
        }
    }
}
