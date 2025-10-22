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
}
