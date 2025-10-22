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
        Schema::table('timesheets_v2', function (Blueprint $table) {
            // Onay durumu: draft (taslak), submitted (gönderildi), approved (onaylı), rejected (reddedildi)
            $table->enum('approval_status', ['draft', 'submitted', 'approved', 'rejected'])
                  ->default('draft')
                  ->after('notes');

            // Kim onayladı
            $table->foreignId('approved_by')
                  ->nullable()
                  ->after('approval_status')
                  ->constrained('users')
                  ->nullOnDelete();

            // Ne zaman onaylandı
            $table->timestamp('approved_at')
                  ->nullable()
                  ->after('approved_by');

            // Onay notları (red nedeni vs.)
            $table->text('approval_notes')
                  ->nullable()
                  ->after('approved_at');

            // İK müdahalesi flag (izin girişi/iptali için)
            $table->boolean('hr_override')
                  ->default(false)
                  ->after('approval_notes');

            $table->foreignId('hr_approved_by')
                  ->nullable()
                  ->after('hr_override')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('hr_approved_at')
                  ->nullable()
                  ->after('hr_approved_by');

            // Index'ler
            $table->index(['approval_status', 'work_date']);
            $table->index(['approved_by', 'approved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timesheets_v2', function (Blueprint $table) {
            $table->dropIndex(['approval_status', 'work_date']);
            $table->dropIndex(['approved_by', 'approved_at']);

            $table->dropForeign(['approved_by']);
            $table->dropForeign(['hr_approved_by']);

            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'approval_notes',
                'hr_override',
                'hr_approved_by',
                'hr_approved_at'
            ]);
        });
    }
};
