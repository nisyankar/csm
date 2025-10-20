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
        Schema::table('employees', function (Blueprint $table) {
            // Department relationship ekleniyor
            $table->foreignId('department_id')->nullable()->after('current_project_id')->constrained('departments')->onDelete('set null');
            
            // Hire date için alias - eğer hire_date column'ı yoksa
            // start_date zaten var, sadece alias olarak kullanacağız
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};