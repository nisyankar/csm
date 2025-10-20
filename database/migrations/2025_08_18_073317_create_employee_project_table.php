<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_project', function (Blueprint $table) {
            $table->id();
            
            // Ana İlişkiler
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            
            // Atama Bilgileri
            $table->date('assigned_date'); // Projeye atanma tarihi
            $table->date('start_date')->nullable(); // Projede çalışmaya başlama tarihi
            $table->date('end_date')->nullable(); // Projeden ayrılma tarihi
            $table->date('planned_end_date')->nullable(); // Planlanan bitiş tarihi
            
            // Rol ve Sorumluluklar
            $table->string('role_in_project'); // Projede rolü
            $table->text('responsibilities')->nullable(); // Sorumlulukları
            $table->enum('assignment_type', [
                'full_time',    // Tam zamanlı
                'part_time',    // Yarı zamanlı
                'temporary',    // Geçici
                'consultant',   // Danışman
                'support'       // Destek
            ])->default('full_time');
            
            // Çalışma Yüzdesi
            $table->integer('work_percentage')->default(100); // Çalışma yüzdesi (0-100)
            $table->decimal('daily_hours', 4, 2)->nullable(); // Günlük çalışma saati
            
            // Ücret Bilgileri (Proje özel)
            $table->decimal('project_daily_rate', 8, 2)->nullable(); // Proje özel günlük ücret
            $table->decimal('project_hourly_rate', 8, 2)->nullable(); // Proje özel saatlik ücret
            $table->boolean('has_project_bonus')->default(false); // Proje bonusu var mı?
            $table->decimal('project_bonus_amount', 8, 2)->nullable(); // Proje bonus miktarı
            
            // Durum
            $table->enum('status', [
                'assigned',     // Atandı
                'active',       // Aktif çalışıyor
                'on_hold',      // Beklemede
                'completed',    // Tamamlandı
                'transferred',  // Başka projeye transfer
                'terminated'    // Sonlandırıldı
            ])->default('assigned');
            
            // Performans ve Değerlendirme
            $table->integer('performance_score')->nullable(); // Performans puanı (1-100)
            $table->text('performance_notes')->nullable(); // Performans notları
            $table->date('last_evaluation_date')->nullable(); // Son değerlendirme tarihi
            
            // Atama Yapan ve Onaylayan
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Özel Yetkiler ve Erişimler
            $table->json('project_permissions')->nullable(); // Projede özel yetkiler
            $table->boolean('can_approve_timesheets')->default(false); // Puantaj onaylayabilir mi?
            $table->boolean('can_manage_team')->default(false); // Ekip yönetebilir mi?
            
            // Konum ve Ulaşım
            $table->string('work_location')->nullable(); // Çalışma lokasyonu
            $table->boolean('transport_provided')->default(false); // Ulaşım sağlanıyor mu?
            $table->decimal('transport_allowance', 6, 2)->nullable(); // Ulaşım ödeneği
            
            // Güvenlik ve Eğitim
            $table->boolean('safety_training_completed')->default(false); // İş güvenliği eğitimi
            $table->date('safety_training_date')->nullable();
            $table->date('safety_training_expiry')->nullable();
            $table->json('required_certifications')->nullable(); // Gerekli sertifikalar
            
            // İstatistik Alanları
            $table->integer('total_work_days')->default(0); // Toplam çalışma günü
            $table->decimal('total_hours_worked', 8, 2)->default(0); // Toplam çalışma saati
            $table->decimal('total_overtime_hours', 8, 2)->default(0); // Toplam fazla mesai
            $table->decimal('total_earnings', 10, 2)->default(0); // Toplam kazanç
            
            // Notlar ve Açıklamalar
            $table->text('assignment_notes')->nullable(); // Atama notları
            $table->text('special_instructions')->nullable(); // Özel talimatlar
            $table->text('termination_reason')->nullable(); // Sonlandırma sebebi
            
            // Geçmiş Takibi
            $table->json('assignment_history')->nullable(); // Atama geçmişi
            $table->boolean('is_rehire')->default(false); // Tekrar atama mı?
            $table->foreignId('previous_assignment_id')->nullable()->constrained('employee_project')->onDelete('set null');
            
            $table->timestamps();
            
            // İndeksler
            $table->index(['employee_id', 'status']);
            $table->index(['project_id', 'status']);
            $table->index(['department_id']);
            $table->index(['assigned_date']);
            $table->index(['start_date', 'end_date']);
            $table->index(['assignment_type']);
            $table->index(['status', 'work_percentage']);
            
            // Unique constraint (aynı personel aynı projede aynı anda birden fazla aktif atama olamaz)
            $table->unique(['employee_id', 'project_id', 'status'], 'unique_active_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_project');
    }
};