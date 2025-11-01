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
            // shift_id'yi nullable yap (mobil check-in için gerekli)
            $table->unsignedBigInteger('shift_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            // shift_id'yi tekrar NOT NULL yap
            $table->unsignedBigInteger('shift_id')->nullable(false)->change();
        });
    }
};
