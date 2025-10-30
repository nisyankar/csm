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
        Schema::create('ppe_assignments', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');

            // KKD Bilgileri
            $table->enum('ppe_type', [
                'helmet',           // Baret
                'safety_boots',     // İş ayakkabısı
                'gloves',           // Eldiven
                'goggles',          // Koruyucu gözlük
                'vest',             // Reflektörlü yelek
                'harness',          // Emniyet kemeri
                'respirator',       // Solunum maskesi
                'ear_protection',   // Kulak koruyucu
                'face_shield',      // Yüz siperi
                'coverall',         // Tulum
                'knee_pads',        // Dizlik
                'dust_mask',        // Toz maskesi
                'welding_mask',     // Kaynak maskesi
                'fall_arrest',      // Düşüş durdurma sistemi
                'other'             // Diğer
            ]);

            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('size')->nullable(); // Beden
            $table->string('serial_number')->nullable();
            $table->text('description')->nullable();

            // Atama Bilgileri
            $table->date('assigned_date');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->date('return_date')->nullable();
            $table->foreignId('returned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('return_condition', [
                'good',      // İyi durumda
                'fair',      // Kullanılabilir
                'poor',      // Kötü durumda
                'damaged',   // Hasarlı
                'lost'       // Kayıp
            ])->nullable();

            // Durum
            $table->enum('status', [
                'assigned',  // Atanmış
                'returned',  // İade edildi
                'lost',      // Kayıp
                'damaged',   // Hasarlı
                'expired',   // Süresi doldu
                'replaced'   // Değiştirildi
            ])->default('assigned');

            // Miktar ve Fiyat
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();

            // Geçerlilik ve Kontrol
            $table->date('expiry_date')->nullable(); // Bazı ekipmanların kullanım ömrü var
            $table->boolean('inspection_required')->default(false);
            $table->date('last_inspection_date')->nullable();
            $table->date('next_inspection_date')->nullable();
            $table->text('inspection_notes')->nullable();

            // Sertifika Bilgileri (özellikle emniyet kemeri, düşüş durdurma vb için)
            $table->string('certificate_number')->nullable();
            $table->date('certificate_expiry')->nullable();

            // Notlar
            $table->text('notes')->nullable();

            // Değiştirme Bilgisi
            $table->foreignId('replaced_by_assignment_id')->nullable()->constrained('ppe_assignments')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['project_id', 'employee_id']);
            $table->index(['ppe_type', 'status']);
            $table->index(['assigned_date']);
            $table->index(['expiry_date']);
            $table->index(['next_inspection_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppe_assignments');
    }
};
