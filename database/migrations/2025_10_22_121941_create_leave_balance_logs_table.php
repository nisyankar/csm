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
        Schema::create('leave_balance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_balance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_request_id')->nullable()->constrained()->cascadeOnDelete();

            // Değişiklik bilgileri
            $table->string('action'); // earned, used, adjusted, carried_forward, expired, cancelled
            $table->decimal('amount', 5, 2); // Değişim miktarı (+ veya -)
            $table->decimal('balance_before', 5, 2); // Önceki bakiye
            $table->decimal('balance_after', 5, 2); // Sonraki bakiye

            // Detaylar
            $table->string('reason')->nullable(); // Neden
            $table->text('description')->nullable(); // Açıklama
            $table->json('metadata')->nullable(); // Ek bilgiler

            // Yapan kişi
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->string('source')->nullable(); // system, manual, approval, adjustment

            $table->timestamps();

            // İndeksler
            $table->index('leave_balance_id');
            $table->index('employee_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balance_logs');
    }
};
