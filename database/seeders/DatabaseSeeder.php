<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ConstructionSeeder::class,
            TimesheetDemoSeeder::class, // Demo puantaj verileri
            PurchasingModuleSeeder::class, // Satınalma modülü demo verileri
            SubcontractorSeeder::class, // Taşeron verileri
        ]);
    }
}
