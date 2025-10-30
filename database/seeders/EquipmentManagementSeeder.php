<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentUsage;
use App\Models\EquipmentMaintenance;
use App\Models\Project;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;

class EquipmentManagementSeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::first();
        $employee = Employee::first();
        $user = User::first();

        if (!$project || !$employee || !$user) {
            $this->command->warn('⚠️  Proje, çalışan veya kullanıcı bulunamadı. Ekipman test verileri oluşturulamadı.');
            return;
        }

        // Create Equipments
        $excavator = Equipment::create([
            'code' => 'EKP-001',
            'name' => 'Caterpillar 320D Ekskavatör',
            'type' => 'excavator',
            'brand' => 'Caterpillar',
            'model' => '320D',
            'serial_number' => 'CAT320D2018001',
            'manufacture_year' => 2018,
            'description' => '20 ton hidrolik ekskavatör',
            'ownership' => 'owned',
            'purchase_date' => Carbon::now()->subYears(3),
            'purchase_price' => 850000,
            'status' => 'in_use',
            'current_project_id' => $project->id,
            'current_location' => 'Ankara Şantiyesi - Alan A',
            'maintenance_interval_days' => 90,
            'last_maintenance_date' => Carbon::now()->subDays(30),
            'next_maintenance_date' => Carbon::now()->addDays(60),
            'created_by' => $user->id,
        ]);

        $bulldozer = Equipment::create([
            'code' => 'EKP-002',
            'name' => 'Komatsu D65 Dozer',
            'type' => 'bulldozer',
            'brand' => 'Komatsu',
            'model' => 'D65PX-18',
            'serial_number' => 'KOM-D65-2020-045',
            'manufacture_year' => 2020,
            'ownership' => 'owned',
            'purchase_date' => Carbon::now()->subYears(1),
            'purchase_price' => 1200000,
            'status' => 'available',
            'maintenance_interval_days' => 90,
            'created_by' => $user->id,
        ]);

        $crane = Equipment::create([
            'code' => 'EKP-003',
            'name' => 'Liebherr LTM 1050 Mobil Vinç',
            'type' => 'mobile_crane',
            'brand' => 'Liebherr',
            'model' => 'LTM 1050-3.1',
            'serial_number' => 'LIE-1050-2019-127',
            'manufacture_year' => 2019,
            'ownership' => 'rented',
            'rental_company' => 'Vinç Kiralama A.Ş.',
            'rental_cost_daily' => 8500,
            'rental_cost_monthly' => 200000,
            'status' => 'in_use',
            'current_project_id' => $project->id,
            'current_location' => 'Ankara Şantiyesi - Kule Bölümü',
            'maintenance_interval_days' => 60,
            'created_by' => $user->id,
        ]);

        $loader = Equipment::create([
            'code' => 'EKP-004',
            'name' => 'JCB 3CX Yükleyici',
            'type' => 'loader',
            'brand' => 'JCB',
            'model' => '3CX ECO',
            'manufacture_year' => 2021,
            'ownership' => 'leased',
            'status' => 'available',
            'maintenance_interval_days' => 90,
            'created_by' => $user->id,
        ]);

        $generator = Equipment::create([
            'code' => 'EKP-005',
            'name' => 'FG Wilson 250kVA Jeneratör',
            'type' => 'generator',
            'brand' => 'FG Wilson',
            'model' => 'P250P',
            'serial_number' => 'FGW-250-2022-089',
            'manufacture_year' => 2022,
            'ownership' => 'owned',
            'purchase_date' => Carbon::now()->subMonths(8),
            'purchase_price' => 180000,
            'status' => 'in_use',
            'current_project_id' => $project->id,
            'current_location' => 'Ankara Şantiyesi - Elektrik Odası',
            'maintenance_interval_days' => 30,
            'last_maintenance_date' => Carbon::now()->subDays(25),
            'next_maintenance_date' => Carbon::now()->addDays(5),
            'created_by' => $user->id,
        ]);

        // Equipment Usages
        EquipmentUsage::create([
            'equipment_id' => $excavator->id,
            'project_id' => $project->id,
            'start_date' => Carbon::now()->subDays(15),
            'end_date' => Carbon::now()->subDays(5),
            'duration_days' => 10,
            'operator_id' => $employee->id,
            'work_site_location' => 'Kazı Sahası A-1',
            'work_description' => 'Temel kazı çalışması',
            'meter_start' => 4520,
            'meter_end' => 4600,
            'meter_total' => 80,
            'meter_unit' => 'hours',
            'fuel_consumed' => 640,
            'fuel_cost' => 19200,
            'status' => 'completed',
            'created_by' => $user->id,
        ]);

        EquipmentUsage::create([
            'equipment_id' => $excavator->id,
            'project_id' => $project->id,
            'start_date' => Carbon::now()->subDays(3),
            'duration_days' => 3,
            'operator_id' => $employee->id,
            'work_site_location' => 'Kazı Sahası B-2',
            'work_description' => 'Drenaj kanalı kazısı',
            'meter_start' => 4600,
            'meter_unit' => 'hours',
            'status' => 'ongoing',
            'created_by' => $user->id,
        ]);

        EquipmentUsage::create([
            'equipment_id' => $crane->id,
            'project_id' => $project->id,
            'start_date' => Carbon::now()->subDays(7),
            'duration_days' => 7,
            'work_site_location' => 'Kule İnşaatı',
            'work_description' => 'Prefabrik eleman montajı',
            'rental_cost' => 59500,
            'rental_period_type' => 'weekly',
            'status' => 'ongoing',
            'created_by' => $user->id,
        ]);

        EquipmentUsage::create([
            'equipment_id' => $bulldozer->id,
            'project_id' => $project->id,
            'start_date' => Carbon::now()->subDays(20),
            'end_date' => Carbon::now()->subDays(15),
            'duration_days' => 5,
            'work_site_location' => 'Arazi Düzenleme Sahası',
            'work_description' => 'Toprak ıslahı ve düzenleme',
            'meter_start' => 2100,
            'meter_end' => 2140,
            'meter_total' => 40,
            'meter_unit' => 'hours',
            'fuel_consumed' => 480,
            'fuel_cost' => 14400,
            'status' => 'completed',
            'created_by' => $user->id,
        ]);

        // Equipment Maintenance Records
        EquipmentMaintenance::create([
            'equipment_id' => $excavator->id,
            'maintenance_code' => 'BKM-001',
            'type' => 'routine',
            'maintenance_date' => Carbon::now()->subDays(30),
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'duration_hours' => 4,
            'description' => 'Rutin bakım - yağ değişimi, filtre kontrolü',
            'findings' => 'Hidrolik sistem normal, motor yağı değiştirildi',
            'work_performed' => ['Motor yağı değişimi', 'Hidrolik yağ kontrolü', 'Filtre değişimi', 'Gresörleme'],
            'parts_replaced' => [
                ['part_name' => 'Motor yağı 15W40', 'quantity' => 20, 'cost' => 3200],
                ['part_name' => 'Yağ filtresi', 'quantity' => 2, 'cost' => 450],
            ],
            'service_provider' => 'internal',
            'technician_name' => 'Mehmet Demir',
            'labor_cost' => 1500,
            'parts_cost' => 3650,
            'total_cost' => 5150,
            'status' => 'completed',
            'next_maintenance_date' => Carbon::now()->addDays(60),
            'created_by' => $user->id,
        ]);

        EquipmentMaintenance::create([
            'equipment_id' => $crane->id,
            'maintenance_code' => 'BKM-002',
            'type' => 'inspection',
            'maintenance_date' => Carbon::now()->subDays(10),
            'description' => 'Periyodik güvenlik muayenesi',
            'findings' => 'Tüm güvenlik sistemleri çalışıyor durumda',
            'service_provider' => 'external',
            'service_company' => 'Vinç Muayene Ltd.',
            'technician_name' => 'Ahmet Kaya',
            'technician_phone' => '0532 555 1234',
            'external_service_cost' => 4500,
            'total_cost' => 4500,
            'status' => 'completed',
            'created_by' => $user->id,
        ]);

        EquipmentMaintenance::create([
            'equipment_id' => $generator->id,
            'maintenance_code' => 'BKM-003',
            'type' => 'preventive',
            'maintenance_date' => Carbon::now()->addDays(5),
            'description' => 'Önleyici bakım - motor kontrolü ve ayarları',
            'service_provider' => 'internal',
            'status' => 'scheduled',
            'created_by' => $user->id,
        ]);

        EquipmentMaintenance::create([
            'equipment_id' => $bulldozer->id,
            'maintenance_code' => 'BKM-004',
            'type' => 'corrective',
            'maintenance_date' => Carbon::now()->subDays(45),
            'description' => 'Paletli zincir onarımı',
            'findings' => 'Sol paletli zincir hasarlı, değiştirildi',
            'parts_replaced' => [
                ['part_name' => 'Paletli zincir segmenti', 'quantity' => 5, 'cost' => 12500],
            ],
            'service_provider' => 'external',
            'service_company' => 'Komatsu Yetkili Servis',
            'labor_cost' => 3500,
            'parts_cost' => 12500,
            'external_service_cost' => 8000,
            'total_cost' => 24000,
            'status' => 'completed',
            'created_by' => $user->id,
        ]);

        EquipmentMaintenance::create([
            'equipment_id' => $loader->id,
            'maintenance_code' => 'BKM-005',
            'type' => 'breakdown',
            'maintenance_date' => Carbon::now()->subDays(5),
            'description' => 'Acil arıza müdahalesi - hidrolik pompa arızası',
            'findings' => 'Hidrolik pompa hasarlı, acil değişim yapıldı',
            'work_performed' => ['Hidrolik pompa değişimi', 'Sistem basınç testi'],
            'parts_replaced' => [
                ['part_name' => 'Hidrolik pompa', 'quantity' => 1, 'cost' => 18500],
            ],
            'service_provider' => 'external',
            'service_company' => 'JCB Yetkili Servis',
            'labor_cost' => 2500,
            'parts_cost' => 18500,
            'external_service_cost' => 5000,
            'total_cost' => 26000,
            'under_warranty' => true,
            'warranty_claim_number' => 'WRN-2024-0012',
            'status' => 'completed',
            'created_by' => $user->id,
        ]);

        $this->command->info('✅ 5 ekipman, 4 kullanım kaydı ve 5 bakım kaydı oluşturuldu.');
    }
}
