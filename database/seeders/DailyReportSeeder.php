<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class DailyReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Projeleri ve kullanıcıları al
        $projects = Project::all();
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['project_manager', 'site_manager', 'foreman']);
        })->get();

        if ($projects->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Önce projeler ve kullanıcılar oluşturulmalı!');
            return;
        }

        // Hava durumu seçenekleri
        $weatherConditions = ['sunny', 'cloudy', 'rainy', 'snowy', 'windy', 'stormy'];
        $weatherWeights = [40, 30, 15, 5, 7, 3]; // Güneşli daha sık

        // İş açıklamaları
        $completedWorks = [
            'Temel kazı işleri tamamlandı',
            'Demir bağlama işleri bitti',
            'Beton dökümü gerçekleştirildi',
            'Kalıp sökümü yapıldı',
            'Duvar örme işleri tamamlandı',
            'Sıva işleri bitirildi',
            'Elektrik tesisatı döşendi',
            'Su tesisatı tamamlandı',
            'Zemin döşeme işleri bitti',
            'Çatı kaplama işleri tamamlandı',
        ];

        $ongoingWorks = [
            'Temel betonarme işlerine devam ediliyor',
            'Kolon kalıpları hazırlanıyor',
            'Duvar imalatlarına devam ediliyor',
            'İnce işler yapılıyor',
            'Dış cephe işleri sürüyor',
            'Pencere montajları devam ediyor',
            'Boya işlerine başlandı',
            'Yer karosu işleri sürüyor',
        ];

        $plannedWorks = [
            'Yarın beton dökümü planlanıyor',
            'Demir sevkiyatı gelecek',
            'Kalıp montajına başlanacak',
            'Sıva işlerine başlanacak',
            'Elektrik pano montajı yapılacak',
            'Dış cephe boyasına başlanacak',
            'Bahçe düzenleme işleri başlayacak',
            'Son kontroller yapılacak',
        ];

        $delayReasons = [
            'Malzeme gecikmesi yaşandı',
            'Hava şartları nedeniyle çalışılamadı',
            'Usta eksikliği oldu',
            'Elektrik kesintisi yaşandı',
            'Projelerde değişiklik talep edildi',
            'Belediye denetimi bekleniyor',
        ];

        $accidentDetails = [
            'Hafif el yaralanması - ilk yardım uygulandı',
            'İşçi merdivenden düştü - hastaneye sevk edildi',
            'Gözüne taş kaçtı - kontrol amaçlı hastaneye gitti',
            'Ayağına çivi battı - tetanos aşısı yapıldı',
        ];

        $materialShortages = [
            'Çimento stoğu azaldı',
            'Demir eksikliği var',
            'Briket malzemesi bitti',
            'Boya malzemeleri yetersiz',
            'Elektrik malzemeleri tükendi',
            'Su borusu stoğu yok',
        ];

        $visitors = [
            'Proje müdürü şantiye ziyareti yaptı',
            'Belediye denetim ekibi geldi',
            'Mal sahibi inceleme yaptı',
            'Mimar proje kontrolü gerçekleştirdi',
            'İş güvenliği uzmanı denetim yaptı',
            'İSG kurulu toplantısı yapıldı',
        ];

        $this->command->info('Günlük raporlar oluşturuluyor...');

        // Son 90 gün için raporlar oluştur
        foreach ($projects as $project) {
            $startDate = Carbon::now()->subDays(90);
            $endDate = Carbon::now();

            // Her proje için rastgele günlerde rapor oluştur (ortalama haftada 5 gün)
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Hafta sonu raporları daha az oluştur
                if ($date->isWeekend() && rand(0, 100) > 20) {
                    continue;
                }

                // Hafta içi %80 ihtimalle rapor oluştur
                if (!$date->isWeekend() && rand(0, 100) > 80) {
                    continue;
                }

                // Rastgele kullanıcı seç
                $reporter = $users->random();

                // Hava durumu (ağırlıklı rastgele)
                $weatherIndex = $this->weightedRandom($weatherWeights);
                $weather = $weatherConditions[$weatherIndex];
                $temperature = $this->getTemperatureForWeather($weather, $date);

                // İşçi sayıları
                $totalWorkers = rand(10, 50);
                $internalWorkers = rand(5, min(15, $totalWorkers));
                $subcontractorWorkers = $totalWorkers - $internalWorkers;

                // Sorun durumları
                $hasDelays = rand(0, 100) < 10; // %10 ihtimal
                $hasAccidents = rand(0, 100) < 3; // %3 ihtimal
                $hasMaterialShortage = rand(0, 100) < 15; // %15 ihtimal

                // Onay durumu (geçmiş raporlar çoğunlukla onaylı)
                $daysAgo = Carbon::now()->diffInDays($date);
                if ($daysAgo > 7) {
                    $status = 'approved'; // 1 haftadan eski raporlar onaylı
                } elseif ($daysAgo > 2) {
                    $status = rand(0, 100) < 80 ? 'approved' : 'submitted';
                } elseif ($daysAgo > 0) {
                    $status = rand(0, 100) < 50 ? 'approved' : 'submitted';
                } else {
                    $status = rand(0, 100) < 30 ? 'submitted' : 'draft';
                }

                DailyReport::create([
                    'project_id' => $project->id,
                    'report_date' => $date->format('Y-m-d'),
                    'reported_by' => $reporter->id,
                    'weather_condition' => $weather,
                    'temperature' => $temperature,
                    'weather_notes' => $weather === 'rainy' ? 'Öğleden sonra yağmur başladı' : null,
                    'total_workers' => $totalWorkers,
                    'internal_workers' => $internalWorkers,
                    'subcontractor_workers' => $subcontractorWorkers,
                    'work_summary' => $this->generateWorkSummary($date, $project),
                    'completed_works' => $this->getRandomItems($completedWorks, rand(2, 4)),
                    'ongoing_works' => $this->getRandomItems($ongoingWorks, rand(2, 3)),
                    'planned_works' => $this->getRandomItems($plannedWorks, rand(1, 3)),
                    'has_delays' => $hasDelays,
                    'delay_reasons' => $hasDelays ? $this->getRandomItems($delayReasons, rand(1, 2)) : [],
                    'has_accidents' => $hasAccidents,
                    'accident_details' => $hasAccidents ? $this->getRandomItems($accidentDetails, 1) : [],
                    'has_material_shortage' => $hasMaterialShortage,
                    'material_shortage_details' => $hasMaterialShortage ? $this->getRandomItems($materialShortages, rand(1, 2)) : [],
                    'visitors' => rand(0, 100) < 20 ? $this->getRandomItems($visitors, 1) : [],
                    'equipment_usage' => $this->generateEquipmentUsage(),
                    'photos' => [],
                    'notes' => rand(0, 100) < 30 ? 'Genel olarak işler plan dahilinde ilerliyor.' : null,
                    'approval_status' => $status,
                    'approved_by' => $status === 'approved' ? $users->random()->id : null,
                    'approved_at' => $status === 'approved' ? $date->copy()->addHours(rand(4, 10)) : null,
                ]);
            }
        }

        $totalReports = DailyReport::count();
        $this->command->info("✓ {$totalReports} günlük rapor başarıyla oluşturuldu!");
    }

    /**
     * Ağırlıklı rastgele seçim
     */
    private function weightedRandom(array $weights): int
    {
        $total = array_sum($weights);
        $random = rand(1, $total);

        $sum = 0;
        foreach ($weights as $index => $weight) {
            $sum += $weight;
            if ($random <= $sum) {
                return $index;
            }
        }

        return 0;
    }

    /**
     * Hava durumuna göre sıcaklık üret
     */
    private function getTemperatureForWeather(string $weather, Carbon $date): float
    {
        // Mevsime göre baz sıcaklık
        $month = $date->month;
        if (in_array($month, [12, 1, 2])) {
            $base = rand(0, 15); // Kış
        } elseif (in_array($month, [3, 4, 5])) {
            $base = rand(10, 25); // İlkbahar
        } elseif (in_array($month, [6, 7, 8])) {
            $base = rand(25, 40); // Yaz
        } else {
            $base = rand(15, 28); // Sonbahar
        }

        // Hava durumuna göre ayarlama
        switch ($weather) {
            case 'snowy':
                return max(-5, $base - rand(10, 20));
            case 'rainy':
                return $base - rand(3, 8);
            case 'cloudy':
                return $base - rand(1, 5);
            case 'sunny':
                return $base + rand(0, 5);
            case 'stormy':
                return $base - rand(5, 10);
            default:
                return $base;
        }
    }

    /**
     * İş özeti oluştur
     */
    private function generateWorkSummary(Carbon $date, $project): string
    {
        $summaries = [
            "Bugün {$project->name} projesinde genel olarak planlandığı gibi çalışmalar yürütüldü. İşçi devamsızlığı olmadı ve tüm ekipler koordineli çalıştı.",
            "{$project->name} şantiyesinde bugün yoğun bir tempo ile çalışıldı. Planlanan işlerin büyük kısmı tamamlandı.",
            "Bugün {$project->name} projesinde rutin çalışmalar gerçekleştirildi. Herhangi bir aksaklık yaşanmadı.",
            "{$project->name} şantiyesinde bugünkü çalışmalar başarılı geçti. Tüm ekipler aktif olarak görev yaptı.",
            "Bugün {$project->name} projesinde belirlenen hedeflere ulaşıldı. İşler plan dahilinde ilerliyor.",
        ];

        return $summaries[array_rand($summaries)];
    }

    /**
     * Rastgele öğeler seç
     */
    private function getRandomItems(array $items, int $count): array
    {
        shuffle($items);
        return array_slice($items, 0, min($count, count($items)));
    }

    /**
     * Ekipman kullanımı üret
     */
    private function generateEquipmentUsage(): array
    {
        $equipment = [
            ['name' => 'Vinç', 'hours' => rand(4, 10)],
            ['name' => 'Mikser', 'hours' => rand(3, 8)],
            ['name' => 'Pompa', 'hours' => rand(2, 6)],
            ['name' => 'Forklift', 'hours' => rand(4, 9)],
            ['name' => 'Kompresör', 'hours' => rand(5, 10)],
        ];

        // %60 ihtimalle ekipman kullanımı var
        if (rand(0, 100) < 60) {
            return $this->getRandomItems($equipment, rand(1, 3));
        }

        return [];
    }
}
