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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            
            // Ana İlişkiler
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id')->nullable()->constrained('employees')->onDelete('set null');
            
            // İzin Türü
            $table->enum('leave_type', [
                'annual',        // Yıllık izin
                'sick',          // Hastalık izni
                'maternity',     // Doğum izni
                'paternity',     // Babalık izni
                'marriage',      // Evlilik izni
                'funeral',       // Cenaze izni
                'military',      // Askerlik izni
                'unpaid',        // Ücretsiz izin
                'emergency',     // Acil durum izni
                'study',         // Eğitim izni
                'other'          // Diğer
            ]);
            
            // Tarih Bilgileri
            $table->date('start_date'); // İzin başlangıç tarihi
            $table->date('end_date'); // İzin bitiş tarihi
            $table->integer('total_days'); // Toplam izin günü sayısı
            $table->integer('working_days'); // İş günü sayısı (hafta sonu hariç)
            
            // Yarım Gün İzin
            $table->boolean('is_half_day')->default(false); // Yarım gün izin mi?
            $table->enum('half_day_period', ['morning', 'afternoon'])->nullable(); // Sabah/öğleden sonra
            
            // İzin Detayları
            $table->text('reason'); // İzin sebebi
            $table->text('description')->nullable(); // Detaylı açıklama
            $table->string('emergency_contact')->nullable(); // Acil durum iletişim
            $table->text('handover_notes')->nullable(); // İş devir notları
            
            // Onay Durumu
            $table->enum('status', [
                'pending',      // Beklemede
                'approved',     // Onaylandı
                'rejected',     // Reddedildi
                'cancelled',    // İptal edildi
                'withdrawn'     // Geri çekildi
            ])->default('pending');
            
            // Onay Süreçi
            $table->timestamp('submitted_at'); // Talep tarihi
            $table->timestamp('reviewed_at')->nullable(); // İnceleme tarihi
            $table->timestamp('approved_at')->nullable(); // Onay tarihi
            $table->text('approval_notes')->nullable(); // Onay notları
            $table->text('rejection_reason')->nullable(); // Red sebebi
            
            // Yedek Personel
            $table->foreignId('substitute_employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->text('substitute_notes')->nullable(); // Vekil ile ilgili notlar
            
            // Belge ve Dosyalar
            $table->json('attached_documents')->nullable(); // Eklenen belgeler (tıbbi rapor vb.)
            $table->boolean('requires_document')->default(false); // Belge gerekli mi?
            $table->enum('document_status', ['not_required', 'pending', 'submitted', 'verified'])->default('not_required');
            
            // İzin Bakiyesi Etkisi
            $table->integer('balance_impact')->default(0); // İzin bakiyesine etkisi
            $table->boolean('is_paid')->default(true); // Ücretli izin mi?
            $table->decimal('salary_impact', 8, 2)->nullable(); // Maaşa etkisi
            
            // Çakışma Kontrolü
            $table->json('conflicting_leaves')->nullable(); // Çakışan izinler
            $table->boolean('conflict_resolved')->default(true); // Çakışma çözüldü mü?
            
            // Otomatik Puantaj Entegrasyonu
            $table->boolean('auto_applied_to_timesheet')->default(false); // Puantaja otomatik yansıdı mı?
            $table->timestamp('applied_to_timesheet_at')->nullable();
            $table->json('timesheet_entries')->nullable(); // Oluşturulan puantaj kayıtları
            
            // Bildirimler
            $table->boolean('employee_notified')->default(false);
            $table->boolean('manager_notified')->default(false);
            $table->boolean('hr_notified')->default(false);
            $table->timestamp('notifications_sent_at')->nullable();
            
            // Öncelik ve Kategori
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('urgency_reason', ['medical', 'family', 'legal', 'personal', 'other'])->nullable();
            
            // Tekrarlayan İzin (Gelecek özellik için)
            $table->boolean('is_recurring')->default(false);
            $table->foreignId('parent_request_id')->nullable()->constrained('leave_requests')->onDelete('set null');
            
            // İstatistik ve Raporlama
            $table->integer('approval_duration_hours')->nullable(); // Onay süresi (saat)
            $table->date('return_date')->nullable(); // Fiili dönüş tarihi
            $table->boolean('returned_early')->default(false); // Erken döndü mü?
            $table->text('return_notes')->nullable(); // Dönüş notları
            
            $table->timestamps();
            
            // İndeksler
            $table->index(['employee_id', 'status']);
            $table->index(['approver_id', 'status']);
            $table->index(['leave_type', 'start_date']);
            $table->index(['start_date', 'end_date']);
            $table->index(['submitted_at']);
            $table->index(['status', 'priority']);
            $table->index(['is_paid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};