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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            // Sözleşme tipi
            $table->enum('contract_type', ['subcontractor', 'supplier'])
                ->default('subcontractor')
                ->comment('Sözleşme tipi: taşeron veya tedarikçi');

            // Temel bilgiler
            $table->string('contract_number')->unique()->comment('Sözleşme numarası');
            $table->string('contract_name')->comment('Sözleşme adı');

            // İlişkiler
            $table->foreignId('project_id')->constrained()->onDelete('cascade')->comment('Proje');
            $table->foreignId('subcontractor_id')->nullable()->constrained()->onDelete('cascade')->comment('Taşeron (sadece subcontractor type için)');
            // Not: supplier_id için Supplier modeli olmadığından şimdilik sadece subcontractor

            // İş tanımı
            $table->text('work_description')->nullable()->comment('İş tanımı');
            $table->text('scope_of_work')->nullable()->comment('İş kapsamı detayı');

            // Mali şartlar
            $table->decimal('contract_value', 15, 2)->comment('Sözleşme bedeli');
            $table->string('currency', 3)->default('TRY')->comment('Para birimi');
            $table->text('payment_terms')->nullable()->comment('Ödeme koşulları');

            // Tarihler
            $table->date('signing_date')->nullable()->comment('İmza tarihi');
            $table->date('start_date')->comment('Başlangıç tarihi');
            $table->date('end_date')->comment('Bitiş tarihi');

            // Teminat bilgileri
            $table->decimal('warranty_amount', 15, 2)->nullable()->comment('Teminat tutarı');
            $table->enum('warranty_type', ['bank_letter', 'cash', 'check', 'none'])
                ->default('none')
                ->comment('Teminat türü');
            $table->date('warranty_start_date')->nullable()->comment('Teminat başlangıç tarihi');
            $table->date('warranty_end_date')->nullable()->comment('Teminat bitiş tarihi');

            // Durum
            $table->enum('status', ['draft', 'active', 'completed', 'terminated', 'expired'])
                ->default('draft')
                ->comment('Sözleşme durumu');

            // Fesih bilgileri
            $table->date('termination_date')->nullable()->comment('Fesih tarihi');
            $table->text('termination_reason')->nullable()->comment('Fesih nedeni');

            // Notlar ve belgeler
            $table->text('notes')->nullable()->comment('Notlar');
            $table->json('documents')->nullable()->comment('Belgeler (JSON)');

            // Sistem alanları
            $table->foreignId('created_by')->nullable()->constrained('users')->comment('Oluşturan kullanıcı');
            $table->foreignId('updated_by')->nullable()->constrained('users')->comment('Güncelleyen kullanıcı');
            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['project_id', 'contract_type']);
            $table->index(['subcontractor_id', 'status']);
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
