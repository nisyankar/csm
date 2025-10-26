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
        Schema::table('progress_payments', function (Blueprint $table) {
            $table->foreignId('quantity_id')
                ->nullable()
                ->after('work_item_id')
                ->constrained('quantities')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_payments', function (Blueprint $table) {
            $table->dropForeign(['quantity_id']);
            $table->dropColumn('quantity_id');
        });
    }
};
