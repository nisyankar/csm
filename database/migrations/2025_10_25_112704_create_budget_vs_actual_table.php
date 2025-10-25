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
        Schema::create('budget_vs_actual', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->unsignedTinyInteger('month'); // 1-12
            $table->enum('category_type', ['income', 'expense']);
            $table->foreignId('category_id');
            $table->decimal('budget_amount', 15, 2)->default(0);
            $table->decimal('actual_amount', 15, 2)->default(0);

            // Computed columns (GENERATED)
            $table->decimal('variance', 15, 2)->storedAs('actual_amount - budget_amount');
            $table->decimal('variance_percentage', 8, 2)->storedAs(
                'CASE WHEN budget_amount = 0 THEN 0 ELSE ((actual_amount - budget_amount) / budget_amount) * 100 END'
            );

            $table->timestamps();

            // Unique constraint: Her proje için aynı yıl/ay/kategori/tip kombinasyonu bir kere olmalı
            $table->unique(['project_id', 'year', 'month', 'category_type', 'category_id'], 'budget_unique');
            $table->index(['project_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_vs_actual');
    }
};
