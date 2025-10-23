<?php

namespace App\Services\Timesheet;

use App\Models\TimesheetV2;
use App\Models\User;
use App\Models\TimesheetApprovalLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Puantaj Onay Servisi
 *
 * Aylık puantaj onayı için kullanılır.
 * - Toplu onay
 * - Tekil onay
 * - Red
 * - İK müdahalesi
 */
class TimesheetApprovalService
{
    /**
     * Aylık puantajları toplu onayla
     *
     * @param int $year Yıl
     * @param int $month Ay (1-12)
     * @param array|null $employeeIds Belirli çalışanlar (null = hepsi)
     * @param int|null $projectId Belirli proje (null = hepsi)
     * @param User $approver Onaylayan kullanıcı
     * @param string|null $notes Onay notları
     * @return array
     */
    public function approveMonthlyTimesheets(
        int $year,
        int $month,
        ?array $employeeIds = null,
        ?int $projectId = null,
        User $approver,
        ?string $notes = null
    ): array {
        DB::beginTransaction();

        try {
            // Onaylanacak puantajları bul
            $query = TimesheetV2::query()
                ->whereYear('work_date', $year)
                ->whereMonth('work_date', $month)
                ->where('approval_status', '!=', 'approved') // Zaten onaylı olanları hariç tut
                ->whereNull('leave_request_id'); // İzin kayıtlarını hariç tut (onlar zaten onaylı)

            if ($employeeIds) {
                $query->whereIn('employee_id', $employeeIds);
            }

            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            $timesheets = $query->get();

            if ($timesheets->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Onaylanacak puantaj kaydı bulunamadı.',
                    'approved_count' => 0,
                    'failed_count' => 0,
                ];
            }

            $approvedCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($timesheets as $timesheet) {
                try {
                    $timesheet->approveNew($approver, $notes);
                    $approvedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = [
                        'timesheet_id' => $timesheet->id,
                        'employee_id' => $timesheet->employee_id,
                        'work_date' => $timesheet->work_date->format('Y-m-d'),
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            Log::info('Monthly timesheets approved', [
                'year' => $year,
                'month' => $month,
                'approver_id' => $approver->id,
                'approved_count' => $approvedCount,
                'failed_count' => $failedCount,
            ]);

            return [
                'success' => true,
                'message' => "{$approvedCount} puantaj onaylandı" . ($failedCount > 0 ? ", {$failedCount} hata." : '.'),
                'approved_count' => $approvedCount,
                'failed_count' => $failedCount,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to approve monthly timesheets', [
                'year' => $year,
                'month' => $month,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Belirli bir çalışanın aylık puantajlarını onayla
     */
    public function approveEmployeeMonth(
        int $employeeId,
        int $year,
        int $month,
        User $approver,
        ?string $notes = null
    ): array {
        return $this->approveMonthlyTimesheets(
            $year,
            $month,
            [$employeeId],
            null,
            $approver,
            $notes
        );
    }

    /**
     * Belirli bir projenin aylık puantajlarını onayla
     */
    public function approveProjectMonth(
        int $projectId,
        int $year,
        int $month,
        User $approver,
        ?string $notes = null
    ): array {
        return $this->approveMonthlyTimesheets(
            $year,
            $month,
            null,
            $projectId,
            $approver,
            $notes
        );
    }

    /**
     * Tekil puantaj onayı
     */
    public function approveSingle(
        TimesheetV2 $timesheet,
        User $approver,
        ?string $notes = null
    ): array {
        DB::beginTransaction();

        try {
            // İzin kaydı ise onaylanamaz
            if ($timesheet->leave_request_id) {
                throw new \Exception('İzin kayıtları manuel olarak onaylanamaz.');
            }

            // Zaten onaylı mı?
            if ($timesheet->isApprovedNew()) {
                return [
                    'success' => false,
                    'message' => 'Bu puantaj zaten onaylanmış.',
                ];
            }

            $timesheet->approveNew($approver, $notes);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Puantaj başarıyla onaylandı.',
                'timesheet_id' => $timesheet->id,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Puantajı reddet
     */
    public function reject(
        TimesheetV2 $timesheet,
        User $rejector,
        string $reason
    ): array {
        DB::beginTransaction();

        try {
            // İzin kaydı ise reddedilemez
            if ($timesheet->leave_request_id) {
                throw new \Exception('İzin kayıtları manuel olarak reddedilemez.');
            }

            $timesheet->reject($rejector, $reason);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Puantaj reddedildi.',
                'timesheet_id' => $timesheet->id,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * İK müdahalesi - onaylanmış puantajı düzelt
     */
    public function hrOverride(
        TimesheetV2 $timesheet,
        User $hrUser,
        array $changes,
        string $reason
    ): array {
        DB::beginTransaction();

        try {
            // İK yetkisi kontrolü
            if (!$hrUser->hasAnyRole(['admin', 'hr'])) {
                throw new \Exception('Bu işlem için İK yetkisi gereklidir.');
            }

            $timesheet->hrOverride($hrUser, $changes, $reason);

            DB::commit();

            return [
                'success' => true,
                'message' => 'İK müdahalesi kaydedildi.',
                'timesheet_id' => $timesheet->id,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Onay istatistiklerini getir
     */
    public function getApprovalStats(int $year, int $month, ?int $projectId = null): array
    {
        $query = TimesheetV2::query()
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $total = $query->count();
        $approved = (clone $query)->where('approval_status', 'approved')->count();
        $pending = (clone $query)->where('approval_status', 'draft')->count();
        $submitted = (clone $query)->where('approval_status', 'submitted')->count();
        $rejected = (clone $query)->where('approval_status', 'rejected')->count();

        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'submitted' => $submitted,
            'rejected' => $rejected,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Onay bekleyen puantajları getir
     */
    public function getPendingApprovals(?int $projectId = null, ?int $employeeId = null)
    {
        $query = TimesheetV2::query()
            ->with(['employee', 'project', 'shift'])
            ->where('approval_status', 'submitted')
            ->orderBy('work_date', 'desc');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->get();
    }
}
