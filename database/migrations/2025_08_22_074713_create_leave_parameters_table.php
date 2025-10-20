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
        // Eğer tablo yoksa oluştur
        if (!Schema::hasTable('leave_parameters')) {
            Schema::create('leave_parameters', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('parameter_key', 100)->unique();
                $table->text('description')->nullable();
                $table->enum('type', ['integer', 'decimal', 'boolean', 'string', 'date', 'json'])->default('string');
                $table->enum('category', [
                    'annual_leave', 
                    'sick_leave', 
                    'maternity_leave', 
                    'paternity_leave', 
                    'unpaid_leave', 
                    'calculation', 
                    'eligibility', 
                    'restrictions'
                ]);
                $table->text('default_value')->nullable();
                $table->decimal('min_value', 10, 2)->nullable();
                $table->decimal('max_value', 10, 2)->nullable();
                $table->text('validation_rules')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->boolean('is_system')->default(false);
                $table->boolean('is_editable')->default(true);
                $table->boolean('applies_to_all')->default(true);
                $table->json('employee_categories')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
                
                // Indexes
                $table->index(['category', 'status']);
                $table->index(['parameter_key']);
                $table->index(['status']);
                $table->index(['is_system']);
                $table->index(['created_by']);
            });
        } else {
            // Tablo varsa eksik kolonları ekle
            Schema::table('leave_parameters', function (Blueprint $table) {
                // Mevcut kolonları kontrol et ve eksikleri ekle
                if (!Schema::hasColumn('leave_parameters', 'name')) {
                    $table->string('name', 255)->after('id');
                }
                if (!Schema::hasColumn('leave_parameters', 'type')) {
                    $table->enum('type', ['integer', 'decimal', 'boolean', 'string', 'date', 'json'])->default('string')->after('description');
                }
                if (!Schema::hasColumn('leave_parameters', 'category')) {
                    $table->enum('category', [
                        'annual_leave', 
                        'sick_leave', 
                        'maternity_leave', 
                        'paternity_leave', 
                        'unpaid_leave', 
                        'calculation', 
                        'eligibility', 
                        'restrictions'
                    ])->after('type');
                }
                if (!Schema::hasColumn('leave_parameters', 'default_value')) {
                    $table->text('default_value')->nullable()->after('category');
                }
                if (!Schema::hasColumn('leave_parameters', 'min_value')) {
                    $table->decimal('min_value', 10, 2)->nullable()->after('default_value');
                }
                if (!Schema::hasColumn('leave_parameters', 'max_value')) {
                    $table->decimal('max_value', 10, 2)->nullable()->after('min_value');
                }
                if (!Schema::hasColumn('leave_parameters', 'status')) {
                    $table->enum('status', ['active', 'inactive'])->default('active')->after('validation_rules');
                }
                if (!Schema::hasColumn('leave_parameters', 'is_system')) {
                    $table->boolean('is_system')->default(false)->after('status');
                }
                if (!Schema::hasColumn('leave_parameters', 'is_editable')) {
                    $table->boolean('is_editable')->default(true)->after('is_system');
                }
                if (!Schema::hasColumn('leave_parameters', 'applies_to_all')) {
                    $table->boolean('applies_to_all')->default(true)->after('is_editable');
                }
                if (!Schema::hasColumn('leave_parameters', 'employee_categories')) {
                    $table->json('employee_categories')->nullable()->after('applies_to_all');
                }
                if (!Schema::hasColumn('leave_parameters', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('created_by');
                }
                if (!Schema::hasColumn('leave_parameters', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_parameters');
    }
};