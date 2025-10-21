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
        Schema::table('project_structures', function (Blueprint $table) {
            $table->integer('total_floors')->nullable()->default(0)->change();
            $table->integer('total_units')->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_structures', function (Blueprint $table) {
            $table->integer('total_floors')->nullable(false)->default(0)->change();
            $table->integer('total_units')->nullable(false)->default(0)->change();
        });
    }
};
