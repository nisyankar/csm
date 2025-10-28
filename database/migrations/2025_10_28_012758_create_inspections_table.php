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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete()->comment('Proje');
            $table->foreignId('inspection_company_id')->constrained()->cascadeOnDelete()->comment('Denetim kuruluşu');
            $table->string('inspection_number')->unique()->comment('Denetim rapor no');
            $table->string('inspector_name')->comment('Denetçi adı');
            $table->date('inspection_date')->comment('Denetim tarihi');
            $table->enum('inspection_type', ['periodic', 'special', 'final'])->comment('Denetim türü: periodic=Periyodik, special=Özel, final=Final');
            $table->enum('status', ['scheduled', 'completed', 'pending_action', 'closed'])->default('scheduled')->comment('Durum: scheduled=Planlandı, completed=Tamamlandı, pending_action=Eylem Bekliyor, closed=Kapatıldı');
            $table->text('findings')->nullable()->comment('Genel bulgular');
            $table->json('non_conformities')->nullable()->comment('Uygunsuzluklar: [{description, severity, deadline, photo}]');
            $table->json('corrective_actions')->nullable()->comment('Düzeltici faaliyetler: [{action, responsible, deadline, status, completion_date}]');
            $table->json('attachments')->nullable()->comment('Ekler: [{name, path, type, size}]');
            $table->string('report_path')->nullable()->comment('Denetim raporu dosya yolu');
            $table->date('next_inspection_date')->nullable()->comment('Sonraki denetim tarihi');
            $table->text('notes')->nullable()->comment('Notlar');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'inspection_date']);
            $table->index('inspection_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
