<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkProgress extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'work_progress';

    protected $fillable = [
        'assignment_id',
        'reported_by',
        'report_date',
        'completed_quantity',
        'quality_rating',
        'work_description',
        'issues',
        'photos',
        'notes',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'report_date' => 'date',
        'completed_quantity' => 'decimal:2',
        'quality_rating' => 'integer',
        'issues' => 'array',
        'photos' => 'array',
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'is_pending',
        'is_approved',
        'is_rejected',
    ];

    /**
     * Relationships
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(WorkItemAssignment::class, 'assignment_id');
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
    public function getIsPendingAttribute(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'pending' => 'Onay Bekliyor',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
        ];

        return $statuses[$this->approval_status] ?? $this->approval_status;
    }

    public function getQualityDisplayAttribute(): string
    {
        if (!$this->quality_rating) {
            return 'Değerlendirilmedi';
        }

        $ratings = [
            1 => 'Çok Zayıf',
            2 => 'Zayıf',
            3 => 'Orta',
            4 => 'İyi',
            5 => 'Mükemmel',
        ];

        return $ratings[$this->quality_rating] ?? 'Bilinmiyor';
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }

    public function scopeByAssignment($query, $assignmentId)
    {
        return $query->where('assignment_id', $assignmentId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('report_date', $date);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('report_date', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('report_date', now()->year)
            ->whereMonth('report_date', now()->month);
    }

    /**
     * Helper Methods
     */
    public function canBeEdited(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function canBeDeleted(): bool
    {
        return $this->approval_status !== 'approved';
    }

    public function hasIssues(): bool
    {
        return !empty($this->issues) && count($this->issues) > 0;
    }

    public function hasPhotos(): bool
    {
        return !empty($this->photos) && count($this->photos) > 0;
    }

    public function getCriticalIssues(): array
    {
        if (!$this->hasIssues()) {
            return [];
        }

        return collect($this->issues)
            ->filter(fn($issue) => ($issue['severity'] ?? '') === 'critical')
            ->values()
            ->toArray();
    }

    public function getIssueCount(): int
    {
        return $this->hasIssues() ? count($this->issues) : 0;
    }

    public function getPhotoCount(): int
    {
        return $this->hasPhotos() ? count($this->photos) : 0;
    }
}
