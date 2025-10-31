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
        Schema::create('project_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('task_code', 20)->unique(); // TASK-001, TASK-002
            $table->string('task_name');
            $table->text('task_description')->nullable();
            $table->enum('task_type', ['phase', 'milestone', 'activity', 'deliverable', 'meeting'])->default('activity');

            // Dates and duration
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration')->nullable(); // Calculated in days
            $table->integer('progress')->default(0); // 0-100

            // Status and priority
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'delayed', 'on_hold', 'cancelled'])->default('not_started');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');

            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');

            // Hierarchy
            $table->foreignId('parent_task_id')->nullable()->constrained('project_schedules')->onDelete('cascade');

            // Dependencies (JSON array of task IDs with dependency type)
            // Example: [{"task_id": 5, "type": "FS"}, {"task_id": 7, "type": "SS"}]
            // FS = Finish-to-Start, SS = Start-to-Start, FF = Finish-to-Finish, SF = Start-to-Finish
            $table->json('predecessors')->nullable();

            // Financial
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->nullable();

            // Progress tracking
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->integer('completion_percentage')->default(0); // 0-100

            // Integration with other modules
            $table->string('reference_type')->nullable(); // e.g., 'ProgressPayment', 'WorkItem'
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of related record

            // Additional info
            $table->text('notes')->nullable();
            $table->string('color', 7)->nullable(); // Hex color for Gantt display

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['project_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index(['assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_schedules');
    }
};
