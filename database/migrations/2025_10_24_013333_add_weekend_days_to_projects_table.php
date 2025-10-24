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
        Schema::table('projects', function (Blueprint $table) {
            // Hafta tatili günleri - JSON formatında saklanacak
            // Örnek: ["saturday", "sunday"] veya ["sunday"]
            $table->json('weekend_days')->default('["saturday", "sunday"]')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('weekend_days');
        });
    }
};
