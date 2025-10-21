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
        Schema::create('work_categories', function (Blueprint $table) {
            $table->id();

            // Kategori Bilgileri
            $table->string('code', 50)->unique(); // KAB, INC, ELK, TST, vb.
            $table->string('name'); // Kaba İnşaat, İnce İnşaat, Elektrik, vb.
            $table->text('description')->nullable();

            // Görsel
            $table->string('icon', 100)->nullable(); // FontAwesome icon adı
            $table->string('color', 20)->nullable(); // Hex renk kodu (#FF5733)

            // Sıralama
            $table->integer('order')->default(0);

            // Durum
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // İndeksler
            $table->index(['is_active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_categories');
    }
};
