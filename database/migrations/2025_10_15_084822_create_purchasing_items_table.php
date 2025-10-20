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
        Schema::create('purchasing_items', function (Blueprint $table) {
            $table->id();

            // İlişki
            $table->foreignId('purchasing_request_id')->constrained()->onDelete('cascade');

            // Ürün Bilgileri
            $table->string('item_name'); // Ürün adı
            $table->text('description')->nullable(); // Açıklama
            $table->string('specification')->nullable(); // Teknik özellikler

            // Kategori
            $table->enum('category', [
                'concrete',  // Beton
                'steel',     // Demir
                'general',   // Genel malzeme
                'equipment', // Ekipman
                'service',   // Hizmet
                'other'      // Diğer
            ])->default('general');

            // Miktar ve Birim
            $table->decimal('quantity', 10, 2); // Miktar
            $table->string('unit'); // Birim (kg, ton, m3, adet, m2, vb.)

            // Fiyat Bilgileri
            $table->decimal('estimated_unit_price', 12, 2)->nullable(); // Tahmini birim fiyat
            $table->decimal('estimated_total_price', 12, 2)->nullable(); // Tahmini toplam
            $table->decimal('actual_unit_price', 12, 2)->nullable(); // Gerçekleşen birim fiyat
            $table->decimal('actual_total_price', 12, 2)->nullable(); // Gerçekleşen toplam

            // Beton Özel Alanları (beton için)
            $table->string('concrete_class')->nullable(); // Beton sınıfı (C25, C30, vb.)
            $table->string('concrete_slump')->nullable(); // Slump değeri
            $table->string('concrete_aggregate_size')->nullable(); // Agrega boyutu

            // Demir Özel Alanları (demir için)
            $table->string('steel_diameter')->nullable(); // Çap (mm)
            $table->string('steel_quality')->nullable(); // Kalite (S420, S500, vb.)
            $table->string('steel_length')->nullable(); // Boy

            // Teslimat
            $table->date('required_date')->nullable(); // Teslim tarihi
            $table->string('delivery_location')->nullable(); // Teslimat yeri

            // Notlar
            $table->text('notes')->nullable(); // Notlar
            $table->integer('priority')->default(0); // Öncelik sırası

            $table->timestamps();

            // İndeksler
            $table->index(['purchasing_request_id']);
            $table->index(['category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchasing_items');
    }
};
