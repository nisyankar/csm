<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $fillable = [
        'warehouse_id',
        'to_warehouse_id',
        'material_id',
        'movement_type',
        'quantity',
        'unit_price',
        'reference_type',
        'reference_id',
        'performed_by',
        'notes',
        'movement_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    // Accessors
    public function getMovementTypeLabelAttribute(): string
    {
        if (!$this->movement_type) {
            return '-';
        }

        return match($this->movement_type) {
            'in' => 'Giriş',
            'out' => 'Çıkış',
            'transfer' => 'Transfer',
            'adjustment' => 'Düzeltme',
            default => $this->movement_type,
        };
    }

    public function getMovementTypeColorAttribute(): string
    {
        if (!$this->movement_type) {
            return 'gray';
        }

        return match($this->movement_type) {
            'in' => 'green',
            'out' => 'red',
            'transfer' => 'blue',
            'adjustment' => 'yellow',
            default => 'gray',
        };
    }

    public function getTotalValueAttribute(): float
    {
        return $this->quantity * ($this->unit_price ?? 0);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeByMaterial($query, $materialId)
    {
        return $query->where('material_id', $materialId);
    }

    public function scopeTransfers($query)
    {
        return $query->where('movement_type', 'transfer');
    }
}
