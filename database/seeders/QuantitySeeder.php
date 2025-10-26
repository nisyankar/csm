<?php

namespace Database\Seeders;

use App\Models\Quantity;
use App\Models\Project;
use App\Models\WorkItem;
use App\Models\ProjectStructure;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class QuantitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // İlk projeyi ve kullanıcıları al
        $project = Project::first();
        $workItems = WorkItem::limit(10)->get();
        $structures = ProjectStructure::where('project_id', $project->id)->get();
        $users = User::limit(2)->get();

        if (!$project || $workItems->isEmpty()) {
            $this->command->warn('⚠️  Proje veya iş kalemi bulunamadı. ConstructionSeeder ve WorkItemSeeder çalıştırıldığından emin olun.');
            return;
        }

        $this->command->info('📊 Metraj kayıtları oluşturuluyor...');

        $quantities = [];

        // Her yapı için metraj kayıtları oluştur
        foreach ($structures as $index => $structure) {
            $floors = ProjectFloor::where('structure_id', $structure->id)->get();

            // İlk 3 iş kalemi için yapı seviyesinde metraj
            foreach ($workItems->take(3) as $workItemIndex => $workItem) {
                $plannedQty = rand(1000, 5000);
                $completedQty = rand(300, $plannedQty);
                $isVerified = rand(0, 100) > 30; // %70 doğrulanmış
                $isApproved = $isVerified && rand(0, 100) > 50; // Doğrulanmışların %50'si onaylı

                $quantities[] = [
                    'project_id' => $project->id,
                    'work_item_id' => $workItem->id,
                    'project_structure_id' => $structure->id,
                    'project_floor_id' => null,
                    'project_unit_id' => null,
                    'planned_quantity' => $plannedQty,
                    'completed_quantity' => $completedQty,
                    'unit' => $workItem->unit,
                    'measurement_date' => Carbon::now()->subDays(rand(1, 60)),
                    'measurement_method' => $this->getRandomMeasurementMethod(),
                    'verified_by' => $isVerified ? $users->first()->id : null,
                    'verified_at' => $isVerified ? Carbon::now()->subDays(rand(1, 30)) : null,
                    'approved_by' => $isApproved ? $users->last()->id : null,
                    'approved_at' => $isApproved ? Carbon::now()->subDays(rand(1, 20)) : null,
                    'notes' => $workItemIndex % 3 == 0 ? 'Ölçüm tamamlandı, kontrol edildi.' : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Her katta kat seviyesinde metraj
            foreach ($floors as $floor) {
                // Sonraki 3 iş kalemi için kat seviyesinde metraj
                foreach ($workItems->slice(3, 3) as $workItem) {
                    $plannedQty = rand(500, 2000);
                    $completedQty = rand(100, $plannedQty);
                    $isVerified = rand(0, 100) > 40; // %60 doğrulanmış
                    $isApproved = $isVerified && rand(0, 100) > 60; // Doğrulanmışların %40'ı onaylı

                    $quantities[] = [
                        'project_id' => $project->id,
                        'work_item_id' => $workItem->id,
                        'project_structure_id' => $structure->id,
                        'project_floor_id' => $floor->id,
                        'project_unit_id' => null,
                        'planned_quantity' => $plannedQty,
                        'completed_quantity' => $completedQty,
                        'unit' => $workItem->unit,
                        'measurement_date' => Carbon::now()->subDays(rand(1, 45)),
                        'measurement_method' => $this->getRandomMeasurementMethod(),
                        'verified_by' => $isVerified ? $users->first()->id : null,
                        'verified_at' => $isVerified ? Carbon::now()->subDays(rand(1, 25)) : null,
                        'approved_by' => $isApproved ? $users->last()->id : null,
                        'approved_at' => $isApproved ? Carbon::now()->subDays(rand(1, 15)) : null,
                        'notes' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Birkaç birim için birim seviyesinde metraj
                $units = ProjectUnit::where('floor_id', $floor->id)->limit(2)->get();
                foreach ($units as $unit) {
                    // Sonraki 2 iş kalemi için birim seviyesinde metraj
                    foreach ($workItems->slice(6, 2) as $workItem) {
                        $plannedQty = rand(100, 500);
                        $completedQty = rand(50, $plannedQty);
                        $isVerified = rand(0, 100) > 50; // %50 doğrulanmış
                        $isApproved = $isVerified && rand(0, 100) > 70; // Doğrulanmışların %30'u onaylı

                        $quantities[] = [
                            'project_id' => $project->id,
                            'work_item_id' => $workItem->id,
                            'project_structure_id' => $structure->id,
                            'project_floor_id' => $floor->id,
                            'project_unit_id' => $unit->id,
                            'planned_quantity' => $plannedQty,
                            'completed_quantity' => $completedQty,
                            'unit' => $workItem->unit,
                            'measurement_date' => Carbon::now()->subDays(rand(1, 30)),
                            'measurement_method' => $this->getRandomMeasurementMethod(),
                            'verified_by' => $isVerified ? $users->first()->id : null,
                            'verified_at' => $isVerified ? Carbon::now()->subDays(rand(1, 20)) : null,
                            'approved_by' => $isApproved ? $users->last()->id : null,
                            'approved_at' => $isApproved ? Carbon::now()->subDays(rand(1, 10)) : null,
                            'notes' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            // İlk yapı için yeterli veri oluşturduk
            if ($index >= 0) {
                break;
            }
        }

        // Verileri toplu olarak ekle
        foreach (array_chunk($quantities, 50) as $chunk) {
            Quantity::insert($chunk);
        }

        $this->command->info('✅ ' . count($quantities) . ' metraj kaydı oluşturuldu.');
        $this->command->info('   - Yapı seviyesi: ' . ($structures->count() * 3) . ' kayıt');
        $this->command->info('   - Kat seviyesi: Çeşitli kayıtlar');
        $this->command->info('   - Birim seviyesi: Çeşitli kayıtlar');
    }

    /**
     * Rastgele ölçüm yöntemi al
     */
    private function getRandomMeasurementMethod(): string
    {
        $methods = [
            'Manuel Ölçüm',
            'Lazer Metre',
            'Dijital Ölçüm',
            'Drone',
            'Teodoli',
            'GPS Ölçüm',
        ];

        return $methods[array_rand($methods)];
    }
}
