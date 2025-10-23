<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectStructure;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use Carbon\Carbon;

class ProjectStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Creating project structures (blocks, floors, units)...');

        // Eğer zaten yapı varsa atla
        if (ProjectStructure::count() > 0) {
            $this->command->info('⚠️  Project structures already exist, skipping...');
            return;
        }

        $projects = Project::all();

        if ($projects->isEmpty()) {
            $this->command->error('❌ No projects found!');
            return;
        }

        $totalStructures = 0;
        $totalFloors = 0;
        $totalUnits = 0;

        foreach ($projects as $project) {
            // Proje tipine göre blok sayısı belirle
            $numberOfBlocks = $this->getNumberOfBlocks($project);

            for ($blockIndex = 1; $blockIndex <= $numberOfBlocks; $blockIndex++) {
                // Blok (Yapı) oluştur
                $structure = ProjectStructure::create([
                    'project_id' => $project->id,
                    'name' => $this->getBlockName($blockIndex, $numberOfBlocks),
                    'code' => 'BLK-' . str_pad($blockIndex, 2, '0', STR_PAD_LEFT),
                    'structure_type' => $this->getBlockType($project, $blockIndex, $numberOfBlocks),
                    'total_floors' => rand(3, 8),
                    'total_units' => 0, // Hesaplanacak
                    'total_area' => rand(1000, 5000),
                    'status' => $this->getStructureStatus(),
                    'planned_start_date' => Carbon::now()->subMonths(rand(2, 6)),
                    'planned_end_date' => Carbon::now()->addMonths(rand(6, 18)),
                    'actual_start_date' => Carbon::now()->subMonths(rand(1, 3)),
                    'description' => $this->getBlockDescription($blockIndex),
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalStructures++;

                // Katları oluştur
                $floorsData = $this->createFloors($structure);
                $totalFloors += count($floorsData);

                // Daireleri oluştur
                $unitsCount = $this->createUnits($structure, $floorsData);
                $totalUnits += $unitsCount;

                // Toplam daire sayısını güncelle
                $structure->update(['total_units' => $unitsCount]);
            }
        }

        $this->command->info("✅ Created {$totalStructures} blocks, {$totalFloors} floors, {$totalUnits} units");
    }

    private function getNumberOfBlocks(Project $project): int
    {
        // Bütçeye göre blok sayısı belirle
        $budget = $project->budget ?? 0;

        if ($budget > 50000000) {
            return rand(3, 5); // Büyük proje
        } elseif ($budget > 20000000) {
            return rand(2, 3); // Orta proje
        } else {
            return rand(1, 2); // Küçük proje
        }
    }

    private function getBlockName(int $index, int $total): string
    {
        $names = ['A Blok', 'B Blok', 'C Blok', 'D Blok', 'E Blok'];
        return $names[$index - 1] ?? "{$index}. Blok";
    }

    private function getBlockType(Project $project, int $blockIndex, int $totalBlocks): string
    {
        // İlk blok genelde konut
        if ($blockIndex === 1) {
            return 'residential_block';
        }

        // Son blok bazen ticari
        if ($blockIndex === $totalBlocks && $totalBlocks > 2) {
            return rand(0, 1) ? 'commercial' : 'residential_block';
        }

        // Diğerleri rastgele ama çoğunlukla konut
        $weights = ['residential_block', 'residential_block', 'residential_block', 'mixed_use', 'commercial'];
        return $weights[array_rand($weights)];
    }

    private function getStructureStatus(): string
    {
        $statuses = ['not_started', 'in_progress', 'in_progress', 'in_progress', 'completed'];
        return $statuses[array_rand($statuses)];
    }

    private function getBlockDescription(int $blockIndex): string
    {
        $descriptions = [
            1 => 'Ana konut bloğu, deniz manzaralı, güney cepheli',
            2 => 'İkinci konut bloğu, park manzaralı, kuzey cepheli',
            3 => 'Üçüncü blok, karışık kullanım, zemin ticari',
            4 => 'Dördüncü blok, lüks daireler',
            5 => 'Beşinci blok, ekonomik konutlar',
        ];

        return $descriptions[$blockIndex] ?? "Blok {$blockIndex}, standart yapı";
    }

    private function createFloors(ProjectStructure $structure): array
    {
        $floors = [];
        $totalFloors = $structure->total_floors;

        // Bodrum katları (opsiyonel)
        $basementCount = rand(0, 2);
        for ($i = $basementCount; $i >= 1; $i--) {
            $floor = ProjectFloor::create([
                'structure_id' => $structure->id,
                'name' => $basementCount > 1 ? "B{$i}" : 'Bodrum Kat',
                'floor_number' => -$i,
                'floor_type' => 'basement',
                'total_units' => 0, // Bodrum genelde park yeri
                'floor_area' => rand(500, 1200),
                'height' => 2.40,
                'status' => 'completed',
                'notes' => 'Otopark ve teknik alanlar',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $floors[] = $floor;
        }

        // Zemin kat
        $groundFloor = ProjectFloor::create([
            'structure_id' => $structure->id,
            'name' => 'Zemin Kat',
            'floor_number' => 0,
            'floor_type' => 'ground',
            'total_units' => $structure->structure_type === 'commercial' ? rand(4, 8) : rand(2, 4),
            'floor_area' => rand(400, 1000),
            'height' => 3.00,
            'status' => 'in_progress',
            'notes' => $structure->structure_type === 'commercial' ? 'Ticari alanlar' : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $floors[] = $groundFloor;

        // Normal katlar
        for ($i = 1; $i <= $totalFloors; $i++) {
            $floor = ProjectFloor::create([
                'structure_id' => $structure->id,
                'name' => "{$i}. Kat",
                'floor_number' => $i,
                'floor_type' => 'standard',
                'total_units' => rand(2, 6),
                'floor_area' => rand(400, 800),
                'height' => 2.80,
                'status' => $i <= 2 ? 'in_progress' : 'not_started',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $floors[] = $floor;
        }

        return $floors;
    }

    private function createUnits(ProjectStructure $structure, array $floors): int
    {
        $totalUnits = 0;

        foreach ($floors as $floor) {
            // Bodrum katlar için daire oluşturma
            if ($floor->floor_type === 'basement') {
                continue;
            }

            $unitsPerFloor = $floor->total_units;

            for ($unitIndex = 1; $unitIndex <= $unitsPerFloor; $unitIndex++) {
                // Daire numarası: Kat numarası + Daire sırası
                // Örn: 301, 302 (3. kat 1. ve 2. daire)
                $floorNum = $floor->floor_number == 0 ? 0 : $floor->floor_number;
                $unitNumber = ($floorNum * 100) + $unitIndex;

                // Daire tipi
                $unitType = $this->getUnitType($floor->floor_type, $structure->structure_type);

                // Metrekare
                $area = $this->getUnitArea($unitType);

                // Oda sayısı
                $roomsData = $this->getRoomsConfiguration($unitType);

                $isSold = rand(0, 100) < 30;

                ProjectUnit::create([
                    'structure_id' => $structure->id,
                    'floor_id' => $floor->id,
                    'unit_code' => (string) $unitNumber,
                    'unit_type' => $unitType,
                    'room_configuration' => "{$roomsData['rooms']}+{$roomsData['bathrooms']}", // Örn: "3+1"
                    'status' => $this->getUnitStatus($floor->status),
                    'gross_area' => $area['gross'],
                    'net_area' => $area['net'],
                    'balcony_area' => $roomsData['balconies'] > 0 ? rand(5, 15) : null,
                    'direction' => $this->getDirection(),
                    'is_sold' => $isSold,
                    'sale_date' => $isSold ? Carbon::now()->subDays(rand(30, 180)) : null,
                    'owner_name' => $isSold ? $this->getRandomOwnerName() : null,
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalUnits++;
            }
        }

        return $totalUnits;
    }

    private function getUnitType(string $floorType, string $structureType): string
    {
        if ($floorType === 'basement') {
            $types = ['parking_space', 'parking_space', 'storage', 'technical_room'];
            return $types[array_rand($types)];
        }

        if ($structureType === 'commercial') {
            return 'shop';
        }

        if ($structureType === 'office_block') {
            return 'office';
        }

        if ($floorType === 'ground' && rand(0, 100) < 30) {
            return 'shop';
        }

        // Konut tipleri
        return 'apartment';
    }

    private function getUnitArea(string $unitType): array
    {
        $areas = [
            'apartment' => ['gross' => rand(80, 150), 'net' => rand(70, 130)],
            'office' => ['gross' => rand(60, 200), 'net' => rand(50, 180)],
            'shop' => ['gross' => rand(50, 200), 'net' => rand(45, 180)],
            'warehouse' => ['gross' => rand(100, 500), 'net' => rand(90, 450)],
            'parking_space' => ['gross' => rand(12, 20), 'net' => rand(12, 20)],
            'storage' => ['gross' => rand(5, 15), 'net' => rand(5, 15)],
            'technical_room' => ['gross' => rand(20, 50), 'net' => rand(20, 50)],
        ];

        return $areas[$unitType] ?? ['gross' => 100, 'net' => 85];
    }

    private function getRoomsConfiguration(string $unitType): array
    {
        $configs = [
            'apartment' => ['rooms' => rand(2, 4), 'bathrooms' => rand(1, 2), 'balconies' => rand(1, 2)],
            'office' => ['rooms' => rand(1, 5), 'bathrooms' => 1, 'balconies' => 0],
            'shop' => ['rooms' => 1, 'bathrooms' => 1, 'balconies' => 0],
            'warehouse' => ['rooms' => 1, 'bathrooms' => 0, 'balconies' => 0],
            'parking_space' => ['rooms' => 0, 'bathrooms' => 0, 'balconies' => 0],
            'storage' => ['rooms' => 0, 'bathrooms' => 0, 'balconies' => 0],
            'technical_room' => ['rooms' => 0, 'bathrooms' => 0, 'balconies' => 0],
        ];

        return $configs[$unitType] ?? ['rooms' => 2, 'bathrooms' => 1, 'balconies' => 1];
    }

    private function getRandomOwnerName(): string
    {
        $names = [
            'Ahmet Yılmaz', 'Mehmet Demir', 'Ayşe Kaya', 'Fatma Şahin',
            'Ali Çelik', 'Zeynep Yıldız', 'Mustafa Arslan', 'Elif Aydın',
            'Hasan Özkan', 'Hatice Doğan', 'İbrahim Kara', 'Emine Çetin',
            'Hüseyin Şen', 'Merve Acar', 'Osman Koç', 'Aysel Yılmaz',
            'Süleyman Özdemir', 'Sevgi Kurt', 'Ramazan Aslan', 'Fadime Öztürk',
        ];

        return $names[array_rand($names)];
    }

    private function getDirection(): string
    {
        $directions = ['north', 'south', 'east', 'west', 'northeast', 'northwest', 'southeast', 'southwest'];
        return $directions[array_rand($directions)];
    }

    private function getView(): ?string
    {
        $views = [null, null, 'sea_view', 'city_view', 'park_view', 'mountain_view', 'garden_view'];
        return $views[array_rand($views)];
    }

    private function getUnitStatus(string $floorStatus): string
    {
        if ($floorStatus === 'completed') {
            return 'completed';
        }

        if ($floorStatus === 'in_progress') {
            $statuses = ['in_progress', 'in_progress', 'in_progress', 'not_started'];
            return $statuses[array_rand($statuses)];
        }

        return 'not_started';
    }
}
