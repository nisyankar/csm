<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SafetyIncident extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'employee_id',
        'incident_date',
        'incident_time',
        'location',
        'incident_type',
        'severity',
        'description',
        'immediate_actions',
        'root_cause',
        'corrective_actions',
        'preventive_actions',
        'witnesses',
        'photos',
        'injured_body_parts',
        'reported_by',
        'investigated_by',
        'reported_at',
        'investigation_completed_at',
        'status',
        'medical_treatment_required',
        'work_stopped',
        'days_lost',
        'cost_estimate',
        'reported_to_authority',
        'authority_report_date',
        'authority_reference_number',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'incident_time' => 'datetime',
        'witnesses' => 'array',
        'photos' => 'array',
        'injured_body_parts' => 'array',
        'reported_at' => 'datetime',
        'investigation_completed_at' => 'datetime',
        'medical_treatment_required' => 'boolean',
        'work_stopped' => 'boolean',
        'days_lost' => 'integer',
        'cost_estimate' => 'decimal:2',
        'reported_to_authority' => 'boolean',
        'authority_report_date' => 'date',
    ];

    /**
     * İlişkiler
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function investigatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigated_by');
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
        return $query->where('incident_type', $type);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNearMiss($query)
    {
        return $query->where('incident_type', 'near_miss');
    }

    public function scopeRequiringInvestigation($query)
    {
        return $query->whereIn('status', ['reported', 'investigating']);
    }

    public function scopeHighSeverity($query)
    {
        return $query->whereIn('severity', ['high', 'critical']);
    }

    /**
     * Helper Methods
     */
    public function isUnderInvestigation(): bool
    {
        return $this->status === 'investigating';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function requiresAuthorityReport(): bool
    {
        // Büyük yaralanma, ölümlü kaza veya 3+ gün iş kaybı
        return $this->incident_type === 'fatal'
            || $this->incident_type === 'major_injury'
            || $this->days_lost >= 3;
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'reported' => 'blue',
            'investigating' => 'yellow',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray'
        };
    }

    public function getIncidentTypeLabelAttribute(): string
    {
        return match($this->incident_type) {
            'minor_injury' => 'Küçük Yaralanma',
            'major_injury' => 'Büyük Yaralanma',
            'near_miss' => 'Ramak Kala',
            'property_damage' => 'Mal Hasarı',
            'environmental' => 'Çevresel Olay',
            'fatal' => 'Ölümlü Kaza',
            default => $this->incident_type
        };
    }

    public function getSeverityLabelAttribute(): string
    {
        return match($this->severity) {
            'low' => 'Düşük',
            'medium' => 'Orta',
            'high' => 'Yüksek',
            'critical' => 'Kritik',
            default => $this->severity
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'reported' => 'Raporlandı',
            'investigating' => 'İnceleniyor',
            'resolved' => 'Çözüldü',
            'closed' => 'Kapatıldı',
            default => $this->status
        };
    }
}
