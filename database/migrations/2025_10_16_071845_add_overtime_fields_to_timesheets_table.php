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
        Schema::table('timesheets', function (Blueprint $table) {
            $table->decimal('overtime_hours', 5, 2)->nullable()->after('attendance_type')->comment('Fazla mesai saati');
            $table->enum('overtime_type', ['weekday', 'weekend', 'holiday'])->nullable()->after('overtime_hours')->comment('Fazla mesai tipi (hafta içi: %50, hafta sonu: %100, resmi tatil: %200)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->dropColumn(['overtime_hours', 'overtime_type']);
        });
    }
};
