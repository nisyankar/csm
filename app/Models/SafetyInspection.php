<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SafetyInspection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'inspector_id',
        'inspection_title',
        'inspection_type',
        'inspection_date',
        'inspection_time',
        'location',
        'area_inspected',
        'checklist',
        'findings',
        'recommendations',
        'photos',
        'overall_status',
        'score',
        'items_checked',
        'items_passed',
        'items_failed',
        'action_items',
        'status',
        'next_inspection_date',
        'reviewed_by',
        'reviewed_at',
        'notes',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'inspection_time' => 'datetime',
        'checklist' => 'array',
        'photos' => 'array',
        'score' => 'decimal:2',
        'items_checked' => 'integer',
        'items_passed' => 'integer',
        'items_failed' => 'integer',
        'action_items' => 'array',
        'next_inspection_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    protected $appends = ['pass_rate'];

    /**
     * İlişkiler
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Accessor - Geçme oranı
     */
    public function getPassRateAttribute(): float
    {
        if ($this->items_checked == 0) {
            return 0;
        }
        return round(($this->items_passed / $this->items_checked) * 100, 2);
    }

    /**
     * Scopes
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('inspection_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByOverallStatus($query, $overallStatus)
    {
        return $query->where('overall_status', $overallStatus);
    }

    public function scopeFailed($query)
    {
        return $query->where('overall_status', 'failed');
    }

    public function scopeRequiresAction($query)
    {
        return $query->where('overall_status', 'requires_action');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
            ->where('inspection_date', '>=', now());
    }

    public function scopeDueForNextInspection($query, $days = 7)
    {
        return $query->whereBetween('next_inspection_date', [now(), now()->addDays($days)]);
    }

    /**
     * Helper Methods
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function hasFailed(): bool
    {
        return $this->overall_status === 'failed';
    }

    public function requiresAction(): bool
    {
        return $this->overall_status === 'requires_action' || $this->overall_status === 'failed';
    }

    public function getInspectionTypeLabelAttribute(): string
    {
        return match($this->inspection_type) {
            'daily' => 'Günlük',
            'weekly' => 'Haftalık',
            'monthly' => 'Aylık',
            'quarterly' => 'Üç Aylık',
            'pre_operation' => 'Operasyon Öncesi',
            'post_incident' => 'Olay Sonrası',
            'special' => 'Özel Denetim',
            'audit' => 'Dış Denetim',
            default => $this->inspection_type
        };
    }

    public function getOverallStatusLabelAttribute(): string
    {
        return match($this->overall_status) {
            'passed' => 'Başarılı',
            'passed_with_notes' => 'Notlarla Geçti',
            'requires_action' => 'Aksiyon Gerekli',
            'failed' => 'Başarısız',
            default => $this->overall_status
        };
    }

    public function getOverallStatusColorAttribute(): string
    {
        return match($this->overall_status) {
            'passed' => 'green',
            'passed_with_notes' => 'blue',
            'requires_action' => 'yellow',
            'failed' => 'red',
            default => 'gray'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'Planlandı',
            'in_progress' => 'Devam Ediyor',
            'completed' => 'Tamamlandı',
            'cancelled' => 'İptal Edildi',
            default => $this->status
        };
    }
}
