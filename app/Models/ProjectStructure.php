<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectStructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'code',
        'name',
        'structure_type',
        'total_floors',
        'total_units',
        'total_area',
        'built_area',
        'status',
        'progress_percentage',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'supervisor_id',
        'description',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'total_floors' => 'integer',
        'total_units' => 'integer',
        'total_area' => 'decimal:2',
        'built_area' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'metadata' => 'array',
    ];

    protected $appends = [
        'calculated_total_floors',
        'calculated_total_units',
        'calculated_total_area',
        'calculated_progress',
    ];

    // İlişkiler

    /**
     * Yapının ait olduğu proje
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Yapı sorumlusu
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    /**
     * Yapıdaki katlar
     */
    public function floors(): HasMany
    {
        return $this->hasMany(ProjectFloor::class, 'structure_id');
    }

    /**
     * Yapıdaki birimler (daireler)
     */
    public function units(): HasMany
    {
        return $this->hasMany(ProjectUnit::class, 'structure_id');
    }

    /**
     * Yapıdaki iş atamaları
     */
    public function workAssignments(): HasMany
    {
        return $this->hasMany(WorkItemAssignment::class, 'structure_id');
    }

    /**
     * Yapıdaki puantajlar
     */
    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'structure_id');
    }

    // Accessor & Mutator

    /**
     * Yapı tipi insan okunabilir
     */
    public function getStructureTypeDisplayAttribute(): string
    {
        return match ($this->structure_type) {
            'residential_block' => 'Konut Blok',
            'office_block' => 'Ofis Blok',
            'commercial' => 'Ticari Yapı',
            'villa' => 'Villa',
            'infrastructure' => 'Altyapı',
            'mixed_use' => 'Karma Kullanım',
            'other' => 'Diğer',
            default => ucfirst($this->structure_type),
        };
    }

    /**
     * Durum insan okunabilir
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
     * Durum badge class
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
     * Gecikmiş mi?
     */
    public function getIsDelayedAttribute(): bool
    {
        return $this->planned_end_date &&
            now() > $this->planned_end_date &&
            $this->status !== 'completed';
    }

    /**
     * Hesaplanmış toplam kat sayısı
     */
    public function getCalculatedTotalFloorsAttribute(): int
    {
        return $this->total_floors ?? $this->floors()->count();
    }

    /**
     * Hesaplanmış toplam birim sayısı
     */
    public function getCalculatedTotalUnitsAttribute(): int
    {
        return $this->total_units ?? $this->units()->count();
    }

    /**
     * Hesaplanmış toplam alan
     */
    public function getCalculatedTotalAreaAttribute(): float
    {
        if ($this->total_area) {
            return (float) $this->total_area;
        }

        return (float) $this->units()->sum('gross_area') ?? 0;
    }

    /**
     * Hesaplanmış ilerleme yüzdesi
     */
    public function getCalculatedProgressAttribute(): float
    {
        if ($this->progress_percentage) {
            return (float) $this->progress_percentage;
        }

        return $this->calculateProgressPercentage();
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

    // Scope'lar

    /**
     * Aktif yapılar
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['in_progress', 'not_started']);
    }

    /**
     * Belirli tipteki yapılar
     */
    public function scopeByType($query, $type)
    {
        return $query->where('structure_type', $type);
    }

    /**
     * Belirli projedeki yapılar
     */
    public function scopeInProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Gecikmiş yapılar
     */
    public function scopeDelayed($query)
    {
        return $query->where('planned_end_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    // Helper metodlar

    /**
     * İlerleme yüzdesini hesapla (tüm iş atamalarının ortalaması)
     */
    public function calculateProgressPercentage(): float
    {
        $assignments = $this->workAssignments;

        if ($assignments->isEmpty()) {
            return 0;
        }

        return round($assignments->avg('progress_percentage'), 2);
    }

    /**
     * İlerleme yüzdesini güncelle
     */
    public function updateProgressPercentage(): void
    {
        $this->update([
            'progress_percentage' => $this->calculateProgressPercentage(),
        ]);
    }

    /**
     * Tamamlanmış iş sayısı
     */
    public function getCompletedWorkCountAttribute(): int
    {
        return $this->workAssignments()->where('status', 'completed')->count();
    }

    /**
     * Aktif iş sayısı
     */
    public function getActiveWorkCountAttribute(): int
    {
        return $this->workAssignments()->where('status', 'in_progress')->count();
    }

    /**
     * Yapıyı başlat
     */
    public function start(): void
    {
        $this->update([
            'status' => 'in_progress',
            'actual_start_date' => $this->actual_start_date ?? now(),
        ]);
    }

    /**
     * Yapıyı tamamla
     */
    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'actual_end_date' => now(),
            'progress_percentage' => 100,
        ]);
    }

    /**
     * Yapıyı beklet
     */
    public function hold(string $reason = null): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['hold_reason'] = $reason;
        $metadata['hold_date'] = now()->toDateString();

        $this->update([
            'status' => 'on_hold',
            'metadata' => $metadata,
        ]);
    }

    /**
     * İlerleme takip metodları (ProgressPayment bazlı)
     */

    /**
     * ProgressPayment ilişkisi
     */
    public function progressPayments(): HasMany
    {
        return $this->hasMany(ProgressPayment::class, 'project_structure_id');
    }

    /**
     * Blok ilerleme yüzdesi (ProgressPayment bazlı)
     * Katlardaki tüm işlerin ortalama ilerlemesi
     */
    public function getProgressPercentageAttribute(): float
    {
        $floors = $this->floors()->get();

        if ($floors->isEmpty()) {
            // Eğer kat yoksa, doğrudan bu bloğa bağlı progressPayments'a bak
            $payments = $this->progressPayments()->get();

            if ($payments->isEmpty()) {
                return 0;
            }

            $totalProgress = $payments->sum('completion_percentage');
            return round($totalProgress / $payments->count(), 2);
        }

        // Katlar varsa, katların ortalama ilerlemesi
        $totalProgress = $floors->sum(fn($floor) => $floor->progress_percentage ?? 0);
        return round($totalProgress / $floors->count(), 2);
    }

    /**
     * Blok ilerleme özeti
     */
    public function getProgressSummary(): array
    {
        $totalPayments = ProgressPayment::where('project_structure_id', $this->id)->count();
        $completedPayments = ProgressPayment::where('project_structure_id', $this->id)
            ->where('status', 'completed')
            ->count();

        $totalAmount = ProgressPayment::where('project_structure_id', $this->id)->sum('total_amount');
        $paidAmount = ProgressPayment::where('project_structure_id', $this->id)
            ->where('status', 'paid')
            ->sum('total_amount');

        return [
            'progress_percentage' => $this->progress_percentage,
            'total_floors' => $this->floors()->count(),
            'completed_floors' => $this->floors()->where('status', 'completed')->count(),
            'total_payments' => $totalPayments,
            'completed_payments' => $completedPayments,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'pending_amount' => $totalAmount - $paidAmount,
        ];
    }
}
