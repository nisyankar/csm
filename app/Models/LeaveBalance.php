<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'year',
        'leave_type',

        // Bakiye bilgileri
        'entitled_days',
        'carried_forward_days',
        'earned_days',
        'adjustment_days',
        'total_days',

        // Kullanım
        'used_days',
        'planned_days',
        'remaining_days',

        // Yasal
        'seniority_years',
        'legal_entitlement',
        'is_prorated',
        'calculation_date',
        'entitlement_start_date',
        'entitlement_end_date',

        // Devir
        'max_carry_forward',
        'expiry_date',

        // Metadata
        'status',
        'notes',
        'calculation_details',
        'calculated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'entitled_days' => 'decimal:2',
        'carried_forward_days' => 'decimal:2',
        'earned_days' => 'decimal:2',
        'adjustment_days' => 'decimal:2',
        'total_days' => 'decimal:2',
        'used_days' => 'decimal:2',
        'planned_days' => 'decimal:2',
        'remaining_days' => 'decimal:2',
        'seniority_years' => 'integer',
        'legal_entitlement' => 'decimal:2',
        'is_prorated' => 'boolean',
        'calculation_date' => 'date',
        'entitlement_start_date' => 'date',
        'entitlement_end_date' => 'date',
        'max_carry_forward' => 'decimal:2',
        'expiry_date' => 'date',
        'calculation_details' => 'array',
        'approved_at' => 'datetime',
    ];

    // İlişkiler

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LeaveBalanceLog::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByLeaveType($query, string $leaveType)
    {
        return $query->where('leave_type', $leaveType);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeCurrentYear($query)
    {
        return $query->where('year', now()->year);
    }

    // Accessors

    public function getUsagePercentageAttribute(): float
    {
        if ($this->total_days == 0) {
            return 0;
        }
        return round(($this->used_days / $this->total_days) * 100, 2);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && now()->gt($this->expiry_date);
    }

    public function getCanCarryForwardAttribute(): bool
    {
        return $this->max_carry_forward > 0 && $this->remaining_days > 0;
    }

    // Helper Methods

    /**
     * Kullanılan günleri artır ve bakiyeyi güncelle
     */
    public function deductDays(float $days, ?LeaveRequest $leaveRequest = null, ?User $user = null): void
    {
        $balanceBefore = $this->remaining_days;

        $this->used_days += $days;
        $this->remaining_days = $this->total_days - $this->used_days - $this->planned_days;
        $this->save();

        // Log kaydı
        $this->logChange(
            action: 'used',
            amount: -$days,
            balanceBefore: $balanceBefore,
            balanceAfter: $this->remaining_days,
            reason: 'İzin kullanımı',
            leaveRequest: $leaveRequest,
            user: $user,
            source: 'approval'
        );
    }

    /**
     * Kullanılan günleri iade et (iptal durumunda)
     */
    public function restoreDays(float $days, ?LeaveRequest $leaveRequest = null, ?User $user = null): void
    {
        $balanceBefore = $this->remaining_days;

        $this->used_days -= $days;
        $this->remaining_days = $this->total_days - $this->used_days - $this->planned_days;
        $this->save();

        // Log kaydı
        $this->logChange(
            action: 'cancelled',
            amount: $days,
            balanceBefore: $balanceBefore,
            balanceAfter: $this->remaining_days,
            reason: 'İzin iptali - iade',
            leaveRequest: $leaveRequest,
            user: $user,
            source: 'system'
        );
    }

    /**
     * Planlı günleri artır (beklemede olan talepler için)
     */
    public function addPlannedDays(float $days, ?LeaveRequest $leaveRequest = null): void
    {
        $balanceBefore = $this->remaining_days;

        $this->planned_days += $days;
        $this->remaining_days = $this->total_days - $this->used_days - $this->planned_days;
        $this->save();

        // Log kaydı
        $this->logChange(
            action: 'planned',
            amount: -$days,
            balanceBefore: $balanceBefore,
            balanceAfter: $this->remaining_days,
            reason: 'İzin planlandı',
            leaveRequest: $leaveRequest,
            source: 'system'
        );
    }

    /**
     * Planlı günleri azalt (talep reddedilince)
     */
    public function removePlannedDays(float $days, ?LeaveRequest $leaveRequest = null): void
    {
        $balanceBefore = $this->remaining_days;

        $this->planned_days -= $days;
        $this->remaining_days = $this->total_days - $this->used_days - $this->planned_days;
        $this->save();

        // Log kaydı
        $this->logChange(
            action: 'unplanned',
            amount: $days,
            balanceBefore: $balanceBefore,
            balanceAfter: $this->remaining_days,
            reason: 'İzin talebi reddedildi',
            leaveRequest: $leaveRequest,
            source: 'system'
        );
    }

    /**
     * Manuel düzeltme yap
     */
    public function adjustBalance(float $amount, string $reason, User $user): void
    {
        $balanceBefore = $this->remaining_days;

        $this->adjustment_days += $amount;
        $this->total_days = $this->entitled_days + $this->carried_forward_days + $this->earned_days + $this->adjustment_days;
        $this->remaining_days = $this->total_days - $this->used_days - $this->planned_days;
        $this->save();

        // Log kaydı
        $this->logChange(
            action: 'adjusted',
            amount: $amount,
            balanceBefore: $balanceBefore,
            balanceAfter: $this->remaining_days,
            reason: $reason,
            user: $user,
            source: 'manual'
        );
    }

    /**
     * Bakiyeyi yeniden hesapla
     */
    public function recalculate(): void
    {
        $this->total_days = $this->entitled_days + $this->carried_forward_days + $this->earned_days + $this->adjustment_days;
        $this->remaining_days = $this->total_days - $this->used_days - $this->planned_days;
        $this->save();
    }

    /**
     * Yeterli bakiye var mı?
     */
    public function hasSufficientBalance(float $requestedDays): bool
    {
        return $this->remaining_days >= $requestedDays;
    }

    /**
     * Bakiye değişikliğini logla
     */
    private function logChange(
        string $action,
        float $amount,
        float $balanceBefore,
        float $balanceAfter,
        string $reason,
        ?LeaveRequest $leaveRequest = null,
        ?User $user = null,
        string $source = 'system'
    ): void {
        LeaveBalanceLog::create([
            'leave_balance_id' => $this->id,
            'employee_id' => $this->employee_id,
            'leave_request_id' => $leaveRequest?->id,
            'action' => $action,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reason' => $reason,
            'created_by' => $user?->id,
            'source' => $source,
        ]);
    }

    /**
     * İzin türü görünen adı
     */
    public function getLeaveTypeDisplayAttribute(): string
    {
        return match($this->leave_type) {
            'annual' => 'Yıllık İzin',
            'sick' => 'Hastalık İzni',
            'maternity' => 'Doğum İzni',
            'paternity' => 'Babalık İzni',
            'marriage' => 'Evlilik İzni',
            'funeral' => 'Cenaze İzni',
            'military' => 'Askerlik İzni',
            'unpaid' => 'Ücretsiz İzin',
            'emergency' => 'Acil Durum İzni',
            'study' => 'Eğitim İzni',
            default => ucfirst($this->leave_type),
        };
    }
}
