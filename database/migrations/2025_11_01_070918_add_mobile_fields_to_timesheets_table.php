<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mobil uygulama check-in/out sistemi için gereken alanlar
     */
    public function up(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            // Check-in/out zamanları (start_time/end_time'dan farklı - mobil için)
            if (!Schema::hasColumn('timesheets', 'check_in_time')) {
                $table->time('check_in_time')->nullable()->after('work_date');
            }

            if (!Schema::hasColumn('timesheets', 'check_out_time')) {
                $table->time('check_out_time')->nullable()->after('check_in_time');
            }

            // Check-in/out yöntemleri (QR, GPS, manuel, NFC)
            if (!Schema::hasColumn('timesheets', 'check_in_method')) {
                $table->enum('check_in_method', ['manual', 'qr', 'gps', 'nfc', 'biometric', 'mobile_offline'])
                      ->nullable()
                      ->after('check_out_time');
            }

            if (!Schema::hasColumn('timesheets', 'check_out_method')) {
                $table->enum('check_out_method', ['manual', 'qr', 'gps', 'nfc', 'biometric', 'mobile_offline'])
                      ->nullable()
                      ->after('check_in_method');
            }

            // Check-in/out konumları (GPS koordinatları)
            if (!Schema::hasColumn('timesheets', 'check_in_location')) {
                $table->json('check_in_location')->nullable()->after('check_out_method');
            }

            if (!Schema::hasColumn('timesheets', 'check_out_location')) {
                $table->json('check_out_location')->nullable()->after('check_in_location');
            }

            // Toplam ve normal mesai saatleri
            if (!Schema::hasColumn('timesheets', 'total_hours')) {
                $table->decimal('total_hours', 5, 2)->default(0)->after('hours_worked');
            }

            if (!Schema::hasColumn('timesheets', 'regular_hours')) {
                $table->decimal('regular_hours', 5, 2)->default(0)->after('total_hours');
            }

            // Mobil için durum (present, absent, leave, holiday, active, completed)
            if (!Schema::hasColumn('timesheets', 'status')) {
                $table->enum('status', ['active', 'completed', 'present', 'absent', 'leave', 'holiday'])
                      ->default('active')
                      ->after('approval_status');
            }

            // Geç kalma, erken çıkış, fazla mesai kontrolleri
            if (!Schema::hasColumn('timesheets', 'is_late')) {
                $table->boolean('is_late')->default(false)->after('status');
            }

            if (!Schema::hasColumn('timesheets', 'is_early_leave')) {
                $table->boolean('is_early_leave')->default(false)->after('is_late');
            }

            if (!Schema::hasColumn('timesheets', 'is_overtime')) {
                $table->boolean('is_overtime')->default(false)->after('is_early_leave');
            }

            // Ret nedeni
            if (!Schema::hasColumn('timesheets', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approval_notes');
            }
        });

        // İndeksler
        try {
            Schema::table('timesheets', function (Blueprint $table) {
                $table->index('status', 'timesheets_status_index');
                $table->index(['check_in_time', 'check_out_time'], 'timesheets_check_times_index');
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
        Schema::table('timesheets', function (Blueprint $table) {
            $table->dropIndex('timesheets_status_index');
            $table->dropIndex('timesheets_check_times_index');

            $table->dropColumn([
                'check_in_time',
                'check_out_time',
                'check_in_method',
                'check_out_method',
                'check_in_location',
                'check_out_location',
                'total_hours',
                'regular_hours',
                'status',
                'is_late',
                'is_early_leave',
                'is_overtime',
                'rejection_reason',
            ]);
        });
    }
};
