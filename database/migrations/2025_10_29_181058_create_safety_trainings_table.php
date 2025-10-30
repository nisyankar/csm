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
        Schema::create('safety_trainings', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('project_id')->constrained()->onDelete('cascade');

            // Eğitim Bilgileri
            $table->string('training_title');
            $table->enum('training_type', [
                'orientation',          // İş başı eğitimi
                'isg_basic',            // Temel İSG
                'fire_safety',          // Yangın güvenliği
                'first_aid',            // İlk yardım
                'height_work',          // Yüksekte çalışma
                'confined_space',       // Kapalı alan
                'crane_operation',      // Vinç operatörü
                'electrical_safety',    // Elektrik güvenliği
                'chemical_handling',    // Kimyasal madde kullanımı
                'emergency_response',   // Acil durum müdahale
                'excavation',           // Kazı çalışmaları
                'scaffolding',          // İskele kurma
                'fall_protection',      // Düşme koruması
                'ppe_usage',            // KKD kullanımı
                'other'                 // Diğer
            ]);

            // Eğitmen ve Tarih
            $table->string('trainer_name')->nullable();
            $table->string('trainer_company')->nullable();
            $table->date('training_date');
            $table->time('start_time')->nullable();
            $table->decimal('duration_hours', 5, 2)->default(0); // Süre (saat)
            $table->string('location');

            // İçerik
            $table->text('description')->nullable();
            $table->text('objectives')->nullable(); // Eğitim hedefleri
            $table->json('topics')->nullable(); // Konu başlıkları

            // Katılımcılar ve Materyaller
            $table->json('attendees')->nullable(); // [{ employee_id, name, attendance, test_score, passed }]
            $table->json('materials')->nullable(); // Eğitim materyalleri (doküman path'leri)

            // Sertifika
            $table->boolean('certificate_issued')->default(false);
            $table->date('certificate_expiry_date')->nullable();
            $table->string('certificate_number')->nullable();

            // Test ve Değerlendirme
            $table->boolean('test_conducted')->default(false);
            $table->decimal('pass_score', 5, 2)->nullable(); // Geçme notu
            $table->json('test_results')->nullable(); // Test sonuçları

            // Durum
            $table->enum('status', [
                'planned',    // Planlandı
                'completed',  // Tamamlandı
                'cancelled'   // İptal edildi
            ])->default('planned');

            // Kim Oluşturdu
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['project_id', 'training_date']);
            $table->index(['training_type', 'status']);
            $table->index(['certificate_expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safety_trainings');
    }
};