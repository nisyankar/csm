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
        Schema::create('temporary_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('from_project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('to_project_id')->constrained('projects')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('employee_id');
            $table->index('from_project_id');
            $table->index('to_project_id');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });

        // Add temporary_assignment_id to timesheets table
        Schema::table('timesheets', function (Blueprint $table) {
            $table->foreignId('temporary_assignment_id')->nullable()->after('project_id')->constrained('temporary_assignments')->onDelete('set null');
            $table->index('temporary_assignment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove column from timesheets table
        Schema::table('timesheets', function (Blueprint $table) {
            $table->dropForeign(['temporary_assignment_id']);
            $table->dropColumn('temporary_assignment_id');
        });

        Schema::dropIfExists('temporary_assignments');
    }
};
