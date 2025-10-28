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
        Schema::table('construction_permits', function (Blueprint $table) {
            // Birim bazlı ruhsat takibi için (özellikle İskan İzni)
            $table->foreignId('project_unit_id')
                  ->nullable()
                  ->after('project_id')
                  ->constrained('project_units')
                  ->nullOnDelete()
                  ->comment('Birim bazlı ruhsat için (İskan İzni gibi). Null ise proje geneli ruhsat.');

            $table->index('project_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('construction_permits', function (Blueprint $table) {
            $table->dropForeign(['project_unit_id']);
            $table->dropColumn('project_unit_id');
        });
    }
};
