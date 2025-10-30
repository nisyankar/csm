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
        Schema::create('equipment_maintenance', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('equipment_id')->constrained('equipments')->onDelete('cascade');

            // Bakım Bilgileri
            $table->string('maintenance_code')->unique(); // Bakım kodu (BKM-001)
            $table->enum('type', [
                'routine',          // Rutin bakım
                'preventive',       // Önleyici bakım
                'corrective',       // Onarım bakımı
                'breakdown',        // Arıza
                'inspection',       // Muayene
                'calibration',      // Kalibrasyon
                'overhaul',         // Kapsamlı revizyon
                'seasonal',         // Sezonluk bakım
                'other'             // Diğer
            ]);

            $table->date('maintenance_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('duration_hours', 8, 2)->nullable();

            // Bakım Detayları
            $table->text('description'); // Bakım açıklaması
            $table->text('findings')->nullable(); // Bulgular
            $table->json('work_performed')->nullable(); // Yapılan işler listesi
            $table->json('parts_replaced')->nullable(); // [{ part_name, quantity, cost, supplier }]

            // Servis Sağlayıcı
            $table->enum('service_provider', [
                'internal',     // İç ekip
                'external'      // Dış servis
            ])->default('internal');

            $table->string('service_company')->nullable(); // Servis şirketi
            $table->string('technician_name')->nullable();
            $table->string('technician_phone')->nullable();

            // Maliyetler
            $table->decimal('labor_cost', 10, 2)->default(0); // İşçilik maliyeti
            $table->decimal('parts_cost', 10, 2)->default(0); // Parça maliyeti
            $table->decimal('external_service_cost', 10, 2)->default(0); // Dış servis maliyeti
            $table->decimal('total_cost', 10, 2)->default(0); // Toplam maliyet

            // Sayaç Bilgisi
            $table->integer('meter_reading')->nullable(); // Bakım sırasındaki sayaç değeri

            // Durum
            $table->enum('status', [
                'scheduled',    // Planlandı
                'in_progress',  // Devam ediyor
                'completed',    // Tamamlandı
                'cancelled'     // İptal edildi
            ])->default('scheduled');

            // Sonraki Bakım
            $table->date('next_maintenance_date')->nullable();
            $table->integer('next_maintenance_meter')->nullable();

            // Belgeler
            $table->json('documents')->nullable(); // Bakım raporları, faturalar
            $table->json('photos')->nullable(); // Fotoğraflar

            // Garanti
            $table->boolean('under_warranty')->default(false);
            $table->string('warranty_claim_number')->nullable();

            // Notlar ve Öneriler
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable(); // Öneriler

            // Finansal Entegrasyon
            $table->boolean('cost_recorded')->default(false);
            $table->foreignId('financial_transaction_id')->nullable()->constrained()->onDelete('set null');

            // Kim Oluşturdu
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['equipment_id', 'maintenance_date']);
            $table->index(['type', 'status']);
            $table->index(['next_maintenance_date']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_maintenance');
    }
};
