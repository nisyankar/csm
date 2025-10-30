<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use App\Models\StockMovement;
use App\Models\StockCount;
use App\Models\Project;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Seeder;

class WarehouseManagementSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get first project and materials
        $project = Project::first();
        if (!$project) {
            $this->command->warn('No projects found. Please run ProjectSeeder first.');
            return;
        }

        $materials = Material::limit(10)->get();
        if ($materials->isEmpty()) {
            $this->command->warn('No materials found. Please run MaterialSeeder first.');
            return;
        }

        $user = User::first();
        if (!$user) {
            $this->command->warn('No users found.');
            return;
        }

        $this->command->info('Creating warehouses...');

        // Create warehouses
        $warehouses = [
            [
                'project_id' => $project->id,
                'name' => 'Ana Depo',
                'location' => 'Şantiye Girişi - Blok A yanı',
                'responsible_user_id' => $user->id,
                'description' => 'Ana malzeme deposu. İnşaat malzemeleri ve ekipmanlar.',
                'is_active' => true,
            ],
            [
                'project_id' => $project->id,
                'name' => 'Elektrik Deposu',
                'location' => 'Teknik Bina - 1. Kat',
                'responsible_user_id' => $user->id,
                'description' => 'Elektrik malzemeleri ve kablolar.',
                'is_active' => true,
            ],
            [
                'project_id' => $project->id,
                'name' => 'Mekanik Depo',
                'location' => 'Teknik Bina - Zemin Kat',
                'responsible_user_id' => $user->id,
                'description' => 'Tesisat ve mekanik malzemeler.',
                'is_active' => true,
            ],
            [
                'project_id' => $project->id,
                'name' => 'Finishing Deposu',
                'location' => 'B Blok Bodrum',
                'responsible_user_id' => $user->id,
                'description' => 'İç finishing malzemeleri.',
                'is_active' => true,
            ],
            [
                'project_id' => $project->id,
                'name' => 'Arşiv Depo',
                'location' => 'Eski Konteyner Alanı',
                'responsible_user_id' => $user->id,
                'description' => 'Kullanılmayan ve arşivlenmiş malzemeler.',
                'is_active' => false,
            ],
        ];

        $createdWarehouses = [];
        foreach ($warehouses as $warehouseData) {
            $createdWarehouses[] = Warehouse::create($warehouseData);
        }

        $this->command->info('Created ' . count($createdWarehouses) . ' warehouses.');

        // Create stock movements (in/out)
        $this->command->info('Creating stock movements...');

        $movementCount = 0;
        foreach ($createdWarehouses as $warehouse) {
            if (!$warehouse->is_active) continue;

            // Create initial stock (IN movements)
            foreach ($materials->random(5) as $material) {
                StockMovement::create([
                    'warehouse_id' => $warehouse->id,
                    'material_id' => $material->id,
                    'movement_type' => 'in',
                    'quantity' => rand(100, 1000),
                    'unit_price' => rand(10, 500),
                    'performed_by' => $user->id,
                    'notes' => 'İlk stok girişi',
                    'movement_date' => now()->subDays(rand(10, 60)),
                ]);
                $movementCount++;
            }

            // Create some OUT movements
            foreach ($materials->random(3) as $material) {
                StockMovement::create([
                    'warehouse_id' => $warehouse->id,
                    'material_id' => $material->id,
                    'movement_type' => 'out',
                    'quantity' => rand(10, 100),
                    'performed_by' => $user->id,
                    'notes' => 'Şantiye kullanımı',
                    'movement_date' => now()->subDays(rand(1, 10)),
                ]);
                $movementCount++;
            }
        }

        $this->command->info('Created ' . $movementCount . ' stock movements.');

        // Create stock transfers
        $this->command->info('Creating stock transfers...');

        if (count($createdWarehouses) >= 2) {
            $activeWarehouses = array_filter($createdWarehouses, fn($w) => $w->is_active);
            $activeWarehouses = array_values($activeWarehouses);

            for ($i = 0; $i < 5; $i++) {
                $fromWarehouse = $activeWarehouses[array_rand($activeWarehouses)];
                $toWarehouse = $activeWarehouses[array_rand($activeWarehouses)];

                // Make sure from and to are different
                while ($fromWarehouse->id === $toWarehouse->id) {
                    $toWarehouse = $activeWarehouses[array_rand($activeWarehouses)];
                }

                $material = $materials->random();

                StockMovement::create([
                    'warehouse_id' => $fromWarehouse->id,
                    'to_warehouse_id' => $toWarehouse->id,
                    'material_id' => $material->id,
                    'movement_type' => 'transfer',
                    'quantity' => rand(5, 50),
                    'performed_by' => $user->id,
                    'notes' => "Transfer: {$fromWarehouse->name} → {$toWarehouse->name}",
                    'movement_date' => now()->subDays(rand(1, 15)),
                ]);
            }

            $this->command->info('Created 5 stock transfers.');
        }

        // Create stock counts
        $this->command->info('Creating stock counts...');

        $countNumber = 1;
        foreach ($createdWarehouses as $warehouse) {
            if (!$warehouse->is_active) continue;

            foreach ($materials->random(3) as $material) {
                // Calculate system stock
                $systemStock = rand(50, 500);

                // Create variation (surplus, shortage, or match)
                $variation = rand(-20, 20);
                $countedStock = max(0, $systemStock + $variation);

                $status = ['pending', 'approved', 'rejected'][rand(0, 2)];

                $count = StockCount::create([
                    'reference_number' => 'SAY-' . str_pad($countNumber++, 3, '0', STR_PAD_LEFT),
                    'warehouse_id' => $warehouse->id,
                    'material_id' => $material->id,
                    'system_quantity' => $systemStock,
                    'counted_quantity' => $countedStock,
                    'difference' => $countedStock - $systemStock,
                    'count_date' => now()->subDays(rand(1, 30)),
                    'counted_by' => $user->id,
                    'notes' => $variation == 0 ? 'Stok uyumlu' : ($variation > 0 ? 'Fazla tespit edildi' : 'Eksik tespit edildi'),
                    'status' => $status,
                ]);

                // If approved or rejected, add approval data
                if ($status !== 'pending') {
                    $count->update([
                        'approved_by' => $user->id,
                        'approved_at' => now()->subDays(rand(0, 5)),
                        'rejection_reason' => $status === 'rejected' ? 'Sayım tekrar edilmeli' : null,
                    ]);
                }
            }
        }

        $this->command->info('Created stock counts.');

        $this->command->info('✓ Warehouse Management seeding completed!');
    }
}
