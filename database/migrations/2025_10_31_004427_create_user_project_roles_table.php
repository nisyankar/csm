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
        Schema::create('user_project_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('role'); // project_manager, site_manager, engineer, foreman, viewer
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('responsibilities')->nullable(); // JSON: specific responsibilities
            $table->text('permissions')->nullable(); // JSON: specific permissions for this project
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'project_id']);
            $table->index(['project_id', 'role']);
            $table->index('is_active');

            // Unique: Bir kullanıcı aynı projede aynı rolde birden fazla olamaz (aktif kayıtlar için)
            $table->unique(['user_id', 'project_id', 'role'], 'unique_user_project_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_project_roles');
    }
};
