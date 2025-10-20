<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Project;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $departmentType = $this->faker->randomElement([
            'structural', 'mechanical', 'electrical', 'finishing', 
            'landscaping', 'safety', 'quality', 'logistics', 'administration'
        ]);

        $departmentData = $this->getDepartmentData($departmentType);
        
        return [
            'code' => $this->generateDepartmentCode($departmentData['prefix']),
            'name' => $departmentData['name'],
            'description' => $departmentData['description'],
            'project_id' => Project::factory(), // Seeder'da set edilecek
            'parent_department_id' => null, // Hiyerarşi seeder'da kurulacak
            'supervisor_id' => null, // Employee'lar oluşturulduktan sonra atanacak
            'type' => $departmentType,
            'budget' => $this->faker->randomFloat(2, 50000, 500000),
            'spent_amount' => function (array $attributes) {
                return $this->faker->randomFloat(2, 0, $attributes['budget'] * 0.7);
            },
            'status' => $this->faker->randomElement(['not_started', 'in_progress', 'completed', 'on_hold']),
            'planned_start_date' => $this->faker->dateTimeBetween('-6 months', '+1 month'),
            'planned_end_date' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['planned_start_date'], '+1 year');
            },
            'actual_start_date' => function (array $attributes) {
                return $attributes['status'] !== 'not_started' ? 
                    $this->faker->optional(0.8)->dateTimeBetween($attributes['planned_start_date'], 'now') : null;
            },
            'actual_end_date' => function (array $attributes) {
                return $attributes['status'] === 'completed' ? 
                    $this->faker->dateTimeBetween($attributes['actual_start_date'] ?? $attributes['planned_start_date'], 'now') : null;
            },
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'estimated_employees' => $this->getEstimatedEmployees($departmentType),
            'notes' => $this->faker->optional(0.4)->paragraph(),
            'custom_fields' => json_encode([
                'safety_level' => $this->faker->randomElement(['standard', 'high', 'critical']),
                'equipment_needed' => $this->faker->optional(0.6)->words(3, true),
                'special_requirements' => $this->faker->optional(0.3)->sentence(),
            ]),
            'location_description' => $this->getLocationDescription($departmentType),
        ];
    }

    /**
     * Departman türüne göre veri döndür
     */
    private function getDepartmentData(string $type): array
    {
        $data = [
            'structural' => [
                'prefix' => 'YPI',
                'name' => 'Yapısal İşler',
                'description' => 'Temel, kolon, kiriş, döşeme gibi yapısal elemanların inşası'
            ],
            'mechanical' => [
                'prefix' => 'MEK',
                'name' => 'Mekanik Tesisat',
                'description' => 'Havalandırma, ısıtma, soğutma sistemleri kurulumu'
            ],
            'electrical' => [
                'prefix' => 'ELK',
                'name' => 'Elektrik Tesisatı',
                'description' => 'Elektrik altyapısı, aydınlatma ve güç sistemleri'
            ],
            'finishing' => [
                'prefix' => 'FIN',
                'name' => 'Finishing İşleri',
                'description' => 'Sıva, boya, döşeme, seramik ve son kat işleri'
            ],
            'landscaping' => [
                'prefix' => 'PEY',
                'name' => 'Peyzaj Düzenlemesi',
                'description' => 'Bahçe düzenlemesi, ağaçlandırma ve dış mekan çalışmaları'
            ],
            'safety' => [
                'prefix' => 'GUV',
                'name' => 'İş Güvenliği',
                'description' => 'İş sağlığı ve güvenliği tedbirleri, denetim ve eğitim'
            ],
            'quality' => [
                'prefix' => 'KAL',
                'name' => 'Kalite Kontrol',
                'description' => 'Kalite standartları denetimi ve test işlemleri'
            ],
            'logistics' => [
                'prefix' => 'LOG',
                'name' => 'Lojistik',
                'description' => 'Malzeme temini, depolama ve nakliye koordinasyonu'
            ],
            'administration' => [
                'prefix' => 'IDR',
                'name' => 'İdari İşler',
                'description' => 'Proje yönetimi, dokümantasyon ve koordinasyon'
            ],
            'other' => [
                'prefix' => 'DGR',
                'name' => 'Diğer İşler',
                'description' => 'Sınıflandırılmamış özel işler'
            ],
        ];

        return $data[$type] ?? $data['other'];
    }

    /**
     * Departman kodu oluştur
     */
    private function generateDepartmentCode(string $prefix): string
    {
        return $prefix . str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Departman türüne göre tahmini personel sayısı
     */
    private function getEstimatedEmployees(string $type): int
    {
        $estimates = [
            'structural' => [8, 25],     // Yapısal işler çok personel gerektirir
            'mechanical' => [4, 12],     // Mekanik tesisat
            'electrical' => [3, 10],     // Elektrik tesisatı
            'finishing' => [6, 18],      // Finishing işleri
            'landscaping' => [3, 8],     // Peyzaj
            'safety' => [2, 5],          // İş güvenliği
            'quality' => [1, 4],         // Kalite kontrol
            'logistics' => [2, 6],       // Lojistik
            'administration' => [1, 3],  // İdari işler
            'other' => [2, 8],           // Diğer
        ];

        $range = $estimates[$type] ?? [2, 8];
        return $this->faker->numberBetween($range[0], $range[1]);
    }

    /**
     * Departman türüne göre lokasyon açıklaması
     */
    private function getLocationDescription(string $type): string
    {
        $locations = [
            'structural' => $this->faker->randomElement([
                'Zemin Kat', 'Bodrum', '1. Kat', '2. Kat', 'Çatı Katı', 'Temel Alanı'
            ]),
            'mechanical' => $this->faker->randomElement([
                'Teknik Alan', 'Makine Dairesi', 'Çatı Katı', 'Bodrum Teknik Alan'
            ]),
            'electrical' => $this->faker->randomElement([
                'Elektrik Panosu Alanı', 'Teknik Şaft', 'Her Kat', 'Dağıtım Merkezi'
            ]),
            'finishing' => $this->faker->randomElement([
                'İç Mekanlar', 'Tüm Katlar', 'Ortak Alanlar', 'Apartman Daireleri'
            ]),
            'landscaping' => $this->faker->randomElement([
                'Bahçe Alanı', 'Ön Bahçe', 'Arka Bahçe', 'Çevre Düzenlemesi'
            ]),
            'safety' => 'Şantiye Geneli',
            'quality' => 'Şantiye Geneli',
            'logistics' => $this->faker->randomElement([
                'Depo Alanı', 'Giriş Kapısı', 'Malzeme Sahasası'
            ]),
            'administration' => $this->faker->randomElement([
                'Şantiye Ofisi', 'Konteyner Ofis', 'Yönetim Binası'
            ]),
        ];

        return $locations[$type] ?? 'Belirlenmemiş';
    }

    /**
     * Aktif departman durumu
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'actual_start_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ]);
    }

    /**
     * Tamamlanmış departman
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = $this->faker->dateTimeBetween('-1 year', '-2 months');
            $endDate = $this->faker->dateTimeBetween($startDate, '-1 month');
            
            return [
                'status' => 'completed',
                'actual_start_date' => $startDate,
                'actual_end_date' => $endDate,
                'spent_amount' => $this->faker->randomFloat(2, 
                    $attributes['budget'] * 0.8, 
                    $attributes['budget'] * 1.1
                ),
            ];
        });
    }

    /**
     * Henüz başlanmamış departman
     */
    public function notStarted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'not_started',
            'actual_start_date' => null,
            'actual_end_date' => null,
            'spent_amount' => 0,
            'planned_start_date' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
        ]);
    }

    /**
     * Beklemede departman
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on_hold',
            'notes' => 'Proje beklemede - ' . $this->faker->randomElement([
                'Malzeme temini bekleniyor',
                'İzin işlemleri devam ediyor',
                'Hava koşulları nedeniyle',
                'Müşteri talebi üzerine',
                'Teknik revizyon gerekli'
            ]),
        ]);
    }

    /**
     * Yapısal işler departmanı
     */
    public function structural(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'structural',
            'code' => $this->generateDepartmentCode('YPI'),
            'name' => 'Yapısal İşler',
            'estimated_employees' => $this->faker->numberBetween(12, 25),
            'priority' => 'high',
        ]);
    }

    /**
     * Elektrik tesisatı departmanı
     */
    public function electrical(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'electrical',
            'code' => $this->generateDepartmentCode('ELK'),
            'name' => 'Elektrik Tesisatı',
            'estimated_employees' => $this->faker->numberBetween(4, 10),
        ]);
    }

    /**
     * Mekanik tesisat departmanı
     */
    public function mechanical(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'mechanical',
            'code' => $this->generateDepartmentCode('MEK'),
            'name' => 'Mekanik Tesisat',
            'estimated_employees' => $this->faker->numberBetween(5, 12),
        ]);
    }

    /**
     * Alt departman (parent_id olan)
     */
    public function subDepartment(): static
    {
        return $this->state(fn (array $attributes) => [
            'budget' => $this->faker->randomFloat(2, 20000, 100000), // Daha küçük bütçe
            'estimated_employees' => $this->faker->numberBetween(2, 8),
        ]);
    }
}