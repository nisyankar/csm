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
        Schema::table('users', function (Blueprint $table) {
            // Employee ilişkisi
            $table->foreignId('employee_id')->nullable()->after('id')->constrained()->onDelete('set null');
            
            // Kullanıcı Türü
            $table->enum('user_type', [
                'admin',        // Sistem yöneticisi
                'hr',           // İnsan kaynakları
                'project_manager', // Proje yöneticisi
                'site_manager', // Şantiye şefi
                'foreman',      // Forman/Usta
                'employee',     // Personel
                'viewer'        // Sadece görüntüleme
            ])->after('email')->default('employee');
            
            // Durum Bilgileri
            $table->boolean('is_active')->after('user_type')->default(true);
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            
            // Profil Bilgileri
            $table->string('phone')->nullable()->after('last_login_ip');
            $table->string('avatar')->nullable()->after('phone');
            $table->enum('language', ['tr', 'en'])->default('tr')->after('avatar');
            $table->string('timezone')->default('Europe/Istanbul')->after('language');
            
            // Güvenlik Ayarları
            $table->boolean('two_factor_enabled')->default(false)->after('timezone');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->timestamp('password_changed_at')->nullable()->after('two_factor_secret');
            $table->boolean('force_password_change')->default(false)->after('password_changed_at');
            
            // Oturum Ayarları
            $table->integer('session_timeout')->default(120)->after('force_password_change'); // dakika
            $table->boolean('auto_logout_enabled')->default(true)->after('session_timeout');
            
            // Bildirim Tercihleri
            $table->json('notification_preferences')->nullable()->after('auto_logout_enabled');
            $table->boolean('email_notifications')->default(true)->after('notification_preferences');
            $table->boolean('sms_notifications')->default(false)->after('email_notifications');
            
            // API ve Token Ayarları
            $table->boolean('api_access_enabled')->default(false)->after('sms_notifications');
            $table->integer('api_rate_limit')->default(60)->after('api_access_enabled'); // per minute
            $table->timestamp('api_last_used_at')->nullable()->after('api_rate_limit');
            
            // Yetki ve Erişim
            $table->json('dashboard_preferences')->nullable()->after('api_last_used_at');
            $table->json('menu_permissions')->nullable()->after('dashboard_preferences');
            $table->json('project_access')->nullable()->after('menu_permissions'); // Erişebileceği projeler
            
            // Çalışma Tercihleri
            $table->time('preferred_work_start')->nullable()->after('project_access');
            $table->time('preferred_work_end')->nullable()->after('preferred_work_start');
            $table->json('work_days')->nullable()->after('preferred_work_end'); // [1,2,3,4,5] pazartesi-cuma
            
            // Onay Yetkileri
            $table->boolean('can_approve_timesheets')->default(false)->after('work_days');
            $table->boolean('can_approve_leaves')->default(false)->after('can_approve_timesheets');
            $table->decimal('approval_limit', 10, 2)->nullable()->after('can_approve_leaves'); // Onay limiti
            
            // Delegasyon
            $table->foreignId('delegate_to')->nullable()->after('approval_limit')->constrained('users')->onDelete('set null');
            $table->date('delegation_start')->nullable()->after('delegate_to');
            $table->date('delegation_end')->nullable()->after('delegation_start');
            $table->text('delegation_reason')->nullable()->after('delegation_end');
            
            // Geçici Erişim
            $table->boolean('is_temporary_user')->default(false)->after('delegation_reason');
            $table->date('access_expires_at')->nullable()->after('is_temporary_user');
            $table->foreignId('created_by_user')->nullable()->after('access_expires_at')->constrained('users')->onDelete('set null');
            
            // Güvenlik Logları için
            $table->integer('failed_login_attempts')->default(0)->after('created_by_user');
            $table->timestamp('account_locked_until')->nullable()->after('failed_login_attempts');
            $table->json('login_history')->nullable()->after('account_locked_until'); // Son 10 login
            
            // Sistem Ayarları
            $table->boolean('show_help_tooltips')->default(true)->after('login_history');
            $table->enum('theme_preference', ['light', 'dark', 'auto'])->default('light')->after('show_help_tooltips');
            $table->json('custom_settings')->nullable()->after('theme_preference');
            
            // Softdelete ekleyelim
            $table->softDeletes()->after('updated_at');
            
            // İndeksler
            $table->index(['employee_id']);
            $table->index(['user_type', 'is_active']);
            $table->index(['last_login_at']);
            $table->index(['is_active', 'user_type']);
            $table->index(['delegate_to']);
            $table->index(['access_expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Foreign key'leri önce drop et
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['delegate_to']);
            $table->dropForeign(['created_by_user']);
            
            // İndeksleri drop et
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['user_type', 'is_active']);
            $table->dropIndex(['last_login_at']);
            $table->dropIndex(['is_active', 'user_type']);
            $table->dropIndex(['delegate_to']);
            $table->dropIndex(['access_expires_at']);
            
            // Kolonları drop et
            $table->dropColumn([
                'employee_id', 'user_type', 'is_active', 'last_login_at', 'last_login_ip',
                'phone', 'avatar', 'language', 'timezone', 'two_factor_enabled', 
                'two_factor_secret', 'password_changed_at', 'force_password_change',
                'session_timeout', 'auto_logout_enabled', 'notification_preferences',
                'email_notifications', 'sms_notifications', 'api_access_enabled',
                'api_rate_limit', 'api_last_used_at', 'dashboard_preferences',
                'menu_permissions', 'project_access', 'preferred_work_start',
                'preferred_work_end', 'work_days', 'can_approve_timesheets',
                'can_approve_leaves', 'approval_limit', 'delegate_to',
                'delegation_start', 'delegation_end', 'delegation_reason',
                'is_temporary_user', 'access_expires_at', 'created_by_user',
                'failed_login_attempts', 'account_locked_until', 'login_history',
                'show_help_tooltips', 'theme_preference', 'custom_settings',
                'deleted_at'
            ]);
        });
    }
};