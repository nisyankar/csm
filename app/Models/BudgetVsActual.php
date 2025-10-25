<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetVsActual extends Model
{
    protected $table = 'budget_vs_actual';

    protected $fillable = [
        'project_id',
        'year',
        'month',
        'category_type',
        'category_id',
        'budget_amount',
        'actual_amount',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'budget_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'variance' => 'decimal:2',
        'variance_percentage' => 'decimal:2',
    ];

    protected $appends = [
        'is_over_budget',
        'month_name',
    ];

    /**
     * İlişkiler
     */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function incomeCategory(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class, 'category_id');
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    /**
     * Kategori (transaction_type'a göre)
     */
    public function category()
    {
        if ($this->category_type === 'income') {
            return $this->incomeCategory();
        }

        return $this->expenseCategory();
    }

    /**
     * Accessors
     */

    public function getIsOverBudgetAttribute(): bool
    {
        return $this->actual_amount > $this->budget_amount;
    }

    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
            5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
            9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
        ];

        return $months[$this->month] ?? '';
    }

    public function getCategoryNameAttribute(): string
    {
        if ($this->category_type === 'income') {
            return $this->incomeCategory?->name ?? 'Bilinmiyor';
        }

        return $this->expenseCategory?->name ?? 'Bilinmiyor';
    }

    /**
     * Scopes
     */

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForPeriod($query, $year, $month = null)
    {
        $query->where('year', $year);

        if ($month) {
            $query->where('month', $month);
        }

        return $query;
    }

    public function scopeIncome($query)
    {
        return $query->where('category_type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('category_type', 'expense');
    }

    public function scopeOverBudget($query)
    {
        return $query->whereRaw('actual_amount > budget_amount');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Helper Methods
     */

    /**
     * Gerçekleşen miktarı güncelle
     */
    public function updateActualAmount(): void
    {
        $this->actual_amount = FinancialTransaction::forProject($this->project_id)
            ->where('transaction_type', $this->category_type)
            ->where('category_id', $this->category_id)
            ->forMonth($this->year, $this->month)
            ->sum('amount');

        $this->save();
    }

    /**
     * Bütçe sapması var mı?
     */
    public function hasVariance(): bool
    {
        return abs($this->variance) > 0.01; // 1 kuruştan fazla fark
    }

    /**
     * Pozitif sapma mı? (gelir fazla, gider az)
     */
    public function hasPositiveVariance(): bool
    {
        if ($this->category_type === 'income') {
            return $this->variance > 0; // Gelir beklenenin üzerinde
        }

        return $this->variance < 0; // Gider beklenenin altında
    }

    /**
     * Negatif sapma mı? (gelir az, gider fazla)
     */
    public function hasNegativeVariance(): bool
    {
        return !$this->hasPositiveVariance();
    }

    /**
     * Sapma badge class'ı
     */
    public function getVarianceBadgeAttribute(): string
    {
        if (!$this->hasVariance()) {
            return 'badge-success';
        }

        if ($this->hasPositiveVariance()) {
            return 'badge-success';
        }

        return 'badge-danger';
    }

    /**
     * Yıl/Ay için kayıt oluştur veya güncelle
     */
    public static function updateOrCreateForPeriod(
        int $projectId,
        int $year,
        int $month,
        string $categoryType,
        int $categoryId,
        float $budgetAmount
    ): self {
        $record = self::updateOrCreate(
            [
                'project_id' => $projectId,
                'year' => $year,
                'month' => $month,
                'category_type' => $categoryType,
                'category_id' => $categoryId,
            ],
            [
                'budget_amount' => $budgetAmount,
            ]
        );

        // Gerçekleşen miktarı hesapla
        $record->updateActualAmount();

        return $record;
    }
}
