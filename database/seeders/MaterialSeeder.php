<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Creating materials...');

        // Eğer malzeme varsa atla (foreign key constraint nedeniyle truncate edilemez)
        if (Material::count() > 0) {
            $this->command->info('⚠️  Materials already exist, skipping...');
            return;
        }

        $materials = [
            // İnşaat Malzemeleri - Yapısal
            [
                'material_code' => 'MAT-001',
                'name' => 'Çimento - CEM I 42,5 R',
                'description' => 'Portland çimentosu, hızlı priz',
                'category' => 'Çimento',
                'unit' => 'ton',
                'estimated_unit_price' => 2500.00,
                'specification' => 'TS EN 197-1 standardına uygun',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-002',
                'name' => 'Beton - C25/30',
                'description' => 'Hazır beton, C25/30 dayanım sınıfı',
                'category' => 'Beton',
                'unit' => 'm³',
                'estimated_unit_price' => 850.00,
                'specification' => 'TS EN 206 standardına uygun, Slump S3',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-003',
                'name' => 'Beton - C30/37',
                'description' => 'Yüksek dayanımlı hazır beton',
                'category' => 'Beton',
                'unit' => 'm³',
                'estimated_unit_price' => 950.00,
                'specification' => 'TS EN 206 standardına uygun, Slump S3',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-004',
                'name' => 'Demir - Nervürlü Ø8',
                'description' => 'Nervürlü inşaat demiri 8mm',
                'category' => 'Demir',
                'unit' => 'ton',
                'estimated_unit_price' => 18500.00,
                'specification' => 'TS 708 standardına uygun, S420',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-005',
                'name' => 'Demir - Nervürlü Ø12',
                'description' => 'Nervürlü inşaat demiri 12mm',
                'category' => 'Demir',
                'unit' => 'ton',
                'estimated_unit_price' => 18200.00,
                'specification' => 'TS 708 standardına uygun, S420',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-006',
                'name' => 'Demir - Nervürlü Ø16',
                'description' => 'Nervürlü inşaat demiri 16mm',
                'category' => 'Demir',
                'unit' => 'ton',
                'estimated_unit_price' => 18000.00,
                'specification' => 'TS 708 standardına uygun, S420',
                'is_active' => true,
            ],

            // Tuğla ve Blok
            [
                'material_code' => 'MAT-007',
                'name' => 'Tuğla - Delikli 13,5x19x29',
                'description' => 'Delikli tuğla, yatay delikli',
                'category' => 'Tuğla',
                'unit' => 'adet',
                'estimated_unit_price' => 4.50,
                'specification' => 'TS EN 771-1 standardına uygun',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-008',
                'name' => 'Gaz Beton - 60x20x7,5',
                'description' => 'Hafif gaz beton blok',
                'category' => 'Blok',
                'unit' => 'adet',
                'estimated_unit_price' => 12.00,
                'specification' => 'TS EN 771-4 standardına uygun, yoğunluk 600 kg/m³',
                'is_active' => true,
            ],

            // Kum ve Agrega
            [
                'material_code' => 'MAT-009',
                'name' => 'Kum - İnce Agrega (0-4mm)',
                'description' => 'Beton ve harç için ince agrega',
                'category' => 'Agrega',
                'unit' => 'ton',
                'estimated_unit_price' => 180.00,
                'specification' => 'TS 706 EN 12620 standardına uygun',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-010',
                'name' => 'Çakıl - Kaba Agrega (16-31,5mm)',
                'description' => 'Beton için kaba agrega',
                'category' => 'Agrega',
                'unit' => 'ton',
                'estimated_unit_price' => 150.00,
                'specification' => 'TS 706 EN 12620 standardına uygun',
                'is_active' => true,
            ],

            // Su Yalıtım
            [
                'material_code' => 'MAT-011',
                'name' => 'Bitümlü Örtü - Elastomerik',
                'description' => 'SBS modifiyeli bitümlü örtü 4mm',
                'category' => 'Su Yalıtım',
                'unit' => 'm²',
                'estimated_unit_price' => 45.00,
                'specification' => 'TS 11758 standardına uygun',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-012',
                'name' => 'Su Yalıtım Boyası - Çimento Esaslı',
                'description' => 'İki komponentli çimento bazlı su yalıtım',
                'category' => 'Su Yalıtım',
                'unit' => 'kg',
                'estimated_unit_price' => 28.00,
                'specification' => 'Negatif ve pozitif su basıncına dayanıklı',
                'is_active' => true,
            ],

            // Isı Yalıtım
            [
                'material_code' => 'MAT-013',
                'name' => 'XPS Mantolama Levhası - 5cm',
                'description' => 'Ekstrüde polistiren yalıtım levhası',
                'category' => 'Isı Yalıtım',
                'unit' => 'm²',
                'estimated_unit_price' => 85.00,
                'specification' => 'TS EN 13164, λ=0,032-0,037 W/mK',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-014',
                'name' => 'Taşyünü - 5cm',
                'description' => 'Taşyünü yalıtım levhası',
                'category' => 'Isı Yalıtım',
                'unit' => 'm²',
                'estimated_unit_price' => 42.00,
                'specification' => 'TS EN 13162, λ=0,034-0,040 W/mK',
                'is_active' => true,
            ],

            // Boya ve Kaplama
            [
                'material_code' => 'MAT-015',
                'name' => 'Plastik Boya - İç Mekan',
                'description' => 'Su bazlı plastik iç cephe boyası',
                'category' => 'Boya',
                'unit' => 'lt',
                'estimated_unit_price' => 45.00,
                'specification' => 'Beyaz, mat, silinebilir',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-016',
                'name' => 'Fayans - 30x60 1.Kalite',
                'description' => 'Seramik duvar kaplaması',
                'category' => 'Fayans',
                'unit' => 'm²',
                'estimated_unit_price' => 85.00,
                'specification' => 'TS EN 14411, parlak yüzey',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-017',
                'name' => 'Seramik - 60x60 Porselen',
                'description' => 'Porselen yer kaplaması',
                'category' => 'Seramik',
                'unit' => 'm²',
                'estimated_unit_price' => 120.00,
                'specification' => 'TS EN 14411, mat yüzey, kaymaz',
                'is_active' => true,
            ],

            // Ahşap
            [
                'material_code' => 'MAT-018',
                'name' => 'Kalıp Tahtası - 2,5x20x400cm',
                'description' => 'Beton kalıbı için çam tahta',
                'category' => 'Ahşap',
                'unit' => 'adet',
                'estimated_unit_price' => 180.00,
                'specification' => '1. sınıf çam, kuru',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-019',
                'name' => 'Kontrplak - 18mm',
                'description' => 'Beton kalıp kontrplağı',
                'category' => 'Ahşap',
                'unit' => 'm²',
                'estimated_unit_price' => 320.00,
                'specification' => 'Filmli kontrplak, 1. kalite',
                'is_active' => true,
            ],

            // Elektrik Malzemeleri
            [
                'material_code' => 'MAT-020',
                'name' => 'Kablo - NYY 3x2,5mm²',
                'description' => 'Yeraltı tipi enerji kablosu',
                'category' => 'Elektrik',
                'unit' => 'metre',
                'estimated_unit_price' => 25.00,
                'specification' => 'TS 9752 standardına uygun',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-021',
                'name' => 'Kablo - NYM 3x1,5mm²',
                'description' => 'Bina içi enerji kablosu',
                'category' => 'Elektrik',
                'unit' => 'metre',
                'estimated_unit_price' => 12.00,
                'specification' => 'TS IEC 60227-4 standardına uygun',
                'is_active' => true,
            ],

            // Sıhhi Tesisat
            [
                'material_code' => 'MAT-022',
                'name' => 'PPR Boru - Ø20mm',
                'description' => 'Polipropilen sıcak/soğuk su borusu',
                'category' => 'Tesisat',
                'unit' => 'metre',
                'estimated_unit_price' => 18.00,
                'specification' => 'TS EN ISO 15874, PN20',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-023',
                'name' => 'PVC Atık Su Borusu - Ø110mm',
                'description' => 'Kanalizasyon borusu',
                'category' => 'Tesisat',
                'unit' => 'metre',
                'estimated_unit_price' => 35.00,
                'specification' => 'TS EN 1329-1, SN8',
                'is_active' => true,
            ],

            // Diğer Yapı Malzemeleri
            [
                'material_code' => 'MAT-024',
                'name' => 'Alçı - Beyaz',
                'description' => 'İç sıva alçısı',
                'category' => 'Sıva',
                'unit' => 'kg',
                'estimated_unit_price' => 3.50,
                'specification' => 'TS EN 13279-1, B1 sınıfı',
                'is_active' => true,
            ],
            [
                'material_code' => 'MAT-025',
                'name' => 'Cam - Şeffaf 4mm',
                'description' => 'Float cam',
                'category' => 'Cam',
                'unit' => 'm²',
                'estimated_unit_price' => 65.00,
                'specification' => 'TS EN 572-1',
                'is_active' => true,
            ],
        ];

        foreach ($materials as $material) {
            Material::create($material);
        }

        $this->command->info('✅ Created ' . count($materials) . ' materials');
    }
}
