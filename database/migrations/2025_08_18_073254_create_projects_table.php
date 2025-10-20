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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            
            // Proje Temel Bilgileri
            $table->string('project_code')->unique(); // Proje kodu
            $table->string('name'); // Proje adı
            $table->text('description')->nullable(); // Proje açıklaması
            
            // Lokasyon Bilgileri
            $table->string('location'); // Şantiye adresi
            $table->string('city');
            $table->string('district')->nullable();
            $table->text('full_address')->nullable();
            $table->string('coordinates')->nullable(); // GPS koordinatları
            
            // Tarih Bilgileri
            $table->date('start_date'); // Proje başlama tarihi
            $table->date('planned_end_date'); // Planlanan bitiş tarihi
            $table->date('actual_end_date')->nullable(); // Gerçek bitiş tarihi
            
            // Mali Bilgiler
            $table->decimal('budget', 15, 2)->nullable(); // Proje bütçesi
            $table->decimal('labor_budget', 15, 2)->nullable(); // İşçilik bütçesi
            $table->decimal('spent_amount', 15, 2)->default(0); // Harcanan miktar
            
            // Proje Yönetimi
            $table->foreignId('project_manager_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('site_manager_id')->nullable()->constrained('employees')->onDelete('set null');
            
            // İletişim Bilgileri
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            
            // Proje Durumu
            $table->enum('status', [
                'planning',      // Planlama
                'active',        // Aktif
                'on_hold',       // Beklemede
                'completed',     // Tamamlandı
                'cancelled'      // İptal edildi
            ])->default('planning');
            
            // Proje Türü
            $table->enum('type', [
                'residential',   // Konut
                'commercial',    // Ticari
                'infrastructure', // Altyapı
                'industrial',    // Endüstriyel
                'other'          // Diğer
            ])->default('residential');
            
            // Proje Önceliği
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            
            // Müşteri Bilgileri
            $table->string('client_name')->nullable();
            $table->string('client_contact')->nullable();
            
            // İlave Bilgiler
            $table->integer('estimated_employees')->nullable(); // Tahmini personel sayısı
            $table->text('notes')->nullable(); // Notlar
            
            $table->timestamps();
            $table->softDeletes();
            
            // İndeksler
            $table->index(['status']);
            $table->index(['type']);
            $table->index(['start_date', 'planned_end_date']);
            $table->index(['project_manager_id']);
            $table->index(['city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};