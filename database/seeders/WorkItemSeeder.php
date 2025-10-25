<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkItem;

class WorkItemSeeder extends Seeder
{
    public function run(): void
    {
        $workItems = [
            ['code' => 'WI-001', 'name' => 'Temel Kazısı', 'unit' => 'm³'],
            ['code' => 'WI-002', 'name' => 'Beton Dökümü', 'unit' => 'm³'],
            ['code' => 'WI-003', 'name' => 'Demir Bağlama', 'unit' => 'kg'],
            ['code' => 'WI-004', 'name' => 'Tuğla Duvar Örme', 'unit' => 'm²'],
            ['code' => 'WI-005', 'name' => 'Kalıp İşleri', 'unit' => 'm²'],
            ['code' => 'WI-006', 'name' => 'Sıva İşleri', 'unit' => 'm²'],
            ['code' => 'WI-007', 'name' => 'Alçı İşleri', 'unit' => 'm²'],
            ['code' => 'WI-008', 'name' => 'Fayans Döşeme', 'unit' => 'm²'],
            ['code' => 'WI-009', 'name' => 'Boya İşleri', 'unit' => 'm²'],
            ['code' => 'WI-010', 'name' => 'Zemin Döşeme', 'unit' => 'm²'],
            ['code' => 'WI-011', 'name' => 'Su Tesisatı', 'unit' => 'm'],
            ['code' => 'WI-012', 'name' => 'Elektrik Tesisatı', 'unit' => 'm'],
            ['code' => 'WI-013', 'name' => 'Doğalgaz Tesisatı', 'unit' => 'm'],
            ['code' => 'WI-014', 'name' => 'Isıtma Tesisatı', 'unit' => 'm'],
            ['code' => 'WI-015', 'name' => 'Mantolama', 'unit' => 'm²'],
            ['code' => 'WI-016', 'name' => 'Cephe Boyası', 'unit' => 'm²'],
            ['code' => 'WI-017', 'name' => 'Dış Cephe Kaplama', 'unit' => 'm²'],
            ['code' => 'WI-018', 'name' => 'Çatı İskeleti', 'unit' => 'm²'],
            ['code' => 'WI-019', 'name' => 'Çatı Örtüsü', 'unit' => 'm²'],
            ['code' => 'WI-020', 'name' => 'Çatı Yalıtımı', 'unit' => 'm²'],
            ['code' => 'WI-021', 'name' => 'Pencere Takımı', 'unit' => 'adet'],
            ['code' => 'WI-022', 'name' => 'Kapı Takımı', 'unit' => 'adet'],
            ['code' => 'WI-023', 'name' => 'Hafriyat', 'unit' => 'm³'],
            ['code' => 'WI-024', 'name' => 'Tesviye', 'unit' => 'm²'],
            ['code' => 'WI-025', 'name' => 'Peyzaj', 'unit' => 'm²'],
        ];

        foreach ($workItems as $item) {
            WorkItem::create([
                'category_id' => 1,
                'code' => $item['code'],
                'name' => $item['name'],
                'unit' => $item['unit'],
                'is_active' => true,
            ]);
        }

        $this->command->info('Created ' . count($workItems) . ' work items.');
    }
}
