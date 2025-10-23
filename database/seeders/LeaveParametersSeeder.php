<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveParametersSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $year = $now->year;

        $parameters = [
            // Temel İzin Süreleri
            [
                'parameter_group' => 'basic',
                'parameter_key' => 'annual_leave_1_5_years',
                'parameter_name' => '1-5 Yıl Arası Yıllık İzin',
                'parameter_value' => '14',
                'data_type' => 'integer',
                'unit' => 'gün',
                'description' => 'Hizmet süresi 1-5 yıl arası personel için yıllık izin gün sayısı (İş Kanunu md. 53)',
                'help_text' => 'Türk İş Kanunu 53. maddesine göre belirlenen asgari izin süresi',
                'validation_rules' => json_encode(['min' => 14, 'max' => 30]),
                'is_active' => true,
                'is_system_parameter' => true,
                'effective_from' => "{$year}-01-01",
            ],
            [
                'parameter_group' => 'basic',
                'parameter_key' => 'annual_leave_5_15_years',
                'parameter_name' => '5-15 Yıl Arası Yıllık İzin',
                'parameter_value' => '20',
                'data_type' => 'integer',
                'unit' => 'gün',
                'description' => 'Hizmet süresi 5-15 yıl arası personel için yıllık izin gün sayısı',
                'help_text' => 'Beş yıldan fazla on beş yıldan az hizmet süresi için geçerli',
                'validation_rules' => json_encode(['min' => 20, 'max' => 35]),
                'is_active' => true,
                'is_system_parameter' => true,
                'effective_from' => "{$year}-01-01",
            ],
            [
                'parameter_group' => 'basic',
                'parameter_key' => 'annual_leave_15_plus_years',
                'parameter_name' => '15+ Yıl Yıllık İzin',
                'parameter_value' => '26',
                'data_type' => 'integer',
                'unit' => 'gün',
                'description' => 'Hizmet süresi 15 yıl ve üzeri personel için yıllık izin gün sayısı',
                'help_text' => 'On beş yıl ve daha fazla hizmet süresi için geçerli',
                'validation_rules' => json_encode(['min' => 26, 'max' => 40]),
                'is_active' => true,
                'is_system_parameter' => true,
                'effective_from' => "{$year}-01-01",
            ],

            // Özel Yaş Grupları
            [
                'parameter_group' => 'special',
                'parameter_key' => 'underage_min_leave',
                'parameter_name' => '18 Yaş Altı Minimum İzin',
                'parameter_value' => '20',
                'data_type' => 'integer',
                'unit' => 'gün',
                'description' => '18 yaşında veya daha küçük işçiler için minimum yıllık izin süresi',
                'help_text' => 'Kıdem ne olursa olsun minimum 20 gün izin hakkı',
                'validation_rules' => json_encode(['min' => 20]),
                'is_active' => true,
                'is_system_parameter' => true,
                'effective_from' => "{$year}-01-01",
            ],
            [
                'parameter_group' => 'special',
                'parameter_key' => 'over_50_min_leave',
                'parameter_name' => '50+ Yaş Minimum İzin',
                'parameter_value' => '20',
                'data_type' => 'integer',
                'unit' => 'gün',
                'description' => '50 yaşında veya daha büyük işçiler için minimum yıllık izin süresi',
                'help_text' => 'Kıdem ne olursa olsun minimum 20 gün izin hakkı',
                'validation_rules' => json_encode(['min' => 20]),
                'is_active' => true,
                'is_system_parameter' => true,
                'effective_from' => "{$year}-01-01",
            ],

            // İş Kuralları
            [
                'parameter_group' => 'business',
                'parameter_key' => 'min_leave_split_days',
                'parameter_name' => 'Minimum İzin Bölümü',
                'parameter_value' => '10',
                'data_type' => 'integer',
                'unit' => 'gün',
                'description' => 'İzin bölündüğünde bir bölümün minimum gün sayısı',
                'help_text' => 'Yasal zorunluluk: Bir bölüm 10 günden az olamaz',
                'validation_rules' => json_encode(['min' => 10]),
                'is_active' => true,
                'is_system_parameter' => true,
                'effective_from' => "{$year}-01-01",
            ],
            [
                'parameter_group' => 'business',
                'parameter_key' => 'min_advance_notice_days',
                'parameter_name' => 'Minimum Ön Bildirim',
                'parameter_value' => '30',
                'data_type' => 'integer',
                'unit' => 'gün',
                'description' => 'İzin talebinin en az kaç gün önceden yapılması gerektiği',
                'help_text' => 'Yasal zorunluluk: En az 1 ay önceden bildirim',
                'validation_rules' => json_encode(['min' => 1, 'max' => 90]),
                'is_active' => true,
                'is_system_parameter' => false,
                'effective_from' => "{$year}-01-01",
            ],

            // Hesaplama Parametreleri
            [
                'parameter_group' => 'calculation',
                'parameter_key' => 'include_weekends_in_leave',
                'parameter_name' => 'Hafta Sonları İzne Dahil',
                'parameter_value' => 'false',
                'data_type' => 'boolean',
                'description' => 'İzin hesaplamasında hafta sonları dahil edilsin mi?',
                'help_text' => 'Yasal kural: Hafta sonları izin süresinden sayılmaz',
                'is_active' => true,
                'is_system_parameter' => true,
                'effective_from' => "{$year}-01-01",
            ],
            [
                'parameter_group' => 'calculation',
                'parameter_key' => 'include_holidays_in_leave',
                'parameter_name' => 'Tatil Günleri İzne Dahil',
                'parameter_value' => 'false',
                'data_type' => 'boolean',
                'description' => 'İzin hesaplamasında genel tatil günleri dahil edilsin mi?',
                'help_text' => 'Yasal kural: Genel tatiller izin süresinden sayılmaz',
                'is_active' => true,
                'is_system_parameter' => true,
                'effective_from' => "{$year}-01-01",
            ],
            [
                'parameter_group' => 'calculation',
                'parameter_key' => 'leave_year_start_month',
                'parameter_name' => 'İzin Yılı Başlangıç Ayı',
                'parameter_value' => '1',
                'data_type' => 'integer',
                'unit' => 'ay',
                'description' => 'İzin yılının başladığı ay (1=Ocak)',
                'help_text' => 'Şirket politikası: İzin yılı Ocak-Aralık veya işe giriş tarihi bazlı',
                'validation_rules' => json_encode(['min' => 1, 'max' => 12]),
                'is_active' => true,
                'is_system_parameter' => false,
                'effective_from' => "{$year}-01-01",
            ],
            [
                'parameter_group' => 'calculation',
                'parameter_key' => 'max_carry_forward_days',
                'parameter_name' => 'Maksimum Devir İzni',
                'parameter_value' => '5',
                'data_type' => 'integer',
                'unit' => 'gün',
                'description' => 'Bir sonraki yıla devredilebilecek maksimum izin gün sayısı',
                'help_text' => 'Şirket politikası ile belirlenir',
                'validation_rules' => json_encode(['min' => 0, 'max' => 15]),
                'is_active' => true,
                'is_system_parameter' => false,
                'effective_from' => "{$year}-01-01",
            ],
        ];

        foreach ($parameters as $parameter) {
            DB::table('leave_parameters')->insert(array_merge($parameter, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $this->command->info('✅ ' . count($parameters) . ' izin parametresi oluşturuldu');
    }
}
