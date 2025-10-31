<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KpiDefinition;
use App\Models\ReportTemplate;
use App\Models\User;

class ReportingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@spt.com')->first();

        if (!$adminUser) {
            $adminUser = User::first();
        }

        if (!$adminUser) {
            $this->command->warn('No users found. Please create a user first.');
            return;
        }

        // KPI Definitions
        $kpis = [
            [
                'name' => 'Proje Tamamlanma Yüzdesi',
                'module' => 'projects',
                'formula' => 'project_completion_percentage',
                'target_value' => 100,
                'warning_threshold' => 80,
                'unit' => '%',
                'description' => 'Projenin planlanan metrajdan ne kadarının tamamlandığını gösterir',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Maliyet Varyansı',
                'module' => 'financials',
                'formula' => 'cost_variance',
                'target_value' => 0,
                'warning_threshold' => 10,
                'unit' => '%',
                'description' => 'Planlanan bütçe ile gerçekleşen harcama arasındaki fark',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'İşgücü Verimliliği',
                'module' => 'timesheets',
                'formula' => 'labor_productivity',
                'target_value' => 1.2,
                'warning_threshold' => 0.8,
                'unit' => 'birim/saat',
                'description' => 'Çalışan başına tamamlanan iş miktarı',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'İSG Kaza Oranı',
                'module' => 'safety',
                'formula' => 'safety_incident_rate',
                'target_value' => 0,
                'warning_threshold' => 2,
                'unit' => '%',
                'description' => 'Çalışan başına düşen kaza sayısı',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Ekipman Kullanım Oranı',
                'module' => 'equipment',
                'formula' => 'equipment_utilization',
                'target_value' => 85,
                'warning_threshold' => 60,
                'unit' => '%',
                'description' => 'Ekipmanların aktif kullanım oranı',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Zamanında Teslim Oranı',
                'module' => 'progress_payments',
                'formula' => 'on_time_delivery',
                'target_value' => 95,
                'warning_threshold' => 80,
                'unit' => '%',
                'description' => 'Hakediş işlemlerinin planlanan tarihte tamamlanma oranı',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($kpis as $kpi) {
            KpiDefinition::firstOrCreate(
                ['name' => $kpi['name'], 'module' => $kpi['module']],
                $kpi
            );
        }

        // Report Templates
        $templates = [
            [
                'name' => 'Hakediş Özet Raporu',
                'type' => 'pdf',
                'module' => 'progress_payments',
                'template_path' => 'reports.pdf.progress_payments',
                'parameters_json' => [
                    'filters' => ['project_id', 'status', 'date_range'],
                    'grouping' => ['project', 'subcontractor', 'status']
                ],
                'description' => 'Hakediş özetlerini proje, taşeron ve durum bazında gösterir',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Puantaj Aylık Raporu',
                'type' => 'excel',
                'module' => 'timesheets',
                'template_path' => null,
                'parameters_json' => [
                    'filters' => ['month', 'year', 'project_id', 'employee_id'],
                    'columns' => ['employee', 'total_hours', 'overtime', 'cost']
                ],
                'description' => 'Aylık puantaj detaylarını ve maliyetlerini gösterir',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Finansal Kar/Zarar Raporu',
                'type' => 'pdf',
                'module' => 'financials',
                'template_path' => 'reports.pdf.financials',
                'parameters_json' => [
                    'filters' => ['date_range', 'project_id', 'category'],
                    'breakdown' => ['income', 'expense', 'profit']
                ],
                'description' => 'Gelir, gider ve kar/zarar analizini gösterir',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'İSG Kaza Raporu',
                'type' => 'pdf',
                'module' => 'safety',
                'template_path' => 'reports.pdf.safety',
                'parameters_json' => [
                    'filters' => ['date_range', 'project_id', 'severity'],
                    'details' => ['incidents', 'inspections', 'trainings']
                ],
                'description' => 'İş kazaları ve güvenlik denetimlerini raporlar',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Ekipman Kullanım Raporu',
                'type' => 'excel',
                'module' => 'equipment',
                'template_path' => null,
                'parameters_json' => [
                    'filters' => ['date_range', 'equipment_type', 'project_id'],
                    'metrics' => ['usage_hours', 'maintenance_cost', 'utilization_rate']
                ],
                'description' => 'Ekipman kullanımı ve bakım maliyetlerini gösterir',
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($templates as $template) {
            ReportTemplate::firstOrCreate(
                ['name' => $template['name'], 'module' => $template['module']],
                $template
            );
        }

        $this->command->info('Reporting module seeded successfully!');
        $this->command->info('Created ' . count($kpis) . ' KPI definitions');
        $this->command->info('Created ' . count($templates) . ' report templates');
    }
}
