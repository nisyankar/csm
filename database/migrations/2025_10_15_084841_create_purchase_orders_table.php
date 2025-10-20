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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('purchasing_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_quotation_id')->nullable()->constrained()->onDelete('set null');

            // Sipariş Bilgileri
            $table->string('order_number')->unique(); // Sipariş numarası (PO-2025-0001)
            $table->date('order_date'); // Sipariş tarihi
            $table->date('expected_delivery_date')->nullable(); // Beklenen teslimat tarihi

            // Mali Bilgiler
            $table->decimal('subtotal', 12, 2)->default(0); // Ara toplam
            $table->decimal('tax_amount', 12, 2)->default(0); // KDV tutarı
            $table->decimal('discount_amount', 12, 2)->default(0); // İndirim tutarı
            $table->decimal('shipping_cost', 12, 2)->default(0); // Nakliye ücreti
            $table->decimal('total_amount', 12, 2)->default(0); // Toplam tutar

            // Ödeme Bilgileri
            $table->string('payment_method')->nullable(); // Ödeme yöntemi
            $table->integer('payment_term_days')->default(0); // Vade
            $table->date('payment_due_date')->nullable(); // Ödeme vadesi
            $table->enum('payment_status', [
                'pending',   // Bekliyor
                'partial',   // Kısmi ödendi
                'paid',      // Ödendi
                'overdue'    // Vadesi geçti
            ])->default('pending');

            // Durum
            $table->enum('status', [
                'draft',      // Taslak
                'approved',   // Onaylandı
                'sent',       // Tedarikçiye gönderildi
                'confirmed',  // Tedarikçi onayladı
                'in_progress', // Hazırlanıyor
                'shipped',    // Kargoya verildi
                'delivered',  // Teslim edildi
                'completed',  // Tamamlandı
                'cancelled'   // İptal edildi
            ])->default('draft');

            // Onay
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // Teslimat
            $table->text('delivery_address')->nullable(); // Teslimat adresi
            $table->string('delivery_contact')->nullable(); // Teslimat irtibat
            $table->text('special_instructions')->nullable(); // Özel talimatlar

            // Notlar
            $table->text('notes')->nullable(); // Notlar
            $table->text('cancellation_reason')->nullable(); // İptal nedeni

            // Dosyalar
            $table->string('attachment_path')->nullable(); // Sipariş dosyası

            // Soft delete
            $table->softDeletes();
            $table->timestamps();

            // İndeksler
            $table->index(['purchasing_request_id']);
            $table->index(['supplier_id']);
            $table->index(['status']);
            $table->index(['payment_status']);
            $table->index(['order_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
