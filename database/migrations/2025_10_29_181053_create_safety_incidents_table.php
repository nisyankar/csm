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
        Schema::create('safety_incidents', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');

            // Olay Bilgileri
            $table->date('incident_date');
            $table->time('incident_time')->nullable();
            $table->string('location', 255);
            $table->enum('incident_type', [
                'minor_injury',      // Küçük yaralanma
                'major_injury',      // Büyük yaralanma
                'near_miss',         // Ramak kala olay
                'property_damage',   // Mal hasarı
                'environmental',     // Çevresel olay
                'fatal'              // Ölümlü kaza
            ]);
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');

            // Açıklamalar
            $table->text('description'); // Olay açıklaması
            $table->text('immediate_actions')->nullable(); // Anında alınan aksiyonlar
            $table->text('root_cause')->nullable(); // Kök sebep analizi
            $table->text('corrective_actions')->nullable(); // Düzeltici faaliyetler
            $table->text('preventive_actions')->nullable(); // Önleyici faaliyetler

            // Ek Bilgiler (JSON)
            $table->json('witnesses')->nullable(); // Tanıklar listesi
            $table->json('photos')->nullable(); // Fotoğraflar
            $table->json('injured_body_parts')->nullable(); // Yaralanan vücut bölgeleri

            // İşlem Bilgileri
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('investigated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reported_at')->nullable();
            $table->timestamp('investigation_completed_at')->nullable();

            // Durum
            $table->enum('status', [
                'reported',      // Raporlandı
                'investigating', // İnceleniyor
                'resolved',      // Çözüldü
                'closed'         // Kapatıldı
            ])->default('reported');

            // Etki Analizi
            $table->boolean('medical_treatment_required')->default(false);
            $table->boolean('work_stopped')->default(false);
            $table->integer('days_lost')->default(0); // İş kaybı günü
            $table->decimal('cost_estimate', 12, 2)->nullable(); // Tahmini maliyet

            // Otorite Bildirimi
            $table->boolean('reported_to_authority')->default(false);
            $table->date('authority_report_date')->nullable();
            $table->string('authority_reference_number')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['project_id', 'incident_date']);
            $table->index(['incident_type', 'severity']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safety_incidents');
    }
};