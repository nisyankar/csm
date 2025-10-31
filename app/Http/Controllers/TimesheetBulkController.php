<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Shift;
use App\Models\TemporaryAssignment;
use App\Services\Timesheet\WeeklyOvertimeCalculator;
use App\Services\Timesheet\LeaveTimesheetSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TimesheetBulkController extends Controller
{
    protected WeeklyOvertimeCalculator $overtimeCalculator;
    protected LeaveTimesheetSyncService $leaveSyncService;

    public function __construct(
        WeeklyOvertimeCalculator $overtimeCalculator,
        LeaveTimesheetSyncService $leaveSyncService
    ) {
        $this->overtimeCalculator = $overtimeCalculator;
        $this->leaveSyncService = $leaveSyncService;
    }

    /**
     * Toplu puantaj giriş sayfası
     */
    public function bulkEntry(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $projectId = $request->get('project_id');
        $departmentId = $request->get('department_id');

        // Projeleri getir
        $projects = Project::with('departments')
            ->where('status', 'active')
            ->get();

        // Vardiyaları getir (aktif olanlar, sıralı)
        $shifts = Shift::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $employees = collect();
        $existingTimesheets = collect();
        $leaveDays = [];

        if ($projectId && $month) {
            // Ay başı ve sonu
            $startDate = Carbon::parse($month . '-01')->startOfMonth();
            $endDate = Carbon::parse($month . '-01')->endOfMonth();

            // Çalışanları getir (normal proje ataması olanlar)
            $employeesQuery = Employee::where('status', 'active')
                ->whereHas('projects', function ($query) use ($projectId) {
                    $query->where('projects.id', $projectId);
                });

            if ($departmentId) {
                $employeesQuery->where('department_id', $departmentId);
            }

            $employees = $employeesQuery->with(['department'])->get();

            // Geçici görevlendirilen çalışanları da ekle (o ay içinde bu projeye atananlar)
            $temporaryEmployees = Employee::where('status', 'active')
                ->whereHas('temporaryAssignments', function ($query) use ($projectId, $startDate, $endDate) {
                    $query->where('to_project_id', $projectId)
                          ->where('status', 'active')
                          ->where(function ($q) use ($startDate, $endDate) {
                              // Görevlendirme tarihleri ay ile kesişiyorsa
                              $q->whereBetween('start_date', [$startDate, $endDate])
                                ->orWhereBetween('end_date', [$startDate, $endDate])
                                ->orWhere(function ($q2) use ($startDate, $endDate) {
                                    $q2->where('start_date', '<=', $startDate)
                                       ->where('end_date', '>=', $endDate);
                                });
                          });
                })
                ->with(['department', 'temporaryAssignments' => function ($query) use ($projectId, $startDate, $endDate) {
                    $query->where('to_project_id', $projectId)
                          ->where('status', 'active')
                          ->where(function ($q) use ($startDate, $endDate) {
                              $q->whereBetween('start_date', [$startDate, $endDate])
                                ->orWhereBetween('end_date', [$startDate, $endDate])
                                ->orWhere(function ($q2) use ($startDate, $endDate) {
                                    $q2->where('start_date', '<=', $startDate)
                                       ->where('end_date', '>=', $endDate);
                                });
                          })
                          ->with('preferredShift');
                }])
                ->get();

            // Geçici görevlendirilen çalışanları mevcut listeye ekle (duplicate kontrolü ile)
            foreach ($temporaryEmployees as $tempEmployee) {
                if (!$employees->contains('id', $tempEmployee->id)) {
                    // Geçici görevlendirme bilgisini ekle
                    $assignment = $tempEmployee->temporaryAssignments->first();
                    $tempEmployee->is_temporary = true;
                    $tempEmployee->temporary_assignment = $assignment;
                    $tempEmployee->default_shift_id = $assignment?->preferred_shift_id; // Varsayılan vardiya
                    $employees->push($tempEmployee);
                }
            }

            // Mevcut puantaj kayıtlarını getir
            $existingTimesheets = Timesheet::where('project_id', $projectId)
                ->whereBetween('work_date', [$startDate, $endDate])
                ->whereIn('employee_id', $employees->pluck('id'))
                ->with(['shift', 'leaveRequest'])
                ->get();

            // İzinli günleri getir
            foreach ($employees as $employee) {
                $employeeLeaveDays = $this->leaveSyncService->getLeaveDaysInRange(
                    $employee->id,
                    $startDate,
                    $endDate
                );

                if (!empty($employeeLeaveDays)) {
                    $leaveDays[$employee->id] = $employeeLeaveDays;
                }
            }
        }

        return Inertia::render('TimesheetsV3/BulkEntry', [
            'projects' => $projects,
            'shifts' => $shifts,
            'employees' => $employees,
            'existingTimesheets' => $existingTimesheets,
            'leaveDays' => $leaveDays,
            'month' => $month,
            'projectId' => $projectId,
            'departmentId' => $departmentId,
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
            'timesheets.*.overtime_type' => 'nullable|in:weekday,weekend,holiday',
            'timesheets.*.notes' => 'nullable|string',
            'month' => 'required|string',
            'project_id' => 'required|exists:projects,id',
        ]);

        // İzinli günleri kontrol et
        $conflicts = $this->leaveSyncService->validateBulkEntry($validated['timesheets']);

        if (!empty($conflicts)) {
            // Çakışan günleri açıklayıcı mesajlara çevir
            $conflictMessages = [];
            foreach ($conflicts as $conflict) {
                $employee = \App\Models\Employee::find($conflict['employee_id']);
                $employeeName = $employee ? "{$employee->first_name} {$employee->last_name}" : "Personel #{$conflict['employee_id']}";
                $conflictMessages[] = "{$employeeName} - " . Carbon::parse($conflict['work_date'])->format('d.m.Y') . ": {$conflict['message']}";
            }

            return back()->withErrors([
                'leave_conflicts' => 'Bazı günler izinli olduğu için puantaj girilemez: ' . implode(' | ', $conflictMessages),
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            $createdCount = 0;
            $updatedCount = 0;

            foreach ($validated['timesheets'] as $timesheetData) {
                // İzinli gün kontrolü (tekrar)
                if ($this->leaveSyncService->isLeaveDay(
                    $timesheetData['employee_id'],
                    Carbon::parse($timesheetData['work_date'])
                )) {
                    continue; // İzinli günü atla
                }

                // Mevcut kaydı kontrol et
                $existing = Timesheet::where('employee_id', $timesheetData['employee_id'])
                    ->where('work_date', $timesheetData['work_date'])
                    ->where('project_id', $timesheetData['project_id'])
                    ->first();

                // İzinden gelmeyen kayıtlar düzenlenebilir
                if ($existing && $existing->auto_generated_from_leave) {
                    continue; // İzinli kaydı atla
                }

                $data = [
                    ...$timesheetData,
                    'entry_method' => 'bulk',
                    'entered_by' => auth()?->id(),
                ];

                if ($existing) {
                    $existing->update($data);
                    $updatedCount++;
                } else {
                    Timesheet::create($data);
                    $createdCount++;
                }
            }

            // Haftalık FM hesaplamalarını yap
            $this->calculateWeeklyOvertimeForMonth(
                $validated['project_id'],
                $validated['month']
            );

            DB::commit();

            return back()->with('success', "Puantaj kaydedildi. {$createdCount} yeni, {$updatedCount} güncellendi.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Puantaj kaydedilemedi: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Aylık haftalık FM hesaplaması
     */
    private function calculateWeeklyOvertimeForMonth(int $projectId, string $month): void
    {
        $date = Carbon::parse($month . '-01');
        $year = $date->year;
        $monthNumber = $date->month;

        $this->overtimeCalculator->calculateForMonth($projectId, $year, $monthNumber);
    }

    /**
     * Tek bir puantaj kaydını sil
     */
    public function destroy(Timesheet $timesheet)
    {
        if ($timesheet->auto_generated_from_leave) {
            return back()->withErrors([
                'error' => 'İzinden gelen kayıtlar silinemez. İzni iptal edin.',
            ]);
        }

        if ($timesheet->is_locked) {
            return back()->withErrors([
                'error' => 'Kilitli kayıtlar silinemez.',
            ]);
        }

        $timesheet->delete();

        // Haftalık hesaplamayı güncelle
        $this->overtimeCalculator->recalculateWeekForTimesheet($timesheet);

        return back()->with('success', 'Puantaj silindi.');
    }

    /**
     * Aylık rapor
     */
    public function monthlyReport(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $projectId = $request->get('project_id');

        if (!$projectId) {
            return back()->withErrors(['error' => 'Proje seçiniz.']);
        }

        $date = Carbon::parse($month . '-01');
        $year = $date->year;
        $monthNumber = $date->month;

        // Haftalık hesaplamaları al
        $results = $this->overtimeCalculator->calculateForMonth($projectId, $year, $monthNumber);

        $employees = Employee::whereIn('id', array_keys($results))
            ->with('department')
            ->get()
            ->keyBy('id');

        $reportData = [];
        foreach ($results as $employeeId => $data) {
            $employee = $employees->get($employeeId);
            if ($employee) {
                $reportData[] = [
                    'employee' => $employee,
                    'data' => $data,
                ];
            }
        }

        return Inertia::render('TimesheetsV3/MonthlyReport', [
            'month' => $month,
            'project' => Project::find($projectId),
            'reportData' => $reportData,
        ]);
    }
}
