<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $projectType = $this->faker->randomElement(['residential', 'commercial', 'infrastructure', 'industrial']);
        $projectData = $this->getProjectData($projectType);
        
        $startDate = $this->faker->dateTimeBetween('-1 year', '+3 months');
        $plannedEndDate = $this->faker->dateTimeBetween($startDate, '+2 years');
        $actualEndDate = $this->faker->optional(0.3)->dateTimeBetween($startDate, 'now');
        
        $budget = $this->faker->randomFloat(2, 500000, 50000000); // 500K - 50M TL
        $laborBudget = $budget * $this->faker->randomFloat(2, 0.25, 0.45); // %25-45 işçilik
        
        return [
            'project_code' => $this->generateProjectCode($projectType),
            'name' => $projectData['name'],
            'description' => $projectData['description'],
            
            // Lokasyon Bilgileri
            'location' => $this->generateProjectLocation(),
            'city' => $this->faker->randomElement([
                'İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya', 'Adana', 
                'Konya', 'Gaziantep', 'Kayseri', 'Mersin'
            ]),
            'district' => $this->faker->optional(0.8)->city(),
            'full_address' => $this->faker->address(),
            'coordinates' => $this->generateCoordinates(),
            
            // Tarih Bilgileri
            'start_date' => $startDate,
            'planned_end_date' => $plannedEndDate,
            'actual_end_date' => $actualEndDate,
            
            // Mali Bilgiler
            'budget' => $budget,
            'labor_budget' => $laborBudget,
            'spent_amount' => $this->faker->randomFloat(2, 0, $budget * 0.8),
            
            // Proje Yönetimi (Seeder'da atanacak)
            'project_manager_id' => null,
            'site_manager_id' => null,
            
            // İletişim Bilgileri
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_email' => $this->faker->optional(0.7)->companyEmail(),
            
            // Proje Durumu
            'status' => $this->faker->randomElement(['planning', 'active', 'on_hold', 'completed', 'cancelled']),
            'type' => $projectType,
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            
            // Müşteri Bilgileri
            'client_name' => $this->faker->optional(0.8)->company(),
            'client_contact' => $this->faker->optional(0.8)->name(),
            
            // İlave Bilgiler
            'estimated_employees' => $this->getEstimatedEmployees($projectType),
            'notes' => $this->faker->optional(0.4)->paragraph(),
        ];
    }

    /**
     * Proje kodu oluştur
     */
    private function generateProjectCode(string $type): string
    {
        $prefixes = [
            'residential' => 'KON',    // Konut
            'commercial' => 'TIC',     // Ticari
            'infrastructure' => 'ALT', // Altyapı
            'industrial' => 'END',     // Endüstriyel
            'other' => 'PRJ'           // Proje
        ];
        
        $prefix = $prefixes[$type] ?? 'PRJ';
        $year = date('Y');
        $number = str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $year . '-' . $number;
    }

    /**
     * Proje türüne göre veri döndür
     */
    private function getProjectData(string $type): array
    {
        $data = [
            'residential' => [
                'name' => $this->faker->randomElement([
                    'Lüks Villa Projesi', 'Konut Sitesi İnşaatı', 'Apartman Kompleksi',
                    'Rezidans Projesi', 'Müstakil Ev İnşaatı', 'Tadilat ve Renovasyon'
                ]),
                'description' => 'Konut amaçlı yapı inşaatı projesi. Modern mimari tasarım ve kaliteli malzeme kullanımı.'
            ],
            'commercial' => [
                'name' => $this->faker->randomElement([
                    'AVM İnşaatı', 'Ofis Binası Projesi', 'Ticari Merkez',
                    'Plaza İnşaatı', 'İş Merkezi', 'Mağaza Renovasyonu'
                ]),
                'description' => 'Ticari amaçlı yapı inşaatı. İş merkezi ve ticaret için optimize edilmiş tasarım.'
            ],
            'infrastructure' => [
                'name' => $this->faker->randomElement([
                    'Köprü İnşaatı', 'Yol Projesi', 'Tünel İnşaatı',
                    'Metro Hattı', 'Havaalanı Projesi', 'Liman İnşaatı'
                ]),
                'description' => 'Altyapı projesi. Ulaşım ve kamu hizmetleri için kritik öneme sahip.'
            ],
            'industrial' => [
                'name' => $this->faker->randomElement([
                    'Fabrika İnşaatı', 'Depo Kompleksi', 'Üretim Tesisi',
                    'Lojistik Merkezi', 'Endüstriyel Tesis', 'İmalat Binası'
                ]),
                'description' => 'Endüstriyel amaçlı yapı inşaatı. Üretim ve lojistik faaliyetleri için tasarlanmış.'
            ],
        ];

        return $data[$type] ?? [
            'name' => 'İnşaat Projesi',
            'description' => 'Genel inşaat projesi açıklaması.'
        ];
    }

    /**
     * Proje lokasyonu oluştur
     */
    private function generateProjectLocation(): string
    {
        $locations = [
            'Şantiye Alanı, Merkez Mahallesi',
            'İnşaat Sahası, Yeni Gelişim Bölgesi',
            'Proje Alanı, Sanayi Sitesi',
            'İmar Alanı, Konut Bölgesi',
            'Kentsel Dönüşüm Alanı',
            'Organize Sanayi Bölgesi',
            'Teknoloji Geliştirme Bölgesi',
            'Ticari Alan, İş Merkezi Bölgesi'
        ];

        return $this->faker->randomElement($locations);
    }

    /**
     * GPS koordinatları oluştur (Türkiye sınırları içinde)
     */
    private function generateCoordinates(): string
    {
        // Türkiye koordinat aralıkları
        $lat = $this->faker->randomFloat(6, 35.8, 42.1); // Enlem
        $lng = $this->faker->randomFloat(6, 25.7, 44.8); // Boylam
        
        return $lat . ',' . $lng;
    }

    /**
     * Proje türüne göre tahmini personel sayısı
     */
    private function getEstimatedEmployees(string $type): int
    {
        $estimates = [
            'residential' => [15, 50],      // Konut projeleri
            'commercial' => [25, 80],       // Ticari projeler
            'infrastructure' => [50, 200],  // Altyapı projeleri
            'industrial' => [30, 120],      // Endüstriyel projeler
            'other' => [10, 60],            // Diğer projeler
        ];

        $range = $estimates[$type] ?? [10, 60];
        return $this->faker->numberBetween($range[0], $range[1]);
    }

    /**
     * Aktif proje durumu
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'start_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
                'spent_amount' => $this->faker->randomFloat(2, 
                    $attributes['budget'] * 0.1, 
                    $attributes['budget'] * 0.7
                ),
            ];
        });
    }

    /**
     * Tamamlanmış proje
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = $this->faker->dateTimeBetween('-2 years', '-6 months');
            $endDate = $this->faker->dateTimeBetween($startDate, '-1 month');
            
            return [
                'status' => 'completed',
                'start_date' => $startDate,
                'actual_end_date' => $endDate,
                'spent_amount' => $this->faker->randomFloat(2, 
                    $attributes['budget'] * 0.85, 
                    $attributes['budget'] * 1.15
                ),
            ];
        });
    }

    /**
     * Planlama aşamasındaki proje
     */
    public function planning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'planning',
            'start_date' => $this->faker->dateTimeBetween('+1 week', '+6 months'),
            'spent_amount' => $this->faker->randomFloat(2, 0, $attributes['budget'] * 0.05),
        ]);
    }

    /**
     * Beklemede olan proje
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on_hold',
            'notes' => 'Proje geçici olarak durduruldu - ' . $this->faker->randomElement([
                'İzin işlemleri bekliyor',
                'Finansman sorunu',
                'Malzeme tedarik sorunu',
                'Hava koşulları',
                'Müşteri talebi'
            ]),
        ]);
    }

    /**
     * Konut projesi
     */
    public function residential(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'residential',
                'project_code' => $this->generateProjectCode('residential'),
                'name' => $this->faker->randomElement([
                    'Green Park Konutları', 'Lüks Rezidans', 'Aile Villası',
                    'Modern Apartman', 'Bahçeli Evler', 'Premium Konut'
                ]),
                'estimated_employees' => $this->faker->numberBetween(15, 50),
                'budget' => $this->faker->randomFloat(2, 1000000, 15000000), // 1M-15M TL
            ];
        });
    }

    /**
     * Ticari proje
     */
    public function commercial(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'commercial',
                'project_code' => $this->generateProjectCode('commercial'),
                'name' => $this->faker->randomElement([
                    'Business Center', 'Ticaret Merkezi', 'Plaza Projesi',
                    'AVM İnşaatı', 'Ofis Kompleksi', 'Showroom Binası'
                ]),
                'estimated_employees' => $this->faker->numberBetween(25, 80),
                'budget' => $this->faker->randomFloat(2, 5000000, 50000000), // 5M-50M TL
                'priority' => 'high',
            ];
        });
    }

    /**
     * Altyapı projesi
     */
    public function infrastructure(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'infrastructure',
                'project_code' => $this->generateProjectCode('infrastructure'),
                'name' => $this->faker->randomElement([
                    'Köprü İnşaatı', 'Yol Genişletme', 'Metro Hattı',
                    'Tünel Projesi', 'Alt Geçit', 'Üst Geçit'
                ]),
                'estimated_employees' => $this->faker->numberBetween(50, 200),
                'budget' => $this->faker->randomFloat(2, 10000000, 100000000), // 10M-100M TL
                'priority' => 'critical',
                'client_name' => $this->faker->randomElement([
                    'Karayolları Genel Müdürlüğü',
                    'Büyükşehir Belediyesi',
                    'Ulaştırma Bakanlığı',
                    'İller Bankası'
                ]),
            ];
        });
    }

    /**
     * Endüstriyel proje
     */
    public function industrial(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'industrial',
                'project_code' => $this->generateProjectCode('industrial'),
                'name' => $this->faker->randomElement([
                    'Üretim Tesisi', 'Fabrika İnşaatı', 'Depo Kompleksi',
                    'Lojistik Merkezi', 'Atölye Binası', 'İmalat Tesisi'
                ]),
                'estimated_employees' => $this->faker->numberBetween(30, 120),
                'budget' => $this->faker->randomFloat(2, 3000000, 30000000), // 3M-30M TL
            ];
        });
    }

    /**
     * Yüksek öncelikli proje
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'critical',
            'status' => 'active',
            'notes' => 'Yüksek öncelikli proje - acil tamamlanması gerekiyor',
        ]);
    }

    /**
     * Büyük ölçekli proje
     */
    public function largescale(): static
    {
        return $this->state(fn (array $attributes) => [
            'budget' => $this->faker->randomFloat(2, 20000000, 100000000), // 20M-100M TL
            'estimated_employees' => $this->faker->numberBetween(100, 300),
            'priority' => 'high',
        ]);
    }

    /**
     * Küçük ölçekli proje
     */
    public function smallscale(): static
    {
        return $this->state(fn (array $attributes) => [
            'budget' => $this->faker->randomFloat(2, 200000, 2000000), // 200K-2M TL
            'estimated_employees' => $this->faker->numberBetween(5, 20),
        ]);
    }
}