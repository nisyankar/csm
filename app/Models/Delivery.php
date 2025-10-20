<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_order_id', 'delivery_number', 'delivery_date', 'delivery_time',
        'waybill_number', 'waybill_date', 'waybill_file_path', 'invoice_number',
        'invoice_date', 'invoice_amount', 'invoice_file_path', 'status', 'received_by',
        'received_at', 'receiver_name', 'quality_check', 'quality_notes', 'delivery_address',
        'driver_name', 'vehicle_plate', 'driver_phone', 'items_count', 'is_complete',
        'missing_items', 'notes', 'rejection_reason', 'damage_report', 'photos',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'waybill_date' => 'date',
        'invoice_date' => 'date',
        'invoice_amount' => 'decimal:2',
        'received_at' => 'datetime',
        'items_count' => 'integer',
        'is_complete' => 'boolean',
        'photos' => 'array',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'scheduled');
    }

    public static function generateDeliveryNumber(): string
    {
        $year = now()->year;
        $lastDelivery = self::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $number = $lastDelivery ? ((int) substr($lastDelivery->delivery_number, -4)) + 1 : 1;
        return 'DEL-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function markAsReceived(int $userId): bool
    {
        $this->status = 'completed';
        $this->received_by = $userId;
        $this->received_at = now();
        return $this->save();
    }

    protected static function booted(): void
    {
        static::creating(function ($delivery) {
            if (!$delivery->delivery_number) {
                $delivery->delivery_number = self::generateDeliveryNumber();
            }
        });
    }
}
