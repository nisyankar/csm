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
        Schema::create('leave_calculations', function (Blueprint $table) {
            $table->id();
            
            // İlişkiler
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('calculated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Hesaplama Dönemi
            $table->date('calculation_date'); // Hesaplama yapılan tarih
            $table->integer('leave_year'); // İzin yılı (2024, 2025...)
            $table->date('period_start'); // Dönem başlangıcı
            $table->date('period_end'); // Dönem bitişi
            
            // Personel Bilgileri (Hesaplama anındaki durum)
            $table->date('employee_start_date'); // İşe başlama tarihi
            $table->date('employee_birth_date'); // Doğum tarihi
            $table->integer('employee_age_at_calculation'); // Hesaplama anındaki yaş
            $table->boolean('is_underground_worker'); // Yer altı işçisi mi?
            $table->string('employee_category', 50); // Personel kategorisi
            
            // Kıdem Hesaplaması
            $table->integer('total_tenure_days'); // Toplam kıdem günü
            $table->integer('tenure_years'); // Kıdem yılı
            $table->integer('tenure_months'); // Kıdem ayı
            $table->integer('previous_employer_days')->default(0); // Önceki işveren kıdemi
            
            // İzin Hesaplama Detayları
            $table->integer('base_annual_leave_days'); // Temel yıllık izin hakkı
            $table->integer('age_bonus_days')->default(0); // Yaş bonusu günleri
            $table->integer('underground_bonus_days')->default(0); // Yer altı bonusu
            $table->integer('category_bonus_days')->default(0); // Kategori bonusu
            $table->integer('seniority_bonus_days')->default(0); // Kıdem bonusu
            $table->integer('total_annual_leave_days'); // Toplam yıllık izin hakkı
            
            // Önceki Dönem Bilgileri
            $table->integer('previous_year_remaining')->default(0); // Önceki yıldan kalan
            $table->integer('carried_forward_days')->default(0); // Devredilen günler
            $table->date('carry_forward_expiry')->nullable(); // Devir son kullanma
            
            // Kullanım Bilgileri
            $table->integer('used_annual_days')->default(0); // Kullanılan yıllık izin
            $table->integer('used_sick_days')->default(0); // Kullanılan hastalık izni
            $table->integer('used_other_days')->default(0); // Diğer izinler
            $table->integer('remaining_annual_days'); // Kalan yıllık izin
            
            // Hesaplama Parametreleri (JSON)
            $table->json('calculation_parameters'); // Kullanılan parametreler
            $table->json('applied_rules'); // Uygulanan kurallar
            $table->json('special_conditions'); // Özel durumlar
            
            // Hesaplama Durumu
            $table->enum('calculation_type', [
                'annual',      // Yıllık hesaplama
                'monthly',     // Aylık güncelleme
                'manual',      // Manuel düzeltme
                'migration',   // Veri geçişi
                'adjustment'   // Düzeltme
            ])->default('annual');
            
            $table->enum('status', [
                'draft',       // Taslak
                'calculated',  // Hesaplandı
                'approved',    // Onaylandı
                'applied',     // Uygulandı
                'cancelled'    // İptal
            ])->default('calculated');
            
            // Doğrulama ve Onay
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamp('applied_at')->nullable(); // Employee tablosuna uygulanma
            
            // Hata ve Uyarılar
            $table->json('calculation_warnings')->nullable(); // Uyarılar
            $table->json('calculation_errors')->nullable(); // Hatalar
            $table->boolean('has_manual_adjustments')->default(false); // Manuel düzeltme var mı?
            $table->text('adjustment_notes')->nullable();
            
            // Karşılaştırma
            $table->integer('previous_calculation_id')->nullable(); // Önceki hesaplama ID
            $table->json('changes_from_previous')->nullable(); // Öncekinden farklar
            
            $table->timestamps();
            
            // İndeksler
            $table->index(['employee_id', 'leave_year']);
            $table->index(['calculation_date']);
            $table->index(['status']);
            $table->index(['leave_year', 'calculation_type']);
            $table->index(['employee_id', 'calculation_date']);
            
            // Unique constraint - aynı personel aynı izin yılında sadece bir aktif hesaplama
            $table->unique(['employee_id', 'leave_year', 'status'], 'unique_active_calculation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_calculations');
    }
};