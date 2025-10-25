<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;

class HolidaysSeeder extends Seeder
{
    /**
     * 2025 Türkiye Resmi Tatilleri
     */
    public function run(): void
    {
        $this->command->info('📅 2025 Resmi Tatilleri oluşturuluyor...');

        $holidays = [
            // Yılbaşı
            ['name' => 'Yılbaşı', 'date' => '2025-01-01', 'year' => 2025, 'type' => 'national', 'is_half_day' => false, 'description' => 'Yılbaşı tatili', 'is_paid' => true, 'is_active' => true],

            // Ulusal Egemenlik ve Çocuk Bayramı
            ['name' => 'Ulusal Egemenlik ve Çocuk Bayramı', 'date' => '2025-04-23', 'year' => 2025, 'type' => 'national', 'is_half_day' => false, 'description' => 'TBMM\'nin açılışının yıldönümü', 'is_paid' => true, 'is_active' => true],

            // Ramazan Bayramı Arefesi (Yarım Gün)
            ['name' => 'Ramazan Bayramı Arefesi', 'date' => '2025-03-30', 'year' => 2025, 'type' => 'religious', 'is_half_day' => true, 'half_day_start' => '13:00', 'description' => 'Ramazan Bayramı öncesi yarım gün tatil', 'is_paid' => true, 'is_active' => true],

            // Ramazan Bayramı (3.5 gün)
            ['name' => 'Ramazan Bayramı 1. Gün', 'date' => '2025-03-31', 'year' => 2025, 'type' => 'religious', 'is_half_day' => false, 'description' => 'Ramazan Bayramı resmi tatil', 'is_paid' => true, 'is_active' => true],
            ['name' => 'Ramazan Bayramı 2. Gün', 'date' => '2025-04-01', 'year' => 2025, 'type' => 'religious', 'is_half_day' => false, 'description' => 'Ramazan Bayramı resmi tatil', 'is_paid' => true, 'is_active' => true],
            ['name' => 'Ramazan Bayramı 3. Gün', 'date' => '2025-04-02', 'year' => 2025, 'type' => 'religious', 'is_half_day' => false, 'description' => 'Ramazan Bayramı resmi tatil', 'is_paid' => true, 'is_active' => true],

            // Emek ve Dayanışma Günü
            ['name' => 'Emek ve Dayanışma Günü', 'date' => '2025-05-01', 'year' => 2025, 'type' => 'national', 'is_half_day' => false, 'description' => '1 Mayıs İşçi Bayramı', 'is_paid' => true, 'is_active' => true],

            // Atatürk'ü Anma, Gençlik ve Spor Bayramı
            ['name' => 'Atatürk\'ü Anma, Gençlik ve Spor Bayramı', 'date' => '2025-05-19', 'year' => 2025, 'type' => 'national', 'is_half_day' => false, 'description' => '19 Mayıs Atatürk\'ü Anma, Gençlik ve Spor Bayramı', 'is_paid' => true, 'is_active' => true],

            // Kurban Bayramı Arefesi (Yarım Gün)
            ['name' => 'Kurban Bayramı Arefesi', 'date' => '2025-06-06', 'year' => 2025, 'type' => 'religious', 'is_half_day' => true, 'half_day_start' => '13:00', 'description' => 'Kurban Bayramı öncesi yarım gün tatil', 'is_paid' => true, 'is_active' => true],

            // Kurban Bayramı (4.5 gün)
            ['name' => 'Kurban Bayramı 1. Gün', 'date' => '2025-06-07', 'year' => 2025, 'type' => 'religious', 'is_half_day' => false, 'description' => 'Kurban Bayramı resmi tatil', 'is_paid' => true, 'is_active' => true],
            ['name' => 'Kurban Bayramı 2. Gün', 'date' => '2025-06-08', 'year' => 2025, 'type' => 'religious', 'is_half_day' => false, 'description' => 'Kurban Bayramı resmi tatil', 'is_paid' => true, 'is_active' => true],
            ['name' => 'Kurban Bayramı 3. Gün', 'date' => '2025-06-09', 'year' => 2025, 'type' => 'religious', 'is_half_day' => false, 'description' => 'Kurban Bayramı resmi tatil', 'is_paid' => true, 'is_active' => true],
            ['name' => 'Kurban Bayramı 4. Gün', 'date' => '2025-06-10', 'year' => 2025, 'type' => 'religious', 'is_half_day' => false, 'description' => 'Kurban Bayramı resmi tatil', 'is_paid' => true, 'is_active' => true],

            // Demokrasi ve Milli Birlik Günü
            ['name' => 'Demokrasi ve Milli Birlik Günü', 'date' => '2025-07-15', 'year' => 2025, 'type' => 'national', 'is_half_day' => false, 'description' => '15 Temmuz Demokrasi ve Milli Birlik Günü', 'is_paid' => true, 'is_active' => true],

            // Zafer Bayramı
            ['name' => 'Zafer Bayramı', 'date' => '2025-08-30', 'year' => 2025, 'type' => 'national', 'is_half_day' => false, 'description' => '30 Ağustos Zafer Bayramı', 'is_paid' => true, 'is_active' => true],

            // Cumhuriyet Bayramı Arefesi (Yarım Gün)
            ['name' => 'Cumhuriyet Bayramı Arefesi', 'date' => '2025-10-28', 'year' => 2025, 'type' => 'national', 'is_half_day' => true, 'half_day_start' => '13:00', 'description' => 'Cumhuriyet Bayramı öncesi yarım gün tatil', 'is_paid' => true, 'is_active' => true],

            // Cumhuriyet Bayramı
            ['name' => 'Cumhuriyet Bayramı 1. Gün', 'date' => '2025-10-29', 'year' => 2025, 'type' => 'national', 'is_half_day' => false, 'description' => '29 Ekim Cumhuriyet Bayramı', 'is_paid' => true, 'is_active' => true],
            ['name' => 'Cumhuriyet Bayramı 2. Gün', 'date' => '2025-10-30', 'year' => 2025, 'type' => 'national', 'is_half_day' => false, 'description' => '29 Ekim Cumhuriyet Bayramı', 'is_paid' => true, 'is_active' => true],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }

        $totalHolidays = count($holidays);
        $fullDayHolidays = collect($holidays)->where('is_half_day', false)->count();
        $halfDayHolidays = collect($holidays)->where('is_half_day', true)->count();

        $this->command->info("✅ 2025 Resmi Tatilleri oluşturuldu!");
        $this->command->info("   - Toplam: {$totalHolidays} tatil günü");
        $this->command->info("   - Tam gün: {$fullDayHolidays} gün");
        $this->command->info("   - Yarım gün (Arefe): {$halfDayHolidays} gün");
    }
}
