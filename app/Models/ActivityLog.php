<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'activity_type',
        'subject_type',
        'subject_id',
        'action',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'route_name',
        'url',
        'method',
        'project_id',
        'severity',
        'is_system_generated',
        'logged_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'logged_at' => 'datetime',
        'is_system_generated' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Accessors
    public function getSeverityLabelAttribute(): string
    {
        return match($this->severity) {
            'info' => 'Bilgi',
            'warning' => 'Uyarı',
            'error' => 'Hata',
            'critical' => 'Kritik',
            default => ucfirst($this->severity),
        };
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'info' => 'blue',
            'warning' => 'yellow',
            'error' => 'orange',
            'critical' => 'red',
            default => 'gray',
        };
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('logged_at', '>=', now()->subDays($days));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('logged_at', today());
    }

    public function scopeSystemGenerated($query)
    {
        return $query->where('is_system_generated', true);
    }

    public function scopeUserGenerated($query)
    {
        return $query->where('is_system_generated', false);
    }

    // Static helper methods
    public static function log(
        string $activityType,
        string $action,
        ?Model $subject = null,
        ?array $properties = null,
        string $severity = 'info'
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'activity_type' => $activityType,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'action' => $action,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'route_name' => request()->route()?->getName(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'severity' => $severity,
            'logged_at' => now(),
        ]);
    }

    public static function logCreated(Model $subject, ?array $attributes = null): self
    {
        return self::log(
            'created',
            class_basename($subject) . ' oluşturuldu',
            $subject,
            ['attributes' => $attributes ?? $subject->getAttributes()]
        );
    }

    public static function logUpdated(Model $subject, array $oldValues, array $newValues): self
    {
        return self::log(
            'updated',
            class_basename($subject) . ' güncellendi',
            $subject,
            [
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ]
        );
    }

    public static function logDeleted(Model $subject): self
    {
        return self::log(
            'deleted',
            class_basename($subject) . ' silindi',
            $subject,
            ['attributes' => $subject->getAttributes()],
            'warning'
        );
    }

    public static function logViewed(Model $subject): self
    {
        return self::log(
            'viewed',
            class_basename($subject) . ' görüntülendi',
            $subject
        );
    }

    public static function logLogin(): self
    {
        return self::log(
            'logged_in',
            'Kullanıcı giriş yaptı',
            null,
            ['user_name' => auth()->user()?->name]
        );
    }

    public static function logLogout(): self
    {
        return self::log(
            'logged_out',
            'Kullanıcı çıkış yaptı',
            null,
            ['user_name' => auth()->user()?->name]
        );
    }

    public static function logCustom(string $action, ?string $description = null, string $severity = 'info'): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'activity_type' => 'custom',
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'route_name' => request()->route()?->getName(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'severity' => $severity,
            'logged_at' => now(),
        ]);
    }
}
