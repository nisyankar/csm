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
        Schema::create('dwg_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');

            // Dosya bilgileri
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size'); // bytes
            $table->string('mime_type')->nullable();

            // Import tipi (ne import ediliyor)
            $table->enum('import_type', ['comprehensive', 'structures_only', 'floors_only', 'units_only'])
                  ->default('comprehensive')
                  ->comment('comprehensive: Tüm yapı/kat/birim, structures_only: Sadece yapılar, floors_only: Sadece katlar, units_only: Sadece birimler');

            // İşlem durumu
            $table->enum('status', ['pending', 'processing', 'ready_for_review', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('processing_duration_seconds')->nullable(); // işlem süresi

            // Parse edilen veriler (JSON)
            $table->json('parsed_data')->nullable(); // DWG'den çıkan raw data
            $table->json('detected_layers')->nullable(); // Tespit edilen layer'lar ve tahminler
            $table->json('layer_mappings')->nullable(); // Kullanıcının yaptığı layer-yapı eşleştirmeleri
            $table->json('created_structures')->nullable(); // Oluşturulan kayıtların ID'leri

            // İstatistikler
            $table->integer('structures_count')->default(0); // Oluşturulan yapı sayısı
            $table->integer('floors_count')->default(0); // Oluşturulan kat sayısı
            $table->integer('units_count')->default(0); // Oluşturulan birim sayısı

            // Hata yönetimi
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable(); // Detaylı hata bilgileri

            // Notlar
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexler
            $table->index('project_id');
            $table->index('status');
            $table->index('uploaded_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dwg_imports');
    }
};
