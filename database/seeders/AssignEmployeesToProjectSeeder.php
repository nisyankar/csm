<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Employee;
use Carbon\Carbon;

class AssignEmployeesToProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Assigning employees to demo project...');

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

        $this->command->info("Found {$employees->count()} employees");

        $assignedCount = 0;

        foreach ($employees as $employee) {
            // Zaten atanmış mı kontrol et
            if ($project->employees()->where('employee_id', $employee->id)->exists()) {
                continue;
            }

            // Rol belirle
            $role = match($employee->category) {
                'manager' => 'Proje Yöneticisi',
                'engineer' => 'Mühendis',
                'foreman' => 'Usta Başı',
                'worker' => 'İşçi',
                'technician' => 'Teknisyen',
                default => 'Çalışan'
            };

            // Atama tipi belirle
            $assignmentType = match($employee->category) {
                'manager', 'engineer' => 'full_time',
                'foreman', 'technician' => 'full_time',
                default => 'part_time'
            };

            // Atama yap
            $project->employees()->attach($employee->id, [
                'role_in_project' => $role,
                'assignment_type' => $assignmentType,
                'work_percentage' => $assignmentType === 'full_time' ? 100 : 50,
                'assigned_date' => Carbon::now()->subMonths(2),
                'start_date' => Carbon::now()->subMonths(2),
                'status' => 'active',
                'assigned_by' => 1, // Admin user
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Current project olarak set et
            $employee->update(['current_project_id' => $project->id]);

            $assignedCount++;
        }

        $this->command->info("✅ Assigned {$assignedCount} employees to project: {$project->name}");
    }
}
