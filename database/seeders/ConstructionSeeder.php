<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Department;
use App\Models\Timesheet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ConstructionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏗️ İnşaat projesi seeding başlıyor...');

        try {
            // 1. Roller ve izinler
            $this->createRoles();

            // 2. Admin kullanıcı
            $admin = $this->createAdmin();

            // 3. Projeler oluştur
            $projects = $this->createProjects($admin);

            // 4. Çalışanlar oluştur
            $employees = $this->createEmployees($admin);

            // 5. Departmanlar
            $departments = $this->createDepartments($projects, $employees);

            // 6. Çalışanları projelere ata
            $this->assignEmployeesToProjects($employees, $projects);

            // 7. Puantaj kayıtları (opsiyonel - az miktarda)
            $this->createSampleTimesheets($employees, $projects, $departments);

            $this->command->info('✅ Seeding başarıyla tamamlandı!');
            $this->printSummary();
        } catch (\Exception $e) {
            $this->command->error('❌ Seeding hatası: ' . $e->getMessage());
            $this->command->info('🔧 Çözüm: MinimalSeeder kullanmayı deneyin');
        }
    }

    private function createRoles(): void
    {
        $this->command->info('🔐 Roller oluşturuluyor...');

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'project_manager']);
        Role::firstOrCreate(['name' => 'site_manager']);
        Role::firstOrCreate(['name' => 'foreman']);
        Role::firstOrCreate(['name' => 'employee']);
    }

    private function createAdmin(): User
    {
        $this->command->info('👑 Admin kullanıcı oluşturuluyor...');

        // Basit user oluşturma - Factory kullanmadan
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@insaat.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'user_type' => 'admin',
            'is_active' => true,
            'language' => 'tr',
            'timezone' => 'Europe/Istanbul',
            'can_approve_timesheets' => true,
            'can_approve_leaves' => true,
            'email_notifications' => true,
            'sms_notifications' => false,
        ]);

        $admin->assignRole('admin');

        // Admin employee kaydı
        Employee::create([
            'user_id' => $admin->id,
            'employee_code' => 'EMP0001',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'tc_number' => '12345678901',
            'birth_date' => '1980-01-01',
            'position' => 'Sistem Yöneticisi',
            'category' => 'manager',
            'start_date' => '2020-01-01',
            'wage_type' => 'monthly',
            'status' => 'active',
            'qr_code' => 'QR000001',
            'annual_leave_days' => 30,
        ]);

        return $admin;
    }

    private function createProjects(User $admin): array
    {
        $this->command->info('📋 Projeler oluşturuluyor...');

        $projects = [];

        // Manuel proje oluşturma - Factory yerine
        $projectData = [
            [
                'project_code' => 'KON-2024-001',
                'name' => 'Lüks Villa Projesi',
                'type' => 'residential',
                'budget' => 5000000,
                'status' => 'active'
            ],
            [
                'project_code' => 'TIC-2024-001',
                'name' => 'AVM İnşaatı',
                'type' => 'commercial',
                'budget' => 25000000,
                'status' => 'active'
            ],
            [
                'project_code' => 'ALT-2024-001',
                'name' => 'Köprü İnşaatı',
                'type' => 'infrastructure',
                'budget' => 50000000,
                'status' => 'planning'
            ]
        ];

        foreach ($projectData as $data) {
            $projects[] = Project::create([
                'project_code' => $data['project_code'],
                'name' => $data['name'],
                'description' => $data['name'] . ' projesi detaylı açıklaması',
                'location' => 'İstanbul Şantiye Alanı',
                'city' => 'İstanbul',
                'start_date' => now()->subMonths(2),
                'planned_end_date' => now()->addMonths(10),
                'budget' => $data['budget'],
                'labor_budget' => $data['budget'] * 0.35,
                'spent_amount' => $data['budget'] * 0.15,
                'project_manager_id' => 1, // Admin employee
                'contact_phone' => '+90 212 555 0101',
                'status' => $data['status'],
                'type' => $data['type'],
                'priority' => 'medium',
                'estimated_employees' => rand(20, 50),
                'coordinates' => '41.0082,28.9784', // İstanbul
            ]);
        }

        return $projects;
    }

    private function createEmployees(User $admin): array
    {
        $this->command->info('👥 Çalışanlar oluşturuluyor...');

        $employees = ['admin' => Employee::where('user_id', $admin->id)->first()];

        // Proje yöneticileri
        for ($i = 1; $i <= 2; $i++) {
            $user = User::create([
                'name' => "Proje Yöneticisi {$i}",
                'email' => "pm{$i}@insaat.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'user_type' => 'project_manager',
                'is_active' => true,
                'language' => 'tr',
                'can_approve_timesheets' => true,
                'can_approve_leaves' => true,
            ]);

            $user->assignRole('project_manager');

            $employees["pm_{$i}"] = Employee::create([
                'user_id' => $user->id,
                'employee_code' => 'PM00' . $i,
                'first_name' => 'Proje',
                'last_name' => "Yöneticisi {$i}",
                'tc_number' => '20000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'birth_date' => '1975-01-01',
                'position' => 'Proje Yöneticisi',
                'category' => 'manager',
                'start_date' => '2022-01-01',
                'wage_type' => 'monthly',
                'status' => 'active',
                'qr_code' => 'QR0000' . ($i + 1),
                'annual_leave_days' => 30,
            ]);
        }

        // Formanlar
        for ($i = 1; $i <= 3; $i++) {
            $user = User::create([
                'name' => "Forman {$i}",
                'email' => "forman{$i}@insaat.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'user_type' => 'foreman',
                'is_active' => true,
                'language' => 'tr',
            ]);

            $user->assignRole('foreman');

            $employees["foreman_{$i}"] = Employee::create([
                'user_id' => $user->id,
                'employee_code' => 'FRM0' . $i,
                'first_name' => 'Forman',
                'last_name' => "Test {$i}",
                'tc_number' => '30000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'birth_date' => '1980-01-01',
                'position' => 'İnşaat Formanı',
                'category' => 'foreman',
                'start_date' => '2023-01-01',
                'daily_wage' => 600,
                'wage_type' => 'daily',
                'status' => 'active',
                'qr_code' => 'QR000' . ($i + 10),
                'annual_leave_days' => 20,
            ]);
        }

        // İşçiler
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => "İşçi {$i}",
                'email' => "isci{$i}@insaat.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'user_type' => 'employee',
                'is_active' => true,
                'language' => 'tr',
            ]);

            $user->assignRole('employee');

            $employees["worker_{$i}"] = Employee::create([
                'user_id' => $user->id,
                'employee_code' => 'WRK0' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'first_name' => 'İşçi',
                'last_name' => "Test {$i}",
                'tc_number' => '40000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'birth_date' => '1990-01-01',
                'position' => 'İnşaat İşçisi',
                'category' => 'worker',
                'start_date' => '2024-01-01',
                'daily_wage' => 400,
                'wage_type' => 'daily',
                'status' => 'active',
                'qr_code' => 'QR00' . ($i + 20),
                'annual_leave_days' => 14,
            ]);
        }

        return $employees;
    }

    private function createDepartments(array $projects, array $employees): array
    {
        $this->command->info('🏢 Departmanlar oluşturuluyor...');

        $departments = [];

        foreach ($projects as $index => $project) {
            // Her proje için 3 temel departman
            $deptTypes = ['structural', 'electrical', 'mechanical'];
            $deptNames = ['Yapısal İşler', 'Elektrik Tesisatı', 'Mekanik Tesisat'];
            $deptCodes = ['YPI', 'ELK', 'MEK'];

            foreach ($deptTypes as $key => $type) {
                $departments[] = Department::create([
                    'code' => $deptCodes[$key] . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'name' => $deptNames[$key],
                    'description' => $deptNames[$key] . ' departmanı - ' . $project->name,
                    'project_id' => $project->id,
                    'supervisor_id' => count($employees) > 3 ? array_values($employees)[3 + $key]->id : null,
                    'type' => $type,
                    'budget' => rand(200000, 800000),
                    'spent_amount' => rand(50000, 300000),
                    'status' => 'in_progress',
                    'planned_start_date' => now()->subMonth(),
                    'planned_end_date' => now()->addMonths(6),
                    'priority' => 'medium',
                    'estimated_employees' => rand(5, 15),
                    'location_description' => 'Şantiye ' . ($index + 1) . '. Alan',
                ]);
            }
        }

        return $departments;
    }

    private function assignEmployeesToProjects(array $employees, array $projects): void
    {
        $this->command->info('🔗 Çalışanlar projelere atanıyor...');

        foreach ($employees as $key => $employee) {
            if (str_contains($key, 'admin') || str_contains($key, 'pm_')) {
                continue; // Admin ve PM'leri atla
            }

            $project = $projects[array_rand($projects)];

            // Employee tablosunu güncelle
            $employee->update(['current_project_id' => $project->id]);

            // Pivot tablosuna ekle
            $project->employees()->attach($employee->id, [
                'assigned_date' => now()->subDays(rand(30, 180)),
                'role_in_project' => $this->getProjectRole($employee->category),
                'assignment_type' => 'full_time',
                'status' => 'active',
                'work_percentage' => 100,
            ]);
        }
    }

    private function createSampleTimesheets(array $employees, array $projects, array $departments): void
    {
        $this->command->info('⏰ Örnek puantaj kayıtları oluşturuluyor...');

        // Sadece son 5 gün için örnek puantaj oluştur
        foreach ($employees as $key => $employee) {
            if (str_contains($key, 'admin') || str_contains($key, 'pm_')) {
                continue; // Admin ve PM'leri atla
            }

            if ($employee->current_project_id) {
                for ($i = 5; $i >= 1; $i--) {
                    $date = now()->subDays($i);

                    if ($date->isWeekend() || rand(1, 100) > 85) {
                        continue; // %85 devam oranı
                    }

                    // Basit timesheet - yeni model yapısına uygun
                    Timesheet::create([
                        'employee_id' => $employee->id,
                        'project_id' => $employee->current_project_id,
                        'shift_id' => 1, // Gündüz vardiyası (ShiftSeeder'dan)
                        'work_date' => $date->format('Y-m-d'),
                        'start_time' => $date->copy()->setTime(8, 0),
                        'end_time' => $date->copy()->setTime(17, 0),
                        'hours_worked' => 8.0,
                        'overtime_hours' => 0,
                        'break_duration' => 1.0,
                        'entry_method' => 'manual',
                        'entered_by' => $employee->user_id,
                        'approval_status' => 'approved',
                        'year' => $date->year,
                        'week_number' => $date->weekOfYear,
                    ]);
                }
            }
        }
    }

    private function getProjectRole(string $category): string
    {
        $roles = [
            'worker' => 'İşçi',
            'foreman' => 'Forman',
            'engineer' => 'Mühendis',
            'manager' => 'Proje Yöneticisi',
            'system_admin' => 'Sistem Yöneticisi', // YENİ
        ];

        return $roles[$category] ?? 'İşçi';
    }

    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 Oluşturulan Veriler:');
        $this->command->info('├── Kullanıcılar: ' . User::count());
        $this->command->info('├── Çalışanlar: ' . Employee::count());
        $this->command->info('├── Projeler: ' . Project::count());
        $this->command->info('├── Departmanlar: ' . Department::count());
        $this->command->info('├── Puantaj Kayıtları: ' . Timesheet::count());
        $this->command->info('└── Roller: ' . Role::count());
        $this->command->info('');
        $this->command->info('🔑 Giriş Bilgileri:');
        $this->command->info('Email: admin@insaat.com');
        $this->command->info('Password: password');
        $this->command->info('');
        $this->command->info('🚀 Sunucuyu başlatın: php artisan serve');
    }
}
