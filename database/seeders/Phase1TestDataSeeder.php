<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectStructure;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use App\Models\WorkCategory;
use App\Models\WorkItem;
use App\Models\WorkItemAssignment;
use App\Models\Subcontractor;
use Carbon\Carbon;

class Phase1TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏗️  Creating Phase 1 test data...');

        // 1. Demo Proje Oluştur
        $project = $this->createDemoProject();

        // 2. Bloklar Oluştur
        $structures = $this->createStructures($project);

        // 3. Katlar Oluştur
        $floors = $this->createFloors($structures);

        // 4. Daireler Oluştur
        $units = $this->createUnits($floors);

        // 5. İş Kalemleri Oluştur
        $workItems = $this->createWorkItems();

        // 6. İş Atamaları Oluştur
        $this->createWorkAssignments($project, $structures, $floors, $units, $workItems);

        $this->command->info('✅ Phase 1 test data created successfully!');
        $this->command->newLine();
        $this->command->info("📊 Summary:");
        $this->command->info("   - 1 Demo Project");
        $this->command->info("   - {$structures->count()} Structures (Blocks)");
        $this->command->info("   - {$floors->count()} Floors");
        $this->command->info("   - {$units->count()} Units (Apartments)");
        $this->command->info("   - {$workItems->count()} Work Items");
    }

    private function createDemoProject(): Project
    {
        $this->command->info('Creating demo project...');

        return Project::create([
            'project_code' => 'KON-2025-001',
            'name' => 'Şehir Konutları Projesi',
            'description' => 'Modern yaşam alanları sunan 3 bloktan oluşan konut projesi',
            'location' => 'Çankaya',
            'city' => 'Ankara',
            'district' => 'Çankaya',
            'full_address' => 'Atatürk Bulvarı No: 123, Çankaya/Ankara',
            'start_date' => Carbon::now()->subMonths(3),
            'planned_end_date' => Carbon::now()->addMonths(18),
            'budget' => 50000000,
            'labor_budget' => 15000000,
            'spent_amount' => 8000000,
            'status' => 'active',
            'type' => 'residential',
            'priority' => 'high',
            'client_name' => 'Şehir İnşaat A.Ş.',
            'estimated_employees' => 150,
            'notes' => 'Demo proje - Faz 1 test verisi',
        ]);
    }

    private function createStructures(Project $project)
    {
        $this->command->info('Creating structures (blocks)...');

        $structures = collect();

        // A Blok - 8 Kat
        $structures->push(ProjectStructure::create([
            'project_id' => $project->id,
            'code' => 'A',
            'name' => 'A Blok',
            'structure_type' => 'residential_block',
            'total_floors' => 8,
            'total_units' => 32, // 4 daire x 8 kat
            'progress_percentage' => 45.50,
            'status' => 'in_progress',
        ]));

        // B Blok - 10 Kat
        $structures->push(ProjectStructure::create([
            'project_id' => $project->id,
            'code' => 'B',
            'name' => 'B Blok',
            'structure_type' => 'residential_block',
            'total_floors' => 10,
            'total_units' => 40, // 4 daire x 10 kat
            'progress_percentage' => 25.00,
            'status' => 'in_progress',
        ]));

        // C Blok - 6 Kat
        $structures->push(ProjectStructure::create([
            'project_id' => $project->id,
            'code' => 'C',
            'name' => 'C Blok',
            'structure_type' => 'residential_block',
            'total_floors' => 6,
            'total_units' => 24, // 4 daire x 6 kat
            'progress_percentage' => 10.00,
            'status' => 'in_progress',
        ]));

        return $structures;
    }

    private function createFloors($structures)
    {
        $this->command->info('Creating floors...');

        $allFloors = collect();

        foreach ($structures as $structure) {
            // Zemin kat + Normal katlar
            for ($floorNum = 0; $floorNum < $structure->total_floors; $floorNum++) {
                $floorType = $floorNum === 0 ? 'ground' : 'standard';

                $floor = ProjectFloor::create([
                    'structure_id' => $structure->id,
                    'floor_number' => $floorNum,
                    'floor_type' => $floorType,
                    'status' => 'in_progress',
                ]);

                $allFloors->push($floor);
            }
        }

        return $allFloors;
    }

    private function createUnits($floors)
    {
        $this->command->info('Creating units (apartments)...');

        $allUnits = collect();
        $configurations = ['2+1', '3+1', '4+1', '3+1']; // Her katta 4 daire

        foreach ($floors as $floor) {
            for ($unitNum = 1; $unitNum <= 4; $unitNum++) {
                $unit = ProjectUnit::create([
                    'structure_id' => $floor->structure_id,
                    'floor_id' => $floor->id,
                    'unit_code' => "D{$unitNum}",
                    'unit_type' => 'apartment',
                    'room_configuration' => $configurations[$unitNum - 1],
                    'gross_area' => $unitNum === 3 ? 120 : 100,
                    'net_area' => $unitNum === 3 ? 95 : 85,
                    'balcony_area' => 15,
                    'status' => 'in_progress',
                ]);

                $allUnits->push($unit);
            }
        }

        return $allUnits;
    }

    private function createWorkItems()
    {
        $this->command->info('Creating work items...');

        $categories = WorkCategory::all();
        $workItems = collect();

        foreach ($categories as $category) {
            switch ($category->code) {
                case 'KAB': // Kaba İnşaat
                    $items = [
                        ['code' => 'KAZ', 'name' => 'Kazı İşleri', 'unit' => 'm3', 'default_unit_price' => 50],
                        ['code' => 'TEM', 'name' => 'Temel İşleri', 'unit' => 'm3', 'default_unit_price' => 350],
                        ['code' => 'BET', 'name' => 'Betonarme İşleri', 'unit' => 'm3', 'default_unit_price' => 450],
                        ['code' => 'DUV', 'name' => 'Duvar Örme', 'unit' => 'm2', 'default_unit_price' => 85],
                    ];
                    break;

                case 'INC': // İnce İnşaat
                    $items = [
                        ['code' => 'SIV', 'name' => 'Sıva İşleri', 'unit' => 'm2', 'default_unit_price' => 45],
                        ['code' => 'SER', 'name' => 'Seramik Kaplama', 'unit' => 'm2', 'default_unit_price' => 120],
                        ['code' => 'BOY', 'name' => 'Boya İşleri', 'unit' => 'm2', 'default_unit_price' => 35],
                        ['code' => 'LAM', 'name' => 'Laminat Parke', 'unit' => 'm2', 'default_unit_price' => 150],
                    ];
                    break;

                case 'ELK': // Elektrik
                    $items = [
                        ['code' => 'EKB', 'name' => 'Elektrik Pano', 'unit' => 'adet', 'default_unit_price' => 2500],
                        ['code' => 'AYD', 'name' => 'Aydınlatma', 'unit' => 'adet', 'default_unit_price' => 450],
                        ['code' => 'PRZ', 'name' => 'Priz Montajı', 'unit' => 'adet', 'default_unit_price' => 75],
                    ];
                    break;

                case 'SUT': // Su Tesisatı
                    $items = [
                        ['code' => 'SUT', 'name' => 'Su Tesisatı', 'unit' => 'mt', 'default_unit_price' => 85],
                        ['code' => 'KAN', 'name' => 'Kanalizasyon', 'unit' => 'mt', 'default_unit_price' => 95],
                    ];
                    break;

                default:
                    continue 2;
            }

            foreach ($items as $itemData) {
                $workItems->push(WorkItem::create([
                    'category_id' => $category->id,
                    'code' => $itemData['code'],
                    'name' => $itemData['name'],
                    'unit' => $itemData['unit'],
                    'default_unit_price' => $itemData['default_unit_price'],
                    'is_active' => true,
                ]));
            }
        }

        return $workItems;
    }

    private function createWorkAssignments($project, $structures, $floors, $units, $workItems)
    {
        $this->command->info('Creating work assignments...');

        // Taşeron varsa kullan, yoksa internal team
        $subcontractor = Subcontractor::active()->approved()->first();

        // A Blok - Kaba İnşaat (Tamamlanmış)
        $kabaInsaat = $workItems->where('code', 'BET')->first();
        if ($kabaInsaat) {
            WorkItemAssignment::create([
                'project_id' => $project->id,
                'structure_id' => $structures[0]->id,
                'work_item_id' => $kabaInsaat->id,
                'assignment_type' => $subcontractor ? 'subcontractor' : 'internal_team',
                'subcontractor_id' => $subcontractor?->id,
                'quantity' => 800,
                'unit_price' => 450,
                'total_price' => 360000,
                'completed_quantity' => 800,
                'remaining_quantity' => 0,
                'progress_percentage' => 100,
                'status' => 'completed',
                'planned_start_date' => Carbon::now()->subMonths(2),
                'planned_end_date' => Carbon::now()->subMonth(),
                'actual_start_date' => Carbon::now()->subMonths(2),
                'actual_end_date' => Carbon::now()->subMonth(),
            ]);
        }

        // A Blok - Sıva İşleri (Devam Ediyor)
        $siva = $workItems->where('code', 'SIV')->first();
        if ($siva) {
            WorkItemAssignment::create([
                'project_id' => $project->id,
                'structure_id' => $structures[0]->id,
                'work_item_id' => $siva->id,
                'assignment_type' => 'internal_team',
                'quantity' => 5000,
                'unit_price' => 45,
                'total_price' => 225000,
                'completed_quantity' => 2500,
                'remaining_quantity' => 2500,
                'progress_percentage' => 50,
                'status' => 'in_progress',
                'planned_start_date' => Carbon::now()->subWeeks(3),
                'planned_end_date' => Carbon::now()->addWeeks(4),
                'actual_start_date' => Carbon::now()->subWeeks(3),
            ]);
        }

        // B Blok - Temel İşleri (Başlamamış)
        $temel = $workItems->where('code', 'TEM')->first();
        if ($temel) {
            WorkItemAssignment::create([
                'project_id' => $project->id,
                'structure_id' => $structures[1]->id,
                'work_item_id' => $temel->id,
                'assignment_type' => $subcontractor ? 'subcontractor' : 'internal_team',
                'subcontractor_id' => $subcontractor?->id,
                'quantity' => 600,
                'unit_price' => 350,
                'total_price' => 210000,
                'completed_quantity' => 0,
                'remaining_quantity' => 600,
                'progress_percentage' => 0,
                'status' => 'not_started',
                'planned_start_date' => Carbon::now()->addWeek(),
                'planned_end_date' => Carbon::now()->addWeeks(6),
            ]);
        }

        // A Blok - Kat 3 - Daire 1 - Özel Seramik İşi
        $seramik = $workItems->where('code', 'SER')->first();
        if ($seramik) {
            $floor3 = $floors->where('structure_id', $structures[0]->id)->where('floor_number', 3)->first();
            $unit1 = $units->where('floor_id', $floor3?->id)->where('unit_code', 'D1')->first();

            if ($unit1) {
                WorkItemAssignment::create([
                    'project_id' => $project->id,
                    'structure_id' => $structures[0]->id,
                    'floor_id' => $floor3->id,
                    'unit_id' => $unit1->id,
                    'work_item_id' => $seramik->id,
                    'assignment_type' => 'internal_team',
                    'quantity' => 85,
                    'unit_price' => 120,
                    'total_price' => 10200,
                    'completed_quantity' => 25,
                    'remaining_quantity' => 60,
                    'progress_percentage' => 29.41,
                    'status' => 'in_progress',
                    'planned_start_date' => Carbon::now()->subDays(5),
                    'planned_end_date' => Carbon::now()->addDays(10),
                    'actual_start_date' => Carbon::now()->subDays(5),
                ]);
            }
        }
    }
}
