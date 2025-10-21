<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeProjectAssignment;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Department;
use Carbon\Carbon;

class EmployeeProjectAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Creating employee-project assignments...');

        // Demo projeyi bul
        $project = Project::where('project_code', 'KON-2025-001')->first();

        if (!$project) {
            $this->command->error('❌ Demo project not found! Run Phase1TestDataSeeder first.');
            return;
        }

        // Tüm çalışanları getir
        $employees = Employee::all();

        if ($employees->isEmpty()) {
            $this->command->error('❌ No employees found!');
            return;
        }

        // Mevcut atamaları temizle (tekrar seed için)
        EmployeeProjectAssignment::where('project_id', $project->id)->delete();

        $this->command->info("Found {$employees->count()} employees");

        $assignedCount = 0;
        $primaryAssigned = false;

        foreach ($employees as $employee) {
            // Rol ve departman belirle
            $roleData = $this->getRoleAndDepartment($employee, $project);

            if (!$roleData) {
                continue;
            }

            // İlk manager'ı primary olarak ata
            $isPrimary = false;
            if (!$primaryAssigned && $employee->category === 'manager') {
                $isPrimary = true;
                $primaryAssigned = true;
            }

            // Atama oluştur
            EmployeeProjectAssignment::create([
                'employee_id' => $employee->id,
                'project_id' => $project->id,
                'department_id' => $roleData['department_id'],
                'is_primary' => $isPrimary,
                'start_date' => Carbon::now()->subMonths(2),
                'end_date' => null, // Devam ediyor
                'status' => 'active',
                'role_in_project' => $roleData['role'],
                'notes' => $isPrimary ? 'Ana proje yöneticisi' : null,
                'assigned_by' => 1, // Admin user
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Employee modelindeki current_project_id'yi de güncelle
            $employee->update(['current_project_id' => $project->id]);

            $assignedCount++;
        }

        $this->command->info("✅ Created {$assignedCount} employee-project assignments");
    }

    private function getRoleAndDepartment(Employee $employee, Project $project): ?array
    {
        // Proje departmanlarını al
        $departments = $project->departments;

        // Eğer departman yoksa genel atama
        if ($departments->isEmpty()) {
            return [
                'role' => $this->getRoleByCategory($employee->category),
                'department_id' => null,
            ];
        }

        // Kategoriye göre departman ve rol belirle
        $departmentMapping = [
            'manager' => ['type' => 'administration', 'role' => 'Proje Yöneticisi'],
            'engineer' => ['type' => 'structural', 'role' => 'İnşaat Mühendisi'],
            'foreman' => ['type' => 'structural', 'role' => 'Usta Başı'],
            'worker' => ['type' => 'structural', 'role' => 'İşçi'],
            'technician' => ['type' => 'mechanical', 'role' => 'Teknisyen'],
        ];

        $mapping = $departmentMapping[$employee->category] ?? null;

        if (!$mapping) {
            return [
                'role' => 'Çalışan',
                'department_id' => $departments->first()->id,
            ];
        }

        // İlgili departmanı bul
        $department = $departments->where('type', $mapping['type'])->first();

        return [
            'role' => $mapping['role'],
            'department_id' => $department ? $department->id : $departments->first()->id,
        ];
    }

    private function getRoleByCategory(string $category): string
    {
        return match($category) {
            'manager' => 'Proje Yöneticisi',
            'engineer' => 'Mühendis',
            'foreman' => 'Usta Başı',
            'worker' => 'İşçi',
            'technician' => 'Teknisyen',
            default => 'Çalışan'
        };
    }
}
