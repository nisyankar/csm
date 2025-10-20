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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            // Firma Bilgileri
            $table->string('supplier_code')->unique(); // Tedarikçi kodu
            $table->string('company_name'); // Firma adı
            $table->string('tax_number')->nullable(); // Vergi numarası
            $table->string('tax_office')->nullable(); // Vergi dairesi
            $table->text('address')->nullable(); // Adres

            // İletişim Bilgileri
            $table->string('contact_person')->nullable(); // İlgili kişi
            $table->string('phone')->nullable(); // Telefon
            $table->string('mobile')->nullable(); // Cep telefonu
            $table->string('email')->nullable(); // E-posta
            $table->string('website')->nullable(); // Web sitesi

            // Kategori ve Uzmanlık
            $table->json('categories')->nullable(); // Tedarikçi kategorileri [concrete, steel, general, etc.]
            $table->text('specialization')->nullable(); // Uzmanlık alanları

            // Değerlendirme
            $table->decimal('rating', 3, 2)->default(0); // Puan (0-5)
            $table->integer('total_orders')->default(0); // Toplam sipariş sayısı
            $table->decimal('total_amount', 15, 2)->default(0); // Toplam sipariş tutarı
            $table->integer('on_time_delivery_count')->default(0); // Zamanında teslimat sayısı
            $table->integer('late_delivery_count')->default(0); // Geç teslimat sayısı

            // Ödeme Koşulları
            $table->integer('payment_term_days')->default(0); // Vade günü
            $table->string('payment_method')->nullable(); // Ödeme yöntemi
            $table->decimal('credit_limit', 12, 2)->default(0); // Kredi limiti

            // Sözleşme
            $table->boolean('has_contract')->default(false); // Sözleşme var mı?
            $table->date('contract_start_date')->nullable(); // Sözleşme başlangıç
            $table->date('contract_end_date')->nullable(); // Sözleşme bitiş

            // Durum
            $table->enum('status', [
                'active',    // Aktif
                'inactive',  // Pasif
                'suspended', // Askıda
                'blacklisted' // Kara liste
            ])->default('active');

            // Notlar
            $table->text('notes')->nullable(); // Genel notlar
            $table->text('blacklist_reason')->nullable(); // Kara liste sebebi

            // Soft delete
            $table->softDeletes();
            $table->timestamps();

            // İndeksler
            $table->index(['status']);
            $table->index(['company_name']);
            $table->index(['rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
