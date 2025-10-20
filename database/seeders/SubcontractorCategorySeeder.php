<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubcontractorCategory;

class SubcontractorCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Betonarme İşleri',
                'code' => 'BETONARME',
                'description' => 'Beton dökümü, demir donatı, kalıp işleri',
                'icon' => '🏗️',
                'is_active' => true,
            ],
            [
                'name' => 'Elektrik İşleri',
                'code' => 'ELEKTRIK',
                'description' => 'Elektrik tesisatı, aydınlatma, pano montajı',
                'icon' => '⚡',
                'is_active' => true,
            ],
            [
                'name' => 'Mekanik Tesisat',
                'code' => 'MEKANIK',
                'description' => 'Sıhhi tesisat, ısıtma, soğutma sistemleri',
                'icon' => '🔧',
                'is_active' => true,
            ],
            [
                'name' => 'Alçı ve Sıva İşleri',
                'code' => 'ALCI_SIVA',
                'description' => 'İç ve dış sıva, alçı işleri, mantolama',
                'icon' => '🧱',
                'is_active' => true,
            ],
            [
                'name' => 'Boya İşleri',
                'code' => 'BOYA',
                'description' => 'İç ve dış boya, dekoratif kaplama',
                'icon' => '🎨',
                'is_active' => true,
            ],
            [
                'name' => 'Seramik ve Kaplama',
                'code' => 'SERAMIK',
                'description' => 'Seramik, fayans, granit kaplama işleri',
                'icon' => '🔲',
                'is_active' => true,
            ],
            [
                'name' => 'Doğrama İşleri',
                'code' => 'DOGRAMA',
                'description' => 'Pencere, kapı, alüminyum doğrama',
                'icon' => '🚪',
                'is_active' => true,
            ],
            [
                'name' => 'Demir Doğrama',
                'code' => 'DEMIR',
                'description' => 'Çelik konstrüksiyon, demir işleri',
                'icon' => '⚙️',
                'is_active' => true,
            ],
            [
                'name' => 'Ahşap İşleri',
                'code' => 'AHSAP',
                'description' => 'Marangozluk, mobilya, ahşap kaplama',
                'icon' => '🪵',
                'is_active' => true,
            ],
            [
                'name' => 'Çatı İşleri',
                'code' => 'CATI',
                'description' => 'Çatı örtüsü, çatı izolasyonu, oluk',
                'icon' => '🏠',
                'is_active' => true,
            ],
            [
                'name' => 'İzolasyon İşleri',
                'code' => 'IZOLASYON',
                'description' => 'Su yalıtımı, ısı yalıtımı, ses yalıtımı',
                'icon' => '🛡️',
                'is_active' => true,
            ],
            [
                'name' => 'Hafriyat ve Zemin',
                'code' => 'HAFRIYAT',
                'description' => 'Kazı, dolgu, tesviye, zemin iyileştirme',
                'icon' => '🚜',
                'is_active' => true,
            ],
            [
                'name' => 'Yol ve Altyapı',
                'code' => 'YOL',
                'description' => 'Yol yapımı, kaldırım, parke döşeme',
                'icon' => '🛣️',
                'is_active' => true,
            ],
            [
                'name' => 'Asansör İşleri',
                'code' => 'ASANSOR',
                'description' => 'Asansör montajı, bakım, yürüyen merdiven',
                'icon' => '🛗',
                'is_active' => true,
            ],
            [
                'name' => 'Peyzaj İşleri',
                'code' => 'PEYZAJ',
                'description' => 'Bahçe düzenleme, sulama, bitkilendirme',
                'icon' => '🌳',
                'is_active' => true,
            ],
            [
                'name' => 'Cam İşleri',
                'code' => 'CAM',
                'description' => 'Cam balkon, cephe giydirme, cam montaj',
                'icon' => '🪟',
                'is_active' => true,
            ],
            [
                'name' => 'Yangın Sistemleri',
                'code' => 'YANGIN',
                'description' => 'Yangın algılama, söndürme, sprinkler',
                'icon' => '🧯',
                'is_active' => true,
            ],
            [
                'name' => 'Güvenlik Sistemleri',
                'code' => 'GUVENLIK',
                'description' => 'Kamera, alarm, kartlı geçiş sistemleri',
                'icon' => '📹',
                'is_active' => true,
            ],
            [
                'name' => 'Asma Tavan',
                'code' => 'ASMA_TAVAN',
                'description' => 'Asma tavan, panel tavan, ızgara tavan',
                'icon' => '⬜',
                'is_active' => true,
            ],
            [
                'name' => 'Merdiven ve Korkuluk',
                'code' => 'MERDIVEN',
                'description' => 'Merdiven yapımı, korkuluk, küpeşte',
                'icon' => '🪜',
                'is_active' => true,
            ],
            [
                'name' => 'Mermer ve Granit',
                'code' => 'MERMER',
                'description' => 'Mermer kaplama, granit işleri, doğal taş',
                'icon' => '💎',
                'is_active' => true,
            ],
            [
                'name' => 'Havalandırma',
                'code' => 'HAVALANDIRMA',
                'description' => 'Havalandırma kanalları, klima santralleri',
                'icon' => '💨',
                'is_active' => true,
            ],
            [
                'name' => 'Temizlik İşleri',
                'code' => 'TEMIZLIK',
                'description' => 'İnşaat sonrası temizlik, cephe temizliği',
                'icon' => '🧹',
                'is_active' => true,
            ],
            [
                'name' => 'Nakliye ve Vinç',
                'code' => 'NAKLIYE',
                'description' => 'Vinç kiralama, malzeme taşıma, nakliye',
                'icon' => '🏗️',
                'is_active' => true,
            ],
            [
                'name' => 'Diğer İşler',
                'code' => 'DIGER',
                'description' => 'Yukarıdaki kategorilere girmeyen işler',
                'icon' => '📦',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            SubcontractorCategory::create($category);
        }
    }
}
