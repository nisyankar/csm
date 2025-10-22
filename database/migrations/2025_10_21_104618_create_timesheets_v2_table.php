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
        Schema::create('timesheets_v2', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('restrict');

            // Tarih ve süre
            $table->date('work_date');
            $table->decimal('hours_worked', 5, 2)->default(0); // Çalışılan saat

            // Proje detayları (Faz 1 ile entegrasyon)
            $table->foreignId('structure_id')->nullable()->constrained('project_structures')->onDelete('set null');
            $table->foreignId('floor_id')->nullable()->constrained('project_floors')->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained('project_units')->onDelete('set null');
            $table->foreignId('work_item_id')->nullable()->constrained('work_items')->onDelete('set null');

            // Notlar
            $table->text('notes')->nullable();

            // Ek bilgiler
            $table->json('metadata')->nullable();

            // Kim girdi
            $table->foreignId('entered_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('entered_at')->useCurrent();

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->unique(['employee_id', 'work_date', 'project_id']);
            $table->index(['work_date', 'project_id']);
            $table->index('shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheets_v2');
    }
};
