<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'user_type',
        'is_active',
        'phone',
        'avatar',
        'language',
        'timezone',
        'two_factor_enabled',
        'notification_preferences',
        'email_notifications',
        'sms_notifications',
        'api_access_enabled',
        'dashboard_preferences',
        'menu_permissions',
        'project_access',
        'preferred_work_start',
        'preferred_work_end',
        'work_days',
        'can_approve_timesheets',
        'can_approve_leaves',
        'approval_limit',
        'delegate_to',
        'delegation_start',
        'delegation_end',
        'delegation_reason',
        'is_temporary_user',
        'access_expires_at',
        'created_by_user',
        'show_help_tooltips',
        'theme_preference',
        'custom_settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'force_password_change' => 'boolean',
            'auto_logout_enabled' => 'boolean',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'api_access_enabled' => 'boolean',
            'api_last_used_at' => 'datetime',
            'notification_preferences' => 'array',
            'dashboard_preferences' => 'array',
            'menu_permissions' => 'array',
            'project_access' => 'array',
            'work_days' => 'array',
            'preferred_work_start' => 'datetime:H:i',
            'preferred_work_end' => 'datetime:H:i',
            'can_approve_timesheets' => 'boolean',
            'can_approve_leaves' => 'boolean',
            'approval_limit' => 'decimal:2',
            'delegation_start' => 'date',
            'delegation_end' => 'date',
            'is_temporary_user' => 'boolean',
            'access_expires_at' => 'date',
            'account_locked_until' => 'datetime',
            'login_history' => 'array',
            'show_help_tooltips' => 'boolean',
            'custom_settings' => 'array',
            'session_timeout' => 'integer',
            'api_rate_limit' => 'integer',
            'failed_login_attempts' => 'integer',
        ];
    }

    // İlişkiler

    /**
     * Kullanıcının personel bilgileri
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Kullanıcının yetki devredileni
     */
    public function delegateTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegate_to');
    }

    /**
     * Bu kullanıcıyı oluşturan kullanıcı
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user');
    }

    /**
     * Bu kullanıcının oluşturduğu kullanıcılar
     */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by_user');
    }

    /**
     * Kullanıcının girdiği puantajlar
     */
    public function enteredTimesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'entered_by');
    }

    /**
     * Kullanıcının yüklediği belgeler
     */
    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    /**
     * Kullanıcının doğruladığı belgeler
     */
    public function verifiedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'verified_by');
    }

    // Accessor ve Mutator'lar

    /**
     * Kullanıcı türü human-readable
     */
    public function getUserTypeDisplayAttribute(): string
    {
        return match($this->user_type) {
            'admin' => 'Sistem Yöneticisi',
            'hr' => 'İnsan Kaynakları',
            'project_manager' => 'Proje Yöneticisi',
            'site_manager' => 'Şantiye Şefi',
            'foreman' => 'Forman/Usta',
            'employee' => 'Personel',
            'viewer' => 'Görüntüleyici',
            default => ucfirst($this->user_type),
        };
    }

    /**
     * Aktif kullanıcı mı?
     */
    public function getIsActiveUserAttribute(): bool
    {
        return $this->is_active && !$this->isAccessExpired();
    }

    /**
     * Erişim süresi dolmuş mu?
     */
    public function isAccessExpired(): bool
    {
        return $this->access_expires_at && now() > $this->access_expires_at;
    }

    /**
     * Hesap kilitli mi?
     */
    public function getIsLockedAttribute(): bool
    {
        return $this->account_locked_until && now() < $this->account_locked_until;
    }

    /**
     * İki faktörlü kimlik doğrulama aktif mi?
     */
    public function getHasTwoFactorAttribute(): bool
    {
        return $this->two_factor_enabled && !empty($this->two_factor_secret);
    }

    /**
     * API erişimi var mı?
     */
    public function getHasApiAccessAttribute(): bool
    {
        return $this->api_access_enabled && $this->is_active;
    }

    /**
     * Delegasyon aktif mi?
     */
    public function getHasActiveDelegationAttribute(): bool
    {
        return $this->delegate_to && 
               $this->delegation_start <= now() && 
               $this->delegation_end >= now();
    }

    /**
     * Avatar URL'i
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        // Gravatar fallback
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?s=150&d=mp";
    }

    // Scope'lar

    /**
     * Aktif kullanıcılar
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Belirli türdeki kullanıcılar
     */
    public function scopeByType($query, $type)
    {
        return $query->where('user_type', $type);
    }

    /**
     * Personel kullanıcıları (employee_id olanlar)
     */
    public function scopeEmployees($query)
    {
        return $query->whereNotNull('employee_id');
    }

    /**
     * Yönetici kullanıcıları
     */
    public function scopeManagers($query)
    {
        return $query->whereIn('user_type', ['admin', 'hr', 'project_manager', 'site_manager']);
    }

    /**
     * Geçici kullanıcılar
     */
    public function scopeTemporary($query)
    {
        return $query->where('is_temporary_user', true);
    }

    /**
     * Süresi dolmuş kullanıcılar
     */
    public function scopeExpired($query)
    {
        return $query->where('access_expires_at', '<', now());
    }

    // Helper metodlar

    /**
     * Kullanıcının belirli bir rolü var mı?
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if (is_string($roles)) {
            return $this->user_type === $roles;
        }

        if (is_array($roles)) {
            return in_array($this->user_type, $roles);
        }

        return false;
    }

    /**
     * Son giriş bilgisini güncelle
     */
    public function updateLastLogin(): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
            'failed_login_attempts' => 0,
        ]);

        // Login geçmişini güncelle
        $this->updateLoginHistory();
    }

    /**
     * Login geçmişini güncelle
     */
    private function updateLoginHistory(): void
    {
        $history = $this->login_history ?? [];
        
        // Yeni giriş ekle
        array_unshift($history, [
            'timestamp' => now()->toISOString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        
        // Son 10 girişi tut
        $history = array_slice($history, 0, 10);
        
        $this->update(['login_history' => $history]);
    }

    /**
     * Başarısız giriş denemesini kaydet
     */
    public function recordFailedLogin(): void
    {
        $attempts = $this->failed_login_attempts + 1;
        
        $updateData = ['failed_login_attempts' => $attempts];
        
        // 5 başarısız denemeden sonra hesabı 15 dakika kilitle
        if ($attempts >= 5) {
            $updateData['account_locked_until'] = now()->addMinutes(15);
        }
        
        $this->update($updateData);
    }

    /**
     * Hesap kilidini kaldır
     */
    public function unlockAccount(): void
    {
        $this->update([
            'account_locked_until' => null,
            'failed_login_attempts' => 0,
        ]);
    }

    /**
     * Şifre değiştirme zorla
     */
    public function forcePasswordChange(): void
    {
        $this->update(['force_password_change' => true]);
    }

    /**
     * Şifre güncelleme
     */
    public function updatePassword(string $password): void
    {
        $this->update([
            'password' => bcrypt($password),
            'password_changed_at' => now(),
            'force_password_change' => false,
        ]);
    }

    /**
     * İki faktörlü kimlik doğrulamayı etkinleştir
     */
    public function enableTwoFactor(string $secret): void
    {
        $this->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt($secret),
        ]);
    }

    /**
     * İki faktörlü kimlik doğrulamayı devre dışı bırak
     */
    public function disableTwoFactor(): void
    {
        $this->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
        ]);
    }

    /**
     * Belirli projeye erişim var mı?
     */
    public function canAccessProject(int $projectId): bool
    {
        // Admin ve HR her projeye erişebilir
        if (in_array($this->user_type, ['admin', 'hr'])) {
            return true;
        }
        
        // Proje erişim listesi kontrolü
        if ($this->project_access) {
            return in_array($projectId, $this->project_access);
        }
        
        // Employee ise sadece kendi projesine
        if ($this->employee) {
            return $this->employee->current_project_id === $projectId;
        }
        
        return false;
    }

    /**
     * Puantaj onaylama yetkisi var mı?
     */
    public function canApproveTimesheet(Timesheet $timesheet): bool
    {
        if (!$this->can_approve_timesheets) {
            return false;
        }
        
        // Admin ve HR her puantajı onaylayabilir
        if (in_array($this->user_type, ['admin', 'hr'])) {
            return true;
        }
        
        // Employee ise sadece astlarının puantajını
        if ($this->employee) {
            return $timesheet->employee->manager_id === $this->employee_id;
        }
        
        return false;
    }

    /**
     * İzin onaylama yetkisi var mı?
     */
    public function canApproveLeave(LeaveRequest $leaveRequest): bool
    {
        if (!$this->can_approve_leaves) {
            return false;
        }
        
        // Admin ve HR her izni onaylayabilir
        if (in_array($this->user_type, ['admin', 'hr'])) {
            return true;
        }
        
        // Employee ise sadece astlarının izni
        if ($this->employee) {
            return $leaveRequest->employee->manager_id === $this->employee_id;
        }
        
        return false;
    }

    /**
     * Yetki delegasyonu ayarla
     */
    public function delegateAuthority(User $delegateTo, \Carbon\Carbon $start, \Carbon\Carbon $end, string $reason): void
    {
        $this->update([
            'delegate_to' => $delegateTo->id,
            'delegation_start' => $start,
            'delegation_end' => $end,
            'delegation_reason' => $reason,
        ]);
    }

    /**
     * Delegasyonu iptal et
     */
    public function revokeDelegation(): void
    {
        $this->update([
            'delegate_to' => null,
            'delegation_start' => null,
            'delegation_end' => null,
            'delegation_reason' => null,
        ]);
    }

    /**
     * Geçici kullanıcı oluştur
     */
    public static function createTemporaryUser(array $userData, \Carbon\Carbon $expiresAt, User $creator): self
    {
        $userData['is_temporary_user'] = true;
        $userData['access_expires_at'] = $expiresAt;
        $userData['created_by_user'] = $creator->id;
        $userData['password'] = bcrypt('temp123456'); // Geçici şifre
        
        return self::create($userData);
    }
}