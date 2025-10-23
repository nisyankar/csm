<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class TimesheetV2 extends Model
{
    use HasFactory;

    protected $table = 'timesheets_v2';

    protected $fillable = [
        'employee_id',
        'project_id',
        'shift_id',
        'work_date',
        'daily_hours',
        'overtime_hours',
        'hours_worked',
        'start_time',
        'end_time',
        'break_duration',
        'notes',
        'is_approved',
        'approved_by',
        'approved_at',
        'is_locked',
        'metadata',
        'entered_by',
        // Yeni onay sistemi alanları
        'approval_status',
        'approval_notes',
        'hr_override',
        'hr_approved_by',
        'hr_approved_at',
        'leave_request_id',
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'hours_worked' => 'decimal:2',
        'break_duration' => 'decimal:2',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'is_locked' => 'boolean',
        'metadata' => 'array',
        'hr_override' => 'boolean',
        'hr_approved_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(TimesheetAdjustment::class, 'timesheet_id');
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(TimesheetApprovalLog::class, 'timesheet_v2_id');
    }

    public function hrApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_approved_by');
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    /**
     * Scopes
     */
    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeInDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('work_date', [$startDate, $endDate]);
    }

    public function scopeForWeek($query, Carbon $weekStart)
    {
        $weekEnd = $weekStart->copy()->endOfWeek();
        return $query->whereBetween('work_date', [$weekStart, $weekEnd]);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('work_date', $year)
                    ->whereMonth('work_date', $month);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    /**
     * Yeni onay sistemi scope'ları
     */
    public function scopeByApprovalStatus($query, string $status)
    {
        return $query->where('approval_status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('approval_status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('approval_status', 'submitted');
    }

    public function scopeApprovedNew($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }

    public function scopeWithHrOverride($query)
    {
        return $query->where('hr_override', true);
    }

    public function scopeLeaveEntries($query)
    {
        return $query->whereNotNull('leave_request_id');
    }

    public function scopeRegularEntries($query)
    {
        return $query->whereNull('leave_request_id');
    }

    /**
     * Helpers
     */
    public function calculateNetHours(): float
    {
        return $this->hours_worked - $this->break_duration;
    }

    public function isEditable(): bool
    {
        return !$this->is_locked && !$this->is_approved;
    }

    public function approve(int $userId): void
    {
        $this->update([
            'is_approved' => true,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function lock(): void
    {
        $this->update(['is_locked' => true]);
    }

    public function unlock(): void
    {
        $this->update(['is_locked' => false]);
    }

    /**
     * Hafta numarasını hesapla
     */
    public function getWeekNumber(): int
    {
        return Carbon::parse($this->work_date)->weekOfYear;
    }

    /**
     * Hafta başlangıcını getir (Pazartesi)
     */
    public function getWeekStart(): Carbon
    {
        return Carbon::parse($this->work_date)->startOfWeek();
    }

    /**
     * Yeni onay sistemi helper metodları
     */
    public function isApprovedNew(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isDraft(): bool
    {
        return $this->approval_status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->approval_status === 'submitted';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function canBeEdited(): bool
    {
        // Onaylanmış puantaj düzenlenemez, sadece İK override ile
        if ($this->isApprovedNew()) {
            return false;
        }

        // İzin kaydıysa ve onaylanmışsa düzenlenemez
        if ($this->leave_request_id && $this->leaveRequest?->status === 'approved') {
            return false;
        }

        return true;
    }

    public function submitForApproval(?string $notes = null): void
    {
        $oldValues = $this->only(['approval_status', 'notes']);

        $this->update([
            'approval_status' => 'submitted',
            'approval_notes' => $notes,
        ]);

        TimesheetApprovalLog::logAction(
            $this,
            'submitted',
            $oldValues,
            $this->only(['approval_status', 'notes']),
            $notes
        );
    }

    public function approveNew(User $user, ?string $notes = null): void
    {
        $oldValues = $this->only(['approval_status', 'approved_by', 'approved_at']);

        $this->update([
            'approval_status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        TimesheetApprovalLog::logAction(
            $this,
            'approved',
            $oldValues,
            $this->only(['approval_status', 'approved_by', 'approved_at']),
            $notes
        );
    }

    public function reject(User $user, string $reason): void
    {
        $oldValues = $this->only(['approval_status', 'approved_by']);

        $this->update([
            'approval_status' => 'rejected',
            'approved_by' => $user->id,
            'approval_notes' => $reason,
        ]);

        TimesheetApprovalLog::logAction(
            $this,
            'rejected',
            $oldValues,
            $this->only(['approval_status', 'approved_by', 'approval_notes']),
            $reason
        );
    }

    public function hrOverride(User $hrUser, array $changes, string $reason): void
    {
        $oldValues = $this->only(array_keys($changes));

        $this->update(array_merge($changes, [
            'hr_override' => true,
            'hr_approved_by' => $hrUser->id,
            'hr_approved_at' => now(),
            'approval_notes' => $reason,
        ]));

        TimesheetApprovalLog::logAction(
            $this,
            'hr_override',
            $oldValues,
            $this->only(array_keys($changes)),
            $reason
        );
    }
}
