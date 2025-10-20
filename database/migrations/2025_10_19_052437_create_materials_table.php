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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Malzeme adı
            $table->text('description')->nullable(); // Açıklama
            $table->string('category')->nullable(); // Kategori (İnşaat, Elektrik, Sıhhi Tesisat, vb.)
            $table->string('unit'); // Birim (adet, kg, m, m2, m3, vb.)
            $table->decimal('estimated_unit_price', 15, 2)->default(0); // Tahmini birim fiyat
            $table->text('specification')->nullable(); // Standart teknik özellik
            $table->string('material_code')->nullable()->unique(); // Malzeme kodu
            $table->boolean('is_active')->default(true); // Aktif mi?
            $table->timestamps();

            // İndeksler
            $table->index('name');
            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
