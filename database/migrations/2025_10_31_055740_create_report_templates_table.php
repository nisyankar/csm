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
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['pdf', 'excel'])->default('pdf');
            $table->enum('module', [
                'progress_payments',
                'timesheets',
                'financials',
                'safety',
                'equipment',
                'stock',
                'quantities',
                'projects'
            ]);
            $table->string('template_path')->nullable();
            $table->json('parameters_json')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
