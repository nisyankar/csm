<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProjectRole extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'project_id',
        'role',
        'is_active',
        'start_date',
        'end_date',
        'responsibilities',
        'permissions',
        'assigned_by',
        'assigned_at',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'responsibilities' => 'array',
        'permissions' => 'array',
        'assigned_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Accessors
    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'project_manager' => 'Proje Yöneticisi',
            'site_manager' => 'Şantiye Şefi',
            'engineer' => 'Mühendis',
            'foreman' => 'Forman',
            'viewer' => 'Görüntüleyici',
            'inspector' => 'Denetçi',
            'safety_officer' => 'İSG Uzmanı',
            default => ucfirst($this->role),
        };
    }

    public function getIsActiveNowAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        // Start date kontrolü
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        // End date kontrolü
        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    // Helper methods
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function extend(\Carbon\Carbon $newEndDate): bool
    {
        return $this->update(['end_date' => $newEndDate]);
    }

    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    public function grantPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            return $this->update(['permissions' => $permissions]);
        }

        return true;
    }

    public function revokePermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        $permissions = array_filter($permissions, fn($p) => $p !== $permission);

        return $this->update(['permissions' => array_values($permissions)]);
    }
}
