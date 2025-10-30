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
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();

            // Ekipman Bilgileri
            $table->string('code')->unique(); // Ekipman kodu (EKP-001)
            $table->string('name'); // Ekipman adı
            $table->enum('type', [
                'excavator',        // Ekskavatör
                'bulldozer',        // Dozer
                'crane',            // Vinç
                'loader',           // Yükleyici
                'grader',           // Greyder
                'roller',           // Silindir
                'forklift',         // Forklift
                'concrete_mixer',   // Beton mikseri
                'pump',             // Pompa
                'generator',        // Jeneratör
                'compressor',       // Kompresör
                'welding_machine',  // Kaynak makinesi
                'scaffolding',      // İskele
                'vehicle',          // Araç
                'tower_crane',      // Kule vinç
                'mobile_crane',     // Mobil vinç
                'other'             // Diğer
            ]);

            // Marka ve Model
            $table->string('brand')->nullable(); // Marka
            $table->string('model')->nullable(); // Model
            $table->string('serial_number')->nullable(); // Seri numarası
            $table->year('manufacture_year')->nullable(); // Üretim yılı

            // Teknik Özellikler
            $table->json('specifications')->nullable(); // Teknik özellikler (kapasite, güç, vb)
            $table->text('description')->nullable();

            // Sahiplik
            $table->enum('ownership', [
                'owned',    // Şirket malı
                'rented',   // Kiralık
                'leased'    // Leasingli
            ])->default('owned');

            // Kira Bilgileri (Kiralık ise)
            $table->string('rental_company')->nullable(); // Kira şirketi
            $table->decimal('rental_cost_daily', 10, 2)->nullable(); // Günlük kira maliyeti
            $table->decimal('rental_cost_monthly', 10, 2)->nullable(); // Aylık kira maliyeti

            // Satın Alma Bilgileri
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->string('supplier')->nullable();

            // Durumu ve Lokasyonu
            $table->enum('status', [
                'available',        // Müsait
                'in_use',          // Kullanımda
                'maintenance',     // Bakımda
                'repair',          // Onarımda
                'out_of_service',  // Hizmet Dışı
                'retired'          // Emekli
            ])->default('available');

            $table->foreignId('current_project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->string('current_location')->nullable(); // Güncel konum

            // Sigorta
            $table->boolean('insured')->default(false);
            $table->string('insurance_company')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->date('insurance_expiry_date')->nullable();

            // Bakım Bilgileri
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->integer('maintenance_interval_days')->default(90); // Bakım aralığı (gün)

            // Ruhsat ve Belgeler
            $table->json('documents')->nullable(); // [{ type, number, expiry_date, file_path }]
            $table->json('certifications')->nullable(); // Sertifikalar

            // Operatör Gereksinimleri
            $table->boolean('requires_operator_license')->default(false);
            $table->string('required_license_type')->nullable();

            // Notlar
            $table->text('notes')->nullable();

            // Kim Oluşturdu
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->index(['type', 'status']);
            $table->index(['ownership', 'status']);
            $table->index(['current_project_id']);
            $table->index(['next_maintenance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};
