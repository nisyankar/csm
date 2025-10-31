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
        Schema::create('route_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('route_name')->unique(); // projects.index, employees.create, etc.
            $table->string('module')->nullable(); // Projects, Employees, Finance, etc.
            $table->string('uri')->nullable(); // /projects, /employees/create
            $table->string('methods')->nullable(); // GET, POST, PUT, DELETE
            $table->string('category')->nullable(); // sub-module grouping
            $table->string('action')->nullable(); // view, create, edit, delete, export, etc.
            $table->string('display_name')->nullable(); // Human-readable name
            $table->text('description')->nullable();
            $table->json('allowed_roles')->nullable(); // ['admin', 'project_manager', 'hr']
            $table->json('allowed_permissions')->nullable(); // Spatie permission names
            $table->boolean('is_public')->default(false); // Public routes (login, register)
            $table->boolean('requires_project_access')->default(false); // Proje erişimi gerekli mi?
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('module');
            $table->index('category');
            $table->index('is_active');
            $table->index('requires_project_access');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_permissions');
    }
};
