<?php

namespace App\Http\Controllers;

use App\Models\RoutePermission;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class RoutePermissionController extends Controller
{
    /**
     * Display a listing of route permissions.
     */
    public function index(Request $request)
    {
        $query = RoutePermission::query()->orderBy('module')->orderBy('route_name');

        // Filters
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('requires_project_access')) {
            $query->where('requires_project_access', $request->requires_project_access);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('route_name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $permissions = $query->paginate(50);

        // Group by module for statistics
        $statistics = [
            'total_routes' => RoutePermission::count(),
            'active_routes' => RoutePermission::where('is_active', true)->count(),
            'protected_routes' => RoutePermission::whereNotNull('allowed_roles')->count(),
            'public_routes' => RoutePermission::where('is_public', true)->count(),
        ];

        // Get unique modules
        $modules = RoutePermission::distinct('module')
            ->pluck('module')
            ->filter()
            ->sort()
            ->values();

        // Available roles
        $availableRoles = $this->getAvailableRoles();

        return Inertia::render('RoutePermissions/Index', [
            'permissions' => $permissions,
            'statistics' => $statistics,
            'modules' => $modules,
            'availableRoles' => $availableRoles,
            'filters' => $request->only(['module', 'is_active', 'requires_project_access', 'search']),
        ]);
    }

    /**
     * Update a single route permission.
     */
    public function update(Request $request, RoutePermission $routePermission)
    {
        $validated = $request->validate([
            'allowed_roles' => 'nullable|array',
            'allowed_roles.*' => 'string',
            'requires_project_access' => 'boolean',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $oldValues = $routePermission->only(['allowed_roles', 'requires_project_access', 'is_public', 'is_active']);

        $routePermission->update($validated);

        // Activity log
        ActivityLog::logUpdated($routePermission, [
            'route_name' => $routePermission->route_name,
            'old_values' => $oldValues,
            'new_values' => $validated,
        ]);

        return back()->with('success', 'Route yetkileri güncellendi.');
    }

    /**
     * Bulk update multiple route permissions.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'route_ids' => 'required|array',
            'route_ids.*' => 'exists:route_permissions,id',
            'allowed_roles' => 'nullable|array',
            'allowed_roles.*' => 'string',
            'requires_project_access' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $updateData = [];

        if (isset($validated['allowed_roles'])) {
            $updateData['allowed_roles'] = $validated['allowed_roles'];
        }
        if (isset($validated['requires_project_access'])) {
            $updateData['requires_project_access'] = $validated['requires_project_access'];
        }
        if (isset($validated['is_public'])) {
            $updateData['is_public'] = $validated['is_public'];
        }
        if (isset($validated['is_active'])) {
            $updateData['is_active'] = $validated['is_active'];
        }

        $count = RoutePermission::whereIn('id', $validated['route_ids'])
            ->update($updateData);

        // Activity log
        ActivityLog::log('updated', 'Toplu route yetki güncellemesi', null, [
            'route_count' => $count,
            'update_data' => $updateData,
        ], 'info');

        return back()->with('success', "{$count} route için yetkiler güncellendi.");
    }

    /**
     * Sync routes from Laravel route list.
     */
    public function syncFromRoutes()
    {
        $routes = Route::getRoutes();
        $synced = 0;
        $created = 0;
        $updated = 0;

        foreach ($routes as $route) {
            $routeName = $route->getName();

            // Skip unnamed routes and non-web routes
            if (!$routeName || !in_array('web', $route->middleware())) {
                continue;
            }

            // Try to determine module from route name
            $module = $this->determineModuleFromRouteName($routeName);

            // Generate display name and action
            $displayName = $this->generateDisplayName($routeName);
            $action = $this->generateActionFromRouteName($routeName);

            $existingPermission = RoutePermission::where('route_name', $routeName)->first();

            if ($existingPermission) {
                // Update existing (don't overwrite display_name if already set)
                $existingPermission->update([
                    'module' => $module,
                    'uri' => $route->uri(),
                    'methods' => implode('|', $route->methods()),
                    'action' => $action,
                    'display_name' => $existingPermission->display_name ?: $displayName,
                ]);
                $updated++;
            } else {
                // Create new
                RoutePermission::create([
                    'route_name' => $routeName,
                    'module' => $module,
                    'uri' => $route->uri(),
                    'methods' => implode('|', $route->methods()),
                    'action' => $action,
                    'display_name' => $displayName,
                    'allowed_roles' => ['admin'], // Default to admin only
                    'requires_project_access' => false,
                    'is_public' => false,
                    'is_active' => true,
                ]);
                $created++;
            }

            $synced++;
        }

        // Activity log
        ActivityLog::log('custom', 'Route listesi senkronize edildi', null, [
            'synced' => $synced,
            'created' => $created,
            'updated' => $updated,
        ], 'info');

        return back()->with('success', "Route senkronizasyonu tamamlandı. {$created} yeni, {$updated} güncelleme.");
    }

    /**
     * Batch assign roles to routes by module.
     */
    public function batchAssignByModule(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string',
            'allowed_roles' => 'required|array',
            'allowed_roles.*' => 'string',
        ]);

        $count = RoutePermission::where('module', $validated['module'])
            ->update(['allowed_roles' => $validated['allowed_roles']]);

        // Activity log
        ActivityLog::log('updated', 'Modül bazlı toplu yetki ataması', null, [
            'module' => $validated['module'],
            'roles' => $validated['allowed_roles'],
            'route_count' => $count,
        ], 'info');

        return back()->with('success', "{$validated['module']} modülündeki {$count} route için yetkiler atandı.");
    }

    /**
     * Get available roles for assignment.
     */
    private function getAvailableRoles(): array
    {
        return [
            ['value' => 'admin', 'label' => 'Admin'],
            ['value' => 'hr', 'label' => 'İK'],
            ['value' => 'project_manager', 'label' => 'Proje Müdürü'],
            ['value' => 'site_manager', 'label' => 'Şantiye Şefi'],
            ['value' => 'engineer', 'label' => 'Mühendis'],
            ['value' => 'foreman', 'label' => 'Usta Başı'],
            ['value' => 'inspector', 'label' => 'Denetçi'],
            ['value' => 'safety_officer', 'label' => 'İSG Uzmanı'],
            ['value' => 'viewer', 'label' => 'Görüntüleyici'],
            ['value' => 'accounting', 'label' => 'Muhasebe'],
            ['value' => 'finance', 'label' => 'Finans'],
        ];
    }

    /**
     * Generate display name from route name.
     */
    private function generateDisplayName(string $routeName): string
    {
        $parts = explode('.', $routeName);

        // Module mapping
        $moduleNames = [
            'dashboard' => 'Dashboard',
            'projects' => 'Projeler',
            'employees' => 'Çalışanlar',
            'attendance' => 'Puantaj',
            'salaries' => 'Maaşlar',
            'advances' => 'Avanslar',
            'warehouses' => 'Depolar',
            'materials' => 'Malzemeler',
            'stock' => 'Stok',
            'equipment' => 'Ekipmanlar',
            'maintenance' => 'Bakım Onarım',
            'subcontractors' => 'Taşeronlar',
            'contracts' => 'Sözleşmeler',
            'progress-payments' => 'Hakediş',
            'quantities' => 'Metraj',
            'work-items' => 'İş Kalemleri',
            'timeline' => 'Zaman Çizelgesi',
            'gantt' => 'Gantt',
            'permits' => 'Ruhsatlar',
            'inspections' => 'Denetimler',
            'health-safety' => 'İSG',
            'safety-incidents' => 'İSG Kazaları',
            'safety-trainings' => 'İSG Eğitimleri',
            'risk-assessments' => 'Risk Değerlendirme',
            'user-project-roles' => 'Proje Rolleri',
            'activity-logs' => 'Aktivite Logları',
            'route-permissions' => 'Route Yetkileri',
            'departments' => 'Departmanlar',
            'users' => 'Kullanıcılar',
            'profile' => 'Profil',
            'settings' => 'Ayarlar',
            'reports' => 'Raporlar',
        ];

        // Action mapping
        $actionNames = [
            'index' => 'Listesi',
            'create' => 'Oluştur',
            'store' => 'Kaydet',
            'show' => 'Detay',
            'edit' => 'Düzenle',
            'update' => 'Güncelle',
            'destroy' => 'Sil',
            'export' => 'Dışa Aktar',
            'import' => 'İçe Aktar',
            'approve' => 'Onayla',
            'reject' => 'Reddet',
            'activate' => 'Aktifleştir',
            'deactivate' => 'Pasifleştir',
            'restore' => 'Geri Yükle',
            'sync' => 'Senkronize Et',
            'download' => 'İndir',
            'upload' => 'Yükle',
            'print' => 'Yazdır',
            'search' => 'Ara',
            'filter' => 'Filtrele',
        ];

        if (count($parts) === 1) {
            // Single part route (dashboard, login, etc.)
            return $moduleNames[$parts[0]] ?? ucfirst($parts[0]);
        }

        $module = $parts[0];
        $action = $parts[1] ?? 'index';

        $moduleName = $moduleNames[$module] ?? ucfirst(str_replace('-', ' ', $module));
        $actionName = $actionNames[$action] ?? ucfirst($action);

        // Handle special cases
        if ($action === 'index') {
            return $moduleName . ' ' . $actionName;
        } elseif (in_array($action, ['create', 'store'])) {
            return 'Yeni ' . $moduleName;
        } elseif (in_array($action, ['edit', 'update'])) {
            return $moduleName . ' ' . $actionName;
        } elseif ($action === 'show') {
            return $moduleName . ' ' . $actionName;
        } else {
            return $moduleName . ' - ' . $actionName;
        }
    }

    /**
     * Generate action from route name.
     */
    private function generateActionFromRouteName(string $routeName): string
    {
        $parts = explode('.', $routeName);
        $action = $parts[1] ?? 'view';

        $actionMap = [
            'index' => 'view',
            'create' => 'create',
            'store' => 'create',
            'show' => 'view',
            'edit' => 'edit',
            'update' => 'edit',
            'destroy' => 'delete',
            'export' => 'export',
            'import' => 'import',
            'approve' => 'approve',
            'reject' => 'reject',
            'download' => 'download',
            'upload' => 'upload',
            'print' => 'print',
            'sync' => 'sync',
        ];

        return $actionMap[$action] ?? $action;
    }

    /**
     * Determine module name from route name.
     */
    private function determineModuleFromRouteName(string $routeName): string
    {
        // Extract first segment from route name (e.g., 'projects.index' -> 'Projects')
        $parts = explode('.', $routeName);
        $firstSegment = $parts[0] ?? '';

        // Common module mappings
        $moduleMap = [
            'dashboard' => 'Dashboard',
            'projects' => 'Projects',
            'employees' => 'Employees',
            'attendance' => 'Attendance',
            'salaries' => 'Salaries',
            'advances' => 'Advances',
            'warehouses' => 'Warehouses',
            'materials' => 'Materials',
            'stock' => 'Stock',
            'equipment' => 'Equipment',
            'maintenance' => 'Maintenance',
            'subcontractors' => 'Subcontractors',
            'contracts' => 'Contracts',
            'progress-payments' => 'ProgressPayments',
            'quantities' => 'Quantities',
            'work-items' => 'WorkItems',
            'timeline' => 'Timeline',
            'gantt' => 'Gantt',
            'permits' => 'Permits',
            'inspections' => 'Inspections',
            'health-safety' => 'HealthSafety',
            'user-project-roles' => 'Roles',
            'activity-logs' => 'ActivityLogs',
            'route-permissions' => 'Permissions',
            'departments' => 'Departments',
            'users' => 'Users',
            'profile' => 'Profile',
            'settings' => 'Settings',
        ];

        return $moduleMap[$firstSegment] ?? ucfirst($firstSegment);
    }
}
