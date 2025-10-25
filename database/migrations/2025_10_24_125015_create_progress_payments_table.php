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
        Schema::create('progress_payments', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcontractor_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_item_id')->constrained()->onDelete('cascade');

            // Referans - Opsiyonel (Blok/Kat/Daire)
            $table->foreignId('project_structure_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('project_floor_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('project_unit_id')->nullable()->constrained()->onDelete('set null');

            // Metraj Bilgileri
            $table->decimal('planned_quantity', 12, 2); // Planlanan metraj
            $table->decimal('completed_quantity', 12, 2)->default(0); // Tamamlanan metraj
            $table->string('unit', 50)->default('m²'); // Birim (m², m³, adet, kg, vb)

            // Fiyat Bilgileri
            $table->decimal('unit_price', 12, 2); // Birim fiyat (TL)
            $table->decimal('total_amount', 12, 2)->storedAs('completed_quantity * unit_price'); // Toplam tutar (otomatik hesaplanan)

            // Hakediş Bilgileri
            $table->date('payment_date')->nullable(); // Ödeme tarihi
            $table->enum('status', ['planned', 'in_progress', 'completed', 'approved', 'paid'])->default('planned');

            // Dönem bilgisi
            $table->integer('period_year')->nullable(); // Hangi yıl
            $table->integer('period_month')->nullable(); // Hangi ay

            // Notlar
            $table->text('notes')->nullable();

            // Onay bilgileri
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['project_id', 'status']);
            $table->index(['subcontractor_id', 'status']);
            $table->index(['work_item_id']);
            $table->index(['period_year', 'period_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_payments');
    }
};
