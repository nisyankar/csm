<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Timesheet;
use App\Models\TimesheetApproval;
use App\Models\Project;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TimesheetDemoSeeder extends Seeder
{
    /**
     * Puantaj demo verilerini oluşturur
     *
     * Demo Kullanıcıları:
     * - admin@insaat.com (Admin - tüm puantajları görebilir)
     * - pm1@insaat.com (Proje Yöneticisi - proje bazlı görüntüler)
     * - forman1@insaat.com (Forman - ekibini yönetir, onaylar)
     * - isci1@insaat.com (İşçi - kendi puantajını görür)
     */
    public function run(): void
    {
        $this->command->info('⏰ Puantaj demo verileri oluşturuluyor...');

        // Bu ayın tüm çalışma günleri için puantaj oluştur
        $this->createMonthlyTimesheets();

        // Onay bekleyen puantajlar
        $this->createPendingTimesheets();

        // Reddedilmiş/revizyon gerektiren örnekler
        $this->createRejectedTimesheets();

        // Fazla mesai örnekleri
        $this->createOvertimeTimesheets();

        // İzinli ve devamsız örnekler
        $this->createLeaveTimesheets();

        $this->printDemoInfo();
    }

    /**
     * Bu ayın tüm çalışma günleri için onaylanmış puantajlar
     */
    private function createMonthlyTimesheets(): void
    {
        $this->command->info('📅 Aylık puantaj kayıtları oluşturuluyor...');

        $employees = Employee::where('category', '!=', 'manager')
            ->whereNotNull('current_project_id')
            ->get();

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->subDay(); // Bugüne kadar

        foreach ($employees as $employee) {
            $current = $startDate->copy();

            while ($current->lte($endDate)) {
                // Hafta sonları ve %10 devamsızlık oranı
                if (!$current->isWeekend() && rand(1, 100) > 10) {
                    // Check if timesheet already exists for this employee and date
                    $exists = Timesheet::where('employee_id', $employee->id)
                        ->where('work_date', $current->format('Y-m-d'))
                        ->exists();

                    if ($exists) {
                        $current->addDay();
                        continue; // Skip if already exists
                    }

                    // Çalışma saatleri - bazıları geç gelir/erken çıkar
                    $startTime = $this->getRandomStartTime($employee);
                    $endTime = $this->getRandomEndTime($employee);
                    $totalMinutes = $this->calculateMinutes($startTime, $endTime);

                    $timesheet = Timesheet::create([
                        'employee_id' => $employee->id,
                        'project_id' => $employee->current_project_id,
                        'work_date' => $current->format('Y-m-d'),
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'break_start' => '12:00:00',
                        'break_end' => '13:00:00',
                        'break_minutes' => 60,
                        'total_minutes' => $totalMinutes - 60,
                        'regular_minutes' => min($totalMinutes - 60, 480),
                        'overtime_minutes' => max(0, $totalMinutes - 60 - 480),
                        'shift_type' => 'day',
                        'attendance_type' => 'present',
                        'entry_method' => rand(1, 100) > 30 ? 'qr_code' : 'manual',
                        'entered_by' => $employee->user_id,
                        'entered_at' => $current->copy()->setTime(rand(8, 9), rand(0, 59)),
                        'approval_status' => 'approved',
                        'submitted_at' => $current->copy()->setTime(17, 30),
                        'first_approved_at' => $current->copy()->addDay()->setTime(10, 0),
                        'entry_location' => 'Şantiye Giriş',
                        'exit_location' => 'Şantiye Giriş',
                        'daily_rate' => $employee->daily_wage,
                        'hourly_rate' => $employee->hourly_wage ?? ($employee->daily_wage / 8),
                        'calculated_wage' => $this->calculateWage($employee, $totalMinutes - 60),
                    ]);

                    // Onay kaydı oluştur
                    $this->createApproval($timesheet, $employee, $current);
                }

                $current->addDay();
            }
        }

        $this->command->info('✅ ' . Timesheet::where('approval_status', 'approved')->count() . ' onaylı puantaj oluşturuldu');
    }

    /**
     * Bugün için onay bekleyen puantajlar
     */
    private function createPendingTimesheets(): void
    {
        $this->command->info('⏳ Onay bekleyen puantajlar oluşturuluyor...');

        $employees = Employee::where('category', 'worker')
            ->whereNotNull('current_project_id')
            ->limit(5)
            ->get();

        foreach ($employees as $employee) {
            $timesheet = Timesheet::create([
                'employee_id' => $employee->id,
                'project_id' => $employee->current_project_id,
                'work_date' => Carbon::today()->format('Y-m-d'),
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
                'break_minutes' => 60,
                'total_minutes' => 480,
                'regular_minutes' => 480,
                'overtime_minutes' => 0,
                'shift_type' => 'day',
                'attendance_type' => 'present',
                'entry_method' => 'qr_code',
                'entered_by' => $employee->user_id,
                'entered_at' => Carbon::today()->setTime(8, 5),
                'approval_status' => 'pending',
                'submitted_at' => Carbon::today()->setTime(17, 10),
                'entry_location' => 'Şantiye Giriş',
                'daily_rate' => $employee->daily_wage,
                'hourly_rate' => $employee->hourly_wage ?? ($employee->daily_wage / 8),
                'calculated_wage' => $employee->daily_wage,
                'notes' => 'QR ile otomatik giriş/çıkış',
            ]);

            // Pending approval kaydı
            TimesheetApproval::create([
                'timesheet_id' => $timesheet->id,
                'approver_id' => $employee->manager_id ?? 3, // Forman
                'approval_level' => 'first',
                'status' => 'pending',
                'assigned_at' => Carbon::today()->setTime(17, 10),
            ]);
        }

        $this->command->info('✅ ' . Timesheet::where('approval_status', 'pending')->count() . ' onay bekleyen puantaj oluşturuldu');
    }

    /**
     * Reddedilmiş ve revizyon gerektiren örnekler
     */
    private function createRejectedTimesheets(): void
    {
        $this->command->info('❌ Reddedilmiş/revizyon puantajları oluşturuluyor...');

        $employees = Employee::where('category', 'worker')->limit(2)->get();

        foreach ($employees as $employee) {
            $workDate = Carbon::yesterday()->format('Y-m-d');

            // Skip if timesheet already exists for this date
            if (Timesheet::where('employee_id', $employee->id)->where('work_date', $workDate)->exists()) {
                continue;
            }

            // Reddedilmiş
            $rejected = Timesheet::create([
                'employee_id' => $employee->id,
                'project_id' => $employee->current_project_id,
                'work_date' => $workDate,
                'start_time' => '09:30:00', // Geç gelmiş
                'end_time' => '16:00:00', // Erken çıkmış
                'total_minutes' => 390,
                'regular_minutes' => 390,
                'overtime_minutes' => 0,
                'shift_type' => 'day',
                'attendance_type' => 'late',
                'entry_method' => 'manual',
                'entered_by' => $employee->user_id,
                'entered_at' => Carbon::yesterday()->setTime(16, 30),
                'approval_status' => 'rejected',
                'late_reason' => 'Trafik nedeniyle geç kaldım',
                'daily_rate' => $employee->daily_wage,
                'calculated_wage' => $employee->daily_wage * 0.7,
            ]);

            TimesheetApproval::create([
                'timesheet_id' => $rejected->id,
                'approver_id' => 3, // Forman
                'approval_level' => 'first',
                'status' => 'rejected',
                'assigned_at' => Carbon::yesterday()->setTime(16, 30),
                'approved_at' => Carbon::yesterday()->setTime(18, 0),
                'rejection_reason' => 'Geç gelme mazereti kabul edilmedi. Lütfen daha erken çıkın.',
            ]);
        }

        $this->command->info('✅ Reddedilmiş örnekler oluşturuldu');
    }

    /**
     * Fazla mesai örnekleri
     */
    private function createOvertimeTimesheets(): void
    {
        $this->command->info('💪 Fazla mesai örnekleri oluşturuluyor...');

        $employees = Employee::where('category', 'worker')->limit(3)->get();

        foreach ($employees as $employee) {
            $workDate = Carbon::today()->subDays(3)->format('Y-m-d');

            // Skip if timesheet already exists for this date
            if (Timesheet::where('employee_id', $employee->id)->where('work_date', $workDate)->exists()) {
                continue;
            }

            $timesheet = Timesheet::create([
                'employee_id' => $employee->id,
                'project_id' => $employee->current_project_id,
                'work_date' => $workDate,
                'start_time' => '08:00:00',
                'end_time' => '20:00:00', // 12 saat
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
                'break_minutes' => 60,
                'total_minutes' => 660, // 11 saat net
                'regular_minutes' => 480,
                'overtime_minutes' => 180, // 3 saat fazla mesai
                'shift_type' => 'day',
                'attendance_type' => 'present',
                'entry_method' => 'qr_code',
                'entered_by' => $employee->user_id,
                'entered_at' => Carbon::today()->subDays(3)->setTime(8, 0),
                'approval_status' => 'approved',
                'submitted_at' => Carbon::today()->subDays(3)->setTime(20, 5),
                'first_approved_at' => Carbon::today()->subDays(2)->setTime(10, 0),
                'notes' => 'Proje teslim tarihi yaklaştığı için fazla mesai',
                'daily_rate' => $employee->daily_wage,
                'hourly_rate' => $employee->hourly_wage ?? ($employee->daily_wage / 8),
                'calculated_wage' => $employee->daily_wage + (($employee->daily_wage / 8) * 3 * 1.5), // %50 fazlasıyla
            ]);
        }

        $this->command->info('✅ Fazla mesai örnekleri oluşturuldu');
    }

    /**
     * İzinli ve devamsız örnekler
     */
    private function createLeaveTimesheets(): void
    {
        $this->command->info('🏖️ İzinli/devamsız örnekleri oluşturuluyor...');

        $employees = Employee::where('category', 'worker')->limit(2)->get();

        foreach ($employees as $employee) {
            $annualLeaveDate = Carbon::today()->subDays(7)->format('Y-m-d');
            $sickLeaveDate = Carbon::today()->subDays(5)->format('Y-m-d');

            // Yıllık izin - skip if exists
            if (!Timesheet::where('employee_id', $employee->id)->where('work_date', $annualLeaveDate)->exists()) {
                Timesheet::create([
                    'employee_id' => $employee->id,
                    'project_id' => $employee->current_project_id,
                    'work_date' => $annualLeaveDate,
                'attendance_type' => 'annual_leave',
                'entry_method' => 'system',
                'entered_by' => 1, // Admin
                'entered_at' => Carbon::today()->subDays(10)->setTime(10, 0),
                'approval_status' => 'approved',
                'submitted_at' => Carbon::today()->subDays(10)->setTime(10, 5),
                'first_approved_at' => Carbon::today()->subDays(9)->setTime(14, 0),
                    'notes' => 'Onaylı yıllık izin',
                    'daily_rate' => $employee->daily_wage,
                    'calculated_wage' => $employee->daily_wage, // İzinde de ücret ödenir
                ]);
            }

            // Hastalık izni - skip if exists
            if (!Timesheet::where('employee_id', $employee->id)->where('work_date', $sickLeaveDate)->exists()) {
                Timesheet::create([
                    'employee_id' => $employee->id,
                    'project_id' => $employee->current_project_id,
                    'work_date' => $sickLeaveDate,
                    'attendance_type' => 'sick_leave',
                    'entry_method' => 'manual',
                    'entered_by' => $employee->user_id,
                    'entered_at' => Carbon::today()->subDays(5)->setTime(9, 0),
                    'approval_status' => 'approved',
                    'submitted_at' => Carbon::today()->subDays(5)->setTime(9, 5),
                    'first_approved_at' => Carbon::today()->subDays(4)->setTime(11, 0),
                    'notes' => 'Grip nedeniyle hastaneye gitti',
                    'absence_reason' => 'Hastalık',
                    'daily_rate' => $employee->daily_wage,
                    'calculated_wage' => $employee->daily_wage,
                ]);
            }
        }

        $this->command->info('✅ İzin örnekleri oluşturuldu');
    }

    /**
     * Helper: Rastgele başlangıç saati
     */
    private function getRandomStartTime($employee): string
    {
        // %70 zamanında (08:00), %20 biraz geç (08:15-08:45), %10 çok geç (09:00+)
        $rand = rand(1, 100);

        if ($rand <= 70) {
            return '08:00:00';
        } elseif ($rand <= 90) {
            return '08:' . rand(15, 45) . ':00';
        } else {
            return '09:' . rand(0, 30) . ':00';
        }
    }

    /**
     * Helper: Rastgele bitiş saati
     */
    private function getRandomEndTime($employee): string
    {
        // %80 normal (17:00), %15 biraz fazla (17:30-18:00), %5 çok fazla (18:30+)
        $rand = rand(1, 100);

        if ($rand <= 80) {
            return '17:00:00';
        } elseif ($rand <= 95) {
            return '17:' . rand(30, 59) . ':00';
        } else {
            return '18:' . rand(30, 59) . ':00';
        }
    }

    /**
     * Helper: Dakika hesaplama
     */
    private function calculateMinutes(string $start, string $end): int
    {
        $startTime = Carbon::parse($start);
        $endTime = Carbon::parse($end);
        return $startTime->diffInMinutes($endTime);
    }

    /**
     * Helper: Ücret hesaplama
     */
    private function calculateWage($employee, int $totalMinutes): float
    {
        $regularMinutes = min($totalMinutes, 480);
        $overtimeMinutes = max(0, $totalMinutes - 480);

        if ($employee->wage_type === 'daily') {
            return $employee->daily_wage;
        } elseif ($employee->wage_type === 'hourly') {
            $hourlyRate = $employee->hourly_wage ?? 50;
            $regularWage = ($regularMinutes / 60) * $hourlyRate;
            $overtimeWage = ($overtimeMinutes / 60) * $hourlyRate * 1.5;
            return $regularWage + $overtimeWage;
        }

        return 0;
    }

    /**
     * Helper: Onay kaydı oluştur
     */
    private function createApproval($timesheet, $employee, $date): void
    {
        // Forman onayı
        TimesheetApproval::create([
            'timesheet_id' => $timesheet->id,
            'approver_id' => $employee->manager_id ?? 3,
            'approval_level' => 'first',
            'status' => 'approved',
            'assigned_at' => $date->copy()->addDay()->setTime(8, 0),
            'approved_at' => $date->copy()->addDay()->setTime(10, 0),
            'approval_notes' => 'Onaylandı',
        ]);
    }

    /**
     * Demo bilgilerini yazdır
     */
    private function printDemoInfo(): void
    {
        $this->command->info('');
        $this->command->info('📊 Puantaj Demo İstatistikleri:');
        $this->command->info('├── Toplam Puantaj: ' . Timesheet::count());
        $this->command->info('├── Onaylı: ' . Timesheet::where('approval_status', 'approved')->count());
        $this->command->info('├── Onay Bekleyen: ' . Timesheet::where('approval_status', 'pending')->count());
        $this->command->info('├── Reddedilen: ' . Timesheet::where('approval_status', 'rejected')->count());
        $this->command->info('└── Fazla Mesai: ' . Timesheet::where('overtime_minutes', '>', 0)->count());
        $this->command->info('');
        $this->command->info('👥 Demo Kullanıcıları (Şifre: password):');
        $this->command->info('├── admin@insaat.com → Tüm puantajları görür');
        $this->command->info('├── pm1@insaat.com → Proje yöneticisi görünümü');
        $this->command->info('├── forman1@insaat.com → Forman - ekibini onaylar');
        $this->command->info('└── isci1@insaat.com → İşçi - kendi puantajını görür');
        $this->command->info('');
    }
}
