<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;

class FinancialCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Gelir Kategorileri
        $incomeCategories = [
            [
                'code' => 'SATIS',
                'name' => 'Satış Gelirleri',
                'description' => 'Daire/Ofis satış gelirleri',
                'children' => [
                    ['code' => 'SATIS-KONUT', 'name' => 'Konut Satışları', 'description' => 'Daire satış gelirleri'],
                    ['code' => 'SATIS-TICARI', 'name' => 'Ticari Satışlar', 'description' => 'Dükkan/Ofis satış gelirleri'],
                ],
            ],
            [
                'code' => 'KIRA',
                'name' => 'Kira Gelirleri',
                'description' => 'Kira gelirleri',
            ],
            [
                'code' => 'DIGER_GELIR',
                'name' => 'Diğer Gelirler',
                'description' => 'Çeşitli gelirler',
            ],
        ];

        foreach ($incomeCategories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $category = IncomeCategory::create($categoryData);

            foreach ($children as $childData) {
                $childData['parent_id'] = $category->id;
                IncomeCategory::create($childData);
            }
        }

        // Gider Kategorileri
        $expenseCategories = [
            [
                'code' => 'PERSONEL',
                'name' => 'Personel Giderleri',
                'description' => 'Maaş ve personel giderleri',
                'children' => [
                    ['code' => 'PERSONEL-MAAS', 'name' => 'Maaşlar', 'description' => 'Aylık maaş ödemeleri'],
                    ['code' => 'PERSONEL-PRIM', 'name' => 'Primler ve İkramiyeler', 'description' => 'Performans primleri'],
                    ['code' => 'PERSONEL-SGK', 'name' => 'SGK ve Diğer Kesintiler', 'description' => 'Yasal kesintiler'],
                ],
            ],
            [
                'code' => 'MALZEME',
                'name' => 'Malzeme Giderleri',
                'description' => 'İnşaat malzeme alımları',
                'children' => [
                    ['code' => 'MALZEME-YAPISAL', 'name' => 'Yapısal Malzemeler', 'description' => 'Çimento, demir, tuğla vb.'],
                    ['code' => 'MALZEME-ELEKTRIK', 'name' => 'Elektrik Malzemeleri', 'description' => 'Kablo, priz, anahtar vb.'],
                    ['code' => 'MALZEME-TESISAT', 'name' => 'Tesisat Malzemeleri', 'description' => 'Boru, vana, musluk vb.'],
                    ['code' => 'MALZEME-BOYA', 'name' => 'Boya ve Kaplama', 'description' => 'Boya, sıva, seramik vb.'],
                ],
            ],
            [
                'code' => 'TASERON',
                'name' => 'Taşeron Ödemeleri',
                'description' => 'Alt yüklenici hakediş ödemeleri',
                'children' => [
                    ['code' => 'TASERON-KABA', 'name' => 'Kaba İnşaat', 'description' => 'Kalıp, demir, beton işleri'],
                    ['code' => 'TASERON-INCE', 'name' => 'İnce İnşaat', 'description' => 'Sıva, boya, döşeme işleri'],
                    ['code' => 'TASERON-TESISAT', 'name' => 'Tesisat İşleri', 'description' => 'Su, kalorifer tesisatı'],
                    ['code' => 'TASERON-ELEKTRIK', 'name' => 'Elektrik İşleri', 'description' => 'Elektrik tesisatı'],
                ],
            ],
            [
                'code' => 'EKIPMAN',
                'name' => 'Ekipman ve Makine Giderleri',
                'description' => 'Makine kiralama ve bakım giderleri',
                'children' => [
                    ['code' => 'EKIPMAN-KIRA', 'name' => 'Ekipman Kiralama', 'description' => 'Vinç, ekskavatör kiralama'],
                    ['code' => 'EKIPMAN-YAKIT', 'name' => 'Yakıt Giderleri', 'description' => 'Makine ve araç yakıtları'],
                    ['code' => 'EKIPMAN-BAKIM', 'name' => 'Bakım ve Onarım', 'description' => 'Makine bakım giderleri'],
                ],
            ],
            [
                'code' => 'GENEL',
                'name' => 'Genel Giderler',
                'description' => 'İdari ve genel giderler',
                'children' => [
                    ['code' => 'GENEL-KIRA', 'name' => 'Kira Giderleri', 'description' => 'Ofis/Şantiye kira giderleri'],
                    ['code' => 'GENEL-ELEKTRIK', 'name' => 'Elektrik-Su-Doğalgaz', 'description' => 'Enerji giderleri'],
                    ['code' => 'GENEL-HABERLESME', 'name' => 'Haberleşme', 'description' => 'Telefon, internet giderleri'],
                    ['code' => 'GENEL-KIRTASIYE', 'name' => 'Kırtasiye', 'description' => 'Ofis malzemeleri'],
                    ['code' => 'GENEL-SIGORTA', 'name' => 'Sigorta Giderleri', 'description' => 'Şantiye sigortaları'],
                ],
            ],
            [
                'code' => 'YASAL',
                'name' => 'Yasal Giderler',
                'description' => 'Ruhsat, izin ve yasal ödemeler',
                'children' => [
                    ['code' => 'YASAL-RUHSAT', 'name' => 'Ruhsat ve İzin Giderleri', 'description' => 'İnşaat ruhsatı vb.'],
                    ['code' => 'YASAL-DENETIM', 'name' => 'Yapı Denetim Giderleri', 'description' => 'Denetim firma ödemeleri'],
                    ['code' => 'YASAL-AVUKAT', 'name' => 'Hukuki Danışmanlık', 'description' => 'Avukatlık giderleri'],
                ],
            ],
            [
                'code' => 'FINANSMAN',
                'name' => 'Finansman Giderleri',
                'description' => 'Kredi faiz ve banka giderleri',
                'children' => [
                    ['code' => 'FINANSMAN-FAIZ', 'name' => 'Faiz Giderleri', 'description' => 'Kredi faiz ödemeleri'],
                    ['code' => 'FINANSMAN-KOMISYON', 'name' => 'Banka Komisyonları', 'description' => 'Banka işlem ücretleri'],
                ],
            ],
            [
                'code' => 'DIGER_GIDER',
                'name' => 'Diğer Giderler',
                'description' => 'Çeşitli giderler',
            ],
        ];

        foreach ($expenseCategories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $category = ExpenseCategory::create($categoryData);

            foreach ($children as $childData) {
                $childData['parent_id'] = $category->id;
                ExpenseCategory::create($childData);
            }
        }

        $this->command->info('✅ Finansal kategoriler başarıyla oluşturuldu!');
        $this->command->info('   - ' . IncomeCategory::count() . ' gelir kategorisi');
        $this->command->info('   - ' . ExpenseCategory::count() . ' gider kategorisi');
    }
}