<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'project_id',
        'parent_department_id',
        'supervisor_id',
        'type',
        'budget',
        'spent_amount',
        'status',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'priority',
        'estimated_employees',
        'notes',
        'location_description',
        'custom_fields',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'estimated_employees' => 'integer',
        'custom_fields' => 'array',
    ];

    // İlişkiler

    /**
     * Bölümün ait olduğu proje
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Üst bölüm
     */
    public function parentDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_department_id');
    }

    /**
     * Alt bölümler
     */
    public function subDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_department_id');
    }

    /**
     * Bölüm sorumlusu
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    /**
     * Bölüm puantajları
     */
    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    // Accessor ve Mutator'lar

    /**
     * Bölüm türü human-readable
     */
    public function getTypeDisplayAttribute(): string
    {
        return match ($this->type) {
            'structural' => 'Yapısal',
            'mechanical' => 'Mekanik',
            'electrical' => 'Elektrik',
            'finishing' => 'Finishing',
            'landscaping' => 'Peyzaj',
            'safety' => 'İş Güvenliği',
            'quality' => 'Kalite Kontrol',
            'logistics' => 'Lojistik',
            'administration' => 'İdari',
            'other' => 'Diğer',
            default => ucfirst($this->type),
        };
    }

    /**
     * Durum human-readable
     */
    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'not_started' => 'Başlanmadı',
            'in_progress' => 'Devam Ediyor',
            'completed' => 'Tamamlandı',
            'on_hold' => 'Beklemede',
            'cancelled' => 'İptal Edildi',
            default => ucfirst($this->status),
        };
    }

    /**
     * Durum badge class'ı
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'not_started' => 'badge-secondary',
            'in_progress' => 'badge-primary',
            'completed' => 'badge-success',
            'on_hold' => 'badge-warning',
            'cancelled' => 'badge-danger',
            default => 'badge-light',
        };
    }

    /**
     * Bütçe kullanım yüzdesi
     */
    public function getBudgetUsagePercentageAttribute(): float
    {
        if (!$this->budget || $this->budget == 0) {
            return 0;
        }
        return round(($this->spent_amount / $this->budget) * 100, 2);
    }

    /**
     * Kalan bütçe
     */
    public function getRemainingBudgetAttribute(): float
    {
        return $this->budget - $this->spent_amount;
    }

    /**
     * Planlanan süre (gün)
     */
    public function getPlannedDurationAttribute(): ?int
    {
        if (!$this->planned_start_date || !$this->planned_end_date) {
            return null;
        }
        return $this->planned_start_date->diffInDays($this->planned_end_date);
    }

    /**
     * Gerçek süre (gün)
     */
    public function getActualDurationAttribute(): ?int
    {
        if (!$this->actual_start_date) {
            return null;
        }

        $endDate = $this->actual_end_date ?? now();
        return $this->actual_start_date->diffInDays($endDate);
    }

    /**
     * Tamamlanma yüzdesi
     */
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->status === 'completed') {
            return 100;
        }

        if ($this->status === 'not_started') {
            return 0;
        }

        if (!$this->planned_duration || $this->planned_duration == 0) {
            return 0;
        }

        $elapsed = $this->actual_start_date ? $this->actual_start_date->diffInDays(now()) : 0;
        return min(round(($elapsed / $this->planned_duration) * 100, 2), 100);
    }

    /**
     * Gecikmiş mi?
     */
    public function getIsDelayedAttribute(): bool
    {
        return $this->planned_end_date &&
            now() > $this->planned_end_date &&
            $this->status !== 'completed';
    }

    /**
     * Departmandaki çalışanlar
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Ana bölüm mü? (parent yok)
     */
    public function getIsMainDepartmentAttribute(): bool
    {
        return is_null($this->parent_department_id);
    }

    /**
     * Alt bölümü var mı?
     */
    public function getHasSubDepartmentsAttribute(): bool
    {
        return $this->subDepartments()->exists();
    }

    /**
     * Tam hiyerarşik ad
     */
    public function getFullNameAttribute(): string
    {
        if ($this->parentDepartment) {
            return $this->parentDepartment->full_name . ' > ' . $this->name;
        }
        return $this->name;
    }

    // Scope'lar

    /**
     * Aktif bölümler
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['in_progress', 'not_started']);
    }

    /**
     * Belirli türdeki bölümler
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Ana bölümler (parent yok)
     */
    public function scopeMainDepartments($query)
    {
        return $query->whereNull('parent_department_id');
    }

    /**
     * Alt bölümler
     */
    public function scopeSubDepartments($query)
    {
        return $query->whereNotNull('parent_department_id');
    }

    /**
     * Belirli projedeki bölümler
     */
    public function scopeInProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Gecikmiş bölümler
     */
    public function scopeDelayed($query)
    {
        return $query->where('planned_end_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Bütçesi aşılmış bölümler
     */
    public function scopeBudgetExceeded($query)
    {
        return $query->whereRaw('spent_amount > budget');
    }

    /**
     * Belirli supervisor'ın bölümleri
     */
    public function scopeBySupervisor($query, $supervisorId)
    {
        return $query->where('supervisor_id', $supervisorId);
    }

    // Helper metodlar

    /**
     * Bölümü başlat
     */
    public function startDepartment(): void
    {
        $this->update([
            'status' => 'in_progress',
            'actual_start_date' => now(),
        ]);
    }

    /**
     * Bölümü tamamla
     */
    public function completeDepartment(): void
    {
        $this->update([
            'status' => 'completed',
            'actual_end_date' => now(),
        ]);
    }

    /**
     * Bölümü bekletmeye al
     */
    public function holdDepartment(string $reason = null): void
    {
        $customFields = $this->custom_fields ?? [];
        $customFields['hold_reason'] = $reason;
        $customFields['hold_date'] = now()->toDateString();

        $this->update([
            'status' => 'on_hold',
            'custom_fields' => $customFields,
        ]);
    }

    /**
     * Toplam işçilik maliyeti hesapla
     */
    public function calculateTotalLaborCost(): float
    {
        return $this->timesheets()
            ->where('approval_status', 'approved')
            ->sum('calculated_wage');
    }

    /**
     * Aktif çalışan sayısı
     */
    public function getActiveEmployeeCount(): int
    {
        return $this->timesheets()
            ->where('work_date', now()->toDateString())
            ->where('attendance_type', 'present')
            ->distinct('employee_id')
            ->count();
    }

    /**
     * Tüm alt bölümleri getir (recursive)
     */
    public function getAllSubDepartments(): \Illuminate\Database\Eloquent\Collection
    {
        $allSubs = collect();

        foreach ($this->subDepartments as $sub) {
            $allSubs->push($sub);
            $allSubs = $allSubs->merge($sub->getAllSubDepartments());
        }

        return $allSubs;
    }

    /**
     * Bölüm kodu oluştur
     */
    public function generateDepartmentCode(): string
    {
        $projectCode = $this->project->project_code ?? 'PRJ';
        $typeCode = strtoupper(substr($this->type, 0, 3));
        $sequence = Department::where('project_id', $this->project_id)->count() + 1;

        $this->code = sprintf('%s-%s-%03d', $projectCode, $typeCode, $sequence);
        $this->save();

        return $this->code;
    }
}
