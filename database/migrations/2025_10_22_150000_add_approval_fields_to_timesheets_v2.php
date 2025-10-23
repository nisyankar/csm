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
        Schema::table('timesheets_v2', function (Blueprint $table) {
            // Kolonları sadece yoksa ekle
            if (!Schema::hasColumn('timesheets_v2', 'approval_status')) {
                $table->enum('approval_status', ['draft', 'submitted', 'approved', 'rejected'])
                      ->default('draft')
                      ->after('notes');
            }

            if (!Schema::hasColumn('timesheets_v2', 'is_approved')) {
                $table->boolean('is_approved')
                      ->default(false)
                      ->after('notes')
                      ->index();
            }

            if (!Schema::hasColumn('timesheets_v2', 'approved_by')) {
                $table->foreignId('approved_by')
                      ->nullable()
                      ->after('notes')
                      ->constrained('users')
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('timesheets_v2', 'approved_at')) {
                $table->timestamp('approved_at')
                      ->nullable()
                      ->after('notes');
            }

            if (!Schema::hasColumn('timesheets_v2', 'approval_notes')) {
                $table->text('approval_notes')
                      ->nullable()
                      ->after('notes');
            }

            if (!Schema::hasColumn('timesheets_v2', 'hr_override')) {
                $table->boolean('hr_override')
                      ->default(false)
                      ->after('notes');
            }

            if (!Schema::hasColumn('timesheets_v2', 'hr_approved_by')) {
                $table->foreignId('hr_approved_by')
                      ->nullable()
                      ->after('notes')
                      ->constrained('users')
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('timesheets_v2', 'hr_approved_at')) {
                $table->timestamp('hr_approved_at')
                      ->nullable()
                      ->after('notes');
            }
        });

        // Index'leri ayrı bir blokta ekle - hata vermemesi için try-catch kullan
        try {
            Schema::table('timesheets_v2', function (Blueprint $table) {
                $table->index(['approval_status', 'work_date'], 'timesheets_v2_approval_status_work_date_index');
            });
        } catch (\Exception $e) {
            // Index zaten varsa devam et
        }

        try {
            Schema::table('timesheets_v2', function (Blueprint $table) {
                $table->index(['approved_by', 'approved_at'], 'timesheets_v2_approved_by_approved_at_index');
            });
        } catch (\Exception $e) {
            // Index zaten varsa devam et
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timesheets_v2', function (Blueprint $table) {
            $table->dropIndex(['approval_status', 'work_date']);
            $table->dropIndex(['approved_by', 'approved_at']);

            $table->dropForeign(['approved_by']);
            $table->dropForeign(['hr_approved_by']);

            $table->dropColumn([
                'approval_status',
                'is_approved',
                'approved_by',
                'approved_at',
                'approval_notes',
                'hr_override',
                'hr_approved_by',
                'hr_approved_at'
            ]);
        });
    }
};
