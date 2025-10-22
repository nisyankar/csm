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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Gündüz Vardiyası, Gece Vardiyası, Hafta Sonu, vb.
            $table->string('code')->unique(); // GN, GC, HS, vb.
            $table->string('shift_type'); // normal, weekend, holiday, leave
            $table->decimal('daily_hours', 5, 2)->default(7.5); // Günlük standart saat
            $table->decimal('overtime_multiplier', 3, 2)->default(1.0); // Fazla mesai çarpanı
            $table->boolean('is_paid')->default(true); // Ücretli mi?
            $table->boolean('counts_as_work_day')->default(true); // Çalışma günü mü?
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Ek bilgiler
            $table->timestamps();

            $table->index(['shift_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
