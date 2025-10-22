<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class TimesheetV3 extends Model
{
    use HasFactory;

    protected $table = 'timesheets_v3';

    protected $fillable = [
        'employee_id',
        'project_id',
        'shift_id',
        'work_date',
        'hours_worked',
        'overtime_hours',
        'overtime_type',
        'start_time',
        'end_time',
        'break_duration',
        'leave_request_id',
        'auto_generated_from_leave',
        'is_leave_day',
        'leave_type',
        'is_locked',
        'is_approved',
        'approved_by',
        'approved_at',
        'week_number',
        'year',
        'weekly_total_hours',
        'weekly_required_hours',
        'weekly_overtime_hours',
        'week_calculation_done',
        'notes',
        'metadata',
        'entry_method',
        'entered_by',
    ];

    protected $casts = [
        'work_date' => 'date',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'break_duration' => 'decimal:2',
        'auto_generated_from_leave' => 'boolean',
        'is_leave_day' => 'boolean',
        'is_locked' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'weekly_total_hours' => 'decimal:2',
        'weekly_required_hours' => 'decimal:2',
        'weekly_overtime_hours' => 'decimal:2',
        'week_calculation_done' => 'boolean',
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

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
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

    public function scopeForWeek($query, int $year, int $weekNumber)
    {
        return $query->where('year', $year)->where('week_number', $weekNumber);
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

    public function scopeManualEntry($query)
    {
        return $query->where('auto_generated_from_leave', false);
    }

    public function scopeLeaveGenerated($query)
    {
        return $query->where('auto_generated_from_leave', true);
    }

    public function scopeLeaveDays($query)
    {
        return $query->where('is_leave_day', true);
    }

    /**
     * Accessors & Helpers
     */

    /**
     * İzinden mi geldi?
     */
    public function isGeneratedFromLeave(): bool
    {
        return $this->auto_generated_from_leave;
    }

    /**
     * Düzenlenebilir mi? (İzinli günler düzenlenemez)
     */
    public function isEditable(): bool
    {
        return !$this->is_locked
            && !$this->is_approved
            && !$this->auto_generated_from_leave;
    }

    /**
     * İzin günü mü?
     */
    public function isLeaveDay(): bool
    {
        return $this->is_leave_day;
    }

    /**
     * Net çalışma saati (mola hariç)
     */
    public function calculateNetHours(): float
    {
        return $this->hours_worked - $this->break_duration;
    }

    /**
     * Onaylama
     */
    public function approve(int $userId): void
    {
        if ($this->auto_generated_from_leave) {
            throw new \Exception('Leave-generated timesheets cannot be manually approved.');
        }

        $this->update([
            'is_approved' => true,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Kilitleme
     */
    public function lock(): void
    {
        $this->update(['is_locked' => true]);
    }

    /**
     * Kilit açma
     */
    public function unlock(): void
    {
        if ($this->auto_generated_from_leave) {
            throw new \Exception('Leave-generated timesheets cannot be unlocked manually.');
        }

        $this->update(['is_locked' => false]);
    }

    /**
     * Hafta numarası ve yılı otomatik ayarla
     */
    public function setWeekInfo(): void
    {
        $date = Carbon::parse($this->work_date);
        $this->year = $date->year;
        $this->week_number = $date->weekOfYear;
    }

    /**
     * Hafta başlangıcını getir (Pazartesi)
     */
    public function getWeekStart(): Carbon
    {
        return Carbon::parse($this->work_date)->startOfWeek();
    }

    /**
     * Hafta sonunu getir (Pazar)
     */
    public function getWeekEnd(): Carbon
    {
        return Carbon::parse($this->work_date)->endOfWeek();
    }

    /**
     * Boot method - otomatik hafta bilgilerini set et
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($timesheet) {
            $date = Carbon::parse($timesheet->work_date);
            $timesheet->year = $date->year;
            $timesheet->week_number = $date->weekOfYear;
        });

        static::updating(function ($timesheet) {
            if ($timesheet->isDirty('work_date')) {
                $date = Carbon::parse($timesheet->work_date);
                $timesheet->year = $date->year;
                $timesheet->week_number = $date->weekOfYear;
            }
        });
    }
}
