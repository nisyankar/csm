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
        Schema::table('employees', function (Blueprint $table) {
            // Taşeron çalışanı mı?
            $table->boolean('is_subcontractor_employee')->default(false)->after('status');

            // Hangi taşeronun çalışanı (null ise direkt şirket çalışanı)
            $table->foreignId('subcontractor_id')
                ->nullable()
                ->after('is_subcontractor_employee')
                ->constrained('subcontractors')
                ->onDelete('set null');

            // Taşeron ile sözleşme başlangıç/bitiş
            $table->date('subcontractor_contract_start')->nullable()->after('subcontractor_id');
            $table->date('subcontractor_contract_end')->nullable()->after('subcontractor_contract_start');

            // Index
            $table->index(['is_subcontractor_employee', 'subcontractor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['subcontractor_id']);
            $table->dropIndex(['is_subcontractor_employee', 'subcontractor_id']);
            $table->dropColumn([
                'is_subcontractor_employee',
                'subcontractor_id',
                'subcontractor_contract_start',
                'subcontractor_contract_end'
            ]);
        });
    }
};
