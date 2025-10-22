<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetCarryover extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'from_year',
        'from_month',
        'carryover_type',
        'hours',
        'is_applied',
        'applied_at',
        'notes',
    ];

    protected $casts = [
        'from_year' => 'integer',
        'from_month' => 'integer',
        'hours' => 'decimal:2',
        'is_applied' => 'boolean',
        'applied_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
        return $query->where('carryover_type', $type);
    }

    public function scopeNotApplied($query)
    {
        return $query->where('is_applied', false);
    }

    public function scopeApplied($query)
    {
        return $query->where('is_applied', true);
    }

    /**
     * Helpers
     */
    public function apply(): void
    {
        $this->update([
            'is_applied' => true,
            'applied_at' => now(),
        ]);
    }

    public function isOvertime(): bool
    {
        return $this->carryover_type === 'overtime';
    }

    public function isShortage(): bool
    {
        return $this->carryover_type === 'shortage';
    }
}
