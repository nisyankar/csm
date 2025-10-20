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
        Schema::table('leave_requests', function (Blueprint $table) {
            // İzin Yılı
            $table->integer('leave_year')
                  ->after('leave_type')
                  ->comment('İzin yılı (2024, 2025...)');
            
            // Yol İzni
            $table->integer('road_leave_days')
                  ->default(0)
                  ->after('working_days')
                  ->comment('Yol izni günü (max 4 gün)');
            
            $table->boolean('road_leave_requested')
                  ->default(false)
                  ->after('road_leave_days')
                  ->comment('Yol izni talep edildi mi?');
            
            $table->text('travel_destination')
                  ->nullable()
                  ->after('road_leave_requested')
                  ->comment('Seyahat hedefi (yol izni için)');
            
            // Avans İzin
            $table->boolean('is_advance_leave')
                  ->default(false)
                  ->after('travel_destination')
                  ->comment('Avans izin mi? (henüz hak etmemiş)');
            
            $table->text('advance_leave_justification')
                  ->nullable()
                  ->after('is_advance_leave')
                  ->comment('Avans izin gerekçesi');
            
            $table->date('advance_leave_due_date')
                  ->nullable()
                  ->after('advance_leave_justification')
                  ->comment('Avans iznin hak edilme tarihi');
            
            // Toplu İzin
            $table->boolean('is_collective_leave')
                  ->default(false)
                  ->after('advance_leave_due_date')
                  ->comment('Toplu izin uygulaması kapsamında mı?');
            
            $table->string('collective_leave_period', 50)
                  ->nullable()
                  ->after('is_collective_leave')
                  ->comment('Toplu izin dönemi (örn: Yaz-2024)');
            
            // Hesaplama Verileri
            $table->integer('employee_age_at_request')
                  ->nullable()
                  ->after('collective_leave_period')
                  ->comment('Talep anındaki çalışan yaşı');
            
            $table->integer('employee_tenure_days')
                  ->nullable()
                  ->after('employee_age_at_request')
                  ->comment('Talep anındaki kıdem günü');
            
            $table->boolean('special_age_condition_applied')
                  ->default(false)
                  ->after('employee_tenure_days')
                  ->comment('Özel yaş koşulu uygulandı mı? (18- veya 50+)');
            
            // İzin Türü Detayları (leave_types tablosuna referans)
            $table->foreignId('leave_type_id')
                  ->nullable()
                  ->after('special_age_condition_applied')
                  ->constrained('leave_types')
                  ->onDelete('set null')
                  ->comment('İzin türü detayları');
            
            // Çakışma ve Kontrol
            $table->json('holiday_dates_in_period')
                  ->nullable()
                  ->after('leave_type_id')
                  ->comment('İzin dönemi içindeki tatil günleri');
            
            $table->integer('overlapping_holidays_count')
                  ->default(0)
                  ->after('holiday_dates_in_period')
                  ->comment('Çakışan tatil günü sayısı');
            
            // Yasal Uyumluluk
            $table->boolean('legal_minimum_notice_met')
                  ->default(true)
                  ->after('overlapping_holidays_count')
                  ->comment('Yasal minimum bildirim süresi karşılandı mı?');
            
            $table->boolean('legal_split_rule_compliant')
                  ->default(true)
                  ->after('legal_minimum_notice_met')
                  ->comment('Yasal bölme kuralına uygun mu? (min 10 gün)');
            
            // Sistem Entegrasyonu
            $table->json('calculation_details')
                  ->nullable()
                  ->after('legal_split_rule_compliant')
                  ->comment('İzin hesaplama detayları (JSON)');
            
            $table->boolean('automatic_calculation')
                  ->default(true)
                  ->after('calculation_details')
                  ->comment('Otomatik hesaplama yapıldı mı?');
            
            // Geçmiş İzinlerle İlişki
            $table->foreignId('previous_request_id')
                  ->nullable()
                  ->after('automatic_calculation')
                  ->constrained('leave_requests')
                  ->onDelete('set null')
                  ->comment('Önceki ilgili izin talebi');
            
            $table->foreignId('next_request_id')
                  ->nullable()
                  ->after('previous_request_id')
                  ->constrained('leave_requests')
                  ->onDelete('set null')
                  ->comment('Sonraki ilgili izin talebi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Foreign key'leri önce kaldır
            $table->dropForeign(['leave_type_id']);
            $table->dropForeign(['previous_request_id']);
            $table->dropForeign(['next_request_id']);
            
            // Kolonları kaldır
            $table->dropColumn([
                'leave_year',
                'road_leave_days',
                'road_leave_requested',
                'travel_destination',
                'is_advance_leave',
                'advance_leave_justification',
                'advance_leave_due_date',
                'is_collective_leave',
                'collective_leave_period',
                'employee_age_at_request',
                'employee_tenure_days',
                'special_age_condition_applied',
                'leave_type_id',
                'holiday_dates_in_period',
                'overlapping_holidays_count',
                'legal_minimum_notice_met',
                'legal_split_rule_compliant',
                'calculation_details',
                'automatic_calculation',
                'previous_request_id',
                'next_request_id'
            ]);
        });
    }
};