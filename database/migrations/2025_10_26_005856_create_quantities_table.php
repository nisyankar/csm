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
        Schema::create('quantities', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('work_item_id')->constrained('work_items')->onDelete('cascade');

            // Opsiyonel Lokasyon Bilgileri
            $table->foreignId('project_structure_id')->nullable()->constrained('project_structures')->onDelete('set null');
            $table->foreignId('project_floor_id')->nullable()->constrained('project_floors')->onDelete('set null');
            $table->foreignId('project_unit_id')->nullable()->constrained('project_units')->onDelete('set null');

            // Metraj Bilgileri
            $table->decimal('planned_quantity', 12, 2)->default(0);
            $table->decimal('completed_quantity', 12, 2)->default(0);
            $table->string('unit', 50); // m², m³, adet, kg, ton, vb.

            // Ölçüm Bilgileri
            $table->date('measurement_date')->nullable();
            $table->string('measurement_method', 100)->nullable(); // Manuel, Lazer, Drone, vb.

            // Onay ve Doğrulama
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            // Notlar
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['project_id', 'work_item_id']);
            $table->index('measurement_date');
            $table->index(['project_structure_id', 'project_floor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quantities');
    }
};
