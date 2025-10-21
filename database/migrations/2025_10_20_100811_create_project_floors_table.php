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
        Schema::create('project_floors', function (Blueprint $table) {
            $table->id();

            // Yapı İlişkisi
            $table->foreignId('structure_id')->constrained('project_structures')->onDelete('cascade');

            // Kat Bilgileri
            $table->integer('floor_number'); // -2, -1, 0, 1, 2, ... (zemin=0, bodrum negatif)
            $table->string('floor_name'); // Bodrum -2, Bodrum -1, Zemin, Kat 1, Çatı
            $table->enum('floor_type', [
                'basement',      // Bodrum
                'ground',        // Zemin
                'standard',      // Normal kat
                'roof',          // Çatı
                'penthouse',     // Çatı katı (penthouse)
                'technical',     // Teknik hacim
                'mezzanine'      // Asma kat
            ])->default('standard');

            // Kat Özellikleri
            $table->integer('total_units')->default(0); // Bu kattaki birim sayısı
            $table->decimal('floor_area', 12, 2)->nullable(); // Kat alanı (m²)
            $table->decimal('height', 5, 2)->nullable(); // Kat yüksekliği (metre)

            // Durum
            $table->enum('status', [
                'not_started',
                'in_progress',
                'completed',
                'on_hold'
            ])->default('not_started');
            $table->decimal('progress_percentage', 5, 2)->default(0);

            // Tarih
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();

            // İlave
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['structure_id', 'floor_number']);
            $table->index(['status']);

            // Unique constraint (aynı yapıda aynı kat numarası olamaz)
            $table->unique(['structure_id', 'floor_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_floors');
    }
};
