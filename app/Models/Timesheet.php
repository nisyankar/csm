<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'project_id',
        'department_id',
        'work_date',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'total_minutes',
        'regular_minutes',
        'overtime_minutes',
        'break_minutes',
        'shift_type',
        'attendance_type',
        'overtime_hours',
        'overtime_type',
        'entry_method',
        'entered_by',
        'entered_at',
        'approval_status',
        'submitted_at',
        'first_approved_at',
        'final_approved_at',
        'entry_location',
        'exit_location',
        'notes',
        'absence_reason',
        'late_reason',
        'daily_rate',
        'hourly_rate',
        'calculated_wage',
        'is_revised',
        'revision_count',
        'original_timesheet_id',
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
        'total_minutes' => 'integer',
        'regular_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'break_minutes' => 'integer',
        'overtime_hours' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'calculated_wage' => 'decimal:2',
        'is_revised' => 'boolean',
        'revision_count' => 'integer',
        'entered_at' => 'datetime',
        'submitted_at' => 'datetime',
        'first_approved_at' => 'datetime',
        'final_approved_at' => 'datetime',
    ];

    // İlişkiler

    /**
     * Puantaja ait personel
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Puantaja ait proje
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Puantaja ait bölüm
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Puantajı giren kullanıcı
     */
    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    /**
     * Puantaj onayları
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(TimesheetApproval::class);
    }

    /**
     * Puantaj revizyonları
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(TimesheetRevision::class);
    }

    /**
     * Orijinal puantaj (revizyon ise)
     */
    public function originalTimesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class, 'original_timesheet_id');
    }

    /**
     * Bu puantajın revizyonları
     */
    public function revisedTimesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'original_timesheet_id');
    }

    /**
     * Puantaja ait yapı (Blok) - Faz 1
     */
    public function structure(): BelongsTo
    {
        return $this->belongsTo(ProjectStructure::class, 'structure_id');
    }

    /**
     * Puantaja ait kat - Faz 1
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(ProjectFloor::class, 'floor_id');
    }

    /**
     * Puantaja ait birim (Daire/Ofis) - Faz 1
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProjectUnit::class, 'unit_id');
    }

    /**
     * Puantaja ait iş kalemi - Faz 1
     */
    public function workItem(): BelongsTo
    {
        return $this->belongsTo(WorkItem::class, 'work_item_id');
    }

    /**
     * Puantaja ait iş ataması - Faz 1
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(WorkItemAssignment::class, 'assignment_id');
    }

    // Accessor ve Mutator'lar

    /**
     * Toplam çalışma saati (saat:dakika formatında)
     */
    public function getTotalHoursAttribute(): string
    {
        $hours = intval($this->total_minutes / 60);
        $minutes = $this->total_minutes % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Normal çalışma saati
     */
    public function getRegularHoursAttribute(): string
    {
        $hours = intval($this->regular_minutes / 60);
        $minutes = $this->regular_minutes % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Fazla mesai saati (hesaplanan - dakikadan saat:dakika formatına çevrilmiş)
     * NOT: Bu accessor overtime_minutes'tan hesaplanır.
     * Manuel girilen overtime_hours için doğrudan overtime_hours attribute'unu kullanın.
     */
    public function getOvertimeHoursDisplayAttribute(): string
    {
        $hours = intval($this->overtime_minutes / 60);
        $minutes = $this->overtime_minutes % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Onay durumu human-readable
     */
    public function getApprovalStatusDisplayAttribute(): string
    {
        $statuses = [
            'draft' => 'Taslak',
            'pending' => 'Onay Bekliyor',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            'revision' => 'Revizyon Gerekli',
        ];

        return $statuses[$this->approval_status] ?? ucfirst($this->approval_status);
    }

    /**
     * Onay durumu badge class'ı
     */
    public function getApprovalStatusBadgeAttribute(): string
    {
        $badges = [
            'draft' => 'badge-secondary',
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            'revision' => 'badge-info',
        ];

        return $badges[$this->approval_status] ?? 'badge-light';
    }

    /**
     * Devam durumu human-readable
     */
    public function getAttendanceTypeDisplayAttribute(): string
    {
        $types = [
            'present' => 'Normal Çalışma',
            'absent' => 'Devamsız',
            'late' => 'Geç Gelme',
            'early_leave' => 'Erken Çıkma',
            'sick_leave' => 'Hastalık İzni',
            'annual_leave' => 'Yıllık İzin',
            'excuse_leave' => 'Mazeret İzni',
            'unpaid_leave' => 'Ücretsiz İzin',
        ];

        return $types[$this->attendance_type] ?? ucfirst($this->attendance_type);
    }

    /**
     * Düzenlenebilir mi?
     */
    public function getIsEditableAttribute(): bool
    {
        return in_array($this->approval_status, ['draft', 'rejected', 'revision']);
    }

    /**
     * Onaylanmış mı?
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Geç kalma var mı?
     */
    public function getIsLateAttribute(): bool
    {
        return $this->attendance_type === 'late' || !empty($this->late_reason);
    }

    /**
     * İzinli mi?
     */
    public function getIsOnLeaveAttribute(): bool
    {
        return in_array($this->attendance_type, ['sick_leave', 'annual_leave', 'excuse_leave', 'unpaid_leave']);
    }

    // Scope'lar

    /**
     * Belirli tarih aralığındaki puantajlar
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('work_date', [$startDate, $endDate]);
    }

    /**
     * Belirli ayın puantajları
     */
    public function scopeInMonth($query, $month, $year = null)
    {
        $year = $year ?? now()->year;
        return $query->whereMonth('work_date', $month)
                    ->whereYear('work_date', $year);
    }

    /**
     * Belirli onay durumundaki puantajlar
     */
    public function scopeByApprovalStatus($query, $status)
    {
        return $query->where('approval_status', $status);
    }

    /**
     * Onay bekleyen puantajlar
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Onaylanmış puantajlar
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Belirli personelin puantajları
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Belirli projenin puantajları
     */
    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Fazla mesai olan puantajlar
     */
    public function scopeWithOvertime($query)
    {
        return $query->where('overtime_minutes', '>', 0);
    }

    /**
     * İzinli puantajlar
     */
    public function scopeOnLeave($query)
    {
        return $query->whereIn('attendance_type', ['sick_leave', 'annual_leave', 'excuse_leave', 'unpaid_leave']);
    }

    // Helper metodlar

    /**
     * Çalışma sürelerini hesapla
     */
    public function calculateWorkingTime(): void
    {
        if (!$this->start_time || !$this->end_time) {
            return;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        
        // Toplam dakika
        $totalMinutes = $end->diffInMinutes($start);
        
        // Mola süresi çıkar
        if ($this->break_start && $this->break_end) {
            $breakStart = Carbon::parse($this->break_start);
            $breakEnd = Carbon::parse($this->break_end);
            $this->break_minutes = $breakEnd->diffInMinutes($breakStart);
            $totalMinutes -= $this->break_minutes;
        }
        
        $this->total_minutes = $totalMinutes;
        
        // Normal mesai (8 saat = 480 dakika)
        $regularMinutes = min($totalMinutes, 480);
        $this->regular_minutes = $regularMinutes;
        
        // Fazla mesai
        $this->overtime_minutes = max(0, $totalMinutes - 480);
    }

    /**
     * Ücret hesapla
     */
    public function calculateWage(): void
    {
        $employee = $this->employee;
        if (!$employee) return;
        
        // Güncel ücret bilgilerini al
        $this->daily_rate = $employee->daily_wage ?? 0;
        $this->hourly_rate = $employee->hourly_wage ?? 0;
        
        if ($this->is_on_leave) {
            // İzinli günlerde ücret hesaplama
            $this->calculated_wage = $this->attendance_type === 'unpaid_leave' ? 0 : $this->daily_rate;
        } else {
            // Normal çalışma günü
            if ($employee->wage_type === 'daily') {
                $this->calculated_wage = $this->daily_rate;
            } elseif ($employee->wage_type === 'hourly') {
                $regularWage = ($this->regular_minutes / 60) * $this->hourly_rate;
                $overtimeWage = ($this->overtime_minutes / 60) * $this->hourly_rate * 1.5; // %50 fazla
                $this->calculated_wage = $regularWage + $overtimeWage;
            }
        }
    }

    /**
     * Onaya gönder
     */
    public function submitForApproval(): void
    {
        $this->update([
            'approval_status' => 'pending',
            'submitted_at' => now(),
        ]);
        
        // İlk onay kaydı oluştur
        $this->createApprovalRecord('first');
    }

    /**
     * Onay kaydı oluştur
     */
    private function createApprovalRecord(string $level): void
    {
        $approver = $this->getApproverForLevel($level);
        
        if ($approver) {
            $this->approvals()->create([
                'approver_id' => $approver->id,
                'approval_level' => $level,
                'status' => 'pending',
                'assigned_at' => now(),
            ]);
        }
    }

    /**
     * Seviyeye göre onaylayıcı bul
     */
    private function getApproverForLevel(string $level): ?Employee
    {
        switch ($level) {
            case 'first':
                return $this->employee->manager;
            case 'second':
                return $this->employee->manager?->manager;
            default:
                return null;
        }
    }

    /**
     * Revizyon oluştur
     */
    public function createRevision(Employee $revisedBy, string $reason, array $changes): TimesheetRevision
    {
        return $this->revisions()->create([
            'revised_by' => $revisedBy->id,
            'revision_number' => $this->revision_count + 1,
            'revision_type' => 'modification',
            'revision_reason' => $reason,
            'reason_category' => 'data_error',
            'changes_made' => $changes,
            'old_values' => $this->getOriginal(),
            'new_values' => $this->getAttributes(),
            'requested_at' => now(),
        ]);
    }

    /**
     * Puantajı klonla (revizyon için)
     */
    public function createRevisionCopy(array $changes = []): self
    {
        $copy = $this->replicate();
        $copy->original_timesheet_id = $this->id;
        $copy->is_revised = true;
        $copy->revision_count = $this->revision_count + 1;
        $copy->approval_status = 'draft';
        
        foreach ($changes as $field => $value) {
            $copy->{$field} = $value;
        }
        
        $copy->save();
        return $copy;
    }
}