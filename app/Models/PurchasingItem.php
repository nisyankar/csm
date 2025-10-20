<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchasing_request_id', 'material_id', 'item_name', 'description', 'specification', 'category',
        'quantity', 'unit', 'estimated_unit_price', 'estimated_total_price',
        'actual_unit_price', 'actual_total_price', 'concrete_class', 'concrete_slump',
        'concrete_aggregate_size', 'steel_diameter', 'steel_quality', 'steel_length',
        'required_date', 'delivery_location', 'notes', 'priority',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'estimated_unit_price' => 'decimal:2',
        'estimated_total_price' => 'decimal:2',
        'actual_unit_price' => 'decimal:2',
        'actual_total_price' => 'decimal:2',
        'required_date' => 'date',
        'priority' => 'integer',
    ];

    public function purchasingRequest(): BelongsTo
    {
        return $this->belongsTo(PurchasingRequest::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function scopeConcrete($query)
    {
        return $query->where('category', 'concrete');
    }

    public function scopeSteel($query)
    {
        return $query->where('category', 'steel');
    }

    protected static function booted(): void
    {
        static::saving(function ($item) {
            if ($item->quantity && $item->estimated_unit_price) {
                $item->estimated_total_price = $item->quantity * $item->estimated_unit_price;
            }
            if ($item->quantity && $item->actual_unit_price) {
                $item->actual_total_price = $item->quantity * $item->actual_unit_price;
            }
        });
    }
}
