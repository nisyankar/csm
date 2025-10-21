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
        Schema::create('work_items', function (Blueprint $table) {
            $table->id();

            // Kategori İlişkisi
            $table->foreignId('category_id')->constrained('work_categories')->onDelete('cascade');

            // İş Kalemi Bilgileri
            $table->string('code', 50); // KAZ, TML, SIV, FAY, ELK, vb.
            $table->string('name'); // Kazı, Temel, Sıva, Fayans, Elektrik Tesisatı
            $table->text('description')->nullable();

            // Birim
            $table->string('unit', 20)->default('m2'); // m2, m3, adet, metre, kg, ton

            // Tahmin
            $table->integer('estimated_duration_days')->nullable(); // Tahmini süre (gün)
            $table->decimal('default_unit_price', 12, 2)->nullable(); // Varsayılan birim fiyat

            // Özellikler
            $table->boolean('requires_approval')->default(false); // Onay gerekiyor mu?
            $table->boolean('is_critical')->default(false); // Kritik iş mi?

            // Sıralama
            $table->integer('order')->default(0);

            // Durum
            $table->boolean('is_active')->default(true);

            // İlave
            $table->json('metadata')->nullable(); // Özel alanlar

            $table->timestamps();

            // İndeksler
            $table->index(['category_id', 'is_active']);
            $table->index(['is_active', 'order']);

            // Unique constraint (aynı kategoride aynı kod olamaz)
            $table->unique(['category_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_items');
    }
};
