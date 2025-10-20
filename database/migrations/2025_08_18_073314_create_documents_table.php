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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            
            // Ana İlişkiler
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Dosya Bilgileri
            $table->string('title'); // Dosya başlığı
            $table->text('description')->nullable(); // Dosya açıklaması
            $table->string('file_name'); // Orijinal dosya adı
            $table->string('file_path'); // Sunucudaki dosya yolu
            $table->string('file_extension'); // Dosya uzantısı
            $table->integer('file_size'); // Dosya boyutu (byte)
            $table->string('mime_type'); // MIME türü
            
            // Dosya Hash (Güvenlik ve Bütünlük)
            $table->string('file_hash')->nullable(); // MD5 veya SHA256 hash
            
            // Evrak Kategorisi
            $table->enum('document_type', [
                'identity',         // Kimlik belgeleri
                'education',        // Eğitim belgeleri
                'health',           // Sağlık raporları
                'insurance',        // Sigorta belgeleri
                'contract',         // İş sözleşmesi
                'certificate',      // Sertifikalar
                'photo',            // Fotoğraflar
                'medical_report',   // Tıbbi raporlar
                'safety_training',  // İş güvenliği eğitimleri
                'bank_info',        // Banka bilgileri
                'reference',        // Referans mektupları
                'other'             // Diğer
            ]);
            
            // Evrak Alt Kategorisi
            $table->string('document_subtype')->nullable(); // Kimlik kartı, ehliyet, diploma vb.
            
            // Geçerlilik Bilgileri
            $table->date('issue_date')->nullable(); // Düzenlenme tarihi
            $table->date('expiry_date')->nullable(); // Son geçerlilik tarihi
            $table->boolean('has_expiry')->default(false); // Son kullanma tarihi var mı?
            $table->integer('expiry_warning_days')->nullable(); // Kaç gün önceden uyarı
            
            // Durum ve Onay
            $table->enum('status', [
                'pending',      // Beklemede
                'verified',     // Doğrulandı
                'rejected',     // Reddedildi
                'expired',      // Süresi doldu
                'archived'      // Arşivlendi
            ])->default('pending');
            
            // Doğrulama Bilgileri
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Zorunluluk ve Öncelik
            $table->boolean('is_mandatory')->default(false); // Zorunlu evrak mı?
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            
            // Gizlilik ve Erişim
            $table->enum('privacy_level', [
                'public',       // Herkes görebilir
                'internal',     // Şirket içi
                'hr_only',      // Sadece İK
                'confidential'  // Gizli
            ])->default('internal');
            
            $table->json('access_permissions')->nullable(); // Özel erişim izinleri
            
            // Versiyonlama
            $table->integer('version')->default(1); // Dosya versiyonu
            $table->foreignId('parent_document_id')->nullable()->constrained('documents')->onDelete('set null');
            $table->boolean('is_latest_version')->default(true);
            
            // Yasal ve Uyumluluk
            $table->boolean('requires_signature')->default(false); // İmza gerekli mi?
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_hash')->nullable(); // Dijital imza hash'i
            
            // Otomatik İşlemler
            $table->boolean('auto_reminder_enabled')->default(false); // Otomatik hatırlatma
            $table->json('reminder_schedule')->nullable(); // Hatırlatma programı
            $table->timestamp('last_reminder_sent')->nullable();
            
            // Arşivleme
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('archived_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('archive_reason')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable(); // Ek bilgiler (OCR sonuçları, etiketler vb.)
            $table->json('tags')->nullable(); // Etiketler
            
            // İstatistik
            $table->integer('download_count')->default(0); // İndirilme sayısı
            $table->timestamp('last_downloaded_at')->nullable();
            $table->foreignId('last_downloaded_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Backup ve Recovery
            $table->boolean('is_backed_up')->default(false);
            $table->timestamp('backed_up_at')->nullable();
            $table->string('backup_location')->nullable();
            
            $table->timestamps();
            $table->softDeletes(); // Soft delete
            
            // İndeksler
            $table->index(['employee_id', 'document_type']);
            $table->index(['document_type', 'status']);
            $table->index(['expiry_date', 'has_expiry']);
            $table->index(['status', 'priority']);
            $table->index(['is_mandatory', 'status']);
            $table->index(['privacy_level']);
            $table->index(['version', 'is_latest_version']);
            $table->index(['file_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};