<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Consolidates all timesheet tables:
     * - Renames old 'timesheets' to 'timesheets_old_backup'
     * - Renames 'timesheets_v3' to 'timesheets_v3_backup'
     * - Renames 'timesheets_v2' to 'timesheets' (main table)
     */
    public function up(): void
    {
        // 1. Eski timesheets tablosunu yedekle
        Schema::rename('timesheets', 'timesheets_old_backup');

        // 2. timesheets_v3'ü yedekle
        Schema::rename('timesheets_v3', 'timesheets_v3_backup');

        // 3. timesheets_v2'yi ana tablo yap
        Schema::rename('timesheets_v2', 'timesheets');

        // 4. İlişkili tabloları güncelle

        // timesheet_approval_logs tablosunda foreign key'i güncelle
        if (Schema::hasTable('timesheet_approval_logs')) {
            // Önce foreign key'i kaldır
            Schema::table('timesheet_approval_logs', function (Blueprint $table) {
                $table->dropForeign(['timesheet_v2_id']);
            });

            // Kolonu yeniden adlandır
            Schema::table('timesheet_approval_logs', function (Blueprint $table) {
                $table->renameColumn('timesheet_v2_id', 'timesheet_id');
            });

            // Yeni foreign key ekle
            Schema::table('timesheet_approval_logs', function (Blueprint $table) {
                $table->foreign('timesheet_id')
                    ->references('id')
                    ->on('timesheets')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // İlişkili tabloları geri al
        if (Schema::hasTable('timesheet_approval_logs')) {
            Schema::table('timesheet_approval_logs', function (Blueprint $table) {
                $table->dropForeign(['timesheet_id']);
            });

            Schema::table('timesheet_approval_logs', function (Blueprint $table) {
                $table->renameColumn('timesheet_id', 'timesheet_v2_id');
            });

            Schema::table('timesheet_approval_logs', function (Blueprint $table) {
                $table->foreign('timesheet_v2_id')
                    ->references('id')
                    ->on('timesheets_v2')
                    ->onDelete('cascade');
            });
        }

        // Tabloları geri yükle
        Schema::rename('timesheets', 'timesheets_v2');
        Schema::rename('timesheets_v3_backup', 'timesheets_v3');
        Schema::rename('timesheets_old_backup', 'timesheets');
    }
};
