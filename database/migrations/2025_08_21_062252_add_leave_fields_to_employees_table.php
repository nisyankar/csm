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
        Schema::table('employees', function (Blueprint $table) {
            // İzin Hesaplama Temeli
            $table->enum('leave_calculation_basis', ['hire_date', 'calendar_year'])
                  ->default('hire_date')
                  ->after('used_leave_days')
                  ->comment('İzin hesaplama temeli: işe giriş tarihi veya takvim yılı');
            
            // Özel Durumlar
            $table->boolean('is_underground_worker')
                  ->default(false)
                  ->after('leave_calculation_basis')
                  ->comment('Yer altı işçisi mi? (+4 gün ek izin)');
            
            $table->boolean('is_disabled_worker')
                  ->default(false)
                  ->after('is_underground_worker')
                  ->comment('Engelli işçi mi? (özel izin hakları)');
            
            // Önceki İşveren Kıdemi
            $table->integer('previous_employer_tenure_days')
                  ->default(0)
                  ->after('is_disabled_worker')
                  ->comment('Önceki işverendeki kıdem günü (alt işveren değişikliği)');
            
            // İzin Yılı Bilgileri
            $table->date('current_leave_year_start')
                  ->nullable()
                  ->after('previous_employer_tenure_days')
                  ->comment('Mevcut izin yılı başlangıcı');
            
            $table->date('current_leave_year_end')
                  ->nullable()
                  ->after('current_leave_year_start')
                  ->comment('Mevcut izin yılı bitişi');
            
            // Detaylı İzin Bakiyeleri
            $table->integer('sick_leave_days_used')
                  ->default(0)
                  ->after('current_leave_year_end')
                  ->comment('Kullanılan hastalık izni günü');
            
            $table->integer('excused_leave_days_used')
                  ->default(0)
                  ->after('sick_leave_days_used')
                  ->comment('Kullanılan mazeret izni günü');
            
            $table->integer('unpaid_leave_days_used')
                  ->default(0)
                  ->after('excused_leave_days_used')
                  ->comment('Kullanılan ücretsiz izin günü');
            
            // Önceki Yıldan Devir
            $table->integer('carried_forward_days')
                  ->default(0)
                  ->after('unpaid_leave_days_used')
                  ->comment('Önceki yıldan devredilen izin günü');
            
            $table->date('carry_forward_expiry')
                  ->nullable()
                  ->after('carried_forward_days')
                  ->comment('Devir izinlerinin son kullanma tarihi');
            
            // Hesaplama Durumu
            $table->boolean('leave_calculation_up_to_date')
                  ->default(false)
                  ->after('carry_forward_expiry')
                  ->comment('İzin hesaplaması güncel mi?');
            
            $table->timestamp('last_leave_calculation_date')
                  ->nullable()
                  ->after('leave_calculation_up_to_date')
                  ->comment('Son izin hesaplama tarihi');
            
            // Yasal Uyumluluk
            $table->boolean('annual_leave_notification_sent')
                  ->default(false)
                  ->after('last_leave_calculation_date')
                  ->comment('Yıllık izin bildirim yazısı gönderildi mi?');
            
            $table->date('annual_leave_notification_date')
                  ->nullable()
                  ->after('annual_leave_notification_sent')
                  ->comment('İzin bildirim yazısı tarihi');
        });
        
        // Status enum güncelleme - 'on_leave' durumu ekleme
        DB::statement("ALTER TABLE employees MODIFY COLUMN status ENUM('active', 'inactive', 'suspended', 'terminated', 'on_leave') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'leave_calculation_basis',
                'is_underground_worker',
                'is_disabled_worker',
                'previous_employer_tenure_days',
                'current_leave_year_start',
                'current_leave_year_end',
                'sick_leave_days_used',
                'excused_leave_days_used',
                'unpaid_leave_days_used',
                'carried_forward_days',
                'carry_forward_expiry',
                'leave_calculation_up_to_date',
                'last_leave_calculation_date',
                'annual_leave_notification_sent',
                'annual_leave_notification_date'
            ]);
        });
        
        // Status enum geri alma
        DB::statement("ALTER TABLE employees MODIFY COLUMN status ENUM('active', 'inactive', 'suspended', 'terminated') DEFAULT 'active'");
    }
};