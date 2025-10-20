<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add missing monthly_salary column
            $table->decimal('monthly_salary', 10, 2)->nullable()->after('hourly_wage');
        });
        
        // Update status enum to include 'suspended'
        DB::statement("ALTER TABLE employees MODIFY COLUMN status ENUM('active', 'inactive', 'suspended', 'terminated') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('monthly_salary');
        });
        
        // Revert status enum
        DB::statement("ALTER TABLE employees MODIFY COLUMN status ENUM('active', 'inactive', 'terminated') DEFAULT 'active'");
    }
};