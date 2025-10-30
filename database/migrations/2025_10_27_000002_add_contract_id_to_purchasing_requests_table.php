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
        Schema::table('purchasing_requests', function (Blueprint $table) {
            $table->foreignId('contract_id')
                ->nullable()
                ->after('project_id')
                ->constrained()
                ->onDelete('set null')
                ->comment('İlgili tedarikçi çerçeve anlaşması (opsiyonel)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchasing_requests', function (Blueprint $table) {
            $table->dropForeign(['contract_id']);
            $table->dropColumn('contract_id');
        });
    }
};
