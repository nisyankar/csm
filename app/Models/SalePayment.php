<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class SalePayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unit_sale_id',
        'customer_id',
        'payment_number',
        'installment_number',
        'payment_type',
        'amount',
        'paid_amount',
        'remaining_amount',
        'currency',
        'late_fee',
        'delay_days',
        'due_date',
        'payment_date',
        'reminder_sent_at',
        'payment_method',
        'bank_name',
        'check_number',
        'check_date',
        'transaction_reference',
        'status',
        'approved_by',
        'approved_at',
        'payment_documents',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
        'reminder_sent_at' => 'date',
        'check_date' => 'date',
        'approved_at' => 'datetime',
        'payment_documents' => 'array',
    ];

    protected $appends = ['status_badge', 'is_overdue'];

    /**
     * İlişkiler
     */
    public function unitSale(): BelongsTo
    {
        return $this->belongsTo(UnitSale::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
            'pending' => ['text' => 'Bekliyor', 'color' => 'yellow'],
            'paid' => ['text' => 'Ödendi', 'color' => 'green'],
            'partial' => ['text' => 'Kısmi Ödeme', 'color' => 'blue'],
            'overdue' => ['text' => 'Vadesi Geçmiş', 'color' => 'red'],
            'cancelled' => ['text' => 'İptal', 'color' => 'gray'],
            default => ['text' => 'Bilinmiyor', 'color' => 'gray'],
        };
    }

    public function getPaymentTypeLabelAttribute(): string
    {
        return match($this->payment_type) {
            'down_payment' => 'Peşinat',
            'installment' => 'Taksit',
            'additional' => 'Ek Ödeme',
            'penalty' => 'Gecikme Faizi',
            'discount' => 'İndirim',
            default => 'Bilinmiyor',
        };
    }

    public function getPaymentMethodLabelAttribute(): ?string
    {
        return match($this->payment_method) {
            'cash' => 'Nakit',
            'bank_transfer' => 'Havale/EFT',
            'credit_card' => 'Kredi Kartı',
            'check' => 'Çek',
            'bank_loan' => 'Banka Kredisi',
            default => null,
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        if (in_array($this->status, ['paid', 'cancelled'])) {
            return false;
        }

        return $this->due_date && $this->due_date->isPast();
    }

    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                    ->whereDate('due_date', '<', now());
            });
    }

    public function scopeForUnitSale($query, $unitSaleId)
    {
        return $query->where('unit_sale_id', $unitSaleId);
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByPaymentType($query, $type)
    {
        return $query->where('payment_type', $type);
    }

    public function scopeDueThisMonth($query)
    {
        return $query->whereYear('due_date', now()->year)
            ->whereMonth('due_date', now()->month);
    }

    public function scopeDueInRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    /**
     * Helper Methods
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isOverdue(): bool
    {
        return $this->is_overdue;
    }

    public function markAsPaid(float $amount, string $paymentMethod, ?string $reference = null): void
    {
        $this->update([
            'paid_amount' => $this->paid_amount + $amount,
            'remaining_amount' => max(0, $this->remaining_amount - $amount),
            'payment_method' => $paymentMethod,
            'transaction_reference' => $reference,
            'payment_date' => now(),
            'status' => $this->remaining_amount - $amount <= 0 ? 'paid' : 'partial',
        ]);
    }

    public function approve(int $userId): void
    {
        $this->update([
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function calculateLateFee(float $feePercentage = 1.5): void
    {
        if (!$this->is_overdue || $this->status === 'paid') {
            return;
        }

        $daysLate = abs($this->days_until_due);
        $lateFee = ($this->amount * $feePercentage / 100) * $daysLate;

        $this->update([
            'late_fee' => $lateFee,
            'delay_days' => $daysLate,
            'status' => 'overdue',
        ]);
    }

    public function sendReminder(): void
    {
        $this->update(['reminder_sent_at' => now()]);

        // TODO: Implement notification/email logic here
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = 'PAY-' . date('Ymd') . '-' . str_pad(SalePayment::count() + 1, 5, '0', STR_PAD_LEFT);
            }

            // Kalan tutarı hesapla
            if (!isset($payment->remaining_amount)) {
                $payment->remaining_amount = $payment->amount - $payment->paid_amount;
            }
        });

        static::updating(function ($payment) {
            // Her güncelleme sonrası kalan tutarı tekrar hesapla
            $payment->remaining_amount = $payment->amount - $payment->paid_amount + $payment->late_fee;
        });
    }
}
