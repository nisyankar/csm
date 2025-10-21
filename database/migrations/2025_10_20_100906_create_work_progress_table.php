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
        Schema::create('work_progress', function (Blueprint $table) {
            $table->id();

            // İş Ataması İlişkisi
            $table->foreignId('assignment_id')->constrained('work_item_assignments')->onDelete('cascade');

            // Rapor Bilgileri
            $table->foreignId('reported_by')->constrained('employees')->onDelete('cascade');
            $table->date('report_date');
            $table->enum('shift', ['morning', 'afternoon', 'night', 'full_day'])->nullable();

            // İlerleme
            $table->decimal('completed_quantity', 12, 2); // Bu raporda tamamlanan miktar
            $table->decimal('total_completed_quantity', 12, 2); // Toplam tamamlanan
            $table->decimal('progress_percentage', 5, 2); // İlerleme yüzdesi

            // Kalite ve Performans
            $table->integer('quality_rating')->nullable(); // 1-5 arası kalite puanı
            $table->integer('worker_count')->nullable(); // Çalışan sayısı
            $table->decimal('hours_worked', 5, 2)->nullable(); // Çalışılan saat

            // Sorunlar ve Gecikmeler
            $table->json('issues')->nullable(); // Sorunlar (JSON array)
            $table->boolean('has_delay')->default(false);
            $table->text('delay_reason')->nullable();
            $table->integer('delay_hours')->nullable();

            // Fotoğraflar
            $table->json('photos')->nullable(); // Fotoğraf yolları (JSON array)

            // Hava Durumu
            $table->string('weather_condition', 50)->nullable(); // güneşli, yağmurlu, karlı
            $table->integer('temperature')->nullable(); // Sıcaklık

            // Malzeme Kullanımı
            $table->json('material_usage')->nullable(); // Kullanılan malzemeler (JSON)

            // Notlar
            $table->text('notes')->nullable();

            // Onay Süreci
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            // İndeksler
            $table->index(['assignment_id', 'report_date']);
            $table->index(['reported_by']);
            $table->index(['approval_status']);
            $table->index(['report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_progress');
    }
};
