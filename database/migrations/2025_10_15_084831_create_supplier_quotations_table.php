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
        Schema::create('supplier_quotations', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('purchasing_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');

            // Teklif Bilgileri
            $table->string('quotation_number')->nullable(); // Teklif numarası (tedarikçinin)
            $table->date('quotation_date')->nullable(); // Teklif tarihi
            $table->date('valid_until')->nullable(); // Geçerlilik tarihi

            // Teklif Kalemleri (JSON olarak tutulabilir veya ayrı tablo)
            $table->json('items')->nullable(); // [{item_id, unit_price, total_price, notes}]

            // Mali Bilgiler
            $table->decimal('subtotal', 12, 2)->default(0); // Ara toplam
            $table->decimal('tax_rate', 5, 2)->default(0); // KDV oranı (%)
            $table->decimal('tax_amount', 12, 2)->default(0); // KDV tutarı
            $table->decimal('discount_rate', 5, 2)->default(0); // İndirim oranı (%)
            $table->decimal('discount_amount', 12, 2)->default(0); // İndirim tutarı
            $table->decimal('shipping_cost', 12, 2)->default(0); // Nakliye ücreti
            $table->decimal('total_amount', 12, 2)->default(0); // Toplam tutar

            // Teslimat
            $table->integer('delivery_days')->default(0); // Teslimat süresi (gün)
            $table->text('delivery_terms')->nullable(); // Teslimat koşulları
            $table->text('payment_terms')->nullable(); // Ödeme koşulları

            // Durum
            $table->enum('status', [
                'draft',      // Taslak
                'submitted',  // Gönderildi
                'under_review', // İnceleniyor
                'selected',   // Seçildi
                'rejected',   // Reddedildi
                'expired'     // Süresi doldu
            ])->default('draft');

            // Değerlendirme
            $table->integer('rating')->nullable(); // Puan (1-5)
            $table->text('notes')->nullable(); // Notlar
            $table->text('rejection_reason')->nullable(); // Red nedeni

            // Dosya
            $table->string('attachment_path')->nullable(); // Teklif dosyası

            // Seçim
            $table->boolean('is_selected')->default(false); // Seçildi mi?
            $table->timestamp('selected_at')->nullable(); // Seçilme tarihi
            $table->foreignId('selected_by')->nullable()->constrained('users')->onDelete('set null');

            // Soft delete
            $table->softDeletes();
            $table->timestamps();

            // İndeksler
            $table->index(['purchasing_request_id']);
            $table->index(['supplier_id']);
            $table->index(['status']);
            $table->index(['is_selected']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_quotations');
    }
};
