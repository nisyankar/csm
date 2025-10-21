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
        Schema::create('project_structures', function (Blueprint $table) {
            $table->id();

            // Proje İlişkisi
            $table->foreignId('project_id')->constrained()->onDelete('cascade');

            // Yapı Temel Bilgileri
            $table->string('code', 50); // A, B, C, 1, 2, vb.
            $table->string('name'); // A Blok, B Blok, Ana Bina, vb.
            $table->enum('structure_type', [
                'residential_block',  // Konut blok
                'office_block',       // Ofis blok
                'commercial',         // Ticari yapı
                'villa',              // Villa
                'infrastructure',     // Altyapı
                'mixed_use',          // Karma kullanım
                'other'
            ])->default('residential_block');

            // Yapı Özellikleri
            $table->integer('total_floors')->default(0); // Toplam kat sayısı
            $table->integer('total_units')->default(0); // Toplam birim sayısı (daire, ofis, vs.)
            $table->decimal('total_area', 12, 2)->nullable(); // Toplam alan (m²)
            $table->decimal('built_area', 12, 2)->nullable(); // İnşaat alanı (m²)

            // Durum ve İlerleme
            $table->enum('status', [
                'not_started',
                'in_progress',
                'completed',
                'on_hold',
                'cancelled'
            ])->default('not_started');
            $table->decimal('progress_percentage', 5, 2)->default(0); // 0-100

            // Tarih Bilgileri
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();

            // Yönetim
            $table->foreignId('supervisor_id')->nullable()
                ->constrained('employees')->onDelete('set null');

            // İlave Bilgiler
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Özel alanlar için

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['project_id', 'status']);
            $table->index(['status']);
            $table->index(['supervisor_id']);

            // Unique constraint (aynı projede aynı kod olamaz)
            $table->unique(['project_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_structures');
    }
};
