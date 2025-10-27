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
        Schema::create('unit_sales', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_unit_id')->constrained()->onDelete('cascade'); // Hangi daire/birim
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');

            // Satış Bilgileri
            $table->string('sale_number')->unique(); // Satış sözleşme numarası
            $table->enum('sale_type', [
                'reservation',    // Rezervasyon
                'sale',          // Kesin Satış
                'presale'        // Ön Satış
            ])->default('reservation');

            // Fiyatlandırma
            $table->decimal('list_price', 15, 2); // Liste fiyatı
            $table->decimal('discount_amount', 15, 2)->default(0); // İndirim tutarı
            $table->decimal('discount_percentage', 5, 2)->default(0); // İndirim yüzdesi
            $table->decimal('final_price', 15, 2); // Nihai satış fiyatı
            $table->string('currency', 3)->default('TRY'); // Para birimi

            // Ödeme Planı
            $table->decimal('down_payment', 15, 2)->default(0); // Peşinat
            $table->integer('installment_count')->default(0); // Taksit sayısı
            $table->decimal('monthly_installment', 15, 2)->nullable(); // Aylık taksit tutarı
            $table->enum('payment_method', [
                'cash',           // Peşin
                'installment',    // Taksit
                'bank_loan',      // Banka Kredisi
                'mixed'           // Karma
            ])->default('installment');

            // Tarihler
            $table->date('reservation_date')->nullable(); // Rezervasyon tarihi
            $table->date('sale_date')->nullable(); // Satış tarihi
            $table->date('contract_date')->nullable(); // Sözleşme tarihi
            $table->date('delivery_date')->nullable(); // Teslim tarihi
            $table->date('deed_transfer_date')->nullable(); // Tapu devir tarihi

            // Tapu Bilgileri
            $table->enum('deed_status', [
                'not_transferred',  // Devredilmedi
                'in_progress',      // İşlemde
                'transferred',      // Devredildi
                'postponed'         // Ertelendi
            ])->default('not_transferred');
            $table->string('deed_type')->nullable(); // Tapu tipi (Kat mülkiyeti, Arsa payı vs.)
            $table->string('title_deed_number')->nullable(); // Tapu cilt/sayfa no
            $table->text('deed_notes')->nullable();

            // Durum
            $table->enum('status', [
                'reserved',       // Rezerve
                'contracted',     // Sözleşmeli
                'in_payment',     // Ödeme Aşamasında
                'completed',      // Tamamlandı
                'cancelled',      // İptal
                'delayed'         // Gecikmiş
            ])->default('reserved');

            // Sözleşme ve Belgeler
            $table->json('contract_documents')->nullable(); // Sözleşme dosyaları
            $table->json('payment_documents')->nullable(); // Ödeme belgeleri
            $table->json('deed_documents')->nullable(); // Tapu belgeleri

            // Notlar ve Açıklamalar
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->date('cancellation_date')->nullable();

            // Komisyon ve Bonuslar
            $table->foreignId('sales_agent_id')->nullable() // Satış temsilcisi
                ->constrained('users')->onDelete('set null');
            $table->decimal('commission_amount', 15, 2)->nullable();
            $table->decimal('commission_percentage', 5, 2)->nullable();

            // Kayıt Bilgileri
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['project_id', 'status']);
            $table->index(['customer_id']);
            $table->index(['sale_type', 'status']);
            $table->index(['deed_status']);
            $table->index(['sale_date']);
            $table->unique(['project_unit_id']); // Bir birim sadece bir kez satılabilir
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_sales');
    }
};
