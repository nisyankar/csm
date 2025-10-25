<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectFloor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'structure_id',
        'floor_number',
        'floor_type',
        'name',
        'total_area',
        'unit_count',
        'status',
        'notes',
    ];

    protected $casts = [
        'floor_number' => 'integer',
        'total_area' => 'decimal:2',
        'unit_count' => 'integer',
    ];

    protected $appends = [
        'floor_display',
    ];

    /**
     * Relationships
     */
    public function structure(): BelongsTo
    {
        return $this->belongsTo(ProjectStructure::class, 'structure_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProjectUnit::class, 'floor_id');
    }

    public function workAssignments(): HasMany
    {
        return $this->hasMany(WorkItemAssignment::class, 'floor_id');
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'floor_id');
    }

    /**
     * Accessors
     */
    public function getFloorDisplayAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }

        if ($this->floor_number < 0) {
            return 'Bodrum ' . abs($this->floor_number);
        } elseif ($this->floor_number == 0) {
            return 'Zemin Kat';
        } else {
            return $this->floor_number . '. Kat';
        }
    }

    public function getFloorTypeDisplayAttribute(): string
    {
        $types = [
            'basement' => 'Bodrum',
            'ground' => 'Zemin Kat',
            'standard' => 'Normal Kat',
            'roof' => 'Çatı',
            'penthouse' => 'Çatı Dubleks',
            'mezzanine' => 'Asma Kat',
            'technical' => 'Teknik Kat',
        ];

        return $types[$this->floor_type] ?? $this->floor_type;
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'planned' => 'Planlandı',
            'in_progress' => 'Devam Ediyor',
            'completed' => 'Tamamlandı',
            'on_hold' => 'Beklemede',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Scopes
     */
    public function scopeByStructure($query, $structureId)
    {
        return $query->where('structure_id', $structureId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('floor_type', $type);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Helper Methods
     */
    public function calculateUnitCount(): int
    {
        return $this->units()->count();
    }

    public function calculateTotalArea(): float
    {
        return $this->units()->sum('gross_area') ?? 0;
    }

    public function updateStats(): void
    {
        $this->update([
            'unit_count' => $this->calculateUnitCount(),
            'total_area' => $this->calculateTotalArea(),
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
        return $this->hasMany(ProgressPayment::class, 'project_floor_id');
    }

    /**
     * Kat ilerleme yüzdesi (ProgressPayment bazlı)
     */
    public function getProgressPercentageAttribute(): float
    {
        $payments = $this->progressPayments()->get();

        if ($payments->isEmpty()) {
            return 0;
        }

        $totalProgress = $payments->sum('completion_percentage');
        return round($totalProgress / $payments->count(), 2);
    }

    /**
     * Kat ilerleme özeti
     */
    public function getProgressSummary(): array
    {
        $totalPayments = $this->progressPayments()->count();
        $completedPayments = $this->progressPayments()->where('status', 'completed')->count();
        $totalAmount = $this->progressPayments()->sum('total_amount');
        $paidAmount = $this->progressPayments()->where('status', 'paid')->sum('total_amount');

        return [
            'progress_percentage' => $this->progress_percentage,
            'total_payments' => $totalPayments,
            'completed_payments' => $completedPayments,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'pending_amount' => $totalAmount - $paidAmount,
        ];
    }
}
