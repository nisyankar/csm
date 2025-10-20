<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class LeaveCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'calculation_year',
        'entitlement_days',
        'carried_forward_days',
        'used_days',
        'pending_days',
        'remaining_days',
        'calculation_date',
        'calculation_method',
        'parameters_used',
        'calculation_details',
        'status',
        'calculated_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'needs_recalculation',
        'recalculation_reason',
        'last_adjustment_at',
        'version',
    ];

    protected $casts = [
        'entitlement_days' => 'decimal:2',
        'carried_forward_days' => 'decimal:2',
        'used_days' => 'decimal:2',
        'pending_days' => 'decimal:2',
        'remaining_days' => 'decimal:2',
        'calculation_date' => 'datetime',
        'parameters_used' => 'array',
        'calculation_details' => 'array',
        'approved_at' => 'datetime',
        'needs_recalculation' => 'boolean',
        'last_adjustment_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    /**
     * Employee this calculation belongs to
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Leave type this calculation is for
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * User who performed the calculation
     */
    public function calculator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    /**
     * User who approved the calculation
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Balance logs related to this calculation
     */
    public function balanceLogs(): HasMany
    {
        return $this->hasMany(LeaveBalanceLog::class, 'calculation_id');
    }

    /**
     * Leave requests that affected this calculation
     */
    public function relatedRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id')
            ->where('leave_type_id', $this->leave_type_id)
            ->whereYear('start_date', $this->calculation_year);
    }

    /**
     * Scopes
     */

    /**
     * Scope to get calculations for a specific year
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('calculation_year', $year);
    }

    /**
     * Scope to get current year calculations
     */
    public function scopeCurrentYear($query)
    {
        return $query->where('calculation_year', now()->year);
    }

    /**
     * Scope to get pending calculations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved calculations
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get calculations that need recalculation
     */
    public function scopeNeedsRecalculation($query)
    {
        return $query->where('needs_recalculation', true);
    }

    /**
     * Scope to get calculations by employee
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope to get calculations by leave type
     */
    public function scopeByLeaveType($query, $leaveTypeId)
    {
        return $query->where('leave_type_id', $leaveTypeId);
    }

    /**
     * Accessors & Mutators
     */

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'pending' => 'Bekleyen',
            'approved' => 'Onaylanmış',
            'rejected' => 'Reddedilmiş',
            'draft' => 'Taslak',
            'superseded' => 'Geçersiz',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        $classes = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'draft' => 'bg-gray-100 text-gray-800',
            'superseded' => 'bg-gray-100 text-gray-600',
        ];

        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get total available days (entitlement + carry forward)
     */
    public function getTotalAvailableDaysAttribute(): float
    {
        return $this->entitlement_days + $this->carried_forward_days;
    }

    /**
     * Get total used days (used + pending)
     */
    public function getTotalUsedDaysAttribute(): float
    {
        return $this->used_days + $this->pending_days;
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->total_available_days <= 0) {
            return 0;
        }

        return round(($this->used_days / $this->total_available_days) * 100, 1);
    }

    /**
     * Get remaining percentage
     */
    public function getRemainingPercentageAttribute(): float
    {
        if ($this->total_available_days <= 0) {
            return 0;
        }

        return round(($this->remaining_days / $this->total_available_days) * 100, 1);
    }

    /**
     * Check if calculation is current (latest version)
     */
    public function getIsCurrentAttribute(): bool
    {
        $latestVersion = self::where([
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'calculation_year' => $this->calculation_year,
        ])->max('version');

        return $this->version == $latestVersion;
    }

    /**
     * Get calculation age in days
     */
    public function getCalculationAgeAttribute(): int
    {
        return $this->calculation_date->diffInDays(now());
    }

    /**
     * Helper Methods
     */

    /**
     * Check if calculation is valid/current
     */
    public function isValid(): bool
    {
        return $this->status === 'approved' && 
               !$this->needs_recalculation && 
               $this->is_current;
    }

    /**
     * Check if calculation is editable
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['pending', 'draft']);
    }

    /**
     * Mark as needing recalculation
     */
    public function markForRecalculation(string $reason = null): void
    {
        $this->update([
            'needs_recalculation' => true,
            'recalculation_reason' => $reason,
        ]);
    }

    /**
     * Approve the calculation
     */
    public function approve(User $approver, string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'needs_recalculation' => false,
        ]);
    }

    /**
     * Reject the calculation
     */
    public function reject(User $approver, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $reason,
        ]);
    }

    /**
     * Create new version of calculation
     */
    public function createNewVersion(array $data): self
    {
        // Mark current calculation as superseded
        $this->update(['status' => 'superseded']);

        // Create new version
        $newVersion = self::create(array_merge($data, [
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'calculation_year' => $this->calculation_year,
            'version' => $this->version + 1,
            'status' => 'pending',
        ]));

        return $newVersion;
    }

    /**
     * Get calculation breakdown for display
     */
    public function getBreakdown(): array
    {
        return [
            'entitlement' => [
                'days' => $this->entitlement_days,
                'description' => 'Yıllık hak edilen izin',
            ],
            'carry_forward' => [
                'days' => $this->carried_forward_days,
                'description' => 'Önceki yıldan devir',
            ],
            'total_available' => [
                'days' => $this->total_available_days,
                'description' => 'Toplam kullanılabilir',
            ],
            'used' => [
                'days' => $this->used_days,
                'description' => 'Kullanılan izin',
            ],
            'pending' => [
                'days' => $this->pending_days,
                'description' => 'Bekleyen talepler',
            ],
            'remaining' => [
                'days' => $this->remaining_days,
                'description' => 'Kalan izin',
            ],
        ];
    }

    /**
     * Get calculation summary for employee
     */
    public function getSummary(): array
    {
        return [
            'employee' => $this->employee->full_name,
            'leave_type' => $this->leaveType->name,
            'year' => $this->calculation_year,
            'total_available' => $this->total_available_days,
            'used' => $this->used_days,
            'remaining' => $this->remaining_days,
            'usage_percentage' => $this->usage_percentage,
            'status' => $this->status_display,
            'last_updated' => $this->updated_at,
        ];
    }

    /**
     * Update used days from leave requests
     */
    public function updateUsageFromRequests(): void
    {
        $approvedRequests = $this->relatedRequests()
            ->where('status', 'approved')
            ->sum('working_days');

        $pendingRequests = $this->relatedRequests()
            ->where('status', 'pending')
            ->sum('working_days');

        $this->update([
            'used_days' => $approvedRequests,
            'pending_days' => $pendingRequests,
            'remaining_days' => $this->total_available_days - $approvedRequests - $pendingRequests,
        ]);
    }

    /**
     * Get parameters used in calculation
     */
    public function getCalculationParameters(): array
    {
        return $this->parameters_used ?? [];
    }

    /**
     * Get calculation details
     */
    public function getCalculationDetails(): array
    {
        return $this->calculation_details ?? [];
    }

    /**
     * Check if calculation needs approval
     */
    public function needsApproval(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Static helper methods
     */

    /**
     * Get latest calculation for employee and leave type
     */
    public static function getLatestForEmployee(int $employeeId, int $leaveTypeId, int $year): ?self
    {
        return self::where([
            'employee_id' => $employeeId,
            'leave_type_id' => $leaveTypeId,
            'calculation_year' => $year,
        ])
        ->orderBy('version', 'desc')
        ->first();
    }

    /**
     * Get all current calculations for employee
     */
    public static function getCurrentForEmployee(int $employeeId, int $year = null): \Illuminate\Database\Eloquent\Collection
    {
        $year = $year ?? now()->year;

        return self::where('employee_id', $employeeId)
            ->where('calculation_year', $year)
            ->where('status', 'approved')
            ->with('leaveType')
            ->get();
    }

    /**
     * Get calculation statistics
     */
    public static function getStatistics(int $year = null): array
    {
        $year = $year ?? now()->year;

        return [
            'total_calculations' => self::forYear($year)->count(),
            'pending_approval' => self::forYear($year)->pending()->count(),
            'approved' => self::forYear($year)->approved()->count(),
            'needs_recalculation' => self::forYear($year)->needsRecalculation()->count(),
            'by_leave_type' => self::forYear($year)
                ->with('leaveType')
                ->get()
                ->groupBy('leave_type_id')
                ->map(function ($calculations) {
                    return [
                        'count' => $calculations->count(),
                        'leave_type' => $calculations->first()->leaveType->name,
                    ];
                }),
        ];
    }
}