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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            
            // Kişisel Bilgiler
            $table->string('employee_code')->unique(); // Personel kodu
            $table->string('first_name');
            $table->string('last_name');
            $table->string('tc_number', 11)->unique();
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique()->nullable();
            $table->text('address')->nullable();
            
            // İş Bilgileri
            $table->string('position'); // Pozisyon (işçi, usta, mühendis, vs)
            $table->string('category')->default('worker'); // worker, foreman, engineer, manager
            $table->date('start_date'); // İşe başlama tarihi
            $table->date('end_date')->nullable(); // İşten ayrılma tarihi
            $table->decimal('daily_wage', 8, 2)->nullable(); // Günlük ücret
            $table->decimal('hourly_wage', 8, 2)->nullable(); // Saatlik ücret
            $table->enum('wage_type', ['daily', 'hourly', 'monthly'])->default('daily');
            
            // Hiyerarşi
            $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Login için
            
            // Proje İlişkisi (ana proje) - Bu alan daha sonra eklenecek
            $table->unsignedBigInteger('current_project_id')->nullable();
            
            // Durum
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            
            // QR Kod
            $table->string('qr_code')->unique()->nullable();
            
            // Profil Fotoğrafı
            $table->string('photo_path')->nullable();
            
            // İzin Hakları
            $table->integer('annual_leave_days')->default(14); // Yıllık izin hakkı
            $table->integer('used_leave_days')->default(0); // Kullanılan izin
            
            $table->timestamps();
            $table->softDeletes(); // Soft delete için
            
            // İndeksler
            $table->index(['status', 'current_project_id']);
            $table->index(['manager_id']);
            $table->index(['category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};