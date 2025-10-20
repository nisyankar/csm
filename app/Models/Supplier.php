<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_code', 'company_name', 'tax_number', 'tax_office', 'address',
        'contact_person', 'phone', 'mobile', 'email', 'website', 'categories',
        'specialization', 'rating', 'total_orders', 'total_amount',
        'on_time_delivery_count', 'late_delivery_count', 'payment_term_days',
        'payment_method', 'credit_limit', 'has_contract', 'contract_start_date',
        'contract_end_date', 'status', 'notes', 'blacklist_reason',
    ];

    protected $casts = [
        'categories' => 'array',
        'rating' => 'decimal:2',
        'total_orders' => 'integer',
        'total_amount' => 'decimal:2',
        'on_time_delivery_count' => 'integer',
        'late_delivery_count' => 'integer',
        'payment_term_days' => 'integer',
        'credit_limit' => 'decimal:2',
        'has_contract' => 'boolean',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
    ];

    public function quotations(): HasMany
    {
        return $this->hasMany(SupplierQuotation::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->whereJsonContains('categories', $category);
    }

    public static function generateSupplierCode(): string
    {
        $lastSupplier = self::orderBy('id', 'desc')->first();
        $number = $lastSupplier ? ((int) substr($lastSupplier->supplier_code, -4)) + 1 : 1;
        return 'SUP-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function getOnTimeDeliveryRate(): float
    {
        $total = $this->on_time_delivery_count + $this->late_delivery_count;
        return $total === 0 ? 0 : ($this->on_time_delivery_count / $total) * 100;
    }

    protected static function booted(): void
    {
        static::creating(function ($supplier) {
            if (!$supplier->supplier_code) {
                $supplier->supplier_code = self::generateSupplierCode();
            }
        });
    }
}
