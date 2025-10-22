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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->year('year'); // İzin yılı
            $table->string('leave_type'); // annual, sick, marriage, vb.

            // İzin bakiye bilgileri
            $table->decimal('entitled_days', 5, 2)->default(0); // Hak edilen izin
            $table->decimal('carried_forward_days', 5, 2)->default(0); // Geçen yıldan devir
            $table->decimal('earned_days', 5, 2)->default(0); // Kazanılan (accrued)
            $table->decimal('adjustment_days', 5, 2)->default(0); // Manuel düzeltme
            $table->decimal('total_days', 5, 2)->default(0); // Toplam kullanılabilir

            // Kullanım bilgileri
            $table->decimal('used_days', 5, 2)->default(0); // Kullanılan
            $table->decimal('planned_days', 5, 2)->default(0); // Planlanmış (onay bekleyen)
            $table->decimal('remaining_days', 5, 2)->default(0); // Kalan

            // Yasal bilgiler
            $table->integer('seniority_years')->default(0); // Kıdem yılı
            $table->decimal('legal_entitlement', 5, 2)->default(0); // Yasal hak
            $table->boolean('is_prorated')->default(false); // Orantısal mı?
            $table->date('calculation_date')->nullable(); // Hesaplama tarihi
            $table->date('entitlement_start_date')->nullable(); // Hak başlangıcı
            $table->date('entitlement_end_date')->nullable(); // Hak bitişi

            // Devir bilgileri
            $table->decimal('max_carry_forward', 5, 2)->nullable(); // Max devir
            $table->date('expiry_date')->nullable(); // Son kullanma

            // Metadata
            $table->string('status')->default('active'); // active, expired, cancelled
            $table->text('notes')->nullable();
            $table->json('calculation_details')->nullable(); // Hesaplama detayları
            $table->foreignId('calculated_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->unique(['employee_id', 'year', 'leave_type']);
            $table->index(['year', 'leave_type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
