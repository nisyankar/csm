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
        Schema::create('safety_inspections', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('inspector_id')->constrained('users')->onDelete('cascade');

            // Denetim Bilgileri
            $table->string('inspection_title');
            $table->enum('inspection_type', [
                'daily',           // Günlük denetim
                'weekly',          // Haftalık denetim
                'monthly',         // Aylık denetim
                'quarterly',       // Üç aylık denetim
                'pre_operation',   // Operasyon öncesi
                'post_incident',   // Olay sonrası
                'special',         // Özel denetim
                'audit'            // Dış denetim/audit
            ]);

            $table->date('inspection_date');
            $table->time('inspection_time')->nullable();
            $table->string('location');
            $table->string('area_inspected')->nullable(); // Denetlenen alan

            // Kontrol Listesi ve Bulgular (JSON)
            $table->json('checklist')->nullable(); // [{ item, category, status, notes, photo, requires_action }]
            $table->text('findings')->nullable(); // Genel bulgular
            $table->text('recommendations')->nullable(); // Öneriler
            $table->json('photos')->nullable(); // Fotoğraflar

            // Genel Değerlendirme
            $table->enum('overall_status', [
                'passed',           // Başarılı
                'passed_with_notes', // Notlarla geçti
                'requires_action',  // Aksiyon gerekli
                'failed'            // Başarısız
            ])->default('passed');

            // Puanlama (0-100)
            $table->decimal('score', 5, 2)->nullable();
            $table->integer('items_checked')->default(0);
            $table->integer('items_passed')->default(0);
            $table->integer('items_failed')->default(0);

            // Aksiyon Maddeleri (JSON)
            $table->json('action_items')->nullable(); // [{ description, priority, assigned_to, due_date, status }]

            // Durum
            $table->enum('status', [
                'scheduled',   // Planlandı
                'in_progress', // Devam ediyor
                'completed',   // Tamamlandı
                'cancelled'    // İptal edildi
            ])->default('scheduled');

            // Takip
            $table->date('next_inspection_date')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();

            // Notlar
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['project_id', 'inspection_date']);
            $table->index(['inspection_type', 'status']);
            $table->index(['overall_status']);
            $table->index(['next_inspection_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safety_inspections');
    }
};