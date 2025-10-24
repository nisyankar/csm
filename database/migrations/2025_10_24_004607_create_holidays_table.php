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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tatil adı (örn: "Ramazan Bayramı 1. Gün", "Ramazan Bayramı Arefesi")
            $table->date('date'); // Tatil tarihi
            $table->integer('year'); // Yıl
            $table->enum('type', ['national', 'religious', 'other'])->default('national'); // Tatil türü
            $table->boolean('is_half_day')->default(false); // Yarım gün tatil mi? (Arefe günleri için)
            $table->time('half_day_start')->nullable(); // Yarım gün tatil başlangıç saati (örn: 13:00)
            $table->text('description')->nullable(); // Açıklama
            $table->boolean('is_paid')->default(true); // Ücretli tatil mi?
            $table->boolean('is_active')->default(true); // Aktif mi?
            $table->timestamps();

            // Aynı tarihte aynı tatil olmasın
            $table->unique(['date', 'name']);

            // Yıl ve tarihe göre hızlı sorgulama
            $table->index(['year', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
