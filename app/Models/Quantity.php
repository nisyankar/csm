<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quantity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'work_item_id',
        'project_structure_id',
        'project_floor_id',
        'project_unit_id',
        'planned_quantity',
        'completed_quantity',
        'unit',
        'measurement_date',
        'measurement_method',
        'verified_by',
        'approved_by',
        'verified_at',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:2',
        'completed_quantity' => 'decimal:2',
        'measurement_date' => 'date',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected $appends = ['completion_percentage', 'remaining_quantity'];

    /**
     * İlerleme yüzdesi hesaplama
     */
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->planned_quantity == 0) {
            return 0;
        }

        return round(($this->completed_quantity / $this->planned_quantity) * 100, 2);
    }

    /**
     * Kalan metraj hesaplama
     */
    public function getRemainingQuantityAttribute(): float
    {
        return max(0, $this->planned_quantity - $this->completed_quantity);
    }

    /**
     * İlişkiler
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function workItem(): BelongsTo
    {
        return $this->belongsTo(WorkItem::class);
    }

    public function projectStructure(): BelongsTo
    {
        return $this->belongsTo(ProjectStructure::class);
    }

    public function projectFloor(): BelongsTo
    {
        return $this->belongsTo(ProjectFloor::class);
    }

    public function projectUnit(): BelongsTo
    {
        return $this->belongsTo(ProjectUnit::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Hakediş ilişkisi
     */
    public function progressPayments(): HasMany
    {
        return $this->hasMany(ProgressPayment::class);
    }

    /**
     * Scopes
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForWorkItem($query, $workItemId)
    {
        return $query->where('work_item_id', $workItemId);
    }

    public function scopeForStructure($query, $structureId)
    {
        return $query->where('project_structure_id', $structureId);
    }

    public function scopeForFloor($query, $floorId)
    {
        return $query->where('project_floor_id', $floorId);
    }

    public function scopeForUnit($query, $unitId)
    {
        return $query->where('project_unit_id', $unitId);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_by');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_by');
    }

    public function scopePending($query)
    {
        return $query->whereNull('verified_by');
    }

    public function scopeCompleted($query)
    {
        return $query->where('completed_quantity', '>=', \DB::raw('planned_quantity'));
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('measurement_date', [$startDate, $endDate]);
    }

    /**
     * Helper Methods
     */
    public function isCompleted(): bool
    {
        return $this->completed_quantity >= $this->planned_quantity;
    }

    public function isVerified(): bool
    {
        return $this->verified_by !== null;
    }

    public function isApproved(): bool
    {
        return $this->approved_by !== null;
    }

    /**
     * Metraj doğrulama
     */
    public function verify(int $userId): void
    {
        $this->update([
            'verified_by' => $userId,
            'verified_at' => now(),
        ]);
    }

    /**
     * Metraj onaylama
     */
    public function approve(int $userId): void
    {
        if (!$this->isVerified()) {
            throw new \Exception('Metraj önce doğrulanmalıdır.');
        }

        $this->update([
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Tamamlanan metrajı güncelleme
     */
    public function updateCompletedQuantity(float $completedQuantity): void
    {
        $this->update([
            'completed_quantity' => $completedQuantity,
            'measurement_date' => now(),
        ]);

        // İlgili hakediş kayıtlarını güncelle
        $this->syncProgressPayments();
    }

    /**
     * Hakediş kayıtlarını metrajla senkronize et
     */
    private function syncProgressPayments(): void
    {
        // Bu metrajın ilişkili olduğu hakediş kayıtlarını güncelle
        $this->progressPayments()->update([
            'completed_quantity' => $this->completed_quantity,
        ]);
    }
}
