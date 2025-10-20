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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');

            // Teslimat Bilgileri
            $table->string('delivery_number')->unique(); // Teslimat numarası
            $table->date('delivery_date'); // Teslimat tarihi
            $table->time('delivery_time')->nullable(); // Teslimat saati

            // İrsaliye
            $table->string('waybill_number')->nullable(); // İrsaliye numarası
            $table->date('waybill_date')->nullable(); // İrsaliye tarihi
            $table->string('waybill_file_path')->nullable(); // İrsaliye dosyası

            // Fatura
            $table->string('invoice_number')->nullable(); // Fatura numarası
            $table->date('invoice_date')->nullable(); // Fatura tarihi
            $table->decimal('invoice_amount', 12, 2)->nullable(); // Fatura tutarı
            $table->string('invoice_file_path')->nullable(); // Fatura dosyası

            // Teslimat Durumu
            $table->enum('status', [
                'scheduled',  // Planlandı
                'in_transit', // Yolda
                'arrived',    // Geldi
                'partial',    // Kısmi teslim
                'completed',  // Tamamlandı
                'rejected',   // Reddedildi
                'returned'    // İade edildi
            ])->default('scheduled');

            // Teslim Bilgileri
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null'); // Teslim alan
            $table->timestamp('received_at')->nullable(); // Teslim alma zamanı
            $table->string('receiver_name')->nullable(); // Teslim alan adı (eğer user değilse)

            // Kalite Kontrolü
            $table->enum('quality_check', [
                'pending',   // Bekliyor
                'passed',    // Geçti
                'failed',    // Başarısız
                'partial'    // Kısmi
            ])->default('pending');
            $table->text('quality_notes')->nullable(); // Kalite notları

            // Teslimat Detayları
            $table->text('delivery_address')->nullable(); // Teslimat adresi
            $table->string('driver_name')->nullable(); // Sürücü adı
            $table->string('vehicle_plate')->nullable(); // Araç plakası
            $table->string('driver_phone')->nullable(); // Sürücü telefon

            // Miktar Bilgisi
            $table->integer('items_count')->default(0); // Kalem sayısı
            $table->boolean('is_complete')->default(false); // Tam teslimat mı?
            $table->text('missing_items')->nullable(); // Eksik kalemler (JSON)

            // Notlar
            $table->text('notes')->nullable(); // Genel notlar
            $table->text('rejection_reason')->nullable(); // Red nedeni
            $table->text('damage_report')->nullable(); // Hasar raporu

            // Fotoğraflar
            $table->json('photos')->nullable(); // Teslimat fotoğrafları

            // Soft delete
            $table->softDeletes();
            $table->timestamps();

            // İndeksler
            $table->index(['purchase_order_id']);
            $table->index(['status']);
            $table->index(['delivery_date']);
            $table->index(['waybill_number']);
            $table->index(['invoice_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
