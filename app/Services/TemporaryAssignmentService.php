<?php

namespace App\Services;

use App\Models\TemporaryAssignment;
use App\Models\Employee;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TemporaryAssignmentService
{
    /**
     * Create a new temporary assignment
     */
    public function createAssignment(array $data): TemporaryAssignment
    {
        // Check for conflicts before creating
        $employee = Employee::findOrFail($data['employee_id']);
        $conflicts = $this->checkConflicts(
            $employee,
            Carbon::parse($data['start_date']),
            Carbon::parse($data['end_date'])
        );

        if ($conflicts->isNotEmpty()) {
            throw new \Exception('Bu tarih aralığında çalışan için başka bir aktif görevlendirme bulunmaktadır.');
        }

        return TemporaryAssignment::create($data);
    }

    /**
     * Approve an assignment
     */
    public function approveAssignment(TemporaryAssignment $assignment, User $approver): bool
    {
        if ($assignment->status !== 'pending') {
            throw new \Exception('Sadece onay bekleyen görevlendirmeler onaylanabilir.');
        }

        return $assignment->approve($approver);
    }

    /**
     * Reject an assignment
     */
    public function rejectAssignment(TemporaryAssignment $assignment, User $approver, string $reason): bool
    {
        if ($assignment->status !== 'pending') {
            throw new \Exception('Sadece onay bekleyen görevlendirmeler reddedilebilir.');
        }

        return $assignment->reject($approver, $reason);
    }

    /**
     * Get active assignment for an employee
     */
    public function getActiveAssignment(Employee $employee): ?TemporaryAssignment
    {
        return TemporaryAssignment::forEmployee($employee->id)
            ->active()
            ->first();
    }

    /**
     * Check for conflicting assignments
     */
    public function checkConflicts(Employee $employee, Carbon $startDate, Carbon $endDate): Collection
    {
        return TemporaryAssignment::forEmployee($employee->id)
            ->whereIn('status', ['pending', 'active'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->get();
    }

    /**
     * Auto-complete expired assignments (for cron job)
     */
    public function autoCompleteExpired(): int
    {
        $expired = TemporaryAssignment::where('status', 'active')
            ->where('end_date', '<', now()->startOfDay())
            ->get();

        $count = 0;
        foreach ($expired as $assignment) {
            $assignment->complete();
            $count++;
        }

        return $count;
    }

    /**
     * Get assignment history for an employee
     */
    public function getAssignmentHistory(Employee $employee): Collection
    {
        return TemporaryAssignment::forEmployee($employee->id)
            ->with(['fromProject', 'toProject', 'requestedBy', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Transfer timesheet to temporary assignment
     */
    public function transferTimesheet(Timesheet $timesheet, TemporaryAssignment $assignment): bool
    {
        // Verify timesheet is within assignment date range
        if ($timesheet->work_date < $assignment->start_date || $timesheet->work_date > $assignment->end_date) {
            throw new \Exception('Puantaj tarihi görevlendirme tarih aralığının dışında.');
        }

        // Verify employee matches
        if ($timesheet->employee_id !== $assignment->employee_id) {
            throw new \Exception('Puantaj çalışanı görevlendirme çalışanı ile eşleşmiyor.');
        }

        $timesheet->update([
            'temporary_assignment_id' => $assignment->id,
            'project_id' => $assignment->to_project_id,
        ]);

        return true;
    }

    /**
     * Get assignments that are expiring soon
     */
    public function getExpiringSoon(int $days = 7): Collection
    {
        return TemporaryAssignment::expiringSoon($days)
            ->with(['employee', 'fromProject', 'toProject'])
            ->get();
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics(): array
    {
        return [
            'total' => TemporaryAssignment::count(),
            'pending' => TemporaryAssignment::pending()->count(),
            'active' => TemporaryAssignment::active()->count(),
            'expiring_soon' => TemporaryAssignment::expiringSoon(7)->count(),
            'completed_this_month' => TemporaryAssignment::where('status', 'completed')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Get assignments for a specific project
     */
    public function getProjectAssignments(int $projectId, ?string $status = null): Collection
    {
        $query = TemporaryAssignment::forProject($projectId)
            ->with(['employee', 'fromProject', 'toProject']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Extend assignment end date
     */
    public function extendAssignment(TemporaryAssignment $assignment, Carbon $newEndDate, string $reason): bool
    {
        if ($assignment->status !== 'active') {
            throw new \Exception('Sadece aktif görevlendirmelerin süresi uzatılabilir.');
        }

        if ($newEndDate <= $assignment->end_date) {
            throw new \Exception('Yeni bitiş tarihi mevcut bitiş tarihinden sonra olmalıdır.');
        }

        // Check for conflicts with new end date
        $conflicts = $this->checkConflicts(
            $assignment->employee,
            $assignment->end_date->addDay(),
            $newEndDate
        );

        if ($conflicts->isNotEmpty()) {
            throw new \Exception('Yeni tarih aralığında çakışan görevlendirme bulunmaktadır.');
        }

        $assignment->update([
            'end_date' => $newEndDate,
            'notes' => ($assignment->notes ? $assignment->notes . "\n\n" : '') .
                      "Süre Uzatıldı: " . now()->format('d.m.Y') . " - Yeni Bitiş: " . $newEndDate->format('d.m.Y') . " - Neden: " . $reason,
        ]);

        return true;
    }
}
