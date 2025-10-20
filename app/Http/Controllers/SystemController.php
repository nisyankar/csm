<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Timesheet;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class SystemController extends Controller
{
    /**
     * Display system settings
     */
    public function settings(): Response
    {
        $this->authorize('system-admin');

        $settings = $this->getSystemSettings();
        $systemInfo = $this->getSystemInfo();
        $performanceMetrics = $this->getPerformanceMetrics();

        return Inertia::render('System/Settings', [
            'settings' => $settings,
            'systemInfo' => $systemInfo,
            'performanceMetrics' => $performanceMetrics,
        ]);
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $this->authorize('system-admin');

        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'time_format' => 'required|string',
            'currency' => 'required|string|max:3',
            'language' => 'required|string|max:2',
            
            // Email settings
            'mail_driver' => 'required|string',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
            
            // Security settings
            'session_lifetime' => 'required|integer|min:1|max:525600',
            'password_min_length' => 'required|integer|min:6|max:32',
            'require_password_confirmation' => 'boolean',
            'enable_two_factor' => 'boolean',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'lockout_duration' => 'required|integer|min:1|max:60',
            
            // File upload settings
            'max_file_size' => 'required|integer|min:1|max:102400', // KB
            'allowed_file_types' => 'required|array',
            'allowed_file_types.*' => 'string',
            
            // Backup settings
            'auto_backup_enabled' => 'boolean',
            'backup_frequency' => 'required|string|in:daily,weekly,monthly',
            'backup_retention_days' => 'required|integer|min:1|max:365',
            
            // Notification settings
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            
            // Maintenance settings
            'maintenance_mode' => 'boolean',
            'maintenance_message' => 'nullable|string|max:500',
        ]);

        try {
            $this->updateSystemConfiguration($validated);

            // Log the settings change
            Log::info('System settings updated', [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->name,
                'changes' => array_keys($validated),
            ]);

            return back()->with('success', 'Sistem ayarları başarıyla güncellendi.');

        } catch (\Exception $e) {
            Log::error('Failed to update system settings', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
            ]);

            return back()->with('error', 'Sistem ayarları güncellenirken hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Display system logs
     */
    public function logs(Request $request): Response
    {
        $this->authorize('system-admin');

        $validated = $request->validate([
            'level' => 'nullable|in:emergency,alert,critical,error,warning,notice,info,debug',
            'date' => 'nullable|date',
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $logs = $this->getSystemLogs($validated);
        $logStats = $this->getLogStatistics();

        return Inertia::render('System/Logs', [
            'logs' => $logs,
            'logStats' => $logStats,
            'filters' => $validated,
            'logLevels' => $this->getLogLevels(),
        ]);
    }

    /**
     * Create system backup
     */
    public function backup(Request $request): RedirectResponse
    {
        $this->authorize('system-admin');

        $validated = $request->validate([
            'include_database' => 'boolean',
            'include_files' => 'boolean',
            'include_logs' => 'boolean',
            'backup_name' => 'nullable|string|max:255',
        ]);

        try {
            $backupName = $validated['backup_name'] ?? 'manual_backup_' . now()->format('Y_m_d_H_i_s');
            
            $backupResult = $this->createSystemBackup($backupName, $validated);

            Log::info('Manual backup created', [
                'admin_id' => Auth::id(),
                'backup_name' => $backupName,
                'backup_size' => $backupResult['size'] ?? 'unknown',
            ]);

            return back()->with('success', "Sistem yedeği başarıyla oluşturuldu: {$backupName}");

        } catch (\Exception $e) {
            Log::error('Failed to create backup', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
            ]);

            return back()->with('error', 'Sistem yedeği oluşturulurken hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Display user management
     */
    public function users(Request $request): Response
    {
        $this->authorize('system-admin');

        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'role' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'sort' => 'nullable|in:name,email,created_at,last_login',
            'direction' => 'nullable|in:asc,desc',
        ]);

        $users = $this->getUsersWithFilters($validated);
        $roles = Role::all();
        $userStats = $this->getUserStatistics();

        return Inertia::render('System/Users', [
            'users' => $users,
            'roles' => $roles,
            'userStats' => $userStats,
            'filters' => $validated,
        ]);
    }

    /**
     * Update user role
     */
    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        $this->authorize('system-admin');

        $validated = $request->validate([
            'role' => 'required|exists:roles,name',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $oldRoles = $user->roles->pluck('name')->toArray();
            
            // Remove all existing roles and assign new one
            $user->syncRoles([$validated['role']]);

            Log::info('User role updated', [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'old_roles' => $oldRoles,
                'new_role' => $validated['role'],
                'reason' => $validated['reason'],
            ]);

            return back()->with('success', "Kullanıcı rolü başarıyla güncellendi: {$user->name}");

        } catch (\Exception $e) {
            Log::error('Failed to update user role', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
            ]);

            return back()->with('error', 'Kullanıcı rolü güncellenirken hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Get system settings
     */
    private function getSystemSettings(): array
    {
        return [
            // Application settings
            'app_name' => config('app.name', 'SPT İnşaat'),
            'app_description' => config('app.description', 'Çalışan ve Proje Yönetim Sistemi'),
            'timezone' => config('app.timezone', 'Europe/Istanbul'),
            'date_format' => config('app.date_format', 'd.m.Y'),
            'time_format' => config('app.time_format', 'H:i'),
            'currency' => config('app.currency', 'TRY'),
            'language' => config('app.locale', 'tr'),
            
            // Email settings
            'mail_driver' => config('mail.default', 'smtp'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_port' => config('mail.mailers.smtp.port'),
            'mail_username' => config('mail.mailers.smtp.username'),
            'mail_encryption' => config('mail.mailers.smtp.encryption'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            
            // Security settings
            'session_lifetime' => config('session.lifetime', 120),
            'password_min_length' => config('auth.password_min_length', 8),
            'require_password_confirmation' => config('auth.require_password_confirmation', true),
            'enable_two_factor' => config('auth.enable_two_factor', false),
            'max_login_attempts' => config('auth.max_attempts', 5),
            'lockout_duration' => config('auth.lockout_duration', 60),
            
            // File upload settings
            'max_file_size' => config('filesystems.max_file_size', 10240), // KB
            'allowed_file_types' => config('filesystems.allowed_types', [
                'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'
            ]),
            
            // Backup settings
            'auto_backup_enabled' => config('backup.auto_enabled', true),
            'backup_frequency' => config('backup.frequency', 'daily'),
            'backup_retention_days' => config('backup.retention_days', 30),
            
            // Notification settings
            'email_notifications' => config('notifications.email_enabled', true),
            'sms_notifications' => config('notifications.sms_enabled', false),
            'push_notifications' => config('notifications.push_enabled', true),
            
            // Maintenance settings
            'maintenance_mode' => app()->isDownForMaintenance(),
            'maintenance_message' => config('app.maintenance_message', 'Sistem bakımda'),
        ];
    }

    /**
     * Get system information
     */
    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_version' => $this->getDatabaseVersion(),
            'storage_used' => $this->getStorageUsage(),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'uptime' => $this->getSystemUptime(),
            'last_backup' => $this->getLastBackupDate(),
        ];
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        return [
            'average_response_time' => $this->getAverageResponseTime(),
            'memory_usage' => $this->getMemoryUsage(),
            'cpu_usage' => $this->getCpuUsage(),
            'active_users' => $this->getActiveUsersCount(),
            'cache_hit_ratio' => $this->getCacheHitRatio(),
            'error_rate' => $this->getErrorRate(),
            'database_queries_per_minute' => $this->getDatabaseQueriesPerMinute(),
        ];
    }

    /**
     * Update system configuration
     */
    private function updateSystemConfiguration(array $settings): void
    {
        // This would update various config files and environment variables
        // For now, we'll use cache to store dynamic settings
        
        foreach ($settings as $key => $value) {
            Cache::forever("system.settings.{$key}", $value);
        }

        // Update .env file for critical settings
        $envUpdates = [
            'APP_NAME' => $settings['app_name'],
            'APP_TIMEZONE' => $settings['timezone'],
            'MAIL_MAILER' => $settings['mail_driver'],
            'MAIL_HOST' => $settings['mail_host'],
            'MAIL_PORT' => $settings['mail_port'],
            'MAIL_FROM_ADDRESS' => $settings['mail_from_address'],
            'MAIL_FROM_NAME' => $settings['mail_from_name'],
        ];

        $this->updateEnvFile($envUpdates);
    }

    /**
     * Update .env file
     */
    private function updateEnvFile(array $updates): void
    {
        $envFile = base_path('.env');
        
        if (!File::exists($envFile)) {
            return;
        }

        $envContent = File::get($envFile);

        foreach ($updates as $key => $value) {
            $value = is_string($value) ? '"' . $value . '"' : $value;
            
            if (str_contains($envContent, $key . '=')) {
                $envContent = preg_replace(
                    '/^' . preg_quote($key) . '=.*$/m',
                    $key . '=' . $value,
                    $envContent
                );
            } else {
                $envContent .= "\n" . $key . '=' . $value;
            }
        }

        File::put($envFile, $envContent);
    }

    /**
     * Get system logs
     */
    private function getSystemLogs(array $filters): array
    {
        // This is a simplified implementation
        // In a real application, you'd parse actual log files
        
        $logsPath = storage_path('logs/laravel.log');
        
        if (!File::exists($logsPath)) {
            return [];
        }

        $logs = [];
        $lines = array_slice(file($logsPath), -100); // Get last 100 lines

        foreach ($lines as $line) {
            if (preg_match('/\[(.*?)\] (\w+)\.(\w+): (.*)/', $line, $matches)) {
                $logs[] = [
                    'timestamp' => $matches[1],
                    'level' => $matches[2],
                    'channel' => $matches[3],
                    'message' => $matches[4],
                ];
            }
        }

        return array_reverse($logs);
    }

    /**
     * Create system backup
     */
    private function createSystemBackup(string $name, array $options): array
    {
        $backupPath = storage_path('app/backups');
        
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $timestamp = now()->format('Y_m_d_H_i_s');
        $backupFile = "{$backupPath}/{$name}_{$timestamp}.tar.gz";

        $commands = [];

        if ($options['include_database'] ?? true) {
            // Database backup command would go here
            $commands[] = $this->getDatabaseBackupCommand();
        }

        if ($options['include_files'] ?? true) {
            // File backup command would go here
            $commands[] = $this->getFileBackupCommand();
        }

        // Execute backup commands
        foreach ($commands as $command) {
            exec($command, $output, $returnCode);
            if ($returnCode !== 0) {
                throw new \Exception("Backup command failed: {$command}");
            }
        }

        return [
            'path' => $backupFile,
            'size' => File::size($backupFile),
            'created_at' => now(),
        ];
    }

    // Additional helper methods...

    private function getDatabaseVersion(): string
    {
        try {
            return DB::select('SELECT VERSION() as version')[0]->version;
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getStorageUsage(): array
    {
        $totalSpace = disk_total_space(storage_path());
        $freeSpace = disk_free_space(storage_path());
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 1),
        ];
    }

    private function formatBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }

    private function getLogLevels(): array
    {
        return [
            'emergency' => 'Acil',
            'alert' => 'Alarm',
            'critical' => 'Kritik',
            'error' => 'Hata',
            'warning' => 'Uyarı',
            'notice' => 'Bilgi',
            'info' => 'Bilgilendirme',
            'debug' => 'Debug',
        ];
    }

    private function getUsersWithFilters(array $filters): array
    {
        $query = User::with(['roles', 'employee'])
            ->withCount(['timesheets', 'leaveRequests']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        $sort = $filters['sort'] ?? 'name';
        $direction = $filters['direction'] ?? 'asc';
        
        return $query->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString()
            ->toArray();
    }

    private function getUserStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('email_verified_at', '!=', null)->count(),
            'users_with_roles' => User::has('roles')->count(),
            'recent_registrations' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    // Placeholder methods for metrics (these would be implemented based on your monitoring system)
    
    private function getSystemUptime(): string { return '15 days, 3 hours'; }
    private function getLastBackupDate(): ?string { return now()->subDays(1)->format('Y-m-d H:i:s'); }
    private function getAverageResponseTime(): string { return '245ms'; }
    private function getMemoryUsage(): string { return '68%'; }
    private function getCpuUsage(): string { return '23%'; }
    private function getActiveUsersCount(): int { return 45; }
    private function getCacheHitRatio(): string { return '94.2%'; }
    private function getErrorRate(): string { return '0.8%'; }
    private function getDatabaseQueriesPerMinute(): int { return 1250; }
    private function getLogStatistics(): array { return ['total' => 1500, 'errors' => 12, 'warnings' => 45]; }
    private function getDatabaseBackupCommand(): string { return 'mysqldump ...'; }
    private function getFileBackupCommand(): string { return 'tar ...'; }
}