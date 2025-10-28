<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConstructionPermit;
use App\Models\Project;
use Carbon\Carbon;

class ConstructionPermitSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();

        if ($projects->isEmpty()) {
            $this->command->warn('No projects found. Please run ProjectSeeder first.');
            return;
        }

        foreach ($projects as $project) {
            // Her proje için yapı ruhsatı oluştur
            $buildingPermit = ConstructionPermit::create([
                'project_id' => $project->id,
                'permit_type' => 'building',
                'permit_number' => 'YR-' . $project->id . '-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'application_date' => Carbon::parse($project->start_date)->subMonths(6),
                'approval_date' => Carbon::parse($project->start_date)->subMonths(3),
                'expiry_date' => Carbon::parse($project->end_date)->addYears(1),
                'status' => 'approved',
                'issuing_authority' => $this->getRandomAuthority(),
                'zoning_status' => $this->getRandomZoningStatus(),
                'notes' => 'Yapı ruhsatı onaylandı. Tüm gerekli belgeler tamamlandı.',
                'created_by' => 1,
            ]);

            // Projenin %30'u tamamlandıysa iskan başvurusu ekle
            if (rand(0, 100) > 70) {
                ConstructionPermit::create([
                    'project_id' => $project->id,
                    'permit_type' => 'occupancy',
                    'permit_number' => 'İİ-' . $project->id . '-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'application_date' => Carbon::parse($project->start_date)->addMonths(rand(12, 18)),
                    'approval_date' => rand(0, 1) ? Carbon::parse($project->start_date)->addMonths(rand(18, 20)) : null,
                    'expiry_date' => null,
                    'status' => rand(0, 1) ? 'approved' : 'pending',
                    'issuing_authority' => $this->getRandomAuthority(),
                    'zoning_status' => $this->getRandomZoningStatus(),
                    'notes' => 'İskan izni başvurusu yapıldı.',
                    'created_by' => 1,
                ]);
            }

            // Projenin %50'si tamamlandıysa yapı kullanma izni ekle
            if (rand(0, 100) > 50) {
                ConstructionPermit::create([
                    'project_id' => $project->id,
                    'permit_type' => 'usage',
                    'permit_number' => 'YKİ-' . $project->id . '-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'application_date' => Carbon::parse($project->start_date)->addMonths(rand(18, 24)),
                    'approval_date' => rand(0, 1) ? Carbon::parse($project->start_date)->addMonths(rand(24, 28)) : null,
                    'expiry_date' => null,
                    'status' => rand(0, 100) > 30 ? 'approved' : (rand(0, 1) ? 'pending' : 'rejected'),
                    'issuing_authority' => $this->getRandomAuthority(),
                    'zoning_status' => $this->getRandomZoningStatus(),
                    'notes' => 'Yapı kullanma izni için gerekli tüm testler tamamlandı.',
                    'created_by' => 1,
                ]);
            }
        }

        $this->command->info('Construction permits seeded successfully!');
    }

    private function getRandomAuthority(): string
    {
        $authorities = [
            'İstanbul Büyükşehir Belediyesi',
            'Ankara Büyükşehir Belediyesi',
            'İzmir Büyükşehir Belediyesi',
            'Kadıköy Belediyesi',
            'Üsküdar Belediyesi',
            'Çankaya Belediyesi',
            'Keçiören Belediyesi',
            'Karşıyaka Belediyesi',
            'Konak Belediyesi',
        ];

        return $authorities[array_rand($authorities)];
    }

    private function getRandomZoningStatus(): string
    {
        $statuses = [
            'Konut Alanı',
            'Ticari Alan',
            'Karma Kullanım Alanı',
            'Sanayi Alanı',
            'Kentsel Dönüşüm Alanı',
            'TOKİ Alanı',
        ];

        return $statuses[array_rand($statuses)];
    }
}
