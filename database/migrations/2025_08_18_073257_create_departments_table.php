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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            
            // Bölüm Temel Bilgileri
            $table->string('code')->unique(); // Bölüm kodu (ZMN, YPI, ELK, TST vb.)
            $table->string('name'); // Bölüm adı
            $table->text('description')->nullable(); // Bölüm açıklaması
            
            // Proje İlişkisi
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            
            // Hiyerarşik Yapı (Alt bölümler için)
            $table->foreignId('parent_department_id')->nullable()->constrained('departments')->onDelete('cascade');
            
            // Bölüm Yönetimi
            $table->foreignId('supervisor_id')->nullable()->constrained('employees')->onDelete('set null');
            
            // Bölüm Türü
            $table->enum('type', [
                'structural',    // Yapısal (zemin, yapı, çatı)
                'mechanical',    // Mekanik (tesisat, havalandırma)
                'electrical',    // Elektrik
                'finishing',     // Finishing (boyar badana, döşeme)
                'landscaping',   // Peyzaj
                'safety',        // İş güvenliği
                'quality',       // Kalite kontrol
                'logistics',     // Lojistik
                'administration', // İdari
                'other'          // Diğer
            ]);
            
            // Mali Bilgiler
            $table->decimal('budget', 12, 2)->nullable(); // Bölüm bütçesi
            $table->decimal('spent_amount', 12, 2)->default(0); // Harcanan miktar
            
            // Durum
            $table->enum('status', [
                'not_started',   // Başlanmadı
                'in_progress',   // Devam ediyor
                'completed',     // Tamamlandı
                'on_hold',       // Beklemede
                'cancelled'      // İptal edildi
            ])->default('not_started');
            
            // Tarih Bilgileri
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            
            // Öncelik
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            
            // İlave Bilgiler
            $table->integer('estimated_employees')->nullable(); // Tahmini personel ihtiyacı
            $table->text('notes')->nullable(); // Notlar
            $table->json('custom_fields')->nullable(); // Gelecekte genişletme için
            
            // Lokasyon (Proje içindeki konum)
            $table->string('location_description')->nullable(); // "2. Kat", "Bodrum", "Bahçe" vb.
            
            $table->timestamps();
            $table->softDeletes();
            
            // İndeksler
            $table->index(['project_id', 'status']);
            $table->index(['type']);
            $table->index(['parent_department_id']);
            $table->index(['supervisor_id']);
            $table->index(['planned_start_date', 'planned_end_date']);
            
            // Unique constraint (aynı projede aynı kod olamaz)
            $table->unique(['project_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};