<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class LeaveType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'is_paid',
        'requires_approval',
        'requires_documentation',
        'max_days_per_year',
        'max_days_per_request',
        'min_days_advance_notice',
        'max_days_advance_notice',
        'carry_forward_allowed',
        'carry_forward_max_days',
        'accrual_rate',
        'waiting_period_days',
        'status',
        'gender_restriction',
        'marital_status_restriction',
        'employee_categories',
        'calculation_rules',
        'documentation_required',
        'created_by',
        'sort_order',
        'color',
        'icon',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'requires_documentation' => 'boolean',
        'carry_forward_allowed' => 'boolean',
        'accrual_rate' => 'decimal:4',
        'employee_categories' => 'array',
        'calculation_rules' => 'array',
        'documentation_required' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    /**
     * Creator of the leave type
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Leave requests using this type
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'leave_type_id');
    }

    /**
     * Leave calculations for this type
     */
    public function leaveCalculations(): HasMany
    {
        return $this->hasMany(LeaveCalculation::class, 'leave_type_id');
    }

    /**
     * Leave balance logs for this type
     */
    public function leaveBalanceLogs(): HasMany
    {
        return $this->hasMany(LeaveBalanceLog::class, 'leave_type_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope to get only active leave types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get paid leave types
     */
    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    /**
     * Scope to get unpaid leave types
     */
    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    /**
     * Scope to get leave types by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get leave types available for employee category
     */
    public function scopeForEmployeeCategory($query, $employeeCategory)
    {
        return $query->where(function ($q) use ($employeeCategory) {
            $q->whereNull('employee_categories')
              ->orWhereJsonContains('employee_categories', $employeeCategory);
        });
    }

    /**
     * Scope to get leave types that require approval
     */
    public function scopeRequiringApproval($query)
    {
        return $query->where('requires_approval', true);
    }

    /**
     * Accessors & Mutators
     */

    /**
     * Get the display name with category
     */
    public function getFullNameAttribute(): string
    {
        $categoryName = $this->getCategoryDisplayName();
        return $categoryName ? "{$this->name} ({$categoryName})" : $this->name;
    }

    /**
     * Get category display name
     */
    public function getCategoryDisplayName(): string
    {
        $categories = [
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
            'other' => 'Diğer',
        ];

        return $categories[$this->category] ?? $this->category;
    }

    /**
     * Get employee categories display names
     */
    public function getEmployeeCategoriesDisplayAttribute(): array
    {
        if (!$this->employee_categories) {
            return ['Tüm Çalışanlar'];
        }

        $categories = [
            'worker' => 'İşçi',
            'foreman' => 'Forman',
            'engineer' => 'Mühendis',
            'manager' => 'Proje Yöneticisi',
            'system_admin' => 'Sistem Yöneticisi',
        ];

        return array_map(function ($category) use ($categories) {
            return $categories[$category] ?? $category;
        }, $this->employee_categories);
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'active' => 'Aktif',
            'inactive' => 'Pasif',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        $classes = [
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
        ];

        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get type icon
     */
    public function getTypeIconAttribute(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        $icons = [
            'annual' => '🏖️',
            'sick' => '🏥',
            'maternity' => '👶',
            'paternity' => '👨‍👶',
            'marriage' => '💒',
            'funeral' => '⚰️',
            'military' => '🪖',
            'unpaid' => '💸',
            'emergency' => '🚨',
            'study' => '📚',
            'other' => '📋',
        ];

        return $icons[$this->category] ?? '📋';
    }

    /**
     * Get type color
     */
    public function getTypeColorAttribute(): string
    {
        if ($this->color) {
            return $this->color;
        }

        $colors = [
            'annual' => '#3B82F6',      // Blue
            'sick' => '#EF4444',        // Red
            'maternity' => '#EC4899',   // Pink
            'paternity' => '#8B5CF6',   // Purple
            'marriage' => '#F59E0B',    // Amber
            'funeral' => '#6B7280',     // Gray
            'military' => '#059669',    // Emerald
            'unpaid' => '#DC2626',      // Red
            'emergency' => '#DC2626',   // Red
            'study' => '#0891B2',       // Cyan
            'other' => '#6B7280',       // Gray
        ];

        return $colors[$this->category] ?? '#6B7280';
    }

    /**
     * Helper Methods
     */

    /**
     * Check if employee can use this leave type
     */
    public function isAvailableForEmployee(Employee $employee): bool
    {
        // Check employee category restriction
        if ($this->employee_categories && !in_array($employee->category, $this->employee_categories)) {
            return false;
        }

        // Check gender restriction
        if ($this->gender_restriction && $employee->gender !== $this->gender_restriction) {
            return false;
        }

        // Check marital status restriction
        if ($this->marital_status_restriction && $employee->marital_status !== $this->marital_status_restriction) {
            return false;
        }

        // Check waiting period
        if ($this->waiting_period_days) {
            $employmentDays = Carbon::parse($employee->start_date)->diffInDays(now());
            if ($employmentDays < $this->waiting_period_days) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get maximum days an employee can request in a single request
     */
    public function getMaxDaysPerRequestForEmployee(Employee $employee): ?int
    {
        // This can be extended to consider employee-specific rules
        return $this->max_days_per_request;
    }

    /**
     * Get maximum days an employee can take per year
     */
    public function getMaxDaysPerYearForEmployee(Employee $employee): ?int
    {
        // This can be extended to consider employee-specific rules
        return $this->max_days_per_year;
    }

    /**
     * Check if advance notice requirement is met
     */
    public function isAdvanceNoticeRequirementMet(Carbon $requestDate, Carbon $leaveStartDate): bool
    {
        if (!$this->min_days_advance_notice) {
            return true;
        }

        $daysDifference = $requestDate->diffInDays($leaveStartDate, false);
        return $daysDifference >= $this->min_days_advance_notice;
    }

    /**
     * Check if leave request exceeds maximum advance notice
     */
    public function exceedsMaxAdvanceNotice(Carbon $requestDate, Carbon $leaveStartDate): bool
    {
        if (!$this->max_days_advance_notice) {
            return false;
        }

        $daysDifference = $requestDate->diffInDays($leaveStartDate, false);
        return $daysDifference > $this->max_days_advance_notice;
    }

    /**
     * Get required documentation list
     */
    public function getRequiredDocumentationList(): array
    {
        if (!$this->requires_documentation || !$this->documentation_required) {
            return [];
        }

        return $this->documentation_required;
    }

    /**
     * Calculate accrued leave days for employee
     */
    public function calculateAccruedDays(Employee $employee, Carbon $fromDate, Carbon $toDate): float
    {
        if (!$this->accrual_rate) {
            return 0;
        }

        $workingDays = 0;
        $current = $fromDate->copy();

        while ($current->lte($toDate)) {
            if (!$current->isWeekend()) {
                $workingDays++;
            }
            $current->addDay();
        }

        return round($workingDays * $this->accrual_rate, 2);
    }

    /**
     * Static helper methods
     */

    /**
     * Get all available categories
     */
    public static function getAvailableCategories(): array
    {
        return [
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
            'other' => 'Diğer',
        ];
    }

    /**
     * Get all available employee categories
     */
    public static function getAvailableEmployeeCategories(): array
    {
        return [
            'worker' => 'İşçi',
            'foreman' => 'Forman',
            'engineer' => 'Mühendis',
            'manager' => 'Proje Yöneticisi',
            'system_admin' => 'Sistem Yöneticisi',
        ];
    }
}