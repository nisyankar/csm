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
        Schema::create('work_item_assignments', function (Blueprint $table) {
            $table->id();

            // Proje ve Lokasyon İlişkileri
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('structure_id')->nullable()->constrained('project_structures')->onDelete('cascade');
            $table->foreignId('floor_id')->nullable()->constrained('project_floors')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained('project_units')->onDelete('cascade');

            // İş Kalemi
            $table->foreignId('work_item_id')->constrained('work_items')->onDelete('cascade');

            // Taşeron/Ekip Bilgisi
            $table->enum('assignment_type', ['subcontractor', 'internal_team'])->default('subcontractor');
            $table->foreignId('subcontractor_id')->nullable()->constrained('subcontractors')->onDelete('set null');
            $table->foreignId('supervisor_id')->nullable()->constrained('employees')->onDelete('set null');

            // Miktar ve Maliyet
            $table->decimal('quantity', 12, 2); // Toplam miktar
            $table->string('unit', 20); // Birim (m2, m3, adet)
            $table->decimal('unit_price', 12, 2)->default(0); // Birim fiyat
            $table->decimal('total_price', 15, 2)->default(0); // Toplam tutar
            $table->decimal('completed_quantity', 12, 2)->default(0); // Tamamlanan miktar
            $table->decimal('remaining_quantity', 12, 2)->default(0); // Kalan miktar

            // Durum ve İlerleme
            $table->enum('status', [
                'not_started',
                'in_progress',
                'completed',
                'on_hold',
                'cancelled'
            ])->default('not_started');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->decimal('progress_percentage', 5, 2)->default(0); // 0-100

            // Tarih
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->timestamp('last_progress_update')->nullable();

            // İlave
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['project_id', 'status']);
            $table->index(['structure_id', 'work_item_id']);
            $table->index(['subcontractor_id']);
            $table->index(['supervisor_id']);
            $table->index(['status', 'priority']);
            $table->index(['planned_start_date', 'planned_end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_item_assignments');
    }
};
