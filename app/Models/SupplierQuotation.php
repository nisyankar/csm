<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierQuotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchasing_request_id', 'supplier_id', 'quotation_number', 'quotation_date',
        'valid_until', 'items', 'subtotal', 'tax_rate', 'tax_amount', 'discount_rate',
        'discount_amount', 'shipping_cost', 'total_amount', 'delivery_days',
        'delivery_terms', 'payment_terms', 'status', 'rating', 'notes',
        'rejection_reason', 'attachment_path', 'is_selected', 'selected_at', 'selected_by',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'delivery_days' => 'integer',
        'rating' => 'integer',
        'is_selected' => 'boolean',
        'selected_at' => 'datetime',
    ];

    public function purchasingRequest(): BelongsTo
    {
        return $this->belongsTo(PurchasingRequest::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function selectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_by');
    }

    public function scopeSelected($query)
    {
        return $query->where('is_selected', true);
    }

    public function scopeForRequest($query, $requestId)
    {
        return $query->where('purchasing_request_id', $requestId);
    }

    public function select(int $userId): bool
    {
        $this->is_selected = true;
        $this->selected_at = now();
        $this->selected_by = $userId;
        $this->status = 'selected';
        return $this->save();
    }

    public function calculateTotal(): void
    {
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        $this->discount_amount = $this->subtotal * ($this->discount_rate / 100);
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount + $this->shipping_cost;
    }

    protected static function booted(): void
    {
        static::saving(function ($quotation) {
            $quotation->tax_amount = $quotation->subtotal * ($quotation->tax_rate / 100);
            $quotation->discount_amount = $quotation->subtotal * ($quotation->discount_rate / 100);
            $quotation->total_amount = $quotation->subtotal + $quotation->tax_amount - $quotation->discount_amount + $quotation->shipping_cost;
        });
    }
}
