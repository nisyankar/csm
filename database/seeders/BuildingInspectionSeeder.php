<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InspectionCompany;
use App\Models\Inspection;
use App\Models\Project;
use Carbon\Carbon;

class BuildingInspectionSeeder extends Seeder
{
    public function run(): void
    {
        // Denetim kuruluşları oluştur
        $companies = $this->createInspectionCompanies();

        // Projeleri al
        $projects = Project::all();

        if ($projects->isEmpty()) {
            $this->command->warn('No projects found. Please run ProjectSeeder first.');
            return;
        }

        // Her proje için denetimler oluştur
        foreach ($projects as $project) {
            $this->createInspectionsForProject($project, $companies);
        }

        $this->command->info('Building inspections seeded successfully!');
    }

    private function createInspectionCompanies(): array
    {
        $companies = [
            [
                'company_name' => 'Yapı Denetim A.Ş.',
                'license_number' => 'YD-2020-001',
                'contact_person' => 'Ahmet Yılmaz',
                'phone' => '0212 555 0101',
                'email' => 'info@yapidenetim.com',
                'address' => 'Levent, İstanbul',
                'is_active' => true,
                'notes' => '15 yıllık deneyim, 500+ proje',
            ],
            [
                'company_name' => 'İnşaat Kontrolör Ltd.',
                'license_number' => 'YD-2019-042',
                'contact_person' => 'Mehmet Demir',
                'phone' => '0312 555 0202',
                'email' => 'iletisim@insaatkontrolör.com',
                'address' => 'Çankaya, Ankara',
                'is_active' => true,
                'notes' => 'ISO 9001 belgeli, profesyonel kadro',
            ],
            [
                'company_name' => 'Teknik Denetim Hizmetleri',
                'license_number' => 'YD-2021-015',
                'contact_person' => 'Ayşe Kaya',
                'phone' => '0232 555 0303',
                'email' => 'destek@teknikdenetim.com',
                'address' => 'Karşıyaka, İzmir',
                'is_active' => true,
                'notes' => 'Ege bölgesinde lider firma',
            ],
            [
                'company_name' => 'Güvenli Yapı Denetim',
                'license_number' => 'YD-2018-087',
                'contact_person' => 'Ali Özkan',
                'phone' => '0216 555 0404',
                'email' => 'info@guvenliyapi.com',
                'address' => 'Kadıköy, İstanbul',
                'is_active' => true,
                'notes' => 'Deprem güvenliği konusunda uzman',
            ],
            [
                'company_name' => 'Kalite Kontrol Denetim A.Ş.',
                'license_number' => 'YD-2022-003',
                'contact_person' => 'Fatma Şahin',
                'phone' => '0242 555 0505',
                'email' => 'bilgi@kalitekontrol.com',
                'address' => 'Muratpaşa, Antalya',
                'is_active' => false,
                'notes' => 'Geçici olarak faaliyet dışı',
            ],
        ];

        $createdCompanies = [];
        foreach ($companies as $company) {
            $createdCompanies[] = InspectionCompany::create($company);
        }

        return $createdCompanies;
    }

    private function createInspectionsForProject(Project $project, array $companies): void
    {
        $activeCompanies = array_filter($companies, fn($c) => $c->is_active);
        if (empty($activeCompanies)) {
            return;
        }

        $company = $activeCompanies[array_rand($activeCompanies)];
        $startDate = Carbon::parse($project->start_date);
        $currentDate = Carbon::now();

        // Periyodik denetimler (3 ayda bir)
        $inspectionDate = $startDate->copy()->addMonths(3);
        $inspectionCount = 1;

        while ($inspectionDate->lessThan($currentDate)) {
            $status = $this->determineStatus($inspectionDate);
            $inspectionType = $inspectionCount % 4 == 0 ? 'special' : 'periodic';

            $nonConformities = $this->generateNonConformities($status);
            $correctiveActions = $this->generateCorrectiveActions($nonConformities);

            Inspection::create([
                'project_id' => $project->id,
                'inspection_company_id' => $company->id,
                'inspection_number' => 'DEN-' . $project->id . '-' . date('Y') . '-' . str_pad($inspectionCount, 3, '0', STR_PAD_LEFT),
                'inspector_name' => $this->getRandomInspector(),
                'inspection_date' => $inspectionDate->format('Y-m-d'),
                'inspection_type' => $inspectionType,
                'status' => $status,
                'findings' => $this->generateFindings($inspectionType, $status),
                'non_conformities' => json_encode($nonConformities),
                'corrective_actions' => json_encode($correctiveActions),
                'next_inspection_date' => $status === 'closed' ? $inspectionDate->copy()->addMonths(3)->format('Y-m-d') : null,
                'notes' => $this->generateNotes($status, $inspectionType),
            ]);

            $inspectionDate->addMonths(3);
            $inspectionCount++;
        }

        // Sonraki denetim planlanmış
        if ($inspectionDate->greaterThan($currentDate)) {
            Inspection::create([
                'project_id' => $project->id,
                'inspection_company_id' => $company->id,
                'inspection_number' => 'DEN-' . $project->id . '-' . date('Y') . '-' . str_pad($inspectionCount, 3, '0', STR_PAD_LEFT),
                'inspector_name' => $this->getRandomInspector(),
                'inspection_date' => $inspectionDate->format('Y-m-d'),
                'inspection_type' => 'periodic',
                'status' => 'scheduled',
                'findings' => null,
                'next_inspection_date' => $inspectionDate->copy()->addMonths(3)->format('Y-m-d'),
                'notes' => 'Denetim planlandı.',
            ]);
        }
    }

    private function determineStatus(Carbon $date): string
    {
        $daysSince = $date->diffInDays(Carbon::now());

        if ($daysSince < 7) {
            return 'completed';
        } elseif ($daysSince < 14) {
            return rand(0, 1) ? 'completed' : 'pending_action';
        } elseif ($daysSince < 30) {
            $rand = rand(1, 3);
            return $rand === 1 ? 'pending_action' : 'closed';
        } else {
            return 'closed';
        }
    }

    private function generateFindings(string $type, string $status): string
    {
        $findings = [
            'periodic' => [
                'İnşaat alanında genel düzen ve temizlik iyi durumda.',
                'Kalıp ve iskele sistemleri kontrol edildi, uygun bulundu.',
                'Beton dökümü ve kürü usulüne uygun yapılıyor.',
                'İş güvenliği ekipmanları yeterli düzeyde.',
            ],
            'special' => [
                'Deprem yönetmeliğine uygunluk kontrolü yapıldı.',
                'Malzeme testleri ve numuneler alındı.',
                'Statik hesap ve uygulama uyumu incelendi.',
                'Özel detayların uygulanması kontrol edildi.',
            ],
            'final' => [
                'Yapı tamamlanma oranı %100.',
                'Tüm testler ve deneyler tamamlandı.',
                'Son kontroller yapıldı, uygun bulundu.',
                'İskan için gerekli şartlar sağlandı.',
            ],
        ];

        return $findings[$type][array_rand($findings[$type])];
    }

    private function generateNonConformities(string $status): array
    {
        if ($status === 'closed' || rand(0, 100) > 60) {
            return [];
        }

        $nonConformities = [
            [
                'description' => 'İş güvenliği bariyerlerinde eksiklikler tespit edildi.',
                'severity' => 'minor',
                'deadline' => Carbon::now()->addDays(7)->format('Y-m-d'),
            ],
            [
                'description' => 'Beton numune sonuçları beklemede.',
                'severity' => 'major',
                'deadline' => Carbon::now()->addDays(14)->format('Y-m-d'),
            ],
            [
                'description' => 'Proje revizyonu onayı alınmamış.',
                'severity' => 'critical',
                'deadline' => Carbon::now()->addDays(3)->format('Y-m-d'),
            ],
        ];

        $count = rand(0, 2);
        return array_slice($nonConformities, 0, $count + 1);
    }

    private function generateCorrectiveActions(array $nonConformities): array
    {
        if (empty($nonConformities)) {
            return [];
        }

        $actions = [];
        foreach ($nonConformities as $nc) {
            $actions[] = [
                'action' => 'Tespit edilen uygunsuzluğun giderilmesi',
                'responsible' => $this->getRandomResponsible(),
                'deadline' => $nc['deadline'],
                'status' => rand(0, 100) > 50 ? 'completed' : 'in_progress',
                'completion_date' => rand(0, 100) > 50 ? Carbon::now()->format('Y-m-d') : null,
            ];
        }

        return $actions;
    }

    private function generateNotes(string $status, string $type): string
    {
        $notes = [
            'scheduled' => 'Denetim planlandı, ekip bilgilendirildi.',
            'completed' => 'Denetim tamamlandı, rapor hazırlandı.',
            'pending_action' => 'Tespit edilen uygunsuzluklar için düzeltici faaliyet bekleniyor.',
            'closed' => 'Tüm uygunsuzluklar giderildi, denetim kapatıldı.',
        ];

        return $notes[$status] ?? '';
    }

    private function getRandomInspector(): string
    {
        $inspectors = [
            'İnş. Müh. Ahmet Yıldırım',
            'İnş. Müh. Mehmet Aydın',
            'Mimar Ayşe Demir',
            'İnş. Müh. Fatma Kaya',
            'İnş. Müh. Ali Özdemir',
            'Mimar Zeynep Şahin',
        ];

        return $inspectors[array_rand($inspectors)];
    }

    private function getRandomResponsible(): string
    {
        $responsibles = [
            'Şantiye Şefi - Mustafa Yılmaz',
            'Teknik Ofis - Ahmet Kara',
            'Kalite Kontrol - Mehmet Aksoy',
            'Proje Müdürü - Ali Çelik',
        ];

        return $responsibles[array_rand($responsibles)];
    }
}
