<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class TemporaryAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'from_project_id',
        'to_project_id',
        'preferred_shift_id',
        'start_date',
        'end_date',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'status_label',
        'duration_days',
        'is_active',
        'is_expired',
    ];

    /**
     * Relationships
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function fromProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'from_project_id');
    }

    public function toProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'to_project_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'temporary_assignment_id');
    }

    public function preferredShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'preferred_shift_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where(function ($q) use ($projectId) {
            $q->where('from_project_id', $projectId)
              ->orWhere('to_project_id', $projectId);
        });
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('status', 'active')
                    ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    /**
     * Accessors
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Onay Bekliyor',
            'active' => 'Aktif',
            'completed' => 'Tamamlandı',
            'cancelled' => 'İptal Edildi',
            default => 'Bilinmiyor',
        };
    }

    public function getDurationDaysAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active'
            && $this->start_date <= now()
            && $this->end_date >= now();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date < now() && $this->status === 'active';
    }

    /**
     * Methods
     */
    public function approve(User $user): bool
    {
        $this->update([
            'status' => 'active',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return true;
    }

    public function reject(User $user, string $reason): bool
    {
        $this->update([
            'status' => 'cancelled',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'notes' => ($this->notes ? $this->notes . "\n\n" : '') . "Reddedilme Nedeni: " . $reason,
        ]);

        return true;
    }

    public function complete(): bool
    {
        $this->update([
            'status' => 'completed',
        ]);

        return true;
    }

    public function cancel(string $reason): bool
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => ($this->notes ? $this->notes . "\n\n" : '') . "İptal Nedeni: " . $reason,
        ]);

        return true;
    }

    /**
     * Get progress percentage (how much time has passed)
     */
    public function getProgressPercentage(): float
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        $totalDays = $this->duration_days;
        $elapsedDays = $this->start_date->diffInDays(now()) + 1;

        if ($elapsedDays <= 0) {
            return 0;
        }

        if ($elapsedDays >= $totalDays) {
            return 100;
        }

        return round(($elapsedDays / $totalDays) * 100, 2);
    }

    /**
     * Get remaining days
     */
    public function getRemainingDays(): int
    {
        if (!$this->end_date) {
            return 0;
        }

        $remaining = now()->diffInDays($this->end_date, false);
        return max(0, (int) $remaining);
    }
}
