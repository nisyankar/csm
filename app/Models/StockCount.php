<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_number',
        'warehouse_id',
        'material_id',
        'system_quantity',
        'counted_quantity',
        'difference',
        'status',
        'count_date',
        'counted_by',
        'approved_by',
        'approved_at',
        'notes',
        'rejection_reason',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:2',
        'counted_quantity' => 'decimal:2',
        'difference' => 'decimal:2',
        'count_date' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function countedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        if (!$this->status) {
            return '-';
        }

        return match($this->status) {
            'pending' => 'Beklemede',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        if (!$this->status) {
            return 'gray';
        }

        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }

    public function getDifferenceTypeAttribute(): string
    {
        if ($this->difference > 0) {
            return 'surplus'; // Fazla
        } elseif ($this->difference < 0) {
            return 'shortage'; // Eksik
        }
        return 'match'; // Eşit
    }

    public function getDifferenceTypeLabelAttribute(): string
    {
        return match($this->difference_type) {
            'surplus' => 'Fazla',
            'shortage' => 'Eksik',
            'match' => 'Eşit',
            default => '-',
        };
    }

    public function getDifferenceColorAttribute(): string
    {
        return match($this->difference_type) {
            'surplus' => 'blue',
            'shortage' => 'red',
            'match' => 'green',
            default => 'gray',
        };
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeWithDifference($query)
    {
        return $query->where('difference', '!=', 0);
    }

    // Helper Methods
    public function approve($userId): bool
    {
        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->approved_at = now();

        return $this->save();
    }

    public function reject($userId, $reason): bool
    {
        $this->status = 'rejected';
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->rejection_reason = $reason;

        return $this->save();
    }

    public static function generateReferenceNumber(): string
    {
        $lastCount = static::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastCount ? ((int) substr($lastCount->reference_number, -3)) + 1 : 1;

        return 'SAY-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}