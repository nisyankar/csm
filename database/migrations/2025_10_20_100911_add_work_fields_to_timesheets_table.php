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
            // Proje Yapısı İlişkileri
            $table->foreignId('structure_id')->nullable()
                ->after('project_id')
                ->constrained('project_structures')
                ->onDelete('set null');

            $table->foreignId('floor_id')->nullable()
                ->after('structure_id')
                ->constrained('project_floors')
                ->onDelete('set null');

            $table->foreignId('unit_id')->nullable()
                ->after('floor_id')
                ->constrained('project_units')
                ->onDelete('set null');

            // İş Kalemi İlişkileri
            $table->foreignId('work_item_id')->nullable()
                ->after('unit_id')
                ->constrained('work_items')
                ->onDelete('set null');

            $table->foreignId('assignment_id')->nullable()
                ->after('work_item_id')
                ->constrained('work_item_assignments')
                ->onDelete('set null');

            // İndeksler
            $table->index(['structure_id', 'work_date']);
            $table->index(['work_item_id', 'work_date']);
            $table->index(['assignment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            // İndeksleri kaldır
            $table->dropIndex(['structure_id', 'work_date']);
            $table->dropIndex(['work_item_id', 'work_date']);
            $table->dropIndex(['assignment_id']);

            // Foreign key'leri kaldır
            $table->dropForeign(['structure_id']);
            $table->dropForeign(['floor_id']);
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['work_item_id']);
            $table->dropForeign(['assignment_id']);

            // Kolonları kaldır
            $table->dropColumn([
                'structure_id',
                'floor_id',
                'unit_id',
                'work_item_id',
                'assignment_id'
            ]);
        });
    }
};
