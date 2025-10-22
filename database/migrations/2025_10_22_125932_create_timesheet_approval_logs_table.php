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
        Schema::create('timesheet_approval_logs', function (Blueprint $table) {
            $table->id();

            // Hangi puantaj kaydı
            $table->foreignId('timesheet_v2_id')
                  ->constrained('timesheets_v2')
                  ->cascadeOnDelete();

            // Kim işlem yaptı
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // İşlem türü
            $table->enum('action', [
                'created',          // Oluşturuldu
                'updated',          // Güncellendi
                'submitted',        // Onaya gönderildi
                'approved',         // Onaylandı
                'rejected',         // Reddedildi
                'hr_override',      // İK müdahalesi
                'leave_cancelled'   // İzin iptal edildi
            ]);

            // Önceki durum
            $table->json('old_values')->nullable();

            // Yeni durum
            $table->json('new_values')->nullable();

            // Açıklama/Not
            $table->text('notes')->nullable();

            // IP adresi
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            // Index'ler
            $table->index(['timesheet_v2_id', 'created_at']);
            $table->index(['user_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_approval_logs');
    }
};
