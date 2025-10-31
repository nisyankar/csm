<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutePermission extends Model
{
    protected $fillable = [
        'route_name',
        'module',
        'category',
        'action',
        'display_name',
        'description',
        'allowed_roles',
        'allowed_permissions',
        'is_public',
        'requires_project_access',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'allowed_roles' => 'array',
        'allowed_permissions' => 'array',
        'is_public' => 'boolean',
        'requires_project_access' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeProtected($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeRequiresProjectAccess($query)
    {
        return $query->where('requires_project_access', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('module')->orderBy('sort_order')->orderBy('display_name');
    }

    // Helper methods
    public function allows(User $user): bool
    {
        // Public route ise herkes erişebilir
        if ($this->is_public) {
            return true;
        }

        // Admin her şeye erişebilir
        if ($user->user_type === 'admin') {
            return true;
        }

        // Role kontrolü
        if ($this->allowed_roles && in_array($user->user_type, $this->allowed_roles)) {
            return true;
        }

        // Spatie permission kontrolü
        if ($this->allowed_permissions) {
            foreach ($this->allowed_permissions as $permission) {
                if ($user->can($permission)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function allowsRole(string $role): bool
    {
        if ($this->is_public) {
            return true;
        }

        if ($role === 'admin') {
            return true;
        }

        return $this->allowed_roles && in_array($role, $this->allowed_roles);
    }

    public function grantToRole(string $role): bool
    {
        $roles = $this->allowed_roles ?? [];

        if (!in_array($role, $roles)) {
            $roles[] = $role;
            return $this->update(['allowed_roles' => $roles]);
        }

        return true;
    }

    public function revokeFromRole(string $role): bool
    {
        $roles = $this->allowed_roles ?? [];

        $roles = array_filter($roles, fn($r) => $r !== $role);

        return $this->update(['allowed_roles' => array_values($roles)]);
    }

    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    // Static helpers
    public static function getGroupedByModule(): array
    {
        $permissions = self::active()->ordered()->get();

        return $permissions->groupBy('module')->map(function ($modulePerms, $moduleName) {
            return [
                'module' => $moduleName,
                'permissions' => $modulePerms->groupBy('category')->map(function ($categoryPerms, $categoryName) {
                    return [
                        'category' => $categoryName,
                        'permissions' => $categoryPerms,
                    ];
                })->values(),
            ];
        })->values()->toArray();
    }

    public static function checkRoute(string $routeName, User $user): bool
    {
        $permission = self::where('route_name', $routeName)->where('is_active', true)->first();

        if (!$permission) {
            // Route tanımlı değilse admin harici engelle (fail-safe)
            return $user->user_type === 'admin';
        }

        return $permission->allows($user);
    }

    public static function syncFromRoutes(): int
    {
        $routes = \Route::getRoutes();
        $count = 0;

        foreach ($routes as $route) {
            $routeName = $route->getName();

            if (!$routeName || str_starts_with($routeName, 'ignition.')) {
                continue;
            }

            // Route zaten varsa atla
            if (self::where('route_name', $routeName)->exists()) {
                continue;
            }

            // Route parse et
            $parts = explode('.', $routeName);
            $module = ucfirst($parts[0] ?? 'Other');
            $action = $parts[1] ?? 'index';

            $displayName = str_replace(['.', '-', '_'], ' ', $routeName);
            $displayName = ucwords($displayName);

            // Default allowed roles (customize as needed)
            $allowedRoles = ['admin'];

            self::create([
                'route_name' => $routeName,
                'module' => $module,
                'action' => $action,
                'display_name' => $displayName,
                'allowed_roles' => $allowedRoles,
                'is_active' => true,
                'sort_order' => $count,
            ]);

            $count++;
        }

        return $count;
    }
}
