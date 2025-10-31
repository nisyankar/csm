<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledReport extends Model
{
    protected $fillable = [
        'template_id',
        'schedule_type',
        'recipients_json',
        'last_run_at',
        'next_run_at',
        'is_active',
    ];

    protected $casts = [
        'recipients_json' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $appends = ['schedule_type_label'];

    /**
     * İlişkiler
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'template_id');
    }

    /**
     * Accessors
     */
    public function getScheduleTypeLabelAttribute(): string
    {
        return match($this->schedule_type) {
            'daily' => 'Günlük',
            'weekly' => 'Haftalık',
            'monthly' => 'Aylık',
            default => (string) $this->schedule_type
        };
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDueToRun($query)
    {
        return $query->where('is_active', true)
            ->where('next_run_at', '<=', now());
    }

    /**
     * Helper Methods
     */
    public function calculateNextRunDate(): void
    {
        $this->next_run_at = match($this->schedule_type) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            default => now()->addDay(),
        };
        $this->save();
    }
}
