<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'report_date',
        'reported_by',
        'weather_condition',
        'temperature',
        'weather_notes',
        'total_workers',
        'subcontractor_workers',
        'internal_workers',
        'work_summary',
        'completed_works',
        'ongoing_works',
        'planned_works',
        'has_delays',
        'delay_reasons',
        'has_accidents',
        'accident_details',
        'has_material_shortage',
        'material_shortage_details',
        'visitors',
        'equipment_usage',
        'photos',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'report_date' => 'date',
        'temperature' => 'decimal:2',
        'total_workers' => 'integer',
        'subcontractor_workers' => 'integer',
        'internal_workers' => 'integer',
        'completed_works' => 'array',
        'ongoing_works' => 'array',
        'planned_works' => 'array',
        'has_delays' => 'boolean',
        'delay_reasons' => 'array',
        'has_accidents' => 'boolean',
        'accident_details' => 'array',
        'has_material_shortage' => 'boolean',
        'material_shortage_details' => 'array',
        'visitors' => 'array',
        'equipment_usage' => 'array',
        'photos' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Accessors
     */
    public function getWeatherDisplayAttribute(): string
    {
        $weather = [
            'sunny' => 'Güneşli',
            'cloudy' => 'Bulutlu',
            'rainy' => 'Yağmurlu',
            'snowy' => 'Karlı',
            'windy' => 'Rüzgarlı',
            'stormy' => 'Fırtınalı',
        ];

        return $weather[$this->weather_condition] ?? '-';
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'draft' => 'Taslak',
            'submitted' => 'Gönderildi',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
        ];

        return $statuses[$this->approval_status] ?? $this->approval_status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->approval_status) {
            'draft' => 'secondary',
            'submitted' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Scopes
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('approval_status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('approval_status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('report_date', '>=', now()->subDays($days));
    }

    /**
     * Helper Methods
     */
    public function submit(): void
    {
        $this->update(['approval_status' => 'submitted']);
    }

    public function approve(int $approverId): void
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);
    }

    public function reject(int $approverId, string $reason): void
    {
        $this->update([
            'approval_status' => 'rejected',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function canEdit(): bool
    {
        return in_array($this->approval_status, ['draft', 'rejected']);
    }

    public function canSubmit(): bool
    {
        return $this->approval_status === 'draft';
    }

    public function canApprove(): bool
    {
        return $this->approval_status === 'submitted';
    }
}