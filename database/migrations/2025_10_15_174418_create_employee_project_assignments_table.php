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
        Schema::create('employee_project_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');

            $table->boolean('is_primary')->default(false)->comment('Ana proje mi?');
            $table->date('start_date')->comment('Atanma başlangıç tarihi');
            $table->date('end_date')->nullable()->comment('Atanma bitiş tarihi');

            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->string('role_in_project')->nullable()->comment('Projedeki görevi');
            $table->text('notes')->nullable();

            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index(['employee_id', 'project_id', 'start_date'], 'idx_emp_proj_start');
            $table->index(['employee_id', 'is_primary'], 'idx_emp_primary');
            $table->index(['project_id', 'status'], 'idx_proj_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_project_assignments');
    }
};
