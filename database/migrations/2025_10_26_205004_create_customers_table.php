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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Kişisel Bilgiler
            $table->string('first_name');
            $table->string('last_name');
            $table->string('tc_number', 11)->unique()->nullable(); // TC Kimlik No
            $table->string('passport_number', 50)->unique()->nullable(); // Pasaport No (Yabancı müşteriler için)
            $table->enum('customer_type', ['individual', 'corporate'])->default('individual'); // Bireysel/Kurumsal
            $table->string('company_name')->nullable(); // Kurumsal müşteriler için şirket adı
            $table->string('tax_office')->nullable(); // Vergi Dairesi
            $table->string('tax_number', 20)->nullable(); // Vergi Numarası

            // İletişim Bilgileri
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->string('mobile_phone', 20)->nullable();
            $table->string('work_phone', 20)->nullable();

            // Adres Bilgileri
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->text('address')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country')->default('Türkiye');

            // Demografik Bilgiler
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('nationality')->default('TC');

            // Referans ve İlişkiler
            $table->string('reference_source')->nullable(); // Müşteri nereden geldi? (Website, Referans, vs.)
            $table->string('reference_person')->nullable(); // Referans veren kişi

            // CRM Bilgileri
            $table->enum('customer_status', [
                'potential',      // Potansiyel
                'interested',     // İlgileniyor
                'active',         // Aktif Müşteri
                'inactive',       // Pasif Müşteri
                'blacklisted'     // Kara liste
            ])->default('potential');
            $table->integer('satisfaction_score')->nullable(); // 1-10 arası memnuniyet puanı
            $table->text('notes')->nullable(); // Notlar

            // Belge ve Dokümanlar
            $table->json('documents')->nullable(); // TC, Pasaport, İkametgah vs. doküman yolları

            // Kayıt Bilgileri
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['customer_type', 'customer_status']);
            $table->index(['email']);
            $table->index(['phone']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
