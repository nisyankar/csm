<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'location',
        'responsible_user_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function receivedTransfers(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'to_warehouse_id');
    }

    public function stockCounts(): HasMany
    {
        return $this->hasMany(StockCount::class);
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Pasif';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->is_active ? 'green' : 'red';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    // Helper Methods
    public function getTotalMaterialsCount(): int
    {
        return $this->stockMovements()
            ->selectRaw('COUNT(DISTINCT material_id) as count')
            ->value('count') ?? 0;
    }

    public function getTotalStockValue(): float
    {
        return $this->stockMovements()
            ->whereIn('movement_type', ['in', 'transfer'])
            ->sum(\DB::raw('quantity * COALESCE(unit_price, 0)'));
    }

    public function getStockByMaterial($materialId): float
    {
        $inbound = $this->stockMovements()
            ->where('material_id', $materialId)
            ->whereIn('movement_type', ['in', 'adjustment'])
            ->sum('quantity');

        $outbound = $this->stockMovements()
            ->where('material_id', $materialId)
            ->where('movement_type', 'out')
            ->sum('quantity');

        $transfersIn = $this->receivedTransfers()
            ->where('material_id', $materialId)
            ->where('movement_type', 'transfer')
            ->sum('quantity');

        $transfersOut = $this->stockMovements()
            ->where('material_id', $materialId)
            ->where('movement_type', 'transfer')
            ->sum('quantity');

        return $inbound + $transfersIn - $outbound - $transfersOut;
    }
}
