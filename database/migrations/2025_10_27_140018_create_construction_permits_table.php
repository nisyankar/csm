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
        Schema::create('construction_permits', function (Blueprint $table) {
            $table->id();

            // Project relation
            $table->foreignId('project_id')->constrained()->onDelete('cascade');

            // Permit details
            $table->enum('permit_type', ['building', 'demolition', 'occupancy', 'usage'])
                  ->comment('Ruhsat Türü: building=Yapı Ruhsatı, demolition=Yıkım Ruhsatı, occupancy=İskan İzni, usage=Yapı Kullanma İzni');
            $table->string('permit_number')->unique()->comment('Ruhsat Numarası');

            // Dates
            $table->date('application_date')->nullable()->comment('Başvuru Tarihi');
            $table->date('approval_date')->nullable()->comment('Onay Tarihi');
            $table->date('expiry_date')->nullable()->comment('Son Geçerlilik Tarihi');

            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired', 'renewed'])
                  ->default('pending')
                  ->comment('Durum: pending=Beklemede, approved=Onaylandı, rejected=Reddedildi, expired=Süresi Doldu, renewed=Yenilendi');

            // Authority and zoning
            $table->string('issuing_authority')->nullable()->comment('Veren Kurum/Belediye');
            $table->string('zoning_status')->nullable()->comment('İmar Durumu');

            // Documents (JSON array of file paths)
            $table->json('documents')->nullable()->comment('Belgeler (JSON)');

            // Additional info
            $table->text('notes')->nullable()->comment('Notlar');

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('project_id');
            $table->index('permit_type');
            $table->index('status');
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('construction_permits');
    }
};
