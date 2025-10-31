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
            $table->integer('order')->default(0)->after('code');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_structures', function (Blueprint $table) {
            $table->dropIndex(['order']);
            $table->dropColumn('order');
        });
    }
};
