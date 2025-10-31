<?php

namespace Database\Seeders;

use App\Models\TemporaryAssignment;
use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TemporaryAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Geçici görevlendirme verileri oluşturuluyor...');

        // Get active employees
        $employees = Employee::where('status', 'active')
            ->whereNotNull('current_project_id')
            ->limit(10)
            ->get();

        // Get at least 2 projects for variety
        $projects = Project::where('status', 'active')
            ->limit(5)
            ->get();

        if ($employees->isEmpty() || $projects->isEmpty()) {
            $this->command->warn('Yeterli çalışan veya proje bulunamadı. Seeder atlanıyor.');
            return;
        }

        // Get admin user for approvals
        $adminUser = User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->first() ?? User::first();

        // Get default shift (usually first active shift)
        $defaultShift = Shift::where('is_active', true)
            ->orderBy('sort_order')
            ->first();

        $count = 0;

        // Create 10 sample assignments with mixed statuses
        for ($i = 0; $i < 10; $i++) {
            $employee = $employees->random();
            $fromProject = $employee->currentProject ?? $projects->random();

            // Get different project for assignment
            $toProject = $projects->where('id', '!=', $fromProject->id)->random() ?? $projects->random();

            // Mix of different statuses
            $statusDistribution = $i % 5;

            if ($statusDistribution === 0) {
                // Pending (5 days in future)
                $status = 'pending';
                $startDate = Carbon::now()->addDays(5);
                $endDate = Carbon::now()->addDays(12);
                $approvedBy = null;
                $approvedAt = null;
            } elseif ($statusDistribution === 1) {
                // Active (started, within 7 days to expire)
                $status = 'active';
                $startDate = Carbon::now()->subDays(3);
                $endDate = Carbon::now()->addDays(4); // Expiring soon
                $approvedBy = $adminUser->id;
                $approvedAt = $startDate->copy()->subDays(5);
            } elseif ($statusDistribution === 2) {
                // Active (recently started)
                $status = 'active';
                $startDate = Carbon::now()->subDays(1);
                $endDate = Carbon::now()->addDays(20);
                $approvedBy = $adminUser->id;
                $approvedAt = $startDate->copy()->subDays(2);
            } elseif ($statusDistribution === 3) {
                // Completed (past)
                $status = 'completed';
                $startDate = Carbon::now()->subDays(30);
                $endDate = Carbon::now()->subDays(5);
                $approvedBy = $adminUser->id;
                $approvedAt = $startDate->copy()->subDays(3);
            } else {
                // Active (longer duration)
                $status = 'active';
                $startDate = Carbon::now()->subDays(15);
                $endDate = Carbon::now()->addDays(30);
                $approvedBy = $adminUser->id;
                $approvedAt = $startDate->copy()->subDays(17);
            }

            // Check if assignment already exists for this employee in this date range
            $exists = TemporaryAssignment::where('employee_id', $employee->id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($nested) use ($startDate, $endDate) {
                          $nested->where('start_date', '<=', $startDate)
                                 ->where('end_date', '>=', $endDate);
                      });
                })
                ->exists();

            if ($exists) {
                continue;
            }

            // Create assignment
            TemporaryAssignment::create([
                'employee_id' => $employee->id,
                'from_project_id' => $fromProject->id,
                'to_project_id' => $toProject->id,
                'preferred_shift_id' => $defaultShift?->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'reason' => $this->getRandomReason(),
                'status' => $status,
                'requested_by' => $adminUser->id,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
                'notes' => $this->getRandomNotes(),
            ]);

            $count++;
        }

        $this->command->info("✅ {$count} geçici görevlendirme oluşturuldu");

        // Print summary
        $this->printStatistics();
    }

    /**
     * Get random reason for assignment
     */
    private function getRandomReason(): string
    {
        $reasons = [
            'Personel eksikliğini giderme',
            'Proje acil ihtiyaç',
            'Beceri geliştirme eğitimi',
            'Proje destek',
            'Yönetici özel isteği',
            'Kalite kontrol desteği',
            'İnsan kaynakları dengesi',
        ];

        return $reasons[array_rand($reasons)];
    }

    /**
     * Get random notes
     */
    private function getRandomNotes(): ?string
    {
        $notes = [
            'Yüksek öncelikli proje',
            'Müşteri talebine göre ataması',
            'Proje bitişine kadar gerekli',
            'Teknik bilgi transferi için',
            'Mevsimsel talep',
            null,
        ];

        return $notes[array_rand($notes)];
    }

    /**
     * Print statistics
     */
    private function printStatistics(): void
    {
        $this->command->info('');
        $this->command->info('📊 Geçici Görevlendirme İstatistikleri:');
        $this->command->info('├── Toplam: ' . TemporaryAssignment::count());
        $this->command->info('├── Onay Bekleyen: ' . TemporaryAssignment::where('status', 'pending')->count());
        $this->command->info('├── Aktif: ' . TemporaryAssignment::where('status', 'active')->count());
        $this->command->info('├── Tamamlanan: ' . TemporaryAssignment::where('status', 'completed')->count());
        $this->command->info('├── İptal Edilmiş: ' . TemporaryAssignment::where('status', 'cancelled')->count());
        $this->command->info('└── 7 Gün İçinde Bitiş: ' . TemporaryAssignment::expiringSoon(7)->count());
        $this->command->info('');
    }
}
