<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'timesheet_id',
        'employee_id',
        'adjustment_type',
        'field_name',
        'original_value',
        'adjusted_value',
        'reason',
        'adjusted_by',
        'adjusted_at',
    ];

    protected $casts = [
        'original_value' => 'decimal:2',
        'adjusted_value' => 'decimal:2',
        'adjusted_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class, 'timesheet_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    /**
     * Scopes
     */
    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('adjustment_type', $type);
    }

    /**
     * Helpers
     */
    public function getDifference(): float
    {
        return $this->adjusted_value - $this->original_value;
    }

    public function isIncrease(): bool
    {
        return $this->getDifference() > 0;
    }

    public function isDecrease(): bool
    {
        return $this->getDifference() < 0;
    }
}
