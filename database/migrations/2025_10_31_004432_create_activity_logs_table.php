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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('activity_type'); // created, updated, deleted, viewed, logged_in, etc.
            $table->string('subject_type')->nullable(); // Model class name (polymorphic)
            $table->unsignedBigInteger('subject_id')->nullable(); // Model ID (polymorphic)
            $table->string('action'); // Human-readable action description
            $table->text('description')->nullable(); // Detailed description
            $table->json('properties')->nullable(); // old_values, new_values, attributes
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('route_name')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, DELETE
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null'); // İlgili proje
            $table->string('severity')->default('info'); // info, warning, error, critical
            $table->boolean('is_system_generated')->default(false);
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'logged_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['activity_type', 'logged_at']);
            $table->index(['project_id', 'logged_at']);
            $table->index('logged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
