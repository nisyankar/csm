<?php

namespace App\Services\Timesheet;

use App\Models\LeaveRequest;
use App\Models\Timesheet;
use App\Models\Shift;
use App\Models\TemporaryAssignment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * İzin - Puantaj Senkronizasyon Servisi
 *
 * İzin talepleri onaylandığında otomatik olarak puantaja yansıtır.
 * İzin iptal edildiğinde puantajdan kaldırır.
 * İzinli günlere manuel müdahale ENGELLENIR.
 */
class LeaveTimesheetSyncService
{
    /**
     * Onaylanan izni puantaja yansıt
     */
    public function syncApprovedLeave(LeaveRequest $leaveRequest): array
    {
        if ($leaveRequest->status !== 'approved') {
            throw new \Exception('Only approved leave requests can be synced to timesheet.');
        }

        if ($leaveRequest->auto_applied_to_timesheet) {
            return [
                'success' => true,
                'message' => 'Leave already synced to timesheet.',
                'timesheet_ids' => $leaveRequest->timesheet_entries ?? [],
            ];
        }

        DB::beginTransaction();

        try {
            $timesheetIds = [];
            $current = Carbon::parse($leaveRequest->start_date);
            $endDate = Carbon::parse($leaveRequest->end_date);

            // İzin türüne göre vardiya belirle
            $shift = $this->getLeaveShift($leaveRequest->leave_type);

            if (!$shift) {
                throw new \Exception("No shift found for leave type: {$leaveRequest->leave_type}");
            }

            while ($current->lte($endDate)) {
                // Sadece çalışma günlerinde puantaj oluştur (hafta sonu hariç)
                if (!$current->isWeekend()) {
                    $timesheet = $this->createOrUpdateLeaveTimesheet(
                        $leaveRequest,
                        $current->copy(),
                        $shift
                    );

                    $timesheetIds[] = $timesheet->id;
                }

                $current->addDay();
            }

            // LeaveRequest'i güncelle
            $leaveRequest->update([
                'auto_applied_to_timesheet' => true,
                'applied_to_timesheet_at' => now(),
                'timesheet_entries' => $timesheetIds,
            ]);

            DB::commit();

            Log::info('Leave synced to timesheet', [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'timesheet_count' => count($timesheetIds),
            ]);

            return [
                'success' => true,
                'message' => 'Leave synced to timesheet successfully.',
                'timesheet_ids' => $timesheetIds,
                'timesheet_count' => count($timesheetIds),
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to sync leave to timesheet', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * İzin için puantaj oluştur veya güncelle
     */
    private function createOrUpdateLeaveTimesheet(
        LeaveRequest $leaveRequest,
        Carbon $date,
        Shift $shift
    ): Timesheet {
        // Varolan kaydı kontrol et
        $existing = Timesheet::where('employee_id', $leaveRequest->employee_id)
            ->where('work_date', $date)
            ->first();

        // KRİTİK KONTROL: Onaylanmış manuel puantaj varsa İK müdahalesi gerekli
        if ($existing && !$existing->auto_generated_from_leave) {
            // V3'te approval_status kontrolü (eğer varsa)
            $isApproved = $existing->is_approved ?? false;

            if ($isApproved) {
                throw new \Exception(
                    "Bu tarihte ({$date->format('d.m.Y')}) onaylanmış puantaj kaydı bulunmaktadır. " .
                    "İzin girişi için İK onayı gereklidir."
                );
            }

            Log::warning('Overwriting manual timesheet entry with leave', [
                'timesheet_id' => $existing->id,
                'employee_id' => $leaveRequest->employee_id,
                'work_date' => $date->format('Y-m-d'),
            ]);
        }

        // KRİTİK: Geçici görevlendirme kontrolü - eğer bu tarihte geçici görevlendirme varsa hedef projeye kaydet
        $projectData = $this->determineProjectForLeave($leaveRequest->employee_id, $date);

        $data = [
            'employee_id' => $leaveRequest->employee_id,
            'project_id' => $projectData['project_id'],
            'shift_id' => $shift->id,
            'work_date' => $date,
            'hours_worked' => $shift->daily_hours,
            'overtime_hours' => 0,
            'overtime_type' => null,
            'attendance_type' => $this->getAttendanceType($leaveRequest->leave_type),
            'leave_request_id' => $leaveRequest->id,
            'auto_generated_from_leave' => true,
            'is_leave_day' => true,
            'leave_type' => $leaveRequest->leave_type,
            'is_locked' => true, // İzinli günler kilitlidir
            'is_approved' => true, // İzin zaten onaylı
            'approval_status' => 'approved', // İzin kayıtları otomatik onaylı
            'approved_by' => $leaveRequest->approver_id,
            'approved_at' => $leaveRequest->approved_at,
            'notes' => $this->generateLeaveNotes($leaveRequest, $projectData),
            'entry_method' => 'leave_sync',
            'entered_by' => $leaveRequest->approver_id,
        ];

        // Eğer geçici görevlendirme varsa, ilişkili bilgiyi de kaydet
        if ($projectData['temporary_assignment_id']) {
            $data['temporary_assignment_id'] = $projectData['temporary_assignment_id'];
        }

        if ($existing) {
            $existing->update($data);
            return $existing;
        }

        return Timesheet::create($data);
    }

    /**
     * İptal edilen/reddedilen izni puantajdan kaldır
     */
    public function removeLeaveFromTimesheet(LeaveRequest $leaveRequest, bool $hrOverride = false): array
    {
        if (!$leaveRequest->auto_applied_to_timesheet) {
            return [
                'success' => true,
                'message' => 'Leave was not synced to timesheet.',
                'deleted_count' => 0,
            ];
        }

        DB::beginTransaction();

        try {
            $timesheetIds = $leaveRequest->timesheet_entries ?? [];

            // KRİTİK KONTROL: Onaylanmış puantaj varsa İK onayı gerekli
            if (!$hrOverride) {
                $approvedTimesheets = Timesheet::whereIn('id', $timesheetIds)
                    ->where('is_approved', true)
                    ->count();

                if ($approvedTimesheets > 0) {
                    throw new \Exception(
                        "İzne ait {$approvedTimesheets} adet onaylanmış puantaj kaydı bulunmaktadır. " .
                        "İzin iptal etmek için İK onayı gereklidir."
                    );
                }
            }

            // İzinden oluşturulan kayıtları sil
            $deletedCount = Timesheet::whereIn('id', $timesheetIds)
                ->where('auto_generated_from_leave', true)
                ->where('leave_request_id', $leaveRequest->id)
                ->delete();

            // LeaveRequest'i güncelle
            $leaveRequest->update([
                'auto_applied_to_timesheet' => false,
                'applied_to_timesheet_at' => null,
                'timesheet_entries' => [],
            ]);

            DB::commit();

            Log::info('Leave removed from timesheet', [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'deleted_count' => $deletedCount,
            ]);

            return [
                'success' => true,
                'message' => 'Leave removed from timesheet successfully.',
                'deleted_count' => $deletedCount,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to remove leave from timesheet', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * İzin türüne göre devamsızlık türünü belirle
     */
    private function getAttendanceType(string $leaveType): string
    {
        $attendanceTypeMap = [
            'annual' => 'annual_leave',
            'sick' => 'sick_leave',
            'unpaid' => 'unpaid_leave',
            'maternity' => 'maternity_leave',
            'paternity' => 'excused_leave',
            'marriage' => 'excused_leave',
            'funeral' => 'excused_leave',
            'emergency' => 'excused_leave',
            'study' => 'excused_leave',
            'military' => 'excused_leave',
        ];

        return $attendanceTypeMap[$leaveType] ?? 'excused_leave';
    }

    /**
     * İzin türüne göre uygun vardiyayı bul
     */
    private function getLeaveShift(string $leaveType): ?Shift
    {
        $shiftTypeMap = [
            'annual' => 'annual_leave',
            'sick' => 'sick_leave',
            'unpaid' => 'unpaid_leave',
            'maternity' => 'maternity_leave',
            'paternity' => 'excused_leave',
            'marriage' => 'excused_leave',
            'funeral' => 'excused_leave',
            'emergency' => 'excused_leave',
            'study' => 'excused_leave',
        ];

        $shiftType = $shiftTypeMap[$leaveType] ?? 'excused_leave';

        return Shift::where('shift_type', $shiftType)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Belirli bir günün izinli gün olup olmadığını kontrol et
     */
    public function isLeaveDay(int $employeeId, Carbon $date): bool
    {
        return Timesheet::where('employee_id', $employeeId)
            ->where('work_date', $date)
            ->where('is_leave_day', true)
            ->where('auto_generated_from_leave', true)
            ->exists();
    }

    /**
     * Belirli bir tarih aralığındaki izinli günleri getir
     */
    public function getLeaveDaysInRange(int $employeeId, Carbon $startDate, Carbon $endDate): array
    {
        $leaveDays = Timesheet::where('employee_id', $employeeId)
            ->where('is_leave_day', true)
            ->where('auto_generated_from_leave', true)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->with('leaveRequest')
            ->get();

        return $leaveDays->map(function ($timesheet) {
            return [
                'date' => $timesheet->work_date->format('Y-m-d'),
                'leave_type' => $timesheet->leave_type,
                'leave_request_id' => $timesheet->leave_request_id,
                'shift_code' => $timesheet->shift->code ?? null,
                'is_locked' => $timesheet->is_locked,
            ];
        })->toArray();
    }

    /**
     * Toplu puantaj girişinde izinli günleri kontrol et
     */
    public function validateBulkEntry(array $timesheetData): array
    {
        $conflicts = [];

        foreach ($timesheetData as $data) {
            $isLeave = $this->isLeaveDay($data['employee_id'], Carbon::parse($data['work_date']));

            if ($isLeave) {
                $conflicts[] = [
                    'employee_id' => $data['employee_id'],
                    'work_date' => $data['work_date'],
                    'message' => 'Bu gün izinli. Manuel puantaj girilemez.',
                ];
            }
        }

        return $conflicts;
    }

    /**
     * İzin günlerini refresh et (değişiklik sonrası)
     */
    public function refreshLeaveTimesheets(LeaveRequest $leaveRequest): array
    {
        // Önce eski kayıtları sil
        $this->removeLeaveFromTimesheet($leaveRequest);

        // Eğer hala onaylı ise yeniden oluştur
        if ($leaveRequest->status === 'approved') {
            return $this->syncApprovedLeave($leaveRequest);
        }

        return [
            'success' => true,
            'message' => 'Leave timesheets removed (leave is no longer approved).',
        ];
    }

    /**
     * İzin için doğru projeyi belirle (geçici görevlendirme varsa hedef proje)
     */
    private function determineProjectForLeave(int $employeeId, Carbon $date): array
    {
        // Geçici görevlendirme kontrolü
        $temporaryAssignment = TemporaryAssignment::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if ($temporaryAssignment) {
            // Geçici görevlendirme varsa hedef projeye kaydet
            return [
                'project_id' => $temporaryAssignment->to_project_id,
                'temporary_assignment_id' => $temporaryAssignment->id,
                'is_temporary' => true,
                'from_project_id' => $temporaryAssignment->from_project_id,
            ];
        }

        // Normal durumda ana projeye kaydet
        $employee = \App\Models\Employee::find($employeeId);
        $projectId = $employee->current_project_id
            ?? $employee->projects->first()?->id
            ?? null;

        return [
            'project_id' => $projectId,
            'temporary_assignment_id' => null,
            'is_temporary' => false,
            'from_project_id' => null,
        ];
    }

    /**
     * İzin notu oluştur (geçici görevlendirme durumunu da belirt)
     */
    private function generateLeaveNotes(LeaveRequest $leaveRequest, array $projectData): string
    {
        $notes = "İzin: {$leaveRequest->leave_type_display}";

        if ($projectData['is_temporary']) {
            $fromProject = \App\Models\Project::find($projectData['from_project_id']);
            $toProject = \App\Models\Project::find($projectData['project_id']);

            $notes .= " (Geçici görevlendirme nedeniyle {$fromProject?->name} yerine {$toProject?->name} projesine kaydedildi)";
        }

        return $notes;
    }
}