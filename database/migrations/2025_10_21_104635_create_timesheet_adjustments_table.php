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
        Schema::create('timesheet_adjustments', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->integer('year');
            $table->integer('month'); // 1-12

            // Hangi alan manuel değiştirildi
            $table->string('field_name'); // total_hours, overtime, shortage, etc.
            $table->string('field_label'); // Display adı

            // Değerler
            $table->decimal('calculated_value', 10, 2); // Sistem hesapladığı
            $table->decimal('adjusted_value', 10, 2); // Manuel girilmiş
            $table->decimal('difference', 10, 2); // Fark

            // Açıklama
            $table->text('reason'); // Düzeltme nedeni
            $table->string('adjustment_type')->default('manual'); // manual, correction, etc.

            // Kim düzeltti
            $table->foreignId('adjusted_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('adjusted_at')->useCurrent();

            $table->timestamps();

            $table->index(['employee_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_adjustments');
    }
};
