<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentUsage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'equipment_id',
        'project_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'duration_days',
        'duration_hours',
        'operator_id',
        'operator_name',
        'work_site_location',
        'work_description',
        'meter_start',
        'meter_end',
        'meter_total',
        'meter_unit',
        'fuel_consumed',
        'fuel_cost',
        'rental_cost',
        'rental_period_type',
        'status',
        'notes',
        'issues_reported',
        'cost_recorded',
        'financial_transaction_id',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_days' => 'decimal:2',
        'duration_hours' => 'decimal:2',
        'meter_start' => 'integer',
        'meter_end' => 'integer',
        'meter_total' => 'integer',
        'fuel_consumed' => 'decimal:2',
        'fuel_cost' => 'decimal:2',
        'rental_cost' => 'decimal:2',
        'issues_reported' => 'array',
        'cost_recorded' => 'boolean',
    ];

    protected $appends = ['status_label', 'status_color', 'total_cost'];

    /**
     * İlişkiler
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'operator_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function financialTransaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class);
    }

    /**
     * Accessors
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'ongoing' => 'Devam Ediyor',
            'completed' => 'Tamamlandı',
            'interrupted' => 'Kesintiye Uğradı',
            default => $this->status
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'ongoing' => 'blue',
            'completed' => 'green',
            'interrupted' => 'red',
            default => 'gray'
        };
    }

    public function getTotalCostAttribute(): float
    {
        $total = 0;

        if ($this->rental_cost) {
            $total += $this->rental_cost;
        }

        if ($this->fuel_cost) {
            $total += $this->fuel_cost;
        }

        return round($total, 2);
    }

    /**
     * Scopes
     */
    public function scopeForEquipment($query, $equipmentId)
    {
        return $query->where('equipment_id', $equipmentId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeWithOperator($query)
    {
        return $query->whereNotNull('operator_id');
    }

    public function scopeCostNotRecorded($query)
    {
        return $query->where('cost_recorded', false)
            ->where('status', 'completed')
            ->whereNotNull('rental_cost');
    }

    /**
     * Helper Methods
     */
    public function isOngoing(): bool
    {
        return $this->status === 'ongoing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function calculateDuration(): void
    {
        if ($this->start_date && $this->end_date) {
            $start = \Carbon\Carbon::parse($this->start_date);
            $end = \Carbon\Carbon::parse($this->end_date);
            $this->duration_days = $end->diffInDays($start);
        }

        if ($this->start_time && $this->end_time) {
            $start = \Carbon\Carbon::parse($this->start_time);
            $end = \Carbon\Carbon::parse($this->end_time);
            $this->duration_hours = $end->diffInHours($start, true);
        }
    }

    public function calculateMeterTotal(): void
    {
        if ($this->meter_start && $this->meter_end) {
            $this->meter_total = $this->meter_end - $this->meter_start;
        }
    }
}
