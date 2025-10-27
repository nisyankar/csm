<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitSale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'project_unit_id',
        'customer_id',
        'sale_number',
        'sale_type',
        'list_price',
        'discount_amount',
        'discount_percentage',
        'final_price',
        'currency',
        'down_payment',
        'installment_count',
        'monthly_installment',
        'payment_method',
        'reservation_date',
        'sale_date',
        'contract_date',
        'delivery_date',
        'deed_transfer_date',
        'deed_status',
        'deed_type',
        'title_deed_number',
        'deed_notes',
        'status',
        'contract_documents',
        'payment_documents',
        'deed_documents',
        'notes',
        'cancellation_reason',
        'cancellation_date',
        'sales_agent_id',
        'commission_amount',
        'commission_percentage',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'list_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'final_price' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'reservation_date' => 'date',
        'sale_date' => 'date',
        'contract_date' => 'date',
        'delivery_date' => 'date',
        'deed_transfer_date' => 'date',
        'cancellation_date' => 'date',
        'contract_documents' => 'array',
        'payment_documents' => 'array',
        'deed_documents' => 'array',
    ];

    protected $appends = [
        'status_badge',
        'deed_status_badge',
        'sale_type_label',
        'payment_method_label',
        'payment_completion_percentage'
    ];

    /**
     * İlişkiler
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projectUnit(): BelongsTo
    {
        return $this->belongsTo(ProjectUnit::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_agent_id');
    }

    public function salePayments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Accessor Methods
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'reserved' => ['text' => 'Rezerve', 'color' => 'yellow'],
            'contracted' => ['text' => 'Sözleşmeli', 'color' => 'blue'],
            'in_payment' => ['text' => 'Ödeme Aşamasında', 'color' => 'indigo'],
            'completed' => ['text' => 'Tamamlandı', 'color' => 'green'],
            'cancelled' => ['text' => 'İptal', 'color' => 'red'],
            'delayed' => ['text' => 'Gecikmiş', 'color' => 'orange'],
            default => ['text' => 'Bilinmiyor', 'color' => 'gray'],
        };
    }

    public function getDeedStatusBadgeAttribute(): array
    {
        return match($this->deed_status) {
            'not_transferred' => ['text' => 'Devredilmedi', 'color' => 'gray'],
            'in_progress' => ['text' => 'İşlemde', 'color' => 'blue'],
            'transferred' => ['text' => 'Devredildi', 'color' => 'green'],
            'postponed' => ['text' => 'Ertelendi', 'color' => 'orange'],
            default => ['text' => 'Bilinmiyor', 'color' => 'gray'],
        };
    }

    public function getSaleTypeLabelAttribute(): string
    {
        return match($this->sale_type) {
            'reservation' => 'Rezervasyon',
            'sale' => 'Kesin Satış',
            'presale' => 'Ön Satış',
            default => 'Bilinmiyor',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'Peşin',
            'installment' => 'Taksit',
            'bank_loan' => 'Banka Kredisi',
            'mixed' => 'Karma',
            default => 'Bilinmiyor',
        };
    }

    public function getPaymentCompletionPercentageAttribute(): float
    {
        if ($this->final_price == 0) {
            return 0;
        }

        $totalPaid = $this->salePayments()
            ->where('status', 'paid')
            ->sum('paid_amount') ?? 0;

        return round(($totalPaid / $this->final_price) * 100, 2);
    }

    /**
     * Scopes
     */
    public function scopeReserved($query)
    {
        return $query->where('status', 'reserved');
    }

    public function scopeContracted($query)
    {
        return $query->where('status', 'contracted');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled']);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDeedStatus($query, $deedStatus)
    {
        return $query->where('deed_status', $deedStatus);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('sale_number', 'like', "%{$search}%")
                ->orWhereHas('customer', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                })
                ->orWhereHas('projectUnit', function ($q2) use ($search) {
                    $q2->where('unit_number', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Helper Methods
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isDelayed(): bool
    {
        return $this->status === 'delayed';
    }

    public function getTotalPaid(): float
    {
        return $this->salePayments()
            ->where('status', 'paid')
            ->sum('paid_amount') ?? 0;
    }

    public function getTotalRemaining(): float
    {
        return $this->final_price - $this->getTotalPaid();
    }

    public function hasOverduePayments(): bool
    {
        return $this->salePayments()
            ->where('status', 'overdue')
            ->exists();
    }

    public function cancel(string $reason): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancellation_date' => now(),
        ]);

        // İlgili ödemeleri iptal et
        $this->salePayments()
            ->whereIn('status', ['pending', 'partial'])
            ->update(['status' => 'cancelled']);
    }

    public function complete(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function transferDeed(): void
    {
        $this->update([
            'deed_status' => 'transferred',
            'deed_transfer_date' => now(),
        ]);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($unitSale) {
            if (empty($unitSale->sale_number)) {
                $unitSale->sale_number = 'SAL-' . date('Ymd') . '-' . str_pad(UnitSale::count() + 1, 4, '0', STR_PAD_LEFT);
            }

            // Final price hesaplama
            if (empty($unitSale->final_price)) {
                $unitSale->final_price = $unitSale->list_price - $unitSale->discount_amount;
            }
        });
    }
}
