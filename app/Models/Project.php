<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_code',
        'name',
        'description',
        'weekend_days',
        'location',
        'city',
        'district',
        'full_address',
        'coordinates',
        'start_date',
        'planned_end_date',
        'actual_end_date',
        'budget',
        'labor_budget',
        'spent_amount',
        'project_manager_id',
        'site_manager_id',
        'contact_phone',
        'contact_email',
        'status',
        'type',
        'priority',
        'client_name',
        'client_contact',
        'estimated_employees',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'planned_end_date' => 'date:Y-m-d',
        'actual_end_date' => 'date:Y-m-d',
        'budget' => 'decimal:2',
        'labor_budget' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'estimated_employees' => 'integer',
        'weekend_days' => 'array',
    ];

    protected $appends = [
        'budget_usage_percentage',
        'remaining_budget',
        'project_duration',
        'elapsed_days',
        'completion_percentage',
        'is_active',
        'is_delayed',
    ];

    // İlişkiler

    /**
     * Proje yöneticisi
     */
    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'project_manager_id');
    }

    /**
     * Şantiye şefi
     */
    public function siteManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'site_manager_id');
    }

    /**
     * Projede çalışan personeller (many-to-many)
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_project')
                    ->withPivot([
                        'assigned_date', 'start_date', 'end_date', 'planned_end_date',
                        'role_in_project', 'responsibilities', 'assignment_type',
                        'work_percentage', 'daily_hours', 'project_daily_rate',
                        'project_hourly_rate', 'has_project_bonus', 'project_bonus_amount',
                        'status', 'performance_score', 'performance_notes',
                        'assigned_by', 'approved_by', 'approved_at'
                    ])
                    ->withTimestamps();
    }

    /**
     * Projenin ana personeli (current_project_id ile bağlı)
     */
    public function currentEmployees(): HasMany
    {
        return $this->hasMany(Employee::class, 'current_project_id');
    }

    /**
     * Proje bölümleri
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Proje puantajları
     */
    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    /**
     * Projede çalışan taşeronlar (many-to-many)
     */
    public function subcontractors(): BelongsToMany
    {
        return $this->belongsToMany(Subcontractor::class, 'project_subcontractor')
                    ->withPivot([
                        'work_type', 'assigned_date', 'start_date', 'end_date', 'assigned_by',
                        'scope_of_work', 'contract_amount', 'status', 'notes'
                    ])
                    ->withTimestamps();
    }

    /**
     * Aktif taşeronlar
     */
    public function activeSubcontractors(): BelongsToMany
    {
        return $this->subcontractors()->wherePivot('status', 'active');
    }

    /**
     * Proje yapıları (Bloklar/Binalar) - Faz 1
     */
    public function structures(): HasMany
    {
        return $this->hasMany(ProjectStructure::class);
    }

    /**
     * Proje katları (Tüm yapılardaki katlar) - Faz 1
     */
    public function floors(): HasManyThrough
    {
        return $this->hasManyThrough(ProjectFloor::class, ProjectStructure::class);
    }

    /**
     * Proje birimleri (Tüm yapılardaki daireler/ofisler) - Faz 1
     */
    public function units(): HasManyThrough
    {
        return $this->hasManyThrough(ProjectUnit::class, ProjectStructure::class);
    }

    /**
     * İş atamaları - Faz 1
     */
    public function workAssignments(): HasMany
    {
        return $this->hasMany(WorkItemAssignment::class);
    }

    /**
     * İş ilerleme raporları - Faz 1
     */
    public function workProgressReports(): HasManyThrough
    {
        return $this->hasManyThrough(WorkProgress::class, WorkItemAssignment::class, 'project_id', 'assignment_id');
    }

    // Accessor ve Mutator'lar

    /**
     * Proje durumu badge class'ı
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'planning' => 'badge-warning',
            'active' => 'badge-success',
            'on_hold' => 'badge-info',
            'completed' => 'badge-primary',
            'cancelled' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Proje türü human-readable
     */
    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'residential' => 'Konut',
            'commercial' => 'Ticari',
            'infrastructure' => 'Altyapı',
            'industrial' => 'Endüstriyel',
            'other' => 'Diğer',
            default => ucfirst($this->type),
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
     * Proje süresi (gün)
     */
    public function getProjectDurationAttribute(): ?int
    {
        if (!$this->start_date || !$this->planned_end_date) {
            return null;
        }
        return $this->start_date->diffInDays($this->planned_end_date);
    }

    /**
     * Geçen süre (gün)
     */
    public function getElapsedDaysAttribute(): int
    {
        return $this->start_date ? (int) $this->start_date->diffInDays(now()) : 0;
    }

    /**
     * Proje tamamlanma yüzdesi (tarih bazlı)
     */
    public function getCompletionPercentageAttribute(): float
    {
        if (!$this->project_duration || $this->project_duration == 0) {
            return 0;
        }
        return min(round(($this->elapsed_days / $this->project_duration) * 100, 2), 100);
    }

    /**
     * Aktif durumda mı?
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Proje gecikmiş mi?
     */
    public function getIsDelayedAttribute(): bool
    {
        return $this->planned_end_date && now() > $this->planned_end_date && $this->status !== 'completed';
    }

    // Scope'lar

    /**
     * Aktif projeler
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Belirli türdeki projeler
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Belirli şehirdeki projeler
     */
    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Belirli yöneticinin projeleri
     */
    public function scopeByManager($query, $managerId)
    {
        return $query->where('project_manager_id', $managerId)
                    ->orWhere('site_manager_id', $managerId);
    }

    /**
     * Gecikmiş projeler
     */
    public function scopeDelayed($query)
    {
        return $query->where('planned_end_date', '<', now())
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Bütçesi aşılmış projeler
     */
    public function scopeBudgetExceeded($query)
    {
        return $query->whereRaw('spent_amount > budget');
    }

    // Helper metodlar

    /**
     * Projeye personel ata
     */
    public function assignEmployee(Employee $employee, array $pivotData = []): void
    {
        $defaultData = [
            'assigned_date' => now(),
            'status' => 'assigned',
            'assignment_type' => 'full_time',
            'work_percentage' => 100,
        ];

        $this->employees()->attach($employee->id, array_merge($defaultData, $pivotData));
    }

    /**
     * Projeden personel çıkar
     */
    public function removeEmployee(Employee $employee): void
    {
        $this->employees()->updateExistingPivot($employee->id, [
            'status' => 'completed',
            'end_date' => now(),
        ]);
    }

    /**
     * Aktif personel sayısı
     */
    public function getActiveEmployeeCount(): int
    {
        return $this->employees()
                   ->wherePivot('status', 'active')
                   ->count();
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
     * Proje kod oluştur
     */
    public function generateProjectCode(): string
    {
        $cityCode = strtoupper(substr($this->city, 0, 3));
        $typeCode = strtoupper(substr($this->type, 0, 3));
        $yearCode = now()->format('y');
        $sequence = Project::whereYear('created_at', now()->year)->count() + 1;

        $this->project_code = sprintf('%s-%s-%s-%03d', $cityCode, $typeCode, $yearCode, $sequence);
        $this->save();

        return $this->project_code;
    }

    /**
     * Belirli bir günün hafta sonu mu olduğunu kontrol et
     */
    public function isWeekendDay(\DateTime $date): bool
    {
        $dayOfWeek = strtolower($date->format('l')); // 'monday', 'tuesday', etc.
        $weekendDays = $this->weekend_days ?? ['saturday', 'sunday'];

        return in_array($dayOfWeek, $weekendDays);
    }

    /**
     * Hafta sonu günlerinin Türkçe adlarını getir
     */
    public function getWeekendDaysDisplayAttribute(): string
    {
        $weekendDays = $this->weekend_days ?? ['saturday', 'sunday'];
        $dayNames = [
            'monday' => 'Pazartesi',
            'tuesday' => 'Salı',
            'wednesday' => 'Çarşamba',
            'thursday' => 'Perşembe',
            'friday' => 'Cuma',
            'saturday' => 'Cumartesi',
            'sunday' => 'Pazar',
        ];

        $turkishDays = array_map(fn($day) => $dayNames[$day] ?? $day, $weekendDays);

        return implode(', ', $turkishDays);
    }

    /**
     * İlerleme takip metodları
     */

    /**
     * Proje ilerleme hesaplaması
     * Tüm blokların ortalama ilerlemesini döndürür
     */
    public function getProgressPercentageAttribute(): float
    {
        $structures = $this->structures()->get();

        if ($structures->isEmpty()) {
            return 0;
        }

        $totalProgress = $structures->sum(function ($structure) {
            return $structure->progress_percentage ?? 0;
        });

        return round($totalProgress / $structures->count(), 2);
    }

    /**
     * İlerleme özeti (dashboard için)
     */
    public function getProgressSummary(): array
    {
        $totalPayments = ProgressPayment::forProject($this->id)->count();
        $completedPayments = ProgressPayment::forProject($this->id)->byStatus('completed')->count();
        $paidPayments = ProgressPayment::forProject($this->id)->byStatus('paid')->count();

        $totalAmount = ProgressPayment::forProject($this->id)->sum('total_amount');
        $paidAmount = ProgressPayment::forProject($this->id)->byStatus('paid')->sum('total_amount');

        return [
            'progress_percentage' => $this->progress_percentage,
            'total_structures' => $this->structures()->count(),
            'completed_structures' => $this->structures()->where('status', 'completed')->count(),
            'total_payments' => $totalPayments,
            'completed_payments' => $completedPayments,
            'paid_payments' => $paidPayments,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'pending_amount' => $totalAmount - $paidAmount,
        ];
    }

    /**
     * Proje ilerleme detayları (Blok bazında)
     */
    public function getDetailedProgress(): array
    {
        return $this->structures()->get()->map(function ($structure) {
            return [
                'structure_id' => $structure->id,
                'structure_name' => $structure->name,
                'progress_percentage' => $structure->progress_percentage,
                'status' => $structure->status,
                'floors_count' => $structure->floors()->count(),
                'completed_floors' => $structure->floors()->where('status', 'completed')->count(),
            ];
        })->toArray();
    }

    /**
     * ProgressPayment ilişkisi
     */
    public function progressPayments(): HasMany
    {
        return $this->hasMany(ProgressPayment::class);
    }

    /**
     * Quantity ilişkisi (Keşif & Metraj)
     */
    public function quantities(): HasMany
    {
        return $this->hasMany(Quantity::class);
    }

    /**
     * Contracts ilişkisi (Sözleşmeler)
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Construction Permits ilişkisi (Ruhsat ve İzinler)
     */
    public function constructionPermits(): HasMany
    {
        return $this->hasMany(ConstructionPermit::class);
    }

    /**
     * Projeye ait denetimler
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }
}