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
        Schema::create('timesheet_carryovers', function (Blueprint $table) {
            $table->id();

            // Personel ve dönem
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->integer('year');
            $table->integer('month'); // 1-12

            // Devir edilen değerler
            $table->decimal('overtime_hours', 8, 2)->default(0); // Devir fazla mesai
            $table->decimal('shortage_hours', 8, 2)->default(0); // Devir eksik saat
            $table->decimal('extra_income', 10, 2)->default(0); // Ek gelir devir

            // Notlar
            $table->text('notes')->nullable();

            // Kim oluşturdu
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');

            $table->timestamps();

            // Bir kişi için ay başına tek bir kayıt
            $table->unique(['employee_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_carryovers');
    }
};
