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
        Schema::create('project_units', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('structure_id')->constrained('project_structures')->onDelete('cascade');
            $table->foreignId('floor_id')->nullable()->constrained('project_floors')->onDelete('cascade');

            // Birim Temel Bilgileri
            $table->string('unit_code', 50); // A-101, B-205, D12, vb.
            $table->enum('unit_type', [
                'apartment',        // Daire
                'office',           // Ofis
                'shop',             // Dükkan/Mağaza
                'warehouse',        // Depo
                'parking_space',    // Otopark
                'storage',          // Depo
                'technical_room',   // Teknik oda
                'common_area',      // Ortak alan
                'other'
            ])->default('apartment');

            // Daire Özellikleri
            $table->string('room_configuration', 50)->nullable(); // 2+1, 3+1, 4+2, studio, vb.
            $table->decimal('gross_area', 10, 2)->nullable(); // Brüt alan (m²)
            $table->decimal('net_area', 10, 2)->nullable(); // Net alan (m²)
            $table->decimal('balcony_area', 10, 2)->nullable(); // Balkon alanı (m²)
            $table->decimal('terrace_area', 10, 2)->nullable(); // Teras alanı (m²)
            $table->decimal('garden_area', 10, 2)->nullable(); // Bahçe alanı (m²)

            // Yön
            $table->enum('direction', [
                'north',
                'south',
                'east',
                'west',
                'northeast',
                'northwest',
                'southeast',
                'southwest',
                'multiple'
            ])->nullable();

            // Durum
            $table->enum('status', [
                'not_started',
                'in_progress',
                'completed',
                'delivered',
                'sold'
            ])->default('not_started');
            $table->decimal('progress_percentage', 5, 2)->default(0);

            // Tarih
            $table->date('planned_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->date('delivery_date')->nullable();

            // Sahiplik/Satış (opsiyonel - satış modülü ile entegre)
            $table->string('owner_name')->nullable();
            $table->string('owner_contact')->nullable();
            $table->boolean('is_sold')->default(false);
            $table->date('sale_date')->nullable();

            // İlave
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->json('features')->nullable(); // Özellikler: klima, otopark, vb.

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['structure_id', 'floor_id']);
            $table->index(['unit_type']);
            $table->index(['status']);
            $table->index(['is_sold']);

            // Unique constraint (aynı yapıda aynı birim kodu olamaz)
            $table->unique(['structure_id', 'unit_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_units');
    }
};
