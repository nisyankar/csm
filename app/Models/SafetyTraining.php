<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SafetyTraining extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'training_title',
        'training_type',
        'trainer_name',
        'trainer_company',
        'training_date',
        'start_time',
        'duration_hours',
        'location',
        'description',
        'objectives',
        'topics',
        'attendees',
        'materials',
        'certificate_issued',
        'certificate_expiry_date',
        'certificate_number',
        'test_conducted',
        'pass_score',
        'test_results',
        'status',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'training_date' => 'date',
        'start_time' => 'datetime',
        'duration_hours' => 'decimal:2',
        'topics' => 'array',
        'attendees' => 'array',
        'materials' => 'array',
        'certificate_issued' => 'boolean',
        'certificate_expiry_date' => 'date',
        'test_conducted' => 'boolean',
        'pass_score' => 'decimal:2',
        'test_results' => 'array',
    ];

    protected $appends = ['attendee_count', 'pass_rate'];

    /**
     * İlişkiler
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Accessor - Katılımcı sayısı
     */
    public function getAttendeeCountAttribute(): int
    {
        if (!$this->attendees) {
            return 0;
        }
        return count($this->attendees);
    }

    /**
     * Accessor - Geçme oranı
     */
    public function getPassRateAttribute(): ?float
    {
        if (!$this->test_conducted || !$this->attendees) {
            return null;
        }

        $passed = collect($this->attendees)->filter(function ($attendee) {
            return isset($attendee['passed']) && $attendee['passed'] === true;
        })->count();

        $total = count($this->attendees);

        return $total > 0 ? round(($passed / $total) * 100, 2) : 0;
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
        return $query->where('training_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'planned')
            ->where('training_date', '>=', now());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCertificateExpiring($query, $days = 30)
    {
        return $query->where('certificate_issued', true)
            ->whereBetween('certificate_expiry_date', [now(), now()->addDays($days)]);
    }

    /**
     * Helper Methods
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCertificateExpired(): bool
    {
        if (!$this->certificate_issued || !$this->certificate_expiry_date) {
            return false;
        }
        return $this->certificate_expiry_date < now();
    }

    public function getTrainingTypeLabelAttribute(): string
    {
        return match($this->training_type) {
            'orientation' => 'İş Başı Eğitimi',
            'isg_basic' => 'Temel İSG',
            'fire_safety' => 'Yangın Güvenliği',
            'first_aid' => 'İlk Yardım',
            'height_work' => 'Yüksekte Çalışma',
            'confined_space' => 'Kapalı Alan',
            'crane_operation' => 'Vinç Operatörü',
            'electrical_safety' => 'Elektrik Güvenliği',
            'chemical_handling' => 'Kimyasal Madde Kullanımı',
            'emergency_response' => 'Acil Durum Müdahale',
            'excavation' => 'Kazı Çalışmaları',
            'scaffolding' => 'İskele Kurma',
            'fall_protection' => 'Düşme Koruması',
            'ppe_usage' => 'KKD Kullanımı',
            'other' => 'Diğer',
            default => $this->training_type
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'planned' => 'Planlandı',
            'completed' => 'Tamamlandı',
            'cancelled' => 'İptal Edildi',
            default => $this->status
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'planned' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray'
        };
    }
}
