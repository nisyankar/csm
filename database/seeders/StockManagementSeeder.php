<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Warehouse;
use App\Models\Material;
use App\Models\StockMovement;
use App\Models\User;

class StockManagementSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();
        $materials = Material::all();
        $users = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'project_manager', 'procurement_manager']);
        })->get();

        // Eğer role'lü user yoksa, tüm active user'ları al
        if ($users->isEmpty()) {
            $users = User::where('is_active', true)->limit(10)->get();
        }

        if ($projects->isEmpty() || $materials->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Projects, materials, or users not found. Please run previous seeders first.');
            return;
        }

        $this->command->info('Creating warehouses and stock movements...');

        foreach ($projects as $project) {
            // Her proje için 2-3 depo oluştur
            $warehouseCount = rand(2, 3);
            $warehouseNames = ['Ana Depo', 'Saha Deposu', 'Malzeme Deposu', 'Yedek Depo'];

            for ($i = 0; $i < $warehouseCount; $i++) {
                $warehouse = Warehouse::create([
                    'project_id' => $project->id,
                    'name' => $warehouseNames[$i] . ' - ' . $project->name,
                    'location' => $this->getRandomLocation(),
                    'responsible_user_id' => $users->random()->id,
                    'description' => 'Proje deposu',
                    'is_active' => true,
                ]);

                // Her depo için rastgele malzemelere stok hareketleri ekle
                $selectedMaterials = $materials->random(rand(5, 10));

                foreach ($selectedMaterials as $material) {
                    // İlk giriş hareketi (başlangıç stoğu)
                    $initialQuantity = rand(50, 500);
                    StockMovement::create([
                        'warehouse_id' => $warehouse->id,
                        'material_id' => $material->id,
                        'movement_type' => 'in',
                        'quantity' => $initialQuantity,
                        'unit_price' => $material->estimated_unit_price ?? rand(10, 500),
                        'performed_by' => $users->random()->id,
                        'notes' => 'İlk stok girişi',
                        'movement_date' => now()->subMonths(rand(1, 3)),
                    ]);

                    // Stok çıkışları
                    $outCount = rand(2, 5);
                    $remainingStock = $initialQuantity;

                    for ($j = 0; $j < $outCount; $j++) {
                        if ($remainingStock <= 10) break;

                        $outQuantity = rand(10, min(50, $remainingStock - 10));
                        StockMovement::create([
                            'warehouse_id' => $warehouse->id,
                            'material_id' => $material->id,
                            'movement_type' => 'out',
                            'quantity' => $outQuantity,
                            'unit_price' => $material->estimated_unit_price ?? rand(10, 500),
                            'performed_by' => $users->random()->id,
                            'notes' => 'Proje kullanımı - ' . $project->project_name,
                            'movement_date' => now()->subDays(rand(1, 60)),
                        ]);

                        $remainingStock -= $outQuantity;
                    }

                    // Ek giriş hareketleri (ihtiyaç halinde)
                    if (rand(0, 1)) {
                        $additionalQuantity = rand(20, 100);
                        StockMovement::create([
                            'warehouse_id' => $warehouse->id,
                            'material_id' => $material->id,
                            'movement_type' => 'in',
                            'quantity' => $additionalQuantity,
                            'unit_price' => $material->estimated_unit_price ?? rand(10, 500),
                            'performed_by' => $users->random()->id,
                            'notes' => 'Ek stok girişi',
                            'movement_date' => now()->subDays(rand(1, 30)),
                        ]);

                        $remainingStock += $additionalQuantity;
                    }

                    // Son stok miktarını malzeme tablosunda güncelle
                    $material->increment('current_stock', $remainingStock);
                }

                $this->command->info("Created warehouse: {$warehouse->name} with stock movements");
            }
        }

        // Transfer ve adjustment hareketleri ekle
        $warehouses = Warehouse::all();
        if ($warehouses->count() >= 2) {
            for ($i = 0; $i < 5; $i++) {
                $warehouse = $warehouses->random();
                $material = $materials->random();
                $quantity = rand(5, 20);

                StockMovement::create([
                    'warehouse_id' => $warehouse->id,
                    'material_id' => $material->id,
                    'movement_type' => rand(0, 1) ? 'transfer' : 'adjustment',
                    'quantity' => $quantity,
                    'unit_price' => $material->estimated_unit_price ?? rand(10, 500),
                    'performed_by' => $users->random()->id,
                    'notes' => 'Stok düzeltmesi veya transfer',
                    'movement_date' => now()->subDays(rand(1, 15)),
                ]);
            }
        }

        $this->command->info('Stock management seeding completed successfully!');
    }

    private function getRandomLocation(): string
    {
        $locations = [
            'Şantiye Sahası - A Blok Yanı',
            'Ana Giriş Yanı',
            'B Blok Zemin Kat',
            'Kapalı Depo Alanı',
            'Açık Depo Sahası',
            'Güvenlik Binası Arkası',
        ];

        return $locations[array_rand($locations)];
    }
}
