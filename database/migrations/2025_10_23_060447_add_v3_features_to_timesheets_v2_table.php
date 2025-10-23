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
            // Zaman takibi - sadece yoksa ekle
            if (!Schema::hasColumn('timesheets_v2', 'overtime_hours')) {
                $table->decimal('overtime_hours', 5, 2)->default(0)->after('hours_worked');
            }

            if (!Schema::hasColumn('timesheets_v2', 'overtime_type')) {
                $table->enum('overtime_type', ['weekday', 'weekend', 'holiday'])->nullable()->after('overtime_hours');
            }

            if (!Schema::hasColumn('timesheets_v2', 'start_time')) {
                $table->time('start_time')->nullable()->after('overtime_type');
            }

            if (!Schema::hasColumn('timesheets_v2', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }

            if (!Schema::hasColumn('timesheets_v2', 'break_duration')) {
                $table->decimal('break_duration', 5, 2)->default(0)->after('end_time');
            }

            // İzin entegrasyonu
            if (!Schema::hasColumn('timesheets_v2', 'leave_request_id')) {
                $table->foreignId('leave_request_id')->nullable()
                    ->after('notes')
                    ->constrained('leave_requests')
                    ->onDelete('set null');
            }

            if (!Schema::hasColumn('timesheets_v2', 'auto_generated_from_leave')) {
                $table->boolean('auto_generated_from_leave')->default(false)->after('leave_request_id');
            }

            if (!Schema::hasColumn('timesheets_v2', 'is_leave_day')) {
                $table->boolean('is_leave_day')->default(false)->after('auto_generated_from_leave');
            }

            if (!Schema::hasColumn('timesheets_v2', 'leave_type')) {
                $table->string('leave_type')->nullable()->after('is_leave_day');
            }

            // Haftalık hesaplama cache
            if (!Schema::hasColumn('timesheets_v2', 'week_number')) {
                $table->integer('week_number')->nullable()->after('leave_type');
            }

            if (!Schema::hasColumn('timesheets_v2', 'year')) {
                $table->integer('year')->nullable()->after('week_number');
            }

            if (!Schema::hasColumn('timesheets_v2', 'weekly_total_hours')) {
                $table->decimal('weekly_total_hours', 5, 2)->nullable()->after('year');
            }

            if (!Schema::hasColumn('timesheets_v2', 'weekly_required_hours')) {
                $table->decimal('weekly_required_hours', 5, 2)->nullable()->after('weekly_total_hours');
            }

            if (!Schema::hasColumn('timesheets_v2', 'weekly_overtime_hours')) {
                $table->decimal('weekly_overtime_hours', 5, 2)->nullable()->after('weekly_required_hours');
            }

            if (!Schema::hasColumn('timesheets_v2', 'week_calculation_done')) {
                $table->boolean('week_calculation_done')->default(false)->after('weekly_overtime_hours');
            }

            // Giriş metodu takibi
            if (!Schema::hasColumn('timesheets_v2', 'entry_method')) {
                $table->enum('entry_method', ['manual', 'bulk', 'import', 'leave_sync', 'system'])
                    ->default('manual')
                    ->after('entered_by');
            }

            // Kilit sistemi (approval sistemiyle uyumlu çalışacak)
            if (!Schema::hasColumn('timesheets_v2', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('week_calculation_done');
            }
        });

        // İndeksler ekle - ayrı blokta çünkü bazı DB sürümleri desteklemiyor
        try {
            Schema::table('timesheets_v2', function (Blueprint $table) {
                $table->index(['year', 'week_number'], 'timesheets_v2_year_week_number_index');
            });
        } catch (\Exception $e) {
            // Index zaten varsa devam et
        }

        try {
            Schema::table('timesheets_v2', function (Blueprint $table) {
                $table->index('is_leave_day', 'timesheets_v2_is_leave_day_index');
            });
        } catch (\Exception $e) {
            // Index zaten varsa devam et
        }

        try {
            Schema::table('timesheets_v2', function (Blueprint $table) {
                $table->index('auto_generated_from_leave', 'timesheets_v2_auto_generated_index');
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
            // İndeksleri kaldır
            $table->dropIndex('timesheets_v2_year_week_number_index');
            $table->dropIndex('timesheets_v2_is_leave_day_index');
            $table->dropIndex('timesheets_v2_auto_generated_index');

            // Foreign key varsa kaldır
            if (Schema::hasColumn('timesheets_v2', 'leave_request_id')) {
                $table->dropForeign(['leave_request_id']);
            }

            // Kolonları kaldır
            $table->dropColumn([
                'overtime_hours',
                'overtime_type',
                'start_time',
                'end_time',
                'break_duration',
                'leave_request_id',
                'auto_generated_from_leave',
                'is_leave_day',
                'leave_type',
                'week_number',
                'year',
                'weekly_total_hours',
                'weekly_required_hours',
                'weekly_overtime_hours',
                'week_calculation_done',
                'entry_method',
                'is_locked',
            ]);
        });
    }
};
