<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LeaveBalanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_balance_id',
        'employee_id',
        'leave_request_id',
        'action',
        'amount',
        'balance_before',
        'balance_after',
        'reason',
        'description',
        'metadata',
        'created_by',
        'source',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    public function leaveBalance(): BelongsTo
    {
        return $this->belongsTo(LeaveBalance::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */

    /**
     * Scope to get logs for a specific employee
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope to get logs for a specific leave type
     */
    public function scopeByLeaveType($query, $leaveTypeId)
    {
        return $query->where('leave_type_id', $leaveTypeId);
    }

    /**
     * Scope to get logs by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get manual adjustments
     */
    public function scopeManualAdjustments($query)
    {
        return $query->where('action_type', 'manual_adjustment');
    }

    /**
     * Scope to get system calculations
     */
    public function scopeSystemCalculations($query)
    {
        return $query->where('action_type', 'system_calculation');
    }

    /**
     * Scope to get logs for a specific date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('effective_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get recent logs
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get logs by reason category
     */
    public function scopeByReasonCategory($query, $category)
    {
        return $query->where('reason_category', $category);
    }

    /**
     * Accessors & Mutators
     */

    /**
     * Get action type display name
     */
    public function getActionTypeDisplayAttribute(): string
    {
        $actionTypes = [
            'manual_adjustment' => 'Manuel Düzenleme',
            'system_calculation' => 'Sistem Hesaplaması',
            'leave_request' => 'İzin Talebi',
            'leave_cancellation' => 'İzin İptali',
            'carry_forward' => 'Devir İşlemi',
            'year_end_process' => 'Yıl Sonu İşlemi',
            'import' => 'Veri Aktarımı',
            'correction' => 'Düzeltme',
        ];

        return $actionTypes[$this->action_type] ?? $this->action_type;
    }

    /**
     * Get reason category display name
     */
    public function getReasonCategoryDisplayAttribute(): string
    {
        $categories = [
            'correction' => 'Düzeltme',
            'bonus' => 'Bonus/Ek İzin',
            'penalty' => 'Ceza/Kesinti',
            'carry_forward' => 'Devir İşlemi',
            'system_error' => 'Sistem Hatası',
            'administrative' => 'İdari İşlem',
            'system_calculation' => 'Sistem Hesaplaması',
            'leave_request_approved' => 'İzin Onaylandı',
            'leave_request_rejected' => 'İzin Reddedildi',
            'leave_cancelled' => 'İzin İptal Edildi',
            'other' => 'Diğer',
        ];

        return $categories[$this->reason_category] ?? $this->reason_category;
    }

    /**
     * Get change type (increase/decrease)
     */
    public function getChangeTypeAttribute(): string
    {
        if ($this->change_amount > 0) {
            return 'increase';
        } elseif ($this->change_amount < 0) {
            return 'decrease';
        } else {
            return 'no_change';
        }
    }

    /**
     * Get change type display
     */
    public function getChangeTypeDisplayAttribute(): string
    {
        $types = [
            'increase' => 'Artış',
            'decrease' => 'Azalış',
            'no_change' => 'Değişiklik Yok',
        ];

        return $types[$this->change_type] ?? 'Bilinmiyor';
    }

    /**
     * Get absolute change amount
     */
    public function getAbsoluteChangeAttribute(): float
    {
        return abs($this->change_amount);
    }

    /**
     * Get formatted change amount with sign
     */
    public function getFormattedChangeAttribute(): string
    {
        $sign = $this->change_amount >= 0 ? '+' : '';
        return $sign . number_format($this->change_amount, 1);
    }

    /**
     * Get change badge class for UI
     */
    public function getChangeBadgeClassAttribute(): string
    {
        $classes = [
            'increase' => 'bg-green-100 text-green-800',
            'decrease' => 'bg-red-100 text-red-800',
            'no_change' => 'bg-gray-100 text-gray-800',
        ];

        return $classes[$this->change_type] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get action badge class for UI
     */
    public function getActionBadgeClassAttribute(): string
    {
        $classes = [
            'manual_adjustment' => 'bg-blue-100 text-blue-800',
            'system_calculation' => 'bg-purple-100 text-purple-800',
            'leave_request' => 'bg-orange-100 text-orange-800',
            'leave_cancellation' => 'bg-yellow-100 text-yellow-800',
            'carry_forward' => 'bg-indigo-100 text-indigo-800',
            'year_end_process' => 'bg-cyan-100 text-cyan-800',
            'import' => 'bg-pink-100 text-pink-800',
            'correction' => 'bg-red-100 text-red-800',
        ];

        return $classes[$this->action_type] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get log age in days
     */
    public function getAgeInDaysAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Helper Methods
     */

    /**
     * Check if this is a manual adjustment
     */
    public function isManualAdjustment(): bool
    {
        return $this->action_type === 'manual_adjustment';
    }

    /**
     * Check if this is a system-generated log
     */
    public function isSystemGenerated(): bool
    {
        return in_array($this->action_type, [
            'system_calculation',
            'leave_request',
            'leave_cancellation',
            'carry_forward',
            'year_end_process'
        ]);
    }

    /**
     * Check if this log represents a balance increase
     */
    public function isIncrease(): bool
    {
        return $this->change_amount > 0;
    }

    /**
     * Check if this log represents a balance decrease
     */
    public function isDecrease(): bool
    {
        return $this->change_amount < 0;
    }

    /**
     * Get metadata value by key
     */
    public function getMetadataValue(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Set metadata value
     */
    public function setMetadataValue(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->update(['metadata' => $metadata]);
    }

    /**
     * Get detailed description of the change
     */
    public function getDetailedDescription(): string
    {
        $description = $this->reason;

        if ($this->reference_number) {
            $description .= " (Ref: {$this->reference_number})";
        }

        if ($this->adjustedBy) {
            $description .= " - {$this->adjustedBy->name} tarafından";
        }

        return $description;
    }

    /**
     * Static helper methods
     */

    /**
     * Log a balance change
     */
    public static function logChange(array $data): self
    {
        return self::create([
            'employee_id' => $data['employee_id'],
            'leave_type_id' => $data['leave_type_id'],
            'calculation_id' => $data['calculation_id'] ?? null,
            'leave_request_id' => $data['leave_request_id'] ?? null,
            'action_type' => $data['action_type'],
            'previous_balance' => $data['previous_balance'],
            'change_amount' => $data['change_amount'],
            'new_balance' => $data['new_balance'],
            'reason_category' => $data['reason_category'],
            'reason' => $data['reason'],
            'reference_number' => $data['reference_number'] ?? null,
            'notes' => $data['notes'] ?? null,
            'effective_date' => $data['effective_date'] ?? now(),
            'adjusted_by' => $data['adjusted_by'] ?? auth()->id(),
            'metadata' => $data['metadata'] ?? [],
        ]);
    }

    /**
     * Log system calculation
     */
    public static function logSystemCalculation(
        Employee $employee,
        LeaveType $leaveType,
        LeaveCalculation $calculation,
        float $previousBalance,
        float $newBalance,
        string $reason = 'Otomatik sistem hesaplaması'
    ): self {
        return self::logChange([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'calculation_id' => $calculation->id,
            'action_type' => 'system_calculation',
            'previous_balance' => $previousBalance,
            'change_amount' => $newBalance - $previousBalance,
            'new_balance' => $newBalance,
            'reason_category' => 'system_calculation',
            'reason' => $reason,
            'metadata' => [
                'calculation_method' => $calculation->calculation_method,
                'calculation_version' => $calculation->version,
            ],
        ]);
    }

    /**
     * Log leave request impact
     */
    public static function logLeaveRequest(
        LeaveRequest $leaveRequest,
        float $previousBalance,
        float $newBalance,
        string $action = 'approved'
    ): self {
        $actionTypes = [
            'approved' => 'leave_request',
            'rejected' => 'leave_cancellation',
            'cancelled' => 'leave_cancellation',
        ];

        $reasonCategories = [
            'approved' => 'leave_request_approved',
            'rejected' => 'leave_request_rejected',
            'cancelled' => 'leave_cancelled',
        ];

        return self::logChange([
            'employee_id' => $leaveRequest->employee_id,
            'leave_type_id' => $leaveRequest->leave_type_id,
            'leave_request_id' => $leaveRequest->id,
            'action_type' => $actionTypes[$action] ?? 'leave_request',
            'previous_balance' => $previousBalance,
            'change_amount' => $newBalance - $previousBalance,
            'new_balance' => $newBalance,
            'reason_category' => $reasonCategories[$action] ?? 'leave_request_approved',
            'reason' => "İzin talebi {$action} - {$leaveRequest->working_days} gün",
            'reference_number' => "LR-{$leaveRequest->id}",
            'metadata' => [
                'leave_request_id' => $leaveRequest->id,
                'leave_days' => $leaveRequest->working_days,
                'leave_start_date' => $leaveRequest->start_date->format('Y-m-d'),
                'leave_end_date' => $leaveRequest->end_date->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Get balance history for employee and leave type
     */
    public static function getBalanceHistory(
        int $employeeId,
        int $leaveTypeId,
        int $year = null
    ): \Illuminate\Database\Eloquent\Collection {
        $query = self::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->with(['adjustedBy', 'leaveRequest']);

        if ($year) {
            $query->whereYear('effective_date', $year);
        }

        return $query->orderBy('effective_date')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get statistics for logs
     */
    public static function getStatistics(array $filters = []): array
    {
        $query = self::query();

        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('effective_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('effective_date', '<=', $filters['date_to']);
        }

        return [
            'total_logs' => $query->count(),
            'manual_adjustments' => $query->where('action_type', 'manual_adjustment')->count(),
            'system_changes' => $query->where('action_type', '!=', 'manual_adjustment')->count(),
            'balance_increases' => $query->where('change_amount', '>', 0)->count(),
            'balance_decreases' => $query->where('change_amount', '<', 0)->count(),
            'total_increase_amount' => $query->where('change_amount', '>', 0)->sum('change_amount'),
            'total_decrease_amount' => abs($query->where('change_amount', '<', 0)->sum('change_amount')),
            'by_action_type' => $query->selectRaw('action_type, COUNT(*) as count')
                ->groupBy('action_type')
                ->pluck('count', 'action_type')
                ->toArray(),
            'by_reason_category' => $query->selectRaw('reason_category, COUNT(*) as count')
                ->groupBy('reason_category')
                ->pluck('count', 'reason_category')
                ->toArray(),
        ];
    }

    /**
     * Get recent activity summary
     */
    public static function getRecentActivity(int $days = 7): array
    {
        $logs = self::with(['employee', 'leaveType', 'adjustedBy'])
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'employee' => $log->employee->full_name,
                'leave_type' => $log->leaveType->name,
                'action' => $log->action_type_display,
                'change' => $log->formatted_change,
                'reason' => $log->reason,
                'adjusted_by' => $log->adjustedBy?->name ?? 'Sistem',
                'date' => $log->created_at,
                'badge_class' => $log->change_badge_class,
            ];
        })->toArray();
    }

    /**
     * Clean old logs (for maintenance)
     */
    public static function cleanOldLogs(int $keepDays = 2555): int // ~7 years
    {
        return self::where('created_at', '<', now()->subDays($keepDays))
            ->where('action_type', '!=', 'manual_adjustment') // Keep manual adjustments
            ->delete();
    }

    /**
     * Export logs to array for reporting
     */
    public static function exportLogs(array $filters = []): array
    {
        $query = self::with(['employee', 'leaveType', 'adjustedBy']);

        // Apply filters
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                switch ($key) {
                    case 'employee_id':
                        $query->where('employee_id', $value);
                        break;
                    case 'leave_type_id':
                        $query->where('leave_type_id', $value);
                        break;
                    case 'action_type':
                        $query->where('action_type', $value);
                        break;
                    case 'reason_category':
                        $query->where('reason_category', $value);
                        break;
                    case 'date_from':
                        $query->whereDate('effective_date', '>=', $value);
                        break;
                    case 'date_to':
                        $query->whereDate('effective_date', '<=', $value);
                        break;
                }
            }
        }

        return $query->orderBy('effective_date', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'Tarih' => $log->effective_date->format('Y-m-d'),
                    'Çalışan' => $log->employee->full_name,
                    'Çalışan Kodu' => $log->employee->employee_code,
                    'İzin Türü' => $log->leaveType->name,
                    'İşlem Türü' => $log->action_type_display,
                    'Önceki Bakiye' => $log->previous_balance,
                    'Değişim' => $log->change_amount,
                    'Yeni Bakiye' => $log->new_balance,
                    'Sebep Kategorisi' => $log->reason_category_display,
                    'Sebep' => $log->reason,
                    'Referans No' => $log->reference_number,
                    'Düzenleyen' => $log->adjustedBy?->name ?? 'Sistem',
                    'Notlar' => $log->notes,
                    'Oluşturulma Tarihi' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }
}