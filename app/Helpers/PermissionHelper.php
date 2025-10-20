<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Project;
use App\Models\Employee;

class PermissionHelper
{
    /**
     * Kullanıcının belirli bir çalışan verisine erişimi var mı?
     */
    public static function canAccessEmployee(User $user, Employee $employee): bool
    {
        // Admin ve HR herkese erişebilir
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }

        // Kendi verilerine erişim
        if ($user->employee_id === $employee->id) {
            return true;
        }

        // Yöneticiler astlarına erişebilir
        if ($user->hasRole(['project_manager', 'site_manager', 'foreman'])) {
            return self::isTeamMember($user, $employee);
        }

        return false;
    }

    /**
     * Kullanıcının belirli bir projeye erişimi var mı?
     */
    public static function canAccessProject(User $user, Project $project): bool
    {
        // Admin ve HR her projeye erişebilir
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }

        // Proje yöneticisi
        if ($project->project_manager_id === $user->employee_id) {
            return true;
        }

        // Şantiye şefi
        if ($project->site_manager_id === $user->employee_id) {
            return true;
        }

        // Çalışan bu projede çalışıyor mu?
        if ($user->employee && $user->employee->current_project_id === $project->id) {
            return true;
        }

        // Kullanıcının project_access listesinde var mı?
        if ($user->project_access && in_array($project->id, $user->project_access)) {
            return true;
        }

        // Çalışan bu projeye atanmış mı? (many-to-many)
        if ($user->employee) {
            return $user->employee->projects()->where('project_id', $project->id)->exists();
        }

        return false;
    }

    /**
     * Kullanıcının takım üyesi olup olmadığını kontrol et
     */
    public static function isTeamMember(User $user, Employee $employee): bool
    {
        if (!$user->employee) {
            return false;
        }

        // Direkt ast mı?
        if ($employee->manager_id === $user->employee_id) {
            return true;
        }

        // Aynı projede çalışıyor mu?
        if ($user->hasRole(['project_manager', 'site_manager'])) {
            $managedProjects = Project::where('project_manager_id', $user->employee_id)
                ->orWhere('site_manager_id', $user->employee_id)
                ->pluck('id');
            
            if ($managedProjects->contains($employee->current_project_id)) {
                return true;
            }

            // Many-to-many project assignments
            $employeeProjects = $employee->projects()->pluck('project_id');
            if ($managedProjects->intersect($employeeProjects)->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kullanıcının takım ID'lerini al
     */
    public static function getTeamEmployeeIds(User $user): array
    {
        if (!$user->employee) {
            return [];
        }

        $teamIds = [$user->employee_id]; // Kendini dahil et

        if ($user->hasRole(['project_manager', 'site_manager'])) {
            // Yönetilen projelerden çalışanları al
            $managedProjects = Project::where('project_manager_id', $user->employee_id)
                ->orWhere('site_manager_id', $user->employee_id)
                ->pluck('id');
            
            $projectEmployees = Employee::whereIn('current_project_id', $managedProjects)->pluck('id');
            $teamIds = array_merge($teamIds, $projectEmployees->toArray());

            // Many-to-many atamalardan da al
            $assignedEmployees = Employee::whereHas('projects', function($query) use ($managedProjects) {
                $query->whereIn('project_id', $managedProjects);
            })->pluck('id');
            $teamIds = array_merge($teamIds, $assignedEmployees->toArray());
        }

        if ($user->hasRole('foreman')) {
            // Direkt astları
            $directReports = Employee::where('manager_id', $user->employee_id)->pluck('id');
            $teamIds = array_merge($teamIds, $directReports->toArray());
        }

        return array_unique($teamIds);
    }

    /**
     * Kullanıcının erişebildiği proje ID'lerini al
     */
    public static function getAccessibleProjectIds(User $user): array
    {
        if ($user->hasRole(['admin', 'hr'])) {
            return Project::pluck('id')->toArray();
        }

        $projectIds = [];

        if ($user->employee) {
            // Yönetilen projeler
            $managedProjects = Project::where('project_manager_id', $user->employee_id)
                ->orWhere('site_manager_id', $user->employee_id)
                ->pluck('id')
                ->toArray();
            $projectIds = array_merge($projectIds, $managedProjects);

            // Mevcut proje
            if ($user->employee->current_project_id) {
                $projectIds[] = $user->employee->current_project_id;
            }

            // Atanmış projeler (many-to-many)
            $assignedProjects = $user->employee->projects()->pluck('project_id')->toArray();
            $projectIds = array_merge($projectIds, $assignedProjects);
        }

        // Kullanıcının özel erişim listesi
        if ($user->project_access) {
            $projectIds = array_merge($projectIds, $user->project_access);
        }

        return array_unique($projectIds);
    }

    /**
     * Kullanıcının puantaj onaylama yetkisi var mı?
     */
    public static function canApproveTimesheet(User $user, Employee $employee): bool
    {
        if (!$user->can('approve-timesheets')) {
            return false;
        }

        // Admin ve HR hepsini onaylayabilir
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }

        // Takım üyesi mi kontrol et
        return self::isTeamMember($user, $employee);
    }

    /**
     * Kullanıcının izin onaylama yetkisi var mı?
     */
    public static function canApproveLeaveRequest(User $user, Employee $employee): bool
    {
        if (!$user->can('approve-leave-requests')) {
            return false;
        }

        // Admin ve HR hepsini onaylayabilir
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }

        // Takım üyesi mi kontrol et
        return self::isTeamMember($user, $employee);
    }

    /**
     * Kullanıcının belge onaylama yetkisi var mı?
     */
    public static function canApproveDocument(User $user, $document): bool
    {
        if (!$user->can('approve-documents')) {
            return false;
        }

        // Admin ve HR hepsini onaylayabilir
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }

        // Proje belgesi ise proje erişimi kontrol et
        if ($document->project_id) {
            $project = Project::find($document->project_id);
            return $project ? self::canAccessProject($user, $project) : false;
        }

        return true;
    }

    /**
     * Kullanıcının maaş bilgilerini görme yetkisi var mı?
     */
    public static function canViewSalary(User $user, Employee $employee): bool
    {
        if (!$user->can('view-employee-salaries')) {
            return false;
        }

        // Admin ve HR hepsini görebilir
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }

        // Kendi maaşını görebilir
        if ($user->employee_id === $employee->id) {
            return true;
        }

        // Proje yöneticileri takımlarının maaşını görebilir
        if ($user->hasRole('project_manager')) {
            return self::isTeamMember($user, $employee);
        }

        return false;
    }

    /**
     * Kullanıcının finansal raporları görme yetkisi var mı?
     */
    public static function canViewFinancialReports(User $user): bool
    {
        return $user->can('view-financial-reports') && 
               $user->hasRole(['admin', 'hr', 'project_manager']);
    }

    /**
     * Yetki kontrolü ile birlikte middleware group öner
     */
    public static function getRequiredMiddleware(string $permission): array
    {
        $middlewareMap = [
            // Admin only permissions
            'manage-system-settings' => ['admin'],
            'manage-user-roles' => ['admin'],
            'backup-system' => ['admin'],
            'view-audit-logs' => ['admin'],

            // HR permissions
            'view-employee-salaries' => ['hr'],
            'edit-employee-salaries' => ['hr'],
            'manage-leave-types' => ['hr'],

            // Manager permissions
            'create-projects' => ['manager'],
            'edit-project-budget' => ['manager'],
            'approve-timesheets' => ['manager'],

            // Employee permissions
            'use-qr-checkin' => ['employee'],
            'view-own-timesheets' => ['employee'],
        ];

        return $middlewareMap[$permission] ?? ['employee'];
    }

    /**
     * Kullanıcının izin parametrelerini yönetme yetkisi var mı?
     */
    public static function canManageLeaveParameters(User $user): bool
    {
        return $user->can('manage-leave-parameters') && 
               $user->hasRole(['admin', 'system_admin']);
    }

    /**
     * Kullanıcının izin türlerini yönetme yetkisi var mı?
     */
    public static function canManageLeaveTypes(User $user): bool
    {
        return $user->can('manage-leave-types') && 
               $user->hasRole(['admin', 'system_admin', 'hr']);
    }

    /**
     * Kullanıcının izin hesaplamalarını görme yetkisi var mı?
     */
    public static function canViewLeaveCalculations(User $user): bool
    {
        return $user->can('view-leave-calculations') && 
               $user->hasRole(['admin', 'system_admin', 'hr']);
    }

    /**
     * Kullanıcının izin bakiyelerini düzeltme yetkisi var mı?
     */
    public static function canAdjustLeaveBalances(User $user): bool
    {
        return $user->can('adjust-leave-balances') && 
               $user->hasRole(['admin', 'system_admin', 'hr']);
    }

    /**
     * Kullanıcının sistem ayarlarını yönetme yetkisi var mı?
     */
    public static function canManageSystemSettings(User $user): bool
    {
        return $user->can('manage-system-settings') && 
               $user->hasRole(['admin', 'system_admin']);
    }

    /**
     * İzin yönetimi menüsünü gösterebilir mi?
     */
    public static function canAccessLeaveManagement(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'system_admin', 'hr']) &&
               $user->hasAnyPermission([
                   'view-leave-parameters',
                   'view-leave-types',
                   'view-leave-calculations'
               ]);
    }

    /**
     * Sistem yöneticisi mi?
     */
    public static function isSystemAdmin(User $user): bool
    {
        return $user->hasRole('system_admin') || $user->hasRole('admin');
    }

    /**
     * Role tabanlı dashboard yönlendirmesi
     */
    public static function getDashboardRoute(User $user): string
    {
        if ($user->hasRole('system_admin')) {
            return route('admin.dashboard');
        }
        
        if ($user->hasRole('admin')) {
            return route('admin.dashboard');
        }
        
        if ($user->hasRole('hr')) {
            return route('hr.dashboard');
        }
        
        if ($user->hasRole(['project_manager', 'site_manager'])) {
            return route('manager.dashboard');
        }
        
        if ($user->hasRole('foreman')) {
            return route('foreman.dashboard');
        }
        
        return route('dashboard');
    }
}