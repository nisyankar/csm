<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProjectAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'project_id',
        'department_id',
        'is_primary',
        'start_date',
        'end_date',
        'status',
        'role_in_project',
        'notes',
        'assigned_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->where(function ($subQ) use ($startDate, $endDate) {
                $subQ->whereBetween('start_date', [$startDate, $endDate]);
            })->orWhere(function ($subQ) use ($startDate, $endDate) {
                $subQ->whereBetween('end_date', [$startDate, $endDate]);
            })->orWhere(function ($subQ) use ($startDate, $endDate) {
                $subQ->where('start_date', '<=', $startDate)
                    ->where(function ($dateQ) use ($endDate) {
                        $dateQ->whereNull('end_date')
                            ->orWhere('end_date', '>=', $endDate);
                    });
            });
        });
    }

    /**
     * Check if assignment is currently active
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        if ($now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Get duration in days
     */
    public function getDurationInDays(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Static method to get primary project for employee
     */
    public static function getPrimaryProject($employeeId)
    {
        return self::where('employee_id', $employeeId)
            ->where('is_primary', true)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Static method to get all active projects for employee
     */
    public static function getActiveProjects($employeeId)
    {
        return self::where('employee_id', $employeeId)
            ->active()
            ->with(['project', 'department'])
            ->get();
    }

    /**
     * Static method to assign employee to project
     */
    public static function assignEmployee($employeeId, $projectId, $data = [])
    {
        // If this is marked as primary, remove primary flag from other assignments
        if ($data['is_primary'] ?? false) {
            self::where('employee_id', $employeeId)
                ->where('status', 'active')
                ->update(['is_primary' => false]);
        }

        return self::create(array_merge([
            'employee_id' => $employeeId,
            'project_id' => $projectId,
            'start_date' => $data['start_date'] ?? now(),
            'status' => 'active',
        ], $data));
    }
}