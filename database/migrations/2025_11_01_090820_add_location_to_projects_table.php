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
        Schema::table('projects', function (Blueprint $table) {
            // Proje konumu (GPS check-in için)
            $table->decimal('latitude', 10, 7)->nullable()->after('status'); // Enlem
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude'); // Boylam
            $table->integer('allowed_radius')->default(100)->after('longitude'); // Metre cinsinden izin verilen yarıçap

            // QR Kod için gizli anahtar
            $table->string('qr_code_secret')->nullable()->after('allowed_radius'); // Günlük QR üretmek için

            // Check-in yöntemleri (hangi yöntemler bu projede kullanılabilir)
            $table->json('allowed_check_in_methods')->nullable()->after('qr_code_secret'); // ['gps', 'qr', 'manual']
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'allowed_radius',
                'qr_code_secret',
                'allowed_check_in_methods',
            ]);
        });
    }
};
