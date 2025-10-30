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
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('assessed_by')->constrained('users')->onDelete('cascade');

            // Değerlendirme Bilgileri
            $table->string('assessment_title');
            $table->string('work_activity'); // İş aktivitesi / Faaliyet
            $table->string('location');
            $table->date('assessment_date');
            $table->text('description')->nullable(); // Çalışma açıklaması

            // Risk Değerlendirme Matrisi (JSON)
            // Her risk için: tehlike, risk, olasılık (1-5), şiddet (1-5), risk seviyesi, kontrol tedbirleri, kalan risk
            $table->json('risk_items')->nullable();
            /* Örnek yapı:
            [{
                "hazard": "Yüksekten düşme",
                "risk": "Yaralanma/Ölüm",
                "likelihood": 3,
                "severity": 5,
                "risk_level": "high",
                "current_controls": ["Korkuluk", "Emniyet kemeri"],
                "additional_controls": ["Düzenli kontrol"],
                "residual_likelihood": 2,
                "residual_severity": 4,
                "residual_risk_level": "medium"
            }]
            */

            // Genel Risk Seviyesi
            $table->enum('overall_risk_level', [
                'low',      // 1-5: Düşük risk
                'medium',   // 6-10: Orta risk
                'high',     // 11-15: Yüksek risk
                'critical'  // 16-25: Kritik risk
            ])->default('medium');

            // Kontrol Tedbirleri
            $table->text('control_measures')->nullable(); // Genel kontrol tedbirleri
            $table->text('emergency_procedures')->nullable(); // Acil durum prosedürleri
            $table->text('training_requirements')->nullable(); // Eğitim gereksinimleri

            // Gerekli Ekipman ve KKD (JSON)
            $table->json('required_ppe')->nullable(); // [{ type, specification }]
            $table->json('required_equipment')->nullable(); // [{ equipment, quantity }]

            // Sorumlu Personel (JSON)
            $table->json('responsible_persons')->nullable(); // [{ role, name, employee_id }]

            // Geçerlilik
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->date('review_date')->nullable(); // Gözden geçirme tarihi

            // Durum
            $table->enum('status', [
                'draft',     // Taslak
                'submitted', // Onaya gönderildi
                'approved',  // Onaylandı
                'active',    // Aktif
                'expired',   // Süresi doldu
                'archived'   // Arşivlendi
            ])->default('draft');

            // Onay
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // Notlar
            $table->text('notes')->nullable();

            // Revizyon
            $table->integer('revision_number')->default(1);
            $table->foreignId('previous_version_id')->nullable()->constrained('risk_assessments')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['project_id', 'status']);
            $table->index(['assessment_date']);
            $table->index(['overall_risk_level']);
            $table->index(['valid_from', 'valid_until']);
            $table->index(['review_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_assessments');
    }
};
