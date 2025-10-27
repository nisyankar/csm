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
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('unit_sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');

            // Ödeme Bilgileri
            $table->string('payment_number')->unique(); // Ödeme referans numarası
            $table->integer('installment_number')->nullable(); // Taksit numarası (0 = Peşinat)
            $table->enum('payment_type', [
                'down_payment',   // Peşinat
                'installment',    // Taksit
                'additional',     // Ek Ödeme
                'penalty',        // Gecikme Faizi
                'discount'        // İndirim
            ])->default('installment');

            // Tutar Bilgileri
            $table->decimal('amount', 15, 2); // Ödeme tutarı
            $table->decimal('paid_amount', 15, 2)->default(0); // Ödenen tutar
            $table->decimal('remaining_amount', 15, 2)->default(0); // Kalan tutar
            $table->string('currency', 3)->default('TRY');

            // Gecikme ve Ceza
            $table->decimal('late_fee', 15, 2)->default(0); // Gecikme faizi
            $table->integer('delay_days')->default(0); // Gecikme gün sayısı

            // Tarihler
            $table->date('due_date'); // Vade tarihi
            $table->date('payment_date')->nullable(); // Ödeme tarihi
            $table->date('reminder_sent_at')->nullable(); // Hatırlatma gönderilme tarihi

            // Ödeme Yöntemi
            $table->enum('payment_method', [
                'cash',           // Nakit
                'bank_transfer',  // Havale/EFT
                'credit_card',    // Kredi Kartı
                'check',          // Çek
                'bank_loan'       // Banka Kredisi
            ])->nullable();

            // Banka ve Çek Bilgileri
            $table->string('bank_name')->nullable();
            $table->string('check_number')->nullable();
            $table->date('check_date')->nullable();
            $table->string('transaction_reference')->nullable(); // Dekont/Fiş no

            // Durum
            $table->enum('status', [
                'pending',        // Bekliyor
                'paid',          // Ödendi
                'partial',       // Kısmi Ödeme
                'overdue',       // Vadesi Geçmiş
                'cancelled'      // İptal
            ])->default('pending');

            // Onay ve İşlemler
            $table->foreignId('approved_by')->nullable()
                ->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // Belgeler ve Notlar
            $table->json('payment_documents')->nullable(); // Ödeme belgeleri (dekont, fatura vs.)
            $table->text('notes')->nullable();

            // Kayıt Bilgileri
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['unit_sale_id', 'status']);
            $table->index(['customer_id']);
            $table->index(['payment_type']);
            $table->index(['due_date']);
            $table->index(['payment_date']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
