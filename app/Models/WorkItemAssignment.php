<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkItemAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'structure_id',
        'floor_id',
        'unit_id',
        'work_item_id',
        'assignment_type',
        'subcontractor_id',
        'quantity',
        'unit_price',
        'total_price',
        'completed_quantity',
        'remaining_quantity',
        'progress_percentage',
        'status',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'completed_quantity' => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
    ];

    protected $appends = [
        'location_display',
        'is_completed',
        'is_delayed',
    ];

    /**
     * Relationships
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(ProjectStructure::class, 'structure_id');
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(ProjectFloor::class, 'floor_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProjectUnit::class, 'unit_id');
    }

    public function workItem(): BelongsTo
    {
        return $this->belongsTo(WorkItem::class, 'work_item_id');
    }

    public function subcontractor(): BelongsTo
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function progressReports(): HasMany
    {
        return $this->hasMany(WorkProgress::class, 'assignment_id');
    }

    /**
     * Accessors
     */
    public function getLocationDisplayAttribute(): string
    {
        $parts = [];

        if ($this->structure) {
            $parts[] = $this->structure->code;
        }

        if ($this->floor) {
            $parts[] = $this->floor->floor_display;
        }

        if ($this->unit) {
            $parts[] = $this->unit->unit_code;
        }

        return empty($parts) ? 'Genel' : implode(' - ', $parts);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsDelayedAttribute(): bool
    {
        if ($this->is_completed || !$this->planned_end_date) {
            return false;
        }

        return now()->greaterThan($this->planned_end_date);
    }

    public function getAssignmentTypeDisplayAttribute(): string
    {
        $types = [
            'subcontractor' => 'Taşeron',
            'internal_team' => 'Kendi Ekibimiz',
        ];

        return $types[$this->assignment_type] ?? $this->assignment_type;
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'not_started' => 'Başlamadı',
            'in_progress' => 'Devam Ediyor',
            'completed' => 'Tamamlandı',
            'on_hold' => 'Beklemede',
            'cancelled' => 'İptal Edildi',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Scopes
     */
    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByStructure($query, $structureId)
    {
        return $query->where('structure_id', $structureId);
    }

    public function scopeByFloor($query, $floorId)
    {
        return $query->where('floor_id', $floorId);
    }

    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeByWorkItem($query, $workItemId)
    {
        return $query->where('work_item_id', $workItemId);
    }

    public function scopeBySubcontractor($query, $subcontractorId)
    {
        return $query->where('subcontractor_id', $subcontractorId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not_started');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDelayed($query)
    {
        return $query->where('planned_end_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Helper Methods
     */
    public function calculateRemainingAmount(): float
    {
        return $this->total_price * (1 - ($this->progress_percentage / 100));
    }

    public function calculateCompletedAmount(): float
    {
        return $this->total_price * ($this->progress_percentage / 100);
    }

    public function updateProgress(float $completedQuantity): void
    {
        $this->update([
            'completed_quantity' => $completedQuantity,
            'remaining_quantity' => $this->quantity - $completedQuantity,
            'progress_percentage' => $this->quantity > 0
                ? round(($completedQuantity / $this->quantity) * 100, 2)
                : 0,
        ]);

        // Auto-update status
        if ($this->progress_percentage >= 100 && $this->status !== 'completed') {
            $this->update([
                'status' => 'completed',
                'actual_end_date' => now(),
            ]);
        } elseif ($this->progress_percentage > 0 && $this->status === 'not_started') {
            $this->update([
                'status' => 'in_progress',
                'actual_start_date' => now(),
            ]);
        }
    }

    public function start(): void
    {
        if ($this->status === 'not_started') {
            $this->update([
                'status' => 'in_progress',
                'actual_start_date' => now(),
            ]);
        }
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'progress_percentage' => 100,
            'completed_quantity' => $this->quantity,
            'remaining_quantity' => 0,
            'actual_end_date' => now(),
        ]);
    }

    public function hold(): void
    {
        $this->update(['status' => 'on_hold']);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
