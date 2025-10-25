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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->enum('transaction_type', ['income', 'expense']);
            $table->foreignId('category_id'); // income_categories veya expense_categories
            $table->date('transaction_date');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();

            // Otomatik entegrasyon için
            $table->string('source_module')->nullable(); // 'timesheet', 'purchasing', 'progress_payment', 'sale'
            $table->unsignedBigInteger('source_id')->nullable(); // İlgili kayıt ID'si

            // Fatura bilgileri
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();

            // Ödeme bilgileri
            $table->enum('payment_method', ['cash', 'bank_transfer', 'credit_card', 'check', 'other'])->nullable();
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'cancelled'])->default('pending');
            $table->decimal('paid_amount', 15, 2)->default(0);

            // Muhasebe bilgileri
            $table->string('accounting_code')->nullable();
            $table->text('notes')->nullable();

            // Onay bilgileri
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['project_id', 'transaction_type', 'transaction_date'], 'ft_project_type_date_idx');
            $table->index(['category_id', 'transaction_type'], 'ft_category_type_idx');
            $table->index(['source_module', 'source_id'], 'ft_source_idx');
            $table->index(['payment_status', 'transaction_date'], 'ft_payment_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
