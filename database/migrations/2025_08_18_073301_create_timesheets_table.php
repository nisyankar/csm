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
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            
            // Ana İlişkiler
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            
            // Tarih Bilgisi
            $table->date('work_date'); // Çalışma tarihi
            
            // Zaman Bilgileri
            $table->time('start_time')->nullable(); // Giriş saati
            $table->time('end_time')->nullable(); // Çıkış saati
            $table->time('break_start')->nullable(); // Mola başlangıç
            $table->time('break_end')->nullable(); // Mola bitiş
            
            // Hesaplanan Süreler (dakika cinsinden)
            $table->integer('total_minutes')->default(0); // Toplam çalışma dakikası
            $table->integer('regular_minutes')->default(0); // Normal mesai dakikası
            $table->integer('overtime_minutes')->default(0); // Fazla mesai dakikası
            $table->integer('break_minutes')->default(0); // Mola dakikası
            
            // Mesai Türleri
            $table->enum('shift_type', [
                'day',      // Gündüz mesaisi
                'night',    // Gece mesaisi
                'overtime', // Fazla mesai
                'weekend',  // Hafta sonu
                'holiday'   // Tatil günü
            ])->default('day');
            
            // Özel Durumlar
            $table->enum('attendance_type', [
                'present',     // Normal çalışma
                'absent',      // Devamsız
                'late',        // Geç gelme
                'early_leave', // Erken çıkma
                'sick_leave',  // Hastalık izni
                'annual_leave', // Yıllık izin
                'excuse_leave', // Mazeret izni
                'unpaid_leave'  // Ücretsiz izin
            ])->default('present');
            
            // Puantaj Girişi
            $table->enum('entry_method', [
                'manual',   // Elle giriş
                'qr_code',  // QR kod ile
                'biometric', // Biyometrik (gelecek için)
                'system'    // Sistem otomatik (izin vb.)
            ])->default('manual');
            
            // Giriş Yapan
            $table->foreignId('entered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('entered_at')->nullable();
            
            // Onay Durumu
            $table->enum('approval_status', [
                'draft',     // Taslak
                'pending',   // Onay bekliyor
                'approved',  // Onaylandı
                'rejected',  // Reddedildi
                'revision'   // Revizyon gerekli
            ])->default('draft');
            
            // Onay Bilgileri
            $table->timestamp('submitted_at')->nullable(); // Onaya gönderilme tarihi
            $table->timestamp('first_approved_at')->nullable(); // 1. onay tarihi
            $table->timestamp('final_approved_at')->nullable(); // 2. onay tarihi
            
            // Lokasyon Bilgisi (GPS ile giriş yapılırsa)
            $table->string('entry_location')->nullable();
            $table->string('exit_location')->nullable();
            
            // Açıklamalar
            $table->text('notes')->nullable(); // Genel notlar
            $table->text('absence_reason')->nullable(); // Devamsızlık sebebi
            $table->text('late_reason')->nullable(); // Geç kalma sebebi
            
            // Ücret Hesaplama
            $table->decimal('daily_rate', 8, 2)->nullable(); // O günkü günlük ücret
            $table->decimal('hourly_rate', 8, 2)->nullable(); // O günkü saatlik ücret
            $table->decimal('calculated_wage', 8, 2)->default(0); // Hesaplanan ücret
            
            // Revizyon Takibi
            $table->boolean('is_revised')->default(false); // Revize edildi mi?
            $table->integer('revision_count')->default(0); // Revizyon sayısı
            $table->foreignId('original_timesheet_id')->nullable()->constrained('timesheets')->onDelete('set null');
            
            $table->timestamps();
            
            // İndeksler
            $table->index(['employee_id', 'work_date']);
            $table->index(['project_id', 'work_date']);
            $table->index(['approval_status']);
            $table->index(['work_date']);
            $table->index(['attendance_type']);
            
            // Unique constraint (aynı personel aynı tarihte birden fazla puantaj olamaz)
            $table->unique(['employee_id', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};