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
            // Proje takvimi ile entegrasyon
            $table->foreignId('project_schedule_id')->nullable()->after('project_id')->constrained('project_schedules')->onDelete('set null');

            // Otomatik güncelleme flag'i
            $table->boolean('auto_update_schedule')->default(false)->after('project_schedule_id');

            // Index
            $table->index('project_schedule_id');
        });

        // project_schedules tablosuna da index ekle
        Schema::table('project_schedules', function (Blueprint $table) {
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_schedules', function (Blueprint $table) {
            $table->dropIndex(['reference_type', 'reference_id']);
        });

        Schema::table('progress_payments', function (Blueprint $table) {
            $table->dropIndex(['project_schedule_id']);
            $table->dropForeign(['project_schedule_id']);
            $table->dropColumn(['project_schedule_id', 'auto_update_schedule']);
        });
    }
};
