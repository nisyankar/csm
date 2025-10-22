<?php

namespace App\Console\Commands;

use App\Services\Leave\LeaveEntitlementService;
use Illuminate\Console\Command;

class CalculateLeaveBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:calculate-balances {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tüm çalışanlar için yıllık izin bakiyelerini hesapla ve oluştur';

    /**
     * Execute the console command.
     */
    public function handle(LeaveEntitlementService $entitlementService)
    {
        $year = $this->argument('year') ?? now()->year;

        $this->info("🚀 {$year} yılı için izin bakiyeleri hesaplanıyor...");

        $results = $entitlementService->calculateForAllEmployees($year);

        $this->newLine();
        $this->info("✅ Başarılı: " . count($results['success']));
        $this->warn("⏭️  Atlandı: " . count($results['skipped']));
        $this->error("❌ Hatalı: " . count($results['errors']));

        if (!empty($results['success'])) {
            $this->newLine();
            $this->table(
                ['Çalışan ID', 'Çalışan Adı', 'Hak Edilen', 'Kalan'],
                array_map(function ($item) {
                    return [
                        $item['employee_id'],
                        $item['employee_name'],
                        $item['entitled_days'] . ' gün',
                        $item['remaining_days'] . ' gün',
                    ];
                }, $results['success'])
            );
        }

        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error('Hatalar:');
            foreach ($results['errors'] as $error) {
                $this->line("  - Çalışan #{$error['employee_id']}: {$error['error']}");
            }
        }

        $this->newLine();
        $this->info('✨ İşlem tamamlandı!');

        return Command::SUCCESS;
    }
}
