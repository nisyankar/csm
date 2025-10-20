<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryHistory extends Model
{
    use HasFactory;

    protected $table = 'employee_salary_history';

    protected $fillable = [
        'employee_id',
        'old_wage_type',
        'old_daily_wage',
        'old_hourly_wage',
        'old_monthly_salary',
        'new_wage_type',
        'new_daily_wage',
        'new_hourly_wage',
        'new_monthly_salary',
        'change_type',
        'change_amount',
        'change_percentage',
        'reason',
        'notes',
        'changed_by',
        'effective_date',
    ];

    protected $casts = [
        'old_daily_wage' => 'decimal:2',
        'old_hourly_wage' => 'decimal:2',
        'old_monthly_salary' => 'decimal:2',
        'new_daily_wage' => 'decimal:2',
        'new_hourly_wage' => 'decimal:2',
        'new_monthly_salary' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'change_percentage' => 'decimal:2',
        'effective_date' => 'date',
    ];

    /**
     * Employee relationship
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * User who made the change
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get formatted change amount with sign
     */
    public function getFormattedChangeAmountAttribute(): string
    {
        $sign = $this->change_type === 'increase' ? '+' : 
                ($this->change_type === 'decrease' ? '-' : '');
        return $sign . number_format($this->change_amount, 2) . ' ₺';
    }

    /**
     * Get change type label in Turkish
     */
    public function getChangeTypeLabelAttribute(): string
    {
        return match($this->change_type) {
            'increase' => 'Artış',
            'decrease' => 'Azalış',
            'adjustment' => 'Düzeltme',
            'promotion' => 'Terfi',
            'correction' => 'Düzeltme',
            default => 'Değişiklik'
        };
    }

    /**
     * Get old salary formatted
     */
    public function getOldSalaryFormattedAttribute(): string
    {
        return match($this->old_wage_type) {
            'daily' => number_format($this->old_daily_wage, 2) . ' ₺/gün',
            'hourly' => number_format($this->old_hourly_wage, 2) . ' ₺/saat',
            'monthly' => number_format($this->old_monthly_salary, 2) . ' ₺/ay',
            default => 'Belirtilmemiş'
        };
    }

    /**
     * Get new salary formatted
     */
    public function getNewSalaryFormattedAttribute(): string
    {
        return match($this->new_wage_type) {
            'daily' => number_format($this->new_daily_wage, 2) . ' ₺/gün',
            'hourly' => number_format($this->new_hourly_wage, 2) . ' ₺/saat',
            'monthly' => number_format($this->new_monthly_salary, 2) . ' ₺/ay',
            default => 'Belirtilmemiş'
        };
    }

    /**
     * Scope for recent changes
     */
    public function scopeRecent($query, $months = 6)
    {
        return $query->where('effective_date', '>=', now()->subMonths($months));
    }

    /**
     * Scope by change type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('change_type', $type);
    }
}