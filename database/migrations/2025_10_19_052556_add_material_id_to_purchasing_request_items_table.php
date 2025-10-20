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
        Schema::table('purchasing_items', function (Blueprint $table) {
            // Malzeme ID'si ekle (nullable çünkü mevcut kayıtlar için boş olabilir)
            $table->foreignId('material_id')->nullable()->after('purchasing_request_id')->constrained('materials')->onDelete('set null');

            // İndeks ekle
            $table->index('material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchasing_items', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropColumn('material_id');
        });
    }
};
