<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveParametersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $currentYear = $now->year;
        
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
                'is_system_parameter' => true,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 14, 'max' => 30])
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
                'is_system_parameter' => true,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 20, 'max' => 35])
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
                'is_system_parameter' => true,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 26, 'max' => 40])
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
                'is_system_parameter' => true,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 20])
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
                'is_system_parameter' => true,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 20])
            ],
            [
                'parameter_group' => 'special',
                'parameter_key' => 'underground_additional_leave',
                'parameter_name' => 'Yer Altı İşçisi Ek İzin',
                'parameter_value' => '4',
                'data_type' => 'integer',
                'unit' => 'gün',
                'description' => 'Yer altı işlerinde çalışan işçiler için ek yıllık izin',
                'help_text' => 'Normal izin günlerine ek olarak verilir',
                'is_system_parameter' => true,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 4, 'max' => 10])
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
                'is_system_parameter' => true,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 10])
            ],
            [
                'parameter_group' => 'business',
                'parameter_key' => 'max_road_leave_days',
                'parameter_name' => 'Maksimum Yol İzni',
                'parameter_value' => '4',
                'data_type' => 'integer',
                'unit' => 'gün',
                'description' => 'Verilecek maksimum ücretsiz yol izni gün sayısı',
                'help_text' => 'Yollarda geçecek süre için ücretsiz izin',
                'is_system_parameter' => true,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 0, 'max' => 4])
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
                'is_system_parameter' => false,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 1, 'max' => 90])
            ],
            [
                'parameter_group' => 'business',
                'parameter_key' => 'collective_leave_start_month',
                'parameter_name' => 'Toplu İzin Başlangıç Ayı',
                'parameter_value' => '4',
                'data_type' => 'integer',
                'unit' => 'ay',
                'description' => 'Toplu izin uygulamasının başlayabileceği en erken ay (Nisan)',
                'help_text' => 'Yasal kısıt: Nisan ayı başı ile Ekim ayı sonu arası',
                'is_system_parameter' => true,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 1, 'max' => 12])
            ],
            [
                'parameter_group' => 'business',
                'parameter_key' => 'collective_leave_end_month',
                'parameter_name' => 'Toplu İzin Bitiş Ayı',
                'parameter_value' => '10',
                'data_type' => 'integer',
                'unit' => 'ay',
                'description' => 'Toplu izin uygulamasının bitebileceği en geç ay (Ekim)',
                'help_text' => 'Yasal kısıt: Nisan ayı başı ile Ekim ayı sonu arası',
                'is_system_parameter' => true,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 1, 'max' => 12])
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
                'is_system_parameter' => true,
                'effective_from' => "{$currentYear}-01-01"
            ],
            [
                'parameter_group' => 'calculation',
                'parameter_key' => 'include_holidays_in_leave',
                'parameter_name' => 'Tatil Günleri İzne Dahil',
                'parameter_value' => 'false',
                'data_type' => 'boolean',
                'description' => 'İzin hesaplamasında genel tatil günleri dahil edilsin mi?',
                'help_text' => 'Yasal kural: Genel tatiller izin süresinden sayılmaz',
                'is_system_parameter' => true,
                'effective_from' => "{$currentYear}-01-01"
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
                'is_system_parameter' => false,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 1, 'max' => 12])
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
                'is_system_parameter' => false,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 0, 'max' => 15])
            ],
            [
                'parameter_group' => 'calculation',
                'parameter_key' => 'carry_forward_expiry_months',
                'parameter_name' => 'Devir İzni Süre Dolumu',
                'parameter_value' => '4',
                'data_type' => 'integer',
                'unit' => 'ay',
                'description' => 'Devreden izinlerin kaç ay içinde kullanılması gerektiği',
                'help_text' => 'Devir izinleri için son kullanma süresi',
                'is_system_parameter' => false,
                'effective_from' => "{$currentYear}-01-01",
                'validation_rules' => json_encode(['min' => 3, 'max' => 12])
            ]
        ];
        
        foreach ($parameters as $parameter) {
            DB::table('leave_parameters')->insert(array_merge($parameter, [
                'created_at' => $now,
                'updated_at' => $now
            ]));
        }
    }
}