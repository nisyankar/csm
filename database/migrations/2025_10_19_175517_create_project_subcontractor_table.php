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
        Schema::create('project_subcontractor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcontractor_id')->constrained()->onDelete('cascade');

            // Atama bilgileri
            $table->date('assigned_date')->default(now());
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');

            // İş tanımı - ZORUNLU (aynı taşeron farklı işler için atanabilir)
            $table->string('work_type', 100); // İş türü (Elektrik, Sıhhi Tesisat vs.)
            $table->text('scope_of_work'); // İş kapsamı detayı
            $table->decimal('contract_amount', 15, 2)->nullable(); // Sözleşme tutarı
            $table->enum('status', ['active', 'completed', 'terminated', 'suspended'])->default('active');

            // Notlar
            $table->text('notes')->nullable();

            $table->timestamps();

            // NOT: Unique constraint YOK - aynı taşeron birden fazla iş için atanabilir
            // Örnek: XYZ Elektrik hem "Elektrik Tesisatı" hem de "Aydınlatma Sistemi" için

            // Index'ler
            $table->index(['project_id', 'subcontractor_id', 'status']);
            $table->index('status');
            $table->index('assigned_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_subcontractor');
    }
};
