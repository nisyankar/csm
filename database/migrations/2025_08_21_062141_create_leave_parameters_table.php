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
        Schema::create('leave_parameters', function (Blueprint $table) {
            $table->id();
            
            // Parametre Bilgileri
            $table->string('parameter_group', 50); // basic, special, business, calculation
            $table->string('parameter_key', 100)->unique(); // unique key
            $table->string('parameter_name'); // Görünen ad
            $table->text('parameter_value'); // JSON veya string değer
            $table->enum('data_type', ['integer', 'decimal', 'boolean', 'string', 'json'])->default('string');
            $table->string('unit', 20)->nullable(); // gün, saat, %, etc.
            
            // Açıklama ve Yardım
            $table->text('description')->nullable();
            $table->text('help_text')->nullable();
            $table->json('validation_rules')->nullable(); // min, max, required vs
            
            // Geçerlilik
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system_parameter')->default(false); // Sistem parametresi silinemesin
            $table->date('effective_from'); // Geçerlilik başlangıcı
            $table->date('effective_until')->nullable(); // Geçerlilik bitişi
            
            // Değişiklik Takibi
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('change_reason')->nullable(); // Değişiklik sebebi
            
            // Önceki Değer Takibi
            $table->text('previous_value')->nullable();
            $table->timestamp('previous_value_date')->nullable();
            
            $table->timestamps();
            
            // İndeksler
            $table->index(['parameter_group', 'is_active']);
            $table->index(['parameter_key']);
            $table->index(['effective_from', 'effective_until']);
            $table->index(['is_active', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_parameters');
    }
};