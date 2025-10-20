<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSystemAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:create-admin {--email=} {--password=} {--name=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sistem yöneticisi kullanıcısı oluşturur';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== SPT İnşaat Sistem Yöneticisi Oluşturma ===');
        $this->line('');

        // Parametreleri al veya sor
        $email = $this->option('email') ?: $this->ask('E-posta adresi');
        $password = $this->option('password') ?: $this->secret('Şifre (minimum 8 karakter)');
        $name = $this->option('name') ?: $this->ask('Ad Soyad');

        // Validasyon
        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
            'name' => $name,
        ], [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'name' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        // E-posta kontrolü
        if (User::where('email', $email)->exists() && !$this->option('force')) {
            $this->error("Bu e-posta adresi zaten kullanımda: {$email}");
            
            if (!$this->confirm('Mevcut kullanıcıyı sistem yöneticisi yapmak ister misiniz?')) {
                return 1;
            }
            
            $user = User::where('email', $email)->first();
            $this->info("Mevcut kullanıcı güncelleniyor: {$user->name}");
        } else {
            // Yeni kullanıcı oluştur
            $this->info('Yeni sistem yöneticisi oluşturuluyor...');
            
            try {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                    'user_type' => 'admin',
                    'is_active' => true,
                ]);
                
                $this->info("✓ Kullanıcı oluşturuldu: {$user->name} ({$user->email})");
            } catch (\Exception $e) {
                $this->error("Kullanıcı oluşturulamadı: " . $e->getMessage());
                return 1;
            }
        }

        // Employee kaydı oluştur
        try {
            $employeeCode = 'SYS' . str_pad($user->id, 4, '0', STR_PAD_LEFT);
            
            $employee = Employee::updateOrCreate(
                ['tc_number' => '99999999999'], // Geçici TC
                [
                    'employee_code' => $employeeCode,
                    'first_name' => explode(' ', $name)[0],
                    'last_name' => explode(' ', $name, 2)[1] ?? '',
                    'tc_number' => '99999999999', // Sistem yöneticisi için geçici
                    'birth_date' => '1990-01-01',
                    'position' => 'Sistem Yöneticisi',
                    'category' => 'system_admin',
                    'start_date' => now()->toDateString(),
                    'wage_type' => 'monthly',
                    'monthly_salary' => 0,
                    'status' => 'active',
                    'annual_leave_days' => 26,
                    'used_leave_days' => 0,
                ]
            );

            // User ile Employee'yi bağla
            $user->update(['employee_id' => $employee->id]);
            
            $this->info("✓ Personel kaydı oluşturuldu: {$employee->employee_code}");
        } catch (\Exception $e) {
            $this->error("Personel kaydı oluşturulamadı: " . $e->getMessage());
            // User'ı sil çünkü employee oluşturulamadı
            $user->delete();
            return 1;
        }

        // Rolleri kontrol et ve oluştur
        try {
            $systemAdminRole = Role::firstOrCreate(['name' => 'system_admin']);
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            
            $this->info("✓ Roller kontrol edildi");
        } catch (\Exception $e) {
            $this->error("Roller oluşturulamadı: " . $e->getMessage());
            return 1;
        }

        // Kullanıcıya rolleri ata
        try {
            $user->assignRole(['system_admin', 'admin']);
            $this->info("✓ Roller atandı: system_admin, admin");
        } catch (\Exception $e) {
            $this->error("Roller atanamadı: " . $e->getMessage());
            return 1;
        }

        // İzinleri kontrol et
        try {
            $this->call('db:seed', ['--class' => 'SystemAdminRoleSeeder']);
            $this->info("✓ İzinler güncellendi");
        } catch (\Exception $e) {
            $this->warn("İzinler güncellenirken hata: " . $e->getMessage());
        }

        // Cache'leri temizle
        try {
            $this->call('permission:cache-reset');
            $this->call('config:clear');
            $this->info("✓ Cache temizlendi");
        } catch (\Exception $e) {
            $this->warn("Cache temizlenirken hata: " . $e->getMessage());
        }

        $this->line('');
        $this->info('🎉 Sistem yöneticisi başarıyla oluşturuldu!');
        $this->line('');
        
        $this->table(
            ['Alan', 'Değer'],
            [
                ['Ad Soyad', $user->name],
                ['E-posta', $user->email],
                ['Personel Kodu', $employee->employee_code],
                ['Roller', $user->roles->pluck('name')->join(', ')],
                ['Durum', 'Aktif'],
                ['Oluşturulma', $user->created_at->format('d.m.Y H:i')],
            ]
        );

        $this->line('');
        $this->info('Giriş URL: ' . config('app.url') . '/login');
        $this->info('E-posta: ' . $email);
        $this->warn('Şifrenizi güvenli bir yerde saklayın!');
        
        return 0;
    }
}