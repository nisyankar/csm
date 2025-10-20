<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SystemAdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        // System Admin rolü oluştur
        $systemAdminRole = Role::firstOrCreate(['name' => 'system_admin']);
        
        // İzin yönetimi ile ilgili permissionlar
        $leaveManagementPermissions = [
            // İzin Parametreleri
            'view-leave-parameters',
            'create-leave-parameters', 
            'edit-leave-parameters',
            'delete-leave-parameters',
            
            // İzin Türleri
            'view-leave-types',
            'create-leave-types',
            'edit-leave-types', 
            'delete-leave-types',
            
            // İzin Hesaplamaları
            'view-leave-calculations',
            'create-leave-calculations',
            'edit-leave-calculations',
            'delete-leave-calculations',
            'recalculate-employee-leaves',
            
            // İzin Bakiyesi
            'view-leave-balance-logs',
            'adjust-leave-balances',
            'bulk-update-leave-balances',
            
            // Sistem Yönetimi
            'manage-system-settings',
            'view-system-logs',
            'backup-restore-data',
            'manage-user-roles',
            'view-audit-logs',
            
            // Rapor ve Analiz
            'view-leave-reports',
            'export-leave-data',
            'view-compliance-reports'
        ];
        
        // Permissionları oluştur
        foreach ($leaveManagementPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // System Admin'e tüm izinleri ver
        $systemAdminRole->givePermissionTo($leaveManagementPermissions);
        
        // Mevcut admin rolüne de izin yönetimi permissionlarını ver
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo($leaveManagementPermissions);
        
        // HR rolüne izin yönetimi permissionlarını ver (sadece okuma)
        $hrRole = Role::firstOrCreate(['name' => 'hr']);
        $hrPermissions = [
            'view-leave-parameters',
            'view-leave-types', 
            'view-leave-calculations',
            'view-leave-balance-logs',
            'view-leave-reports',
            'adjust-leave-balances'
        ];
        $hrRole->givePermissionTo($hrPermissions);
    }
}