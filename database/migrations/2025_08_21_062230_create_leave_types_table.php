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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            
            // Temel Bilgiler
            $table->string('code', 20)->unique(); // annual, sick, maternity, etc.
            $table->string('name', 100); // Görünen ad
            $table->text('description')->nullable();
            $table->string('color_code', 7)->default('#3B82F6'); // Hex renk kodu
            $table->string('icon', 50)->nullable(); // Icon class
            
            // İzin Özellikleri
            $table->boolean('is_paid')->default(true); // Ücretli mi?
            $table->boolean('affects_annual_balance')->default(true); // Yıllık izin bakiyesini etkiler mi?
            $table->boolean('requires_approval')->default(true); // Onay gerekir mi?
            $table->boolean('requires_document')->default(false); // Belge gerekir mi?
            $table->boolean('can_be_split')->default(true); // Bölünebilir mi?
            
            // Süre Kısıtlamaları
            $table->integer('max_consecutive_days')->nullable(); // Maksimum ardışık gün
            $table->integer('max_total_days_per_year')->nullable(); // Yılda maksimum toplam gün
            $table->integer('min_advance_notice_days')->default(1); // Minimum ön bildirim gün
            $table->integer('max_advance_request_days')->nullable(); // Maksimum ileri tarih
            
            // Özel Koşullar
            $table->boolean('only_workdays')->default(true); // Sadece iş günlerinde mi?
            $table->boolean('includes_weekends')->default(false); // Hafta sonlarını dahil eder mi?
            $table->boolean('includes_holidays')->default(false); // Tatil günlerini dahil eder mi?
            $table->json('excluded_months')->nullable(); // Kullanılamayacak aylar [1,2,12]
            $table->json('allowed_employee_categories')->nullable(); // İzin verilecek personel kategorileri
            
            // Gender/Age Restrictions
            $table->enum('gender_restriction', ['none', 'male', 'female'])->default('none');
            $table->integer('min_age_requirement')->nullable();
            $table->integer('max_age_requirement')->nullable();
            $table->integer('min_tenure_months')->nullable(); // Minimum kıdem ayı
            
            // Tekrar ve Sıklık
            $table->boolean('can_be_recurring')->default(false); // Tekrarlanabilir mi?
            $table->integer('max_occurrences_per_year')->nullable(); // Yılda maksimum kaç kez
            $table->integer('min_gap_between_requests')->nullable(); // Talepler arası minimum gün
            
            // Otomatik İşlemler
            $table->boolean('auto_approve_conditions')->default(false); // Otomatik onay koşulları var mı?
            $table->json('auto_approve_rules')->nullable(); // Otomatik onay kuralları
            $table->boolean('create_timesheet_entry')->default(true); // Puantaj girişi oluştur
            $table->boolean('send_calendar_invite')->default(false); // Takvim daveti gönder
            
            // Bildirimler
            $table->boolean('notify_manager')->default(true);
            $table->boolean('notify_hr')->default(false);
            $table->boolean('notify_substitute')->default(false);
            $table->json('notification_templates')->nullable(); // Bildirim şablonları
            
            // İstatistik ve Raporlama
            $table->boolean('include_in_reports')->default(true);
            $table->boolean('affects_attendance_percentage')->default(true);
            $table->string('report_category', 50)->nullable(); // Rapor kategorisi
            
            // Durum
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // Sıralama
            
            // Yasal Gereklilikler
            $table->text('legal_reference')->nullable(); // Yasal dayanak
            $table->boolean('is_mandatory')->default(false); // Zorunlu izin mi?
            $table->json('compliance_rules')->nullable(); // Uyum kuralları
            
            $table->timestamps();
            
            // İndeksler
            $table->index(['is_active', 'sort_order']);
            $table->index(['code']);
            $table->index(['is_paid']);
            $table->index(['requires_approval']);
            $table->index(['affects_annual_balance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};