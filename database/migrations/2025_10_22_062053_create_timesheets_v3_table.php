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
        Schema::create('timesheets_v3', function (Blueprint $table) {
            $table->id();

            // Core fields
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->date('work_date');

            // Time tracking
            $table->decimal('hours_worked', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->string('overtime_type')->nullable(); // 'weekday', 'weekend', 'holiday'
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('break_duration', 5, 2)->default(0);

            // Leave integration - CRITICAL
            $table->foreignId('leave_request_id')->nullable()->constrained('leave_requests')->onDelete('set null');
            $table->boolean('auto_generated_from_leave')->default(false);
            $table->boolean('is_leave_day')->default(false);
            $table->string('leave_type')->nullable(); // 'annual', 'sick', etc.

            // Locking & approval
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // Weekly overtime calculation cache
            $table->integer('week_number')->nullable();
            $table->integer('year')->nullable();
            $table->decimal('weekly_total_hours', 5, 2)->nullable();
            $table->decimal('weekly_required_hours', 5, 2)->nullable();
            $table->decimal('weekly_overtime_hours', 5, 2)->nullable();
            $table->boolean('week_calculation_done')->default(false);

            // Metadata
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            // Entry method tracking
            $table->enum('entry_method', ['manual', 'bulk', 'import', 'leave_sync', 'system'])->default('manual');
            $table->foreignId('entered_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Indexes
            $table->unique(['employee_id', 'work_date', 'project_id'], 'unique_employee_work_date');
            $table->index('work_date');
            $table->index(['year', 'week_number']);
            $table->index('is_leave_day');
            $table->index('auto_generated_from_leave');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheets_v3');
    }
};
