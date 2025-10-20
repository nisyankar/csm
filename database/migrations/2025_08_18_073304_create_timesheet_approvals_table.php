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
        Schema::create('timesheet_approvals', function (Blueprint $table) {
            $table->id();
            
            // Ana İlişkiler
            $table->foreignId('timesheet_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('employees')->onDelete('cascade');
            
            // Onay Seviyesi
            $table->enum('approval_level', [
                'first',    // 1. Onay (Direkt Yönetici)
                'second',   // 2. Onay (Üst Yönetici/İK)
                'final'     // Son Onay
            ]);
            
            // Onay Durumu
            $table->enum('status', [
                'pending',   // Beklemede
                'approved',  // Onaylandı
                'rejected',  // Reddedildi
                'cancelled'  // İptal edildi
            ])->default('pending');
            
            // Onay Detayları
            $table->timestamp('approved_at')->nullable(); // Onay tarihi
            $table->text('approval_notes')->nullable(); // Onay notları
            $table->text('rejection_reason')->nullable(); // Red sebebi
            
            // Onay Öncesi ve Sonrası Değerler (Değişiklik takibi için)
            $table->json('original_data')->nullable(); // Onay öncesi veri
            $table->json('approved_data')->nullable(); // Onaylanan veri
            
            // Onay Süreci Bilgileri
            $table->timestamp('assigned_at'); // Onaya atanma tarihi
            $table->timestamp('deadline')->nullable(); // Onay deadline'ı
            $table->boolean('is_automatic')->default(false); // Otomatik onay mı?
            
            // Bildirim Durumu
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            
            // Yetki Delegasyonu
            $table->foreignId('delegated_from')->nullable()->constrained('employees')->onDelete('set null');
            $table->text('delegation_reason')->nullable();
            
            // IP ve Lokasyon (Güvenlik için)
            $table->string('approval_ip')->nullable();
            $table->string('approval_location')->nullable();
            
            $table->timestamps();
            
            // İndeksler
            $table->index(['timesheet_id', 'approval_level']);
            $table->index(['approver_id', 'status']);
            $table->index(['status', 'assigned_at']);
            $table->index(['approved_at']);
            
            // Unique constraint (aynı puantaj için aynı seviyede birden fazla onay olamaz)
            $table->unique(['timesheet_id', 'approval_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_approvals');
    }
};