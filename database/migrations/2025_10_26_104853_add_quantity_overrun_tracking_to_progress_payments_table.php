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
        Schema::table('progress_payments', function (Blueprint $table) {
            // Metraj aşımı tracking
            $table->boolean('is_quantity_overrun')->default(false)->after('status');
            $table->decimal('overrun_amount', 15, 2)->nullable()->after('is_quantity_overrun');
            $table->text('overrun_notes')->nullable()->after('overrun_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_payments', function (Blueprint $table) {
            $table->dropColumn(['is_quantity_overrun', 'overrun_amount', 'overrun_notes']);
        });
    }
};
