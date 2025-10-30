<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskAssessment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'assessed_by',
        'assessment_title',
        'work_activity',
        'location',
        'assessment_date',
        'description',
        'risk_items',
        'overall_risk_level',
        'control_measures',
        'emergency_procedures',
        'training_requirements',
        'required_ppe',
        'required_equipment',
        'responsible_persons',
        'valid_from',
        'valid_until',
        'review_date',
        'status',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'notes',
        'revision_number',
        'previous_version_id',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'risk_items' => 'array',
        'required_ppe' => 'array',
        'required_equipment' => 'array',
        'responsible_persons' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'review_date' => 'date',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'revision_number' => 'integer',
    ];

    /**
     * İlişkiler
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(RiskAssessment::class, 'previous_version_id');
    }

    /**
     * Scopes
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByRiskLevel($query, $level)
    {
        return $query->where('overall_risk_level', $level);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('valid_from', '<=', now())
            ->where(function($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now());
    }

    public function scopeExpiringOrDueReview($query, $days = 30)
    {
        return $query->where(function($q) use ($days) {
            $q->whereBetween('valid_until', [now(), now()->addDays($days)])
                ->orWhereBetween('review_date', [now(), now()->addDays($days)]);
        });
    }

    public function scopeHighRisk($query)
    {
        return $query->whereIn('overall_risk_level', ['high', 'critical']);
    }

    /**
     * Helper Methods
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->valid_from && $this->valid_from > now()) {
            return false;
        }

        if ($this->valid_until && $this->valid_until < now()) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until < now();
    }

    public function needsReview(): bool
    {
        return $this->review_date && $this->review_date <= now();
    }

    public function getRiskLevelLabelAttribute(): string
    {
        return match($this->overall_risk_level) {
            'low' => 'Düşük Risk',
            'medium' => 'Orta Risk',
            'high' => 'Yüksek Risk',
            'critical' => 'Kritik Risk',
            default => $this->overall_risk_level
        };
    }

    public function getRiskLevelColorAttribute(): string
    {
        return match($this->overall_risk_level) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Taslak',
            'submitted' => 'Onaya Gönderildi',
            'approved' => 'Onaylandı',
            'active' => 'Aktif',
            'expired' => 'Süresi Doldu',
            'archived' => 'Arşivlendi',
            default => $this->status
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'submitted' => 'blue',
            'approved' => 'green',
            'active' => 'green',
            'expired' => 'red',
            'archived' => 'gray',
            default => 'gray'
        };
    }
}
