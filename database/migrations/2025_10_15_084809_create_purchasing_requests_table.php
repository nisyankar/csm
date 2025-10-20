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
        Schema::create('purchasing_requests', function (Blueprint $table) {
            $table->id();

            // Talep Bilgileri
            $table->string('request_code')->unique(); // Talep kodu (otomatik: PR-2025-0001)
            $table->string('title'); // Talep başlığı
            $table->text('description')->nullable(); // Açıklama

            // İlişkiler
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade'); // Talep eden
            $table->foreignId('project_id')->constrained()->onDelete('cascade'); // Proje
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null'); // Departman

            // Durum
            $table->enum('status', [
                'draft',      // Taslak
                'pending',    // Onay bekliyor
                'approved',   // Onaylandı
                'ordered',    // Sipariş verildi
                'delivered',  // Teslim edildi
                'cancelled',  // İptal edildi
                'rejected'    // Reddedildi
            ])->default('draft');

            // Aciliyet
            $table->enum('urgency', [
                'low',       // Düşük
                'normal',    // Normal
                'high',      // Yüksek
                'urgent'     // Acil
            ])->default('normal');

            // Kategori
            $table->enum('category', [
                'concrete',  // Beton
                'steel',     // Demir
                'general',   // Genel malzeme
                'equipment', // Ekipman
                'service',   // Hizmet
                'other'      // Diğer
            ])->default('general');

            // Tarihler
            $table->date('required_date')->nullable(); // İhtiyaç tarihi
            $table->timestamp('submitted_at')->nullable(); // Onaya gönderilme
            $table->timestamp('approved_at')->nullable(); // Onaylanma
            $table->timestamp('ordered_at')->nullable(); // Sipariş verilme
            $table->timestamp('delivered_at')->nullable(); // Teslim edilme

            // Onaylayan kişiler
            $table->foreignId('approved_by_supervisor')->nullable()->constrained('users')->onDelete('set null'); // Şef onayı
            $table->foreignId('approved_by_manager')->nullable()->constrained('users')->onDelete('set null'); // Yönetici onayı

            // Notlar
            $table->text('supervisor_notes')->nullable(); // Şef notları
            $table->text('manager_notes')->nullable(); // Yönetici notları
            $table->text('rejection_reason')->nullable(); // Red sebebi

            // Mali Bilgiler
            $table->decimal('estimated_total', 12, 2)->default(0); // Tahmini toplam tutar
            $table->decimal('actual_total', 12, 2)->default(0); // Gerçekleşen toplam tutar

            // Soft delete
            $table->softDeletes();
            $table->timestamps();

            // İndeksler
            $table->index(['status']);
            $table->index(['urgency']);
            $table->index(['category']);
            $table->index(['requested_by']);
            $table->index(['project_id']);
            $table->index(['required_date']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchasing_requests');
    }
};
