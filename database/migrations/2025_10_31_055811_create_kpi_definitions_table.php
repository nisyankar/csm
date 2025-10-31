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
        Schema::create('kpi_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('module', [
                'progress_payments',
                'timesheets',
                'financials',
                'safety',
                'equipment',
                'stock',
                'quantities',
                'projects',
                'general'
            ]);
            $table->text('formula');
            $table->decimal('target_value', 15, 2)->nullable();
            $table->decimal('warning_threshold', 15, 2)->nullable();
            $table->string('unit')->default('%');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_definitions');
    }
};
