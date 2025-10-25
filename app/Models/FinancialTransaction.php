<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'transaction_type',
        'category_id',
        'transaction_date',
        'amount',
        'description',
        'source_module',
        'source_id',
        'invoice_number',
        'invoice_date',
        'payment_method',
        'payment_status',
        'paid_amount',
        'accounting_code',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'invoice_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'remaining_amount',
        'payment_percentage',
    ];

    /**
     * İlişkiler
     */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Gelir kategorisi (sadece income için)
     */
    public function incomeCategory(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class, 'category_id');
    }

    /**
     * Gider kategorisi (sadece expense için)
     */
    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    /**
     * Kategori (polymorphic - transaction_type'a göre)
     */
    public function category()
    {
        if ($this->transaction_type === 'income') {
            return $this->incomeCategory();
        }

        return $this->expenseCategory();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Source model - Otomatik entegrasyon için
     * Kaynak modeli dinamik olarak yükler
     */
    public function source()
    {
        if (!$this->source_module || !$this->source_id) {
            return null;
        }

        $modelClass = match($this->source_module) {
            'timesheet' => Timesheet::class,
            'purchasing' => PurchaseOrder::class,
            'progress_payment' => ProgressPayment::class,
            'sale' => null, // Faz 2'de eklenecek
            default => null,
        };

        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($this->source_id);
    }

    /**
     * Accessors
     */

    public function getRemainingAmountAttribute(): float
    {
        return $this->amount - $this->paid_amount;
    }

    public function getPaymentPercentageAttribute(): float
    {
        if ($this->amount == 0) {
            return 0;
        }

        return round(($this->paid_amount / $this->amount) * 100, 2);
    }

    /**
     * Scopes
     */

    public function scopeIncome($query)
    {
        return $query->where('transaction_type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('transaction_type', 'expense');
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('transaction_date', $year)
                    ->whereMonth('transaction_date', $month);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopeFromSource($query, $sourceModule, $sourceId = null)
    {
        $query->where('source_module', $sourceModule);

        if ($sourceId) {
            $query->where('source_id', $sourceId);
        }

        return $query;
    }

    /**
     * Helper Methods
     */

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === 'partial';
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function isIncome(): bool
    {
        return $this->transaction_type === 'income';
    }

    public function isExpense(): bool
    {
        return $this->transaction_type === 'expense';
    }

    /**
     * Ödeme yapma
     */
    public function makePayment(float $amount, ?string $paymentMethod = null): void
    {
        $this->paid_amount += $amount;

        if ($this->paid_amount >= $this->amount) {
            $this->payment_status = 'paid';
            $this->paid_amount = $this->amount; // Fazla ödeme olmasın
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        }

        if ($paymentMethod) {
            $this->payment_method = $paymentMethod;
        }

        $this->save();
    }

    /**
     * Onaylama
     */
    public function approve(int $userId): void
    {
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
    }

    /**
     * Kategori adını getir
     */
    public function getCategoryNameAttribute(): string
    {
        if ($this->transaction_type === 'income') {
            return $this->incomeCategory?->name ?? 'Bilinmiyor';
        }

        return $this->expenseCategory?->name ?? 'Bilinmiyor';
    }

    /**
     * Kaynak modül adını human-readable formatta getir
     */
    public function getSourceModuleDisplayAttribute(): ?string
    {
        if (!$this->source_module) {
            return null;
        }

        return match($this->source_module) {
            'timesheet' => 'Puantaj',
            'purchasing' => 'Satınalma',
            'progress_payment' => 'Hakediş',
            'sale' => 'Satış',
            default => ucfirst($this->source_module),
        };
    }

    /**
     * Ödeme durumu badge class'ı
     */
    public function getPaymentStatusBadgeAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'badge-success',
            'partial' => 'badge-warning',
            'pending' => 'badge-info',
            'cancelled' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
