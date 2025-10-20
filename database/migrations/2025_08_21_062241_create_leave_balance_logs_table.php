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
        Schema::create('leave_balance_logs', function (Blueprint $table) {
            $table->id();
            
            // İlişkiler
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_request_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('leave_calculation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Değişiklik Detayları
            $table->enum('change_type', [
                'grant',           // İzin hakkı verme
                'use',             // İzin kullanma
                'return',          // İzin iadesi
                'adjustment',      // Düzeltme
                'carry_forward',   // Devir
                'expiry',          // Süre dolumu
                'migration',       // Veri geçişi
                'cancellation'     // İptal
            ]);
            
            $table->string('leave_type', 50); // annual, sick, etc.
            $table->integer('change_amount'); // Değişiklik miktarı (+/-)
            $table->string('change_unit', 10)->default('days'); // days, hours
            
            // Bakiye Durumu
            $table->integer('balance_before'); // Değişiklik öncesi bakiye
            $table->integer('balance_after'); // Değişiklik sonrası bakiye
            $table->integer('total_entitled'); // Toplam hak edilen
            $table->integer('total_used'); // Toplam kullanılan
            
            // İşlem Detayları
            $table->date('effective_date'); // Etkin tarih
            $table->text('reason'); // Değişiklik sebebi
            $table->text('description')->nullable(); // Detaylı açıklama
            $table->json('additional_data')->nullable(); // Ek veriler
            
            // Reversal/Geri Alma
            $table->boolean('is_reversed')->default(false); // Geri alındı mı?
            $table->foreignId('reversed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->foreignId('reversal_log_id')->nullable()->constrained('leave_balance_logs')->onDelete('set null');
            
            // Sistem Bilgileri
            $table->string('source_system', 50)->default('web'); // web, api, import, system
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('request_data')->nullable(); // Request verileri
            
            // Doğrulama
            $table->boolean('is_verified')->default(true); // Doğrulandı mı?
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            
            // İndeksler
            $table->index(['employee_id', 'effective_date']);
            $table->index(['leave_request_id']);
            $table->index(['change_type', 'leave_type']);
            $table->index(['effective_date']);
            $table->index(['changed_by']);
            $table->index(['is_reversed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balance_logs');
    }
};