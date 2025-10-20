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
        Schema::create('employee_salary_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            
            // Eski değerler
            $table->enum('old_wage_type', ['daily', 'hourly', 'monthly'])->nullable();
            $table->decimal('old_daily_wage', 10, 2)->nullable();
            $table->decimal('old_hourly_wage', 10, 2)->nullable();
            $table->decimal('old_monthly_salary', 10, 2)->nullable();
            
            // Yeni değerler
            $table->enum('new_wage_type', ['daily', 'hourly', 'monthly']);
            $table->decimal('new_daily_wage', 10, 2)->nullable();
            $table->decimal('new_hourly_wage', 10, 2)->nullable();
            $table->decimal('new_monthly_salary', 10, 2)->nullable();
            
            // Değişiklik bilgileri
            $table->enum('change_type', ['increase', 'decrease', 'adjustment', 'promotion', 'correction']);
            $table->decimal('change_amount', 10, 2); // Değişiklik miktarı
            $table->decimal('change_percentage', 5, 2)->nullable(); // Yüzdelik değişim
            $table->string('reason'); // Değişiklik nedeni
            $table->text('notes')->nullable(); // Ek notlar
            
            // Kim ve ne zaman
            $table->foreignId('changed_by')->constrained('users')->onDelete('restrict');
            $table->date('effective_date'); // Yürürlük tarihi
            $table->timestamps();
            
            // Indexes
            $table->index(['employee_id', 'effective_date']);
            $table->index('change_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salary_history');
    }
};