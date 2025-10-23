<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Subcontractor;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectSubcontractorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Creating project-subcontractor assignments...');

        // Eğer zaten atama varsa atla
        if (DB::table('project_subcontractor')->count() > 0) {
            $this->command->info('⚠️  Project-subcontractor assignments already exist, skipping...');
            return;
        }

        // Projeleri ve taşeronları al
        $projects = Project::all();
        $subcontractors = Subcontractor::where('is_approved', true)->get();

        if ($projects->isEmpty()) {
            $this->command->error('❌ No projects found!');
            return;
        }

        if ($subcontractors->isEmpty()) {
            $this->command->error('❌ No approved subcontractors found!');
            return;
        }

        $assignmentCount = 0;

        foreach ($projects as $project) {
            // Her proje için rastgele 3-6 taşeron ata
            $numberOfSubcontractors = rand(3, 6);
            $selectedSubcontractors = $subcontractors->random(min($numberOfSubcontractors, $subcontractors->count()));

            foreach ($selectedSubcontractors as $index => $subcontractor) {
                // İlk taşeron primary olsun
                $isPrimary = ($index === 0);

                // Rastgele durum (çoğu active olmalı)
                $statusOptions = ['active', 'active', 'active', 'active', 'completed', 'suspended'];
                $status = $statusOptions[array_rand($statusOptions)];

                // Sözleşme miktarı (gerçekçi değerler)
                $contractAmount = rand(50000, 500000);

                // Tarihler
                $startDate = Carbon::now()->subMonths(rand(1, 6));
                $endDate = $status === 'completed'
                    ? Carbon::now()->subMonths(rand(0, 2))
                    : Carbon::now()->addMonths(rand(3, 12));

                DB::table('project_subcontractor')->insert([
                    'project_id' => $project->id,
                    'subcontractor_id' => $subcontractor->id,
                    'assigned_date' => $startDate->format('Y-m-d'),
                    'assigned_by' => 1, // Admin user
                    'work_type' => $subcontractor->category->name ?? 'Genel',
                    'scope_of_work' => $this->getWorkScope($subcontractor->category->name ?? 'Genel'),
                    'contract_amount' => $contractAmount,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $status === 'active' ? null : $endDate->format('Y-m-d'),
                    'status' => $status,
                    'notes' => $isPrimary ? 'Ana taşeron - tüm işlerden sorumlu' : null,
                    'created_at' => $startDate,
                    'updated_at' => now(),
                ]);

                $assignmentCount++;
            }
        }

        $this->command->info("✅ Created {$assignmentCount} project-subcontractor assignments");
    }

    private function getWorkScope(string $categoryName): string
    {
        $scopes = [
            'Elektrik Tesisat' => 'Elektrik pano montajı, kablo çekimi, aydınlatma sistemleri kurulumu, topraklama işleri',
            'Mekanik Tesisat' => 'Sıhhi tesisat, ısıtma-soğutma sistemleri, mekanik vantilasyon, yangın söndürme sistemleri',
            'Demir Doğrama' => 'Çelik konstrüksiyon imalatı, demir doğrama işleri, korkuluk ve merdiven imalatı',
            'Boya Badana' => 'İç ve dış cephe boyası, dekoratif boya uygulamaları, astarlama işleri',
            'Seramik Kaplama' => 'Seramik ve fayans döşeme, granit kaplama, özel kesim işleri',
            'Alüminyum Doğrama' => 'Pencere ve kapı montajı, cam balkon sistemleri, otomatik kapı sistemleri',
            'Betonarme' => 'Beton dökümü, kalıp imalatı ve montajı, demir bağlama işleri',
            'İzolasyon' => 'Su yalıtımı, ısı yalıtımı, ses yalıtımı, dış cephe mantolama',
            'Çatı Örtüsü' => 'Çatı imalatı, çatı kaplaması, oluk ve iniş boruları montajı',
            'Peyzaj' => 'Bahçe düzenlemesi, bitkilendirme, sulama sistemleri, zemin kaplaması',
            'Alçı Sıva' => 'Alçı sıva uygulamaları, asma tavan sistemleri, saten alçı işleri',
            'Havalandırma' => 'Havalandırma kanalları, aspiratör sistemleri, mekanik vantilasyon',
        ];

        return $scopes[$categoryName] ?? 'Genel inşaat işleri ve destek hizmetleri';
    }
}
