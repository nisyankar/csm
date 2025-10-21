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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->date('report_date');
            $table->foreignId('reported_by')->constrained('users')->onDelete('restrict');

            // Hava Durumu
            $table->enum('weather_condition', [
                'sunny', 'cloudy', 'rainy', 'snowy', 'windy', 'stormy'
            ])->nullable();
            $table->decimal('temperature', 5, 2)->nullable()->comment('Celsius');
            $table->text('weather_notes')->nullable();

            // İş Gücü
            $table->integer('total_workers')->default(0);
            $table->integer('subcontractor_workers')->default(0);
            $table->integer('internal_workers')->default(0);

            // İş Durumu
            $table->text('work_summary')->comment('Günün genel özeti');
            $table->json('completed_works')->nullable()->comment('Tamamlanan işler');
            $table->json('ongoing_works')->nullable()->comment('Devam eden işler');
            $table->json('planned_works')->nullable()->comment('Yarın planlanan işler');

            // Sorunlar ve Engeller
            $table->boolean('has_delays')->default(false);
            $table->json('delay_reasons')->nullable();
            $table->boolean('has_accidents')->default(false);
            $table->json('accident_details')->nullable();
            $table->boolean('has_material_shortage')->default(false);
            $table->json('material_shortage_details')->nullable();

            // Ziyaretçiler
            $table->json('visitors')->nullable()->comment('Müşteri, müfettiş vb.');

            // Ekipman
            $table->json('equipment_usage')->nullable()->comment('Kullanılan ekipmanlar');

            // Fotoğraflar
            $table->json('photos')->nullable()->comment('Günlük fotoğraflar');

            // Onay
            $table->enum('approval_status', ['draft', 'submitted', 'approved', 'rejected'])
                ->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // İndeksler
            $table->unique(['project_id', 'report_date']);
            $table->index('report_date');
            $table->index(['project_id', 'approval_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
