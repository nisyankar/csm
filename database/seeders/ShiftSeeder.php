<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            // Normal çalışma vardiyaları
            [
                'name' => 'Gündüz Vardiyası',
                'code' => 'GN',
                'shift_type' => 'normal',
                'daily_hours' => 7.5,
                'overtime_multiplier' => 1.0,
                'is_paid' => true,
                'counts_as_work_day' => true,
                'is_active' => true,
                'sort_order' => 1,
                'description' => 'Standart gündüz çalışma vardiyası (08:00-17:00)',
            ],
            [
                'name' => 'Gece Vardiyası',
                'code' => 'GC',
                'shift_type' => 'normal',
                'daily_hours' => 7.5,
                'overtime_multiplier' => 1.0,
                'is_paid' => true,
                'counts_as_work_day' => true,
                'is_active' => true,
                'sort_order' => 2,
                'description' => 'Gece çalışma vardiyası (20:00-05:00)',
            ],

            // Hafta sonu / Bayram
            [
                'name' => 'Hafta Sonu Çalışması',
                'code' => 'HS',
                'shift_type' => 'weekend',
                'daily_hours' => 7.5,
                'overtime_multiplier' => 1.5,
                'is_paid' => true,
                'counts_as_work_day' => true,
                'is_active' => true,
                'sort_order' => 10,
                'description' => 'Cumartesi/Pazar çalışması (1.5x ücret)',
            ],
            [
                'name' => 'Hafta Tatili',
                'code' => 'HT',
                'shift_type' => 'weekend',
                'daily_hours' => 0,
                'overtime_multiplier' => 0,
                'is_paid' => false,
                'counts_as_work_day' => false,
                'is_active' => true,
                'sort_order' => 10.5,
                'description' => 'Hafta tatili - çalışılmadı',
            ],
            [
                'name' => 'Bayram Günü Çalışması',
                'code' => 'BY',
                'shift_type' => 'holiday',
                'daily_hours' => 7.5,
                'overtime_multiplier' => 2.0,
                'is_paid' => true,
                'counts_as_work_day' => true,
                'is_active' => true,
                'sort_order' => 11,
                'description' => 'Resmi bayram günü çalışması (2x ücret)',
            ],
            [
                'name' => 'İstirahat Günü Çalışması',
                'code' => 'IR',
                'shift_type' => 'rest_day',
                'daily_hours' => 7.5,
                'overtime_multiplier' => 1.5,
                'is_paid' => true,
                'counts_as_work_day' => true,
                'is_active' => true,
                'sort_order' => 12,
                'description' => 'Hafta sonu istirahat günü çalışması',
            ],

            // İzinler
            [
                'name' => 'Yıllık İzin',
                'code' => 'YI',
                'shift_type' => 'annual_leave',
                'daily_hours' => 0,
                'overtime_multiplier' => 0,
                'is_paid' => true,
                'counts_as_work_day' => false,
                'is_active' => true,
                'sort_order' => 20,
                'description' => 'Ücretli yıllık izin',
            ],
            [
                'name' => 'Hastalık Raporu',
                'code' => 'RP',
                'shift_type' => 'sick_leave',
                'daily_hours' => 0,
                'overtime_multiplier' => 0,
                'is_paid' => true,
                'counts_as_work_day' => false,
                'is_active' => true,
                'sort_order' => 21,
                'description' => 'Sağlık kurulu raporu (ücretli)',
            ],
            [
                'name' => 'Ücretsiz İzin',
                'code' => 'UI',
                'shift_type' => 'unpaid_leave',
                'daily_hours' => 0,
                'overtime_multiplier' => 0,
                'is_paid' => false,
                'counts_as_work_day' => false,
                'is_active' => true,
                'sort_order' => 22,
                'description' => 'Ücretsiz devamsızlık',
            ],
            [
                'name' => 'Mazeret İzni',
                'code' => 'MI',
                'shift_type' => 'excused_leave',
                'daily_hours' => 0,
                'overtime_multiplier' => 0,
                'is_paid' => true,
                'counts_as_work_day' => false,
                'is_active' => true,
                'sort_order' => 23,
                'description' => 'Ücretli mazeret izni (evlenme, ölüm vb.)',
            ],
            [
                'name' => 'Doğum İzni',
                'code' => 'DI',
                'shift_type' => 'maternity_leave',
                'daily_hours' => 0,
                'overtime_multiplier' => 0,
                'is_paid' => false,
                'counts_as_work_day' => false,
                'is_active' => true,
                'sort_order' => 24,
                'description' => 'Ücretsiz doğum izni',
            ],

            // Özel günler
            [
                'name' => 'Arefe Günü',
                'code' => 'AR',
                'shift_type' => 'half_day',
                'daily_hours' => 4.0,
                'overtime_multiplier' => 1.0,
                'is_paid' => true,
                'counts_as_work_day' => true,
                'is_active' => true,
                'sort_order' => 30,
                'description' => 'Bayram arefe günü (yarım gün - 4 saat)',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                ['code' => $shift['code']],
                $shift
            );
        }

        $this->command->info('Vardiyalar başarıyla oluşturuldu!');
    }
}
