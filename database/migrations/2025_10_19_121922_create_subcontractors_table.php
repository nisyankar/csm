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
        // Taşeron Kategorileri
        Schema::create('subcontractor_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Taşeronlar
        Schema::create('subcontractors', function (Blueprint $table) {
            $table->id();

            // Firma Bilgileri
            $table->string('company_name');
            $table->string('trade_title')->nullable(); // Ticari ünvan
            $table->string('tax_office', 100);
            $table->string('tax_number', 20);

            // Adres ve İletişim
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website')->nullable();

            // Yetkili Kişi
            $table->string('authorized_person', 100)->nullable();
            $table->string('authorized_title', 100)->nullable(); // Yetkili ünvanı
            $table->string('authorized_phone', 20)->nullable();
            $table->string('authorized_email', 100)->nullable();

            // Banka Bilgileri
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_branch', 100)->nullable();
            $table->string('iban', 34)->nullable();

            // Kategori
            $table->foreignId('category_id')->nullable()->constrained('subcontractor_categories')->onDelete('set null');

            // Performans ve Durum
            $table->decimal('rating', 3, 2)->default(0); // 0.00 - 5.00
            $table->integer('completed_projects')->default(0);
            $table->decimal('total_contract_value', 15, 2)->default(0);

            // Durum
            $table->enum('status', ['active', 'inactive', 'blacklisted'])->default('active');
            $table->boolean('is_approved')->default(false); // Onaylı tedarikçi

            // Notlar
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['status']);
            $table->index(['category_id']);
            $table->index(['is_approved']);
            $table->index(['company_name']);
            $table->index(['tax_number']);
        });

        // Taşeron Sertifikaları ve Belgeler
        Schema::create('subcontractor_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcontractor_id')->constrained()->onDelete('cascade');

            $table->enum('certificate_type', [
                'kapasite_raporu',      // Kapasite Raporu
                'deneyim_belgesi',      // İş Deneyim Belgesi
                'iso_9001',             // ISO 9001 Kalite Yönetimi
                'iso_14001',            // ISO 14001 Çevre Yönetimi
                'iso_45001',            // ISO 45001 İş Sağlığı ve Güvenliği
                'sgk_borcu_yok',        // SGK Borcu Yoktur
                'vergi_borcu_yok',      // Vergi Borcu Yoktur
                'ticaret_sicil',        // Ticaret Sicil Gazetesi
                'imza_sirküleri',       // İmza Sirküleri
                'other'                 // Diğer
            ]);

            $table->string('certificate_name', 100);
            $table->string('certificate_number', 50)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('issuing_authority', 100)->nullable(); // Belgeyi veren kurum
            $table->string('document_path')->nullable();
            $table->enum('status', ['valid', 'expired', 'pending'])->default('valid');
            $table->text('notes')->nullable();

            $table->timestamps();

            // İndeksler
            $table->index(['subcontractor_id']);
            $table->index(['certificate_type']);
            $table->index(['status']);
            $table->index(['expiry_date']);
        });

        // Taşeron Performans Değerlendirmeleri
        Schema::create('subcontractor_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcontractor_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');

            // Değerlendirme Kriterleri (1-5 yıldız)
            $table->decimal('quality_score', 3, 2)->nullable(); // Kalite
            $table->decimal('timeline_score', 3, 2)->nullable(); // Süre
            $table->decimal('safety_score', 3, 2)->nullable(); // İş Güvenliği
            $table->decimal('communication_score', 3, 2)->nullable(); // İletişim
            $table->decimal('cost_score', 3, 2)->nullable(); // Maliyet
            $table->decimal('overall_score', 3, 2); // Genel Puan (ortalaması)

            // Yorumlar
            $table->text('strengths')->nullable(); // Güçlü yönler
            $table->text('weaknesses')->nullable(); // Zayıf yönler
            $table->text('recommendations')->nullable(); // Tavsiyeler
            $table->text('comments')->nullable(); // Genel yorum

            // Kim, Ne Zaman
            $table->foreignId('rated_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('rated_at');

            // Onay
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            // İndeksler
            $table->index(['subcontractor_id']);
            $table->index(['project_id']);
            $table->index(['overall_score']);
            $table->index(['rated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcontractor_ratings');
        Schema::dropIfExists('subcontractor_certifications');
        Schema::dropIfExists('subcontractors');
        Schema::dropIfExists('subcontractor_categories');
    }
};
