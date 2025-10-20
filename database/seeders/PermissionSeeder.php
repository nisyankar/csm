<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Önce tüm permission'ları oluştur
        $permissions = [
            // Employee Management
            'view-employees',
            'create-employees', 
            'edit-employees',
            'delete-employees',
            'assign-employee-project',
            'generate-employee-qr',
            'terminate-employee',
            
            // Project Management
            'view-projects',
            'create-projects',
            'edit-projects', 
            'delete-projects',
            'assign-project-manager',
            'update-project-status',
            'view-project-budget',
            'edit-project-budget',
            
            // Timesheet Management
            'view-timesheets',
            'create-timesheets',
            'edit-timesheets',
            'delete-timesheets',
            'approve-timesheets',
            'reject-timesheets',
            'bulk-approve-timesheets',
            'override-timesheet-approval',
            
            // Department Management
            'view-departments',
            'create-departments',
            'edit-departments',
            'delete-departments',
            'assign-department-supervisor',
            
            // Leave Management
            'view-leave-requests',
            'create-leave-requests',
            'edit-leave-requests',
            'delete-leave-requests',
            'approve-leave-requests',
            'reject-leave-requests',
            'cancel-leave-requests',
            'bulk-approve-leaves',
            
            // Document Management
            'view-documents',
            'upload-documents',
            'edit-documents',
            'delete-documents',
            'approve-documents',
            'archive-documents',
            'download-documents',
            
            // Reports & Analytics
            'view-reports',
            'export-reports',
            'view-analytics',
            'view-financial-reports',
            'view-employee-performance',
            
            // QR Code Management
            'generate-qr-codes',
            'scan-qr-codes',
            'validate-qr-codes',
            'bulk-generate-qr',
            
            // Notification Management
            'send-notifications',
            'manage-notification-templates',
            'bulk-notifications',
            
            // System Administration
            'manage-users',
            'manage-roles',
            'manage-permissions',
            'system-settings',
            'backup-restore',
            'view-logs',
            
            // File Upload
            'upload-files',
            'manage-file-security',
            'bulk-upload-files',
        ];

        // Permission'ları oluştur
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roller ve yetkileri tanımla
        $rolePermissions = [
            'admin' => $permissions, // Admin tüm yetkilere sahip
            
            'hr' => [
                'view-employees', 'create-employees', 'edit-employees', 'delete-employees',
                'assign-employee-project', 'generate-employee-qr', 'terminate-employee',
                'view-projects', 'create-projects', 'edit-projects',
                'view-timesheets', 'approve-timesheets', 'bulk-approve-timesheets',
                'view-departments', 'create-departments', 'edit-departments',
                'view-leave-requests', 'approve-leave-requests', 'bulk-approve-leaves',
                'view-documents', 'upload-documents', 'approve-documents',
                'view-reports', 'export-reports', 'view-analytics', 'view-employee-performance',
                'generate-qr-codes', 'bulk-generate-qr',
                'send-notifications', 'manage-notification-templates',
                'manage-users', 'upload-files'
            ],
            
            'project_manager' => [
                'view-employees', 'edit-employees', 'assign-employee-project',
                'view-projects', 'edit-projects', 'assign-project-manager', 'update-project-status',
                'view-project-budget', 'edit-project-budget',
                'view-timesheets', 'approve-timesheets', 'bulk-approve-timesheets',
                'view-departments', 'create-departments', 'edit-departments', 'assign-department-supervisor',
                'view-leave-requests', 'approve-leave-requests', 'bulk-approve-leaves',
                'view-documents', 'upload-documents', 'approve-documents',
                'view-reports', 'export-reports', 'view-analytics', 'view-financial-reports',
                'generate-qr-codes', 'scan-qr-codes',
                'send-notifications', 'upload-files'
            ],
            
            'site_manager' => [
                'view-employees', 'edit-employees', 'assign-employee-project',
                'view-projects', 'edit-projects', 'update-project-status',
                'view-timesheets', 'approve-timesheets', 'bulk-approve-timesheets',
                'view-departments', 'edit-departments', 'assign-department-supervisor',
                'view-leave-requests', 'approve-leave-requests',
                'view-documents', 'upload-documents',
                'view-reports', 'export-reports',
                'generate-qr-codes', 'scan-qr-codes', 'validate-qr-codes',
                'send-notifications', 'upload-files'
            ],
            
            'foreman' => [
                'view-employees',
                'view-projects',
                'view-timesheets', 'create-timesheets', 'edit-timesheets',
                'view-departments',
                'view-leave-requests', 'create-leave-requests',
                'view-documents', 'upload-documents',
                'scan-qr-codes', 'validate-qr-codes',
                'upload-files'
            ],
            
            'employee' => [
                'view-timesheets', 'create-timesheets', 'edit-timesheets',
                'view-leave-requests', 'create-leave-requests', 'edit-leave-requests', 'cancel-leave-requests',
                'view-documents', 'upload-documents',
                'scan-qr-codes'
            ]
        ];

        // Rolleri oluştur ve yetkileri ata
        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($permissions);
        }

        $this->command->info('Permissions and roles created successfully!');
    }
}