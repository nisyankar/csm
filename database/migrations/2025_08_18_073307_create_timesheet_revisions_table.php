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
        Schema::create('timesheet_revisions', function (Blueprint $table) {
            $table->id();
            
            // Ana İlişkiler
            $table->foreignId('timesheet_id')->constrained()->onDelete('cascade');
            $table->foreignId('revised_by')->constrained('employees')->onDelete('cascade');
            $table->foreignId('requested_by')->nullable()->constrained('employees')->onDelete('set null');
            
            // Revizyon Bilgileri
            $table->integer('revision_number'); // Revizyon numarası (1, 2, 3...)
            $table->enum('revision_type', [
                'correction',    // Düzeltme
                'addition',      // Ekleme
                'deletion',      // Silme
                'modification'   // Değişiklik
            ]);
            
            // Revizyon Sebebi
            $table->text('revision_reason'); // Revizyon sebebi (zorunlu)
            $table->enum('reason_category', [
                'data_error',        // Veri hatası
                'time_correction',   // Zaman düzeltmesi
                'attendance_change', // Devam durumu değişikliği
                'management_request', // Yönetim talebi
                'employee_request',  // Personel talebi
                'system_error',      // Sistem hatası
                'other'             // Diğer
            ]);
            
            // Değişiklik Detayları
            $table->json('changes_made'); // Yapılan değişikliklerin detayı
            $table->json('old_values'); // Eski değerler
            $table->json('new_values'); // Yeni değerler
            
            // Revizyon Durumu
            $table->enum('status', [
                'pending',      // Beklemede
                'approved',     // Onaylandı
                'rejected',     // Reddedildi
                'in_progress'   // İşleniyor
            ])->default('pending');
            
            // Onay Süreci
            $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Revizyon Talep Detayları
            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('processing_time_minutes')->nullable(); // İşlem süresi
            
            // Öncelik
            $table->enum('priority', [
                'low',      // Düşük
                'medium',   // Orta
                'high',     // Yüksek
                'urgent'    // Acil
            ])->default('medium');
            
            // Etki Analizi
            $table->boolean('affects_payroll')->default(false); // Bordroyu etkiler mi?
            $table->boolean('affects_overtime')->default(false); // Fazla mesaiyi etkiler mi?
            $table->decimal('wage_impact', 8, 2)->nullable(); // Ücret etkisi
            
            // Bildirimler
            $table->json('notified_users')->nullable(); // Bilgilendirilen kullanıcılar
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            
            // Güvenlik ve Denetim
            $table->string('revision_ip')->nullable(); // Revizyon yapılan IP
            $table->text('additional_notes')->nullable(); // Ek notlar
            $table->json('attachments')->nullable(); // Ek dosyalar (gerekirse)
            
            // Sistemsel Bilgiler
            $table->boolean('is_bulk_revision')->default(false); // Toplu revizyon mu?
            $table->foreignId('bulk_revision_id')->nullable()->constrained('timesheet_revisions')->onDelete('set null');
            
            $table->timestamps();
            
            // İndeksler
            $table->index(['timesheet_id', 'revision_number']);
            $table->index(['revised_by', 'status']);
            $table->index(['requested_at']);
            $table->index(['status', 'priority']);
            $table->index(['reason_category']);
            $table->index(['affects_payroll']);
            
            // Unique constraint (aynı puantaj için aynı revizyon numarası olamaz)
            $table->unique(['timesheet_id', 'revision_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_revisions');
    }
};