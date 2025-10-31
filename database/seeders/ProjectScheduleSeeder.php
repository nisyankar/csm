<?php

namespace Database\Seeders;

use App\Models\ProjectSchedule;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ProjectScheduleSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $project = Project::first();
        if (!$project) {
            $this->command->warn('No projects found. Please run ConstructionSeeder first.');
            return;
        }

        $employees = Employee::limit(5)->get();
        if ($employees->isEmpty()) {
            $this->command->warn('No employees found.');
            return;
        }

        $departments = Department::where('project_id', $project->id)->limit(3)->get();

        $this->command->info('Creating project schedule tasks...');

        $taskNumber = 1;
        $startDate = Carbon::now()->startOfMonth();

        // Phase 1: Hazırlık Fazı
        $phase1 = ProjectSchedule::create([
            'project_id' => $project->id,
            'task_code' => 'TASK-' . str_pad($taskNumber++, 3, '0', STR_PAD_LEFT),
            'task_name' => 'Hazırlık Fazı',
            'task_description' => 'Proje başlangıç hazırlıkları ve site kurulumu',
            'task_type' => 'phase',
            'start_date' => $startDate->copy(),
            'end_date' => $startDate->copy()->addDays(30),
            'duration' => 31,
            'status' => 'completed',
            'priority' => 'critical',
            'assigned_to' => $employees->random()->id,
            'estimated_cost' => 500000,
            'actual_cost' => 480000,
            'completion_percentage' => 100,
            'actual_start_date' => $startDate->copy(),
            'actual_end_date' => $startDate->copy()->addDays(28),
            'color' => '#10B981',
        ]);

        // Sub-tasks for Phase 1
        ProjectSchedule::create([
            'project_id' => $project->id,
            'task_code' => 'TASK-' . str_pad($taskNumber++, 3, '0', STR_PAD_LEFT),
            'task_name' => 'Ruhsat ve İzinler',
            'task_description' => 'Gerekli ruhsat ve izinlerin alınması',
            'task_type' => 'activity',
            'start_date' => $startDate->copy(),
            'end_date' => $startDate->copy()->addDays(15),
            'duration' => 16,
            'status' => 'completed',
            'priority' => 'high',
            'assigned_to' => $employees->random()->id,
            'parent_task_id' => $phase1->id,
            'estimated_cost' => 100000,
            'actual_cost' => 95000,
            'completion_percentage' => 100,
            'actual_start_date' => $startDate->copy(),
            'actual_end_date' => $startDate->copy()->addDays(14),
            'color' => '#10B981',
        ]);

        ProjectSchedule::create([
            'project_id' => $project->id,
            'task_code' => 'TASK-' . str_pad($taskNumber++, 3, '0', STR_PAD_LEFT),
            'task_name' => 'Şantiye Kurulumu',
            'task_description' => 'Ofis, yemekhane ve depo kurulumu',
            'task_type' => 'activity',
            'start_date' => $startDate->copy()->addDays(10),
            'end_date' => $startDate->copy()->addDays(30),
            'duration' => 21,
            'status' => 'completed',
            'priority' => 'medium',
            'assigned_to' => $employees->random()->id,
            'department_id' => $departments->isNotEmpty() ? $departments->random()->id : null,
            'parent_task_id' => $phase1->id,
            'estimated_cost' => 400000,
            'actual_cost' => 385000,
            'completion_percentage' => 100,
            'actual_start_date' => $startDate->copy()->addDays(10),
            'actual_end_date' => $startDate->copy()->addDays(28),
            'color' => '#10B981',
        ]);

        // Phase 2: Temel ve Altyapı
        $phase2Start = $startDate->copy()->addDays(30);
        $phase2 = ProjectSchedule::create([
            'project_id' => $project->id,
            'task_code' => 'TASK-' . str_pad($taskNumber++, 3, '0', STR_PAD_LEFT),
            'task_name' => 'Temel ve Altyapı Fazı',
            'task_description' => 'Kazı, temel ve altyapı çalışmaları',
            'task_type' => 'phase',
            'start_date' => $phase2Start->copy(),
            'end_date' => $phase2Start->copy()->addDays(60),
            'duration' => 61,
            'status' => 'in_progress',
            'priority' => 'critical',
            'assigned_to' => $employees->random()->id,
            'estimated_cost' => 2500000,
            'actual_cost' => 1800000,
            'completion_percentage' => 75,
            'actual_start_date' => $phase2Start->copy(),
            'predecessors' => [['task_id' => $phase1->id, 'type' => 'FS']],
            'color' => '#3B82F6',
        ]);

        ProjectSchedule::create([
            'project_id' => $project->id,
            'task_code' => 'TASK-' . str_pad($taskNumber++, 3, '0', STR_PAD_LEFT),
            'task_name' => 'Kazı İşleri',
            'task_description' => 'Temel kazısı ve hafriyat',
            'task_type' => 'activity',
            'start_date' => $phase2Start->copy(),
            'end_date' => $phase2Start->copy()->addDays(20),
            'duration' => 21,
            'status' => 'completed',
            'priority' => 'high',
            'assigned_to' => $employees->random()->id,
            'department_id' => $departments->isNotEmpty() ? $departments->random()->id : null,
            'parent_task_id' => $phase2->id,
            'estimated_cost' => 800000,
            'actual_cost' => 750000,
            'completion_percentage' => 100,
            'actual_start_date' => $phase2Start->copy(),
            'actual_end_date' => $phase2Start->copy()->addDays(19),
            'color' => '#10B981',
        ]);

        ProjectSchedule::create([
            'project_id' => $project->id,
            'task_code' => 'TASK-' . str_pad($taskNumber++, 3, '0', STR_PAD_LEFT),
            'task_name' => 'Temel Betonu',
            'task_description' => 'Temel ve radye plak dökümü',
            'task_type' => 'activity',
            'start_date' => $phase2Start->copy()->addDays(20),
            'end_date' => $phase2Start->copy()->addDays(45),
            'duration' => 26,
            'status' => 'in_progress',
            'priority' => 'critical',
            'assigned_to' => $employees->random()->id,
            'department_id' => $departments->isNotEmpty() ? $departments->random()->id : null,
            'parent_task_id' => $phase2->id,
            'estimated_cost' => 1200000,
            'actual_cost' => 900000,
            'completion_percentage' => 70,
            'actual_start_date' => $phase2Start->copy()->addDays(20),
            'color' => '#3B82F6',
        ]);

        // Phase 3: Kaba İnşaat
        $phase3Start = $phase2Start->copy()->addDays(60);
        $phase3 = ProjectSchedule::create([
            'project_id' => $project->id,
            'task_code' => 'TASK-' . str_pad($taskNumber++, 3, '0', STR_PAD_LEFT),
            'task_name' => 'Kaba İnşaat Fazı',
            'task_description' => 'Kolon, kiriş ve döşeme işleri',
            'task_type' => 'phase',
            'start_date' => $phase3Start->copy(),
            'end_date' => $phase3Start->copy()->addDays(90),
            'duration' => 91,
            'status' => 'not_started',
            'priority' => 'high',
            'assigned_to' => $employees->random()->id,
            'estimated_cost' => 5000000,
            'completion_percentage' => 0,
            'predecessors' => [['task_id' => $phase2->id, 'type' => 'FS']],
            'color' => '#9CA3AF',
        ]);

        // Milestones
        ProjectSchedule::create([
            'project_id' => $project->id,
            'task_code' => 'TASK-' . str_pad($taskNumber++, 3, '0', STR_PAD_LEFT),
            'task_name' => 'Temel Tamamlanması',
            'task_description' => 'Temel çalışmalarının tamamlanma milestone',
            'task_type' => 'milestone',
            'start_date' => $phase2Start->copy()->addDays(60),
            'end_date' => $phase2Start->copy()->addDays(60),
            'duration' => 1,
            'status' => 'not_started',
            'priority' => 'critical',
            'assigned_to' => $employees->random()->id,
            'predecessors' => [['task_id' => $phase2->id, 'type' => 'FS']],
            'color' => '#F59E0B',
        ]);

        ProjectSchedule::create([
            'project_id' => $project->id,
            'task_code' => 'TASK-' . str_pad($taskNumber++, 3, '0', STR_PAD_LEFT),
            'task_name' => 'Kaba İnşaat Bitimi',
            'task_description' => 'Kaba inşaatın tamamlanması',
            'task_type' => 'milestone',
            'start_date' => $phase3Start->copy()->addDays(90),
            'end_date' => $phase3Start->copy()->addDays(90),
            'duration' => 1,
            'status' => 'not_started',
            'priority' => 'critical',
            'assigned_to' => $employees->random()->id,
            'predecessors' => [['task_id' => $phase3->id, 'type' => 'FS']],
            'color' => '#F59E0B',
        ]);

        // Deliverable
        ProjectSchedule::create([
            'project_id' => $project->id,
            'task_code' => 'TASK-' . str_pad($taskNumber++, 3, '0', STR_PAD_LEFT),
            'task_name' => 'Statik Rapor Onayı',
            'task_description' => 'Statik proje raporu hazırlığı ve onay süreci',
            'task_type' => 'deliverable',
            'start_date' => $startDate->copy()->addDays(5),
            'end_date' => $startDate->copy()->addDays(20),
            'duration' => 16,
            'status' => 'completed',
            'priority' => 'high',
            'assigned_to' => $employees->random()->id,
            'estimated_cost' => 50000,
            'actual_cost' => 50000,
            'completion_percentage' => 100,
            'actual_start_date' => $startDate->copy()->addDays(5),
            'actual_end_date' => $startDate->copy()->addDays(18),
            'color' => '#10B981',
        ]);

        // Meeting
        $nextMonday = Carbon::now()->next('Monday');
        ProjectSchedule::create([
            'project_id' => $project->id,
            'task_code' => 'TASK-' . str_pad($taskNumber++, 3, '0', STR_PAD_LEFT),
            'task_name' => 'Haftalık Koordinasyon Toplantısı',
            'task_description' => 'Proje ekibi koordinasyon toplantısı',
            'task_type' => 'meeting',
            'start_date' => $nextMonday,
            'end_date' => $nextMonday,
            'duration' => 1,
            'status' => 'not_started',
            'priority' => 'medium',
            'assigned_to' => $employees->random()->id,
            'color' => '#8B5CF6',
        ]);

        $this->command->info('Created ' . ($taskNumber - 1) . ' project schedule tasks.');
    }
}