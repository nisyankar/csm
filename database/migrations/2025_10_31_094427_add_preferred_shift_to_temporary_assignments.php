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
        Schema::table('temporary_assignments', function (Blueprint $table) {
            $table->foreignId('preferred_shift_id')->nullable()->after('to_project_id')->constrained('shifts')->onDelete('set null');
            $table->index('preferred_shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temporary_assignments', function (Blueprint $table) {
            $table->dropForeign(['preferred_shift_id']);
            $table->dropColumn('preferred_shift_id');
        });
    }
};
