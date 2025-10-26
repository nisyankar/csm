<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchasing_request_id', 'supplier_id', 'supplier_quotation_id', 'order_number',
        'order_date', 'expected_delivery_date', 'subtotal', 'tax_amount', 'discount_amount',
        'shipping_cost', 'total_amount', 'payment_method', 'payment_term_days',
        'payment_due_date', 'payment_status', 'status', 'approved_by', 'approved_at',
        'delivery_address', 'delivery_contact', 'special_instructions', 'notes',
        'cancellation_reason', 'attachment_path',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payment_term_days' => 'integer',
        'payment_due_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function purchasingRequest(): BelongsTo
    {
        return $this->belongsTo(PurchasingRequest::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function supplierQuotation(): BelongsTo
    {
        return $this->belongsTo(SupplierQuotation::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public static function generateOrderNumber(): string
    {
        $year = now()->year;
        $lastOrder = self::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $number = $lastOrder ? ((int) substr($lastOrder->order_number, -4)) + 1 : 1;
        return 'PO-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function approve(int $userId): bool
    {
        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->approved_at = now();
        $saved = $this->save();

        if ($saved) {
            // Fire event to create financial transaction
            event(new \App\Events\PurchaseOrderApprovedEvent($this));
        }

        return $saved;
    }

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = self::generateOrderNumber();
            }
            if ($order->order_date && $order->payment_term_days) {
                $order->payment_due_date = $order->order_date->addDays($order->payment_term_days);
            }
        });
    }
}
