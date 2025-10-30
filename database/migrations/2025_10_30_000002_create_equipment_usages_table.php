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
        Schema::create('equipment_usages', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('equipment_id')->constrained('equipments')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');

            // Kullanım Bilgileri
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Süre Hesaplaması
            $table->decimal('duration_days', 8, 2)->nullable(); // Gün cinsinden
            $table->decimal('duration_hours', 10, 2)->nullable(); // Saat cinsinden

            // Operatör ve Şantiye Bilgisi
            $table->foreignId('operator_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->string('operator_name')->nullable(); // Operatör adı (manuel giriş)
            $table->string('work_site_location')->nullable(); // Şantiye lokasyonu
            $table->text('work_description')->nullable(); // Yapılan iş

            // Sayaç Bilgileri
            $table->integer('meter_start')->nullable(); // Başlangıç saati (km/saat/çalışma saati)
            $table->integer('meter_end')->nullable(); // Bitiş saati
            $table->integer('meter_total')->nullable(); // Toplam kullanım
            $table->enum('meter_unit', ['hours', 'kilometers', 'cycles'])->default('hours');

            // Yakıt Tüketimi
            $table->decimal('fuel_consumed', 10, 2)->nullable(); // Tüketilen yakıt (litre)
            $table->decimal('fuel_cost', 10, 2)->nullable(); // Yakıt maliyeti

            // Kira Maliyeti (Kiralık ekipman için)
            $table->decimal('rental_cost', 10, 2)->nullable();
            $table->enum('rental_period_type', ['daily', 'weekly', 'monthly'])->nullable();

            // Durum ve Notlar
            $table->enum('status', [
                'ongoing',      // Devam ediyor
                'completed',    // Tamamlandı
                'interrupted',  // Kesintiye uğradı
            ])->default('ongoing');

            $table->text('notes')->nullable();
            $table->json('issues_reported')->nullable(); // Bildirilen sorunlar

            // Finansal Entegrasyon
            $table->boolean('cost_recorded')->default(false); // Maliyet kaydedildi mi?
            $table->foreignId('financial_transaction_id')->nullable()->constrained()->onDelete('set null');

            // Kim Oluşturdu
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['equipment_id', 'start_date']);
            $table->index(['project_id', 'start_date']);
            $table->index(['operator_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_usages');
    }
};
