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
        Schema::create('inspection_companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->comment('Denetim kuruluşu adı');
            $table->string('license_number')->unique()->comment('Faaliyet belgesi no');
            $table->string('contact_person')->nullable()->comment('İrtibat kişisi');
            $table->string('phone')->nullable()->comment('Telefon');
            $table->string('email')->nullable()->comment('E-posta');
            $table->text('address')->nullable()->comment('Adres');
            $table->boolean('is_active')->default(true)->comment('Aktif durumu');
            $table->text('notes')->nullable()->comment('Notlar');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_companies');
    }
};
