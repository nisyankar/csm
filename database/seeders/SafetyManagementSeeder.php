<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SafetyIncident;
use App\Models\SafetyTraining;
use App\Models\SafetyInspection;
use App\Models\RiskAssessment;
use App\Models\PpeAssignment;
use App\Models\Project;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;

class SafetyManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $project = Project::first();
        $employee = Employee::first();
        $user = User::first();

        if (!$project || !$employee || !$user) {
            $this->command->warn('⚠️  Proje, çalışan veya kullanıcı bulunamadı. İSG test verileri oluşturulamadı.');
            return;
        }

        // Safety Incidents
        SafetyIncident::create([
            'project_id' => $project->id,
            'employee_id' => $employee->id,
            'incident_date' => Carbon::now()->subDays(5),
            'incident_time' => '14:30:00',
            'location' => 'Şantiye Ana Giriş',
            'incident_type' => 'near_miss',
            'severity' => 'medium',
            'description' => 'İskele malzemesi düşme riski tespit edildi',
            'immediate_actions' => 'Alan kordon altına alındı, malzemeler güvenli hale getirildi',
            'reported_by' => $user->id,
            'reported_at' => Carbon::now()->subDays(5),
            'status' => 'investigating',
            'medical_treatment_required' => false,
            'work_stopped' => true,
        ]);

        SafetyIncident::create([
            'project_id' => $project->id,
            'employee_id' => $employee->id,
            'incident_date' => Carbon::now()->subDays(15),
            'location' => '3. Kat İnşaat Alanı',
            'incident_type' => 'minor_injury',
            'severity' => 'low',
            'description' => 'Küçük kesik yaralanması',
            'immediate_actions' => 'İlk yardım uygulandı',
            'reported_by' => $user->id,
            'reported_at' => Carbon::now()->subDays(15),
            'status' => 'resolved',
            'medical_treatment_required' => true,
            'days_lost' => 0,
        ]);

        // Safety Trainings
        SafetyTraining::create([
            'project_id' => $project->id,
            'training_title' => 'Temel İSG Eğitimi',
            'training_type' => 'isg_basic',
            'trainer_name' => 'Ahmet Yılmaz',
            'trainer_company' => 'İSG Akademi',
            'training_date' => Carbon::now()->addDays(7),
            'start_time' => '09:00:00',
            'duration_hours' => 8,
            'location' => 'Şantiye Toplantı Salonu',
            'description' => 'Şantiye çalışanları için zorunlu temel iş sağlığı ve güvenliği eğitimi',
            'status' => 'planned',
            'created_by' => $user->id,
            'certificate_issued' => true,
            'certificate_expiry_date' => Carbon::now()->addYears(2),
        ]);

        SafetyTraining::create([
            'project_id' => $project->id,
            'training_title' => 'Yüksekte Çalışma Eğitimi',
            'training_type' => 'height_work',
            'trainer_name' => 'Mehmet Demir',
            'training_date' => Carbon::now()->subDays(10),
            'duration_hours' => 6,
            'location' => 'Şantiye Alanı',
            'status' => 'completed',
            'created_by' => $user->id,
            'test_conducted' => true,
            'pass_score' => 70,
        ]);

        // Safety Inspections
        SafetyInspection::create([
            'project_id' => $project->id,
            'inspector_id' => $user->id,
            'inspection_title' => 'Haftalık Güvenlik Denetimi',
            'inspection_type' => 'weekly',
            'inspection_date' => Carbon::now()->subDays(2),
            'location' => 'Tüm Şantiye Alanı',
            'overall_status' => 'passed_with_notes',
            'score' => 85,
            'items_checked' => 20,
            'items_passed' => 17,
            'items_failed' => 3,
            'status' => 'completed',
            'next_inspection_date' => Carbon::now()->addDays(5),
        ]);

        // Risk Assessments
        RiskAssessment::create([
            'project_id' => $project->id,
            'assessed_by' => $user->id,
            'assessment_title' => 'Beton Dökümü Risk Değerlendirmesi',
            'work_activity' => 'Kolon ve kiriş beton dökümü',
            'location' => '5. Kat',
            'assessment_date' => Carbon::now(),
            'description' => '5. kat kolon ve kiriş beton dökümü için risk analizi',
            'overall_risk_level' => 'medium',
            'control_measures' => 'Emniyet kemeri, bariyer sistemi, eğitimli personel',
            'valid_from' => Carbon::now(),
            'valid_until' => Carbon::now()->addMonths(6),
            'status' => 'active',
            'revision_number' => 1,
        ]);

        // PPE Assignments
        PpeAssignment::create([
            'project_id' => $project->id,
            'employee_id' => $employee->id,
            'ppe_type' => 'helmet',
            'brand' => '3M',
            'model' => 'H-700 Series',
            'size' => 'Standart',
            'assigned_date' => Carbon::now()->subDays(30),
            'assigned_by' => $user->id,
            'quantity' => 1,
            'unit_price' => 150,
            'total_price' => 150,
            'status' => 'assigned',
        ]);

        PpeAssignment::create([
            'project_id' => $project->id,
            'employee_id' => $employee->id,
            'ppe_type' => 'safety_boots',
            'brand' => 'Puma',
            'model' => 'Safety Boots S3',
            'size' => '42',
            'assigned_date' => Carbon::now()->subDays(30),
            'assigned_by' => $user->id,
            'quantity' => 1,
            'unit_price' => 450,
            'total_price' => 450,
            'status' => 'assigned',
        ]);

        $this->command->info('✅ İSG modülü test verileri oluşturuldu!');
    }
}