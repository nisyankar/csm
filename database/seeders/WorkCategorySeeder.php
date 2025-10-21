<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'code' => 'KAB',
                'name' => 'Kaba İnşaat',
                'description' => 'Kazı, temel, kolon, kiriş, döşeme gibi yapısal işler',
                'icon' => 'fa-hard-hat',
                'color' => '#6c757d',
                'order' => 1,
            ],
            [
                'code' => 'INC',
                'name' => 'İnce İnşaat',
                'description' => 'Sıva, alçıpan, fayans, seramik gibi bitirme işleri',
                'icon' => 'fa-paint-roller',
                'color' => '#17a2b8',
                'order' => 2,
            ],
            [
                'code' => 'ELK',
                'name' => 'Elektrik Tesisatı',
                'description' => 'Elektrik kablo döşeme, pano montajı, aydınlatma',
                'icon' => 'fa-bolt',
                'color' => '#ffc107',
                'order' => 3,
            ],
            [
                'code' => 'SUT',
                'name' => 'Su Tesisatı',
                'description' => 'Temiz su, pis su, yağmur suyu tesisatları',
                'icon' => 'fa-faucet',
                'color' => '#007bff',
                'order' => 4,
            ],
            [
                'code' => 'ISI',
                'name' => 'Isıtma-Soğutma',
                'description' => 'Kalorifer, klima, havalandırma sistemleri',
                'icon' => 'fa-temperature-half',
                'color' => '#dc3545',
                'order' => 5,
            ],
            [
                'code' => 'DIS',
                'name' => 'Dış Cephe',
                'description' => 'Mantolama, dış cephe kaplaması, cam balkon',
                'icon' => 'fa-building',
                'color' => '#6f42c1',
                'order' => 6,
            ],
            [
                'code' => 'BTM',
                'name' => 'Bitirme İşleri',
                'description' => 'Boya, kapı-pencere, mutfak, banyo dolapları',
                'icon' => 'fa-check-circle',
                'color' => '#28a745',
                'order' => 7,
            ],
            [
                'code' => 'PEY',
                'name' => 'Peyzaj',
                'description' => 'Bahçe düzenleme, yeşil alan, otopark',
                'icon' => 'fa-tree',
                'color' => '#20c997',
                'order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            \App\Models\WorkCategory::create($category);
        }
    }
}
