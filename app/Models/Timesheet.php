<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Timesheet extends Model
{
    use HasFactory;

    protected $table = 'timesheets';

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
        'notes',
        'is_approved',
        'approved_by',
        'approved_at',
        'is_locked',
        'metadata',
        'entered_by',
        'entry_method',
        // Yeni onay sistemi alanları
        'approval_status',
        'approval_notes',
        'hr_override',
        'hr_approved_by',
        'hr_approved_at',
        // İzin entegrasyonu
        'leave_request_id',
        'auto_generated_from_leave',
        'is_leave_day',
        'leave_type',
        // Haftalık hesaplama cache
        'week_number',
        'year',
        'weekly_total_hours',
        'weekly_required_hours',
        'weekly_overtime_hours',
        'week_calculation_done',
        // Proje detayları
        'structure_id',
        'floor_id',
        'unit_id',
        'work_item_id',
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'break_duration' => 'decimal:2',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'is_locked' => 'boolean',
        'metadata' => 'array',
        'hr_override' => 'boolean',
        'hr_approved_at' => 'datetime',
        'auto_generated_from_leave' => 'boolean',
        'is_leave_day' => 'boolean',
        'weekly_total_hours' => 'decimal:2',
        'weekly_required_hours' => 'decimal:2',
        'weekly_overtime_hours' => 'decimal:2',
        'week_calculation_done' => 'boolean',
    ];

    protected $appends = [
        'calculated_wage',
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
        return $this->hasMany(TimesheetApprovalLog::class, 'timesheet_id');
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

        TimesheetApprovalLog::logActionWithUser(
            $this,
            $user->id,
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

        TimesheetApprovalLog::logActionWithUser(
            $this,
            $user->id,
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

        TimesheetApprovalLog::logActionWithUser(
            $this,
            $hrUser->id,
            'hr_override',
            $oldValues,
            $this->only(array_keys($changes)),
            $reason
        );
    }

    /**
     * V3'ten gelen helper metodlar
     */

    /**
     * İzinden mi geldi?
     */
    public function isGeneratedFromLeave(): bool
    {
        return $this->auto_generated_from_leave;
    }

    /**
     * İzin günü mü?
     */
    public function isLeaveDay(): bool
    {
        return $this->is_leave_day;
    }

    /**
     * Hafta numarasını hesapla
     */
    public function setWeekInfo(): void
    {
        $date = Carbon::parse($this->work_date);
        $this->year = $date->year;
        $this->week_number = $date->weekOfYear;
    }

    /**
     * Hafta sonunu getir (Pazar)
     */
    public function getWeekEnd(): Carbon
    {
        return Carbon::parse($this->work_date)->endOfWeek();
    }

    /**
     * Scope: Hafta ile sorgulama
     */
    public function scopeForWeekNumber($query, int $year, int $weekNumber)
    {
        return $query->where('year', $year)->where('week_number', $weekNumber);
    }

    /**
     * Scope: Manuel girişler
     */
    public function scopeManualEntry($query)
    {
        return $query->where('auto_generated_from_leave', false);
    }

    /**
     * Scope: İzinden oluşturulanlar
     */
    public function scopeLeaveGenerated($query)
    {
        return $query->where('auto_generated_from_leave', true);
    }

    /**
     * Scope: İzin günleri
     */
    public function scopeLeaveDays($query)
    {
        return $query->where('is_leave_day', true);
    }

    /**
     * Accessor: Hesaplanan ücreti getir
     */
    public function getCalculatedWageAttribute(): float
    {
        if (!$this->employee) {
            return 0;
        }

        // Çalışan ücret bilgisi yoksa 0 döndür
        if (!$this->employee->daily_wage && !$this->employee->hourly_wage && !$this->employee->monthly_salary) {
            return 0;
        }

        // İzin günleri için ücret hesabı
        if ($this->is_leave_day) {
            return $this->employee->calculateDailyRate();
        }

        // Normal çalışma saatleri ücreti
        $regularWage = 0;
        $overtimeWage = 0;

        // Çalışan ücret tipine göre hesapla
        switch ($this->employee->wage_type) {
            case 'hourly':
                $regularWage = ($this->hours_worked ?? 0) * ($this->employee->hourly_wage ?? 0);

                // Fazla mesai hesabı (overtime_type'a göre)
                if ($this->overtime_hours > 0) {
                    $overtimeMultiplier = match($this->overtime_type) {
                        'weekday' => 1.5,  // Hafta içi %50
                        'weekend' => 2.0,  // Hafta sonu %100
                        'holiday' => 3.0,  // Tatil %200
                        default => 1.5
                    };
                    $overtimeWage = $this->overtime_hours * ($this->employee->hourly_wage ?? 0) * $overtimeMultiplier;
                }
                break;

            case 'daily':
                $regularWage = ($this->employee->daily_wage ?? 0);

                // Fazla mesai için saatlik hesap (günlük ücret / 8 saat)
                if ($this->overtime_hours > 0) {
                    $hourlyRate = ($this->employee->daily_wage ?? 0) / 8;
                    $overtimeMultiplier = match($this->overtime_type) {
                        'weekday' => 1.5,
                        'weekend' => 2.0,
                        'holiday' => 3.0,
                        default => 1.5
                    };
                    $overtimeWage = $this->overtime_hours * $hourlyRate * $overtimeMultiplier;
                }
                break;

            case 'monthly':
                // Aylık maaşlılar için günlük hesap (maaş / 30 gün)
                $regularWage = ($this->employee->monthly_salary ?? 0) / 30;

                // Fazla mesai için saatlik hesap
                if ($this->overtime_hours > 0) {
                    $hourlyRate = ($this->employee->monthly_salary ?? 0) / 30 / 8;
                    $overtimeMultiplier = match($this->overtime_type) {
                        'weekday' => 1.5,
                        'weekend' => 2.0,
                        'holiday' => 3.0,
                        default => 1.5
                    };
                    $overtimeWage = $this->overtime_hours * $hourlyRate * $overtimeMultiplier;
                }
                break;
        }

        return round($regularWage + $overtimeWage, 2);
    }

    /**
     * Boot method - otomatik hafta bilgilerini set et
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($timesheet) {
            // Hafta bilgilerini otomatik set et
            $date = Carbon::parse($timesheet->work_date);
            $timesheet->year = $date->year;
            $timesheet->week_number = $date->weekOfYear;

            // entry_method yoksa default'u set et
            if (empty($timesheet->entry_method)) {
                $timesheet->entry_method = 'manual';
            }
        });

        static::updating(function ($timesheet) {
            // work_date değiştiyse hafta bilgilerini güncelle
            if ($timesheet->isDirty('work_date')) {
                $date = Carbon::parse($timesheet->work_date);
                $timesheet->year = $date->year;
                $timesheet->week_number = $date->weekOfYear;
            }
        });
    }
}
