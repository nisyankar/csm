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
            // Temel veriler
            ShiftSeeder::class, // Vardiya tanımları (GN, GC, HS, RT, vb.)
            ConstructionSeeder::class, // Projeler, çalışanlar, temel veriler

            // İzin sistemi
            LeaveTypesSeeder::class, // İzin türleri (yıllık, hastalık, vb.)
            LeaveParametersSeeder::class, // İzin parametreleri ve kuralları

            // Proje atamaları
            EmployeeProjectAssignmentSeeder::class, // Çalışan-proje atamaları

            // Resmi Tatiller
            HolidaysSeeder::class, // 2025 Resmi Tatil günleri

            // Puantaj ve raporlar - GEÇİCİ DEVRE DIŞI (timesheet model güncellenmeli)
            // TimesheetDemoSeeder::class, // Demo puantaj verileri
            DailyReportSeeder::class, // Günlük rapor verileri

            // Malzeme ve stok
            MaterialSeeder::class, // İnşaat malzemeleri

            // Satınalma modülü
            PurchasingModuleSeeder::class, // Satınalma modülü demo verileri

            // Yetki ve roller
            PermissionSeeder::class, // İzinler ve roller
            SystemAdminRoleSeeder::class, // Sistem admin rolü

            // İş kalemleri ve taşeronlar
            WorkCategorySeeder::class, // İş kategorileri
            WorkItemSeeder::class, // İş kalemleri
            SubcontractorSeeder::class, // Taşeron verileri

            // Proje yapısı ve taşeron atamaları
            ProjectStructureSeeder::class, // Blok, kat, daire yapıları
            ProjectSubcontractorSeeder::class, // Proje-taşeron atamaları

            // Hakediş kayıtları
            ProgressPaymentSeeder::class, // Taşeron hakediş verileri

            // Finansal Yönetim
            FinancialCategoriesSeeder::class, // Gelir/Gider kategorileri

            // Keşif & Metraj Yönetimi
            QuantitySeeder::class, // Metraj kayıtları

            // Ruhsat Yönetimi
            ConstructionPermitSeeder::class, // Yapı ruhsatı, İskan, Kullanma izni

            // Yapı Denetim Sistemi
            BuildingInspectionSeeder::class, // Denetim kuruluşları ve denetim kayıtları

            // Stok Yönetimi
            StockManagementSeeder::class, // Depolar ve stok hareketleri
        ]);
    }
}
