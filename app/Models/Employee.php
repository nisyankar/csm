<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'tc_number',
        'birth_date',
        'gender',
        'phone',
        'email',
        'address',
        'position',
        'category',
        'start_date',
        'end_date',
        'daily_wage',
        'hourly_wage',
        'monthly_salary',
        'wage_type',
        'manager_id',
        'user_id',
        'current_project_id',
        'department_id',
        'status',
        'qr_code',
        'photo_path',
        'annual_leave_days',
        'used_leave_days',
        'is_subcontractor_employee',
        'subcontractor_id',
        'subcontractor_contract_start',
        'subcontractor_contract_end',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'daily_wage' => 'decimal:2',
        'hourly_wage' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
        'annual_leave_days' => 'integer',
        'used_leave_days' => 'integer',
        'is_subcontractor_employee' => 'boolean',
        'subcontractor_contract_start' => 'date',
        'subcontractor_contract_end' => 'date',
    ];

    protected $appends = [
        'full_name',
        'hire_date' // start_date alias'ı
    ];

    // İlişkiler

    /**
     * Personelin departmanı
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Personelin yöneticisi
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Personelin altındaki çalışanlar
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    /**
     * Personelin kullanıcı hesabı
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Personelin bağlı olduğu taşeron (varsa)
     */
    public function subcontractor(): BelongsTo
    {
        return $this->belongsTo(Subcontractor::class);
    }

    /**
     * Personelin mevcut projesi
     */
    public function currentProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'current_project_id');
    }

    /**
     * Alias for currentProject - Controller'da kullanılan
     */
    public function project(): BelongsTo
    {
        return $this->currentProject();
    }

    /**
     * Personelin çalıştığı tüm projeler (many-to-many)
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'employee_project')
                    ->withPivot([
                        'assigned_date', 'start_date', 'end_date', 'planned_end_date',
                        'role_in_project', 'responsibilities', 'assignment_type',
                        'work_percentage', 'daily_hours', 'project_daily_rate',
                        'project_hourly_rate', 'has_project_bonus', 'project_bonus_amount',
                        'status', 'performance_score', 'performance_notes',
                        'assigned_by', 'approved_by', 'approved_at'
                    ])
                    ->withTimestamps();
    }

    /**
     * Personelin proje atamaları (yeni tablo)
     */
    public function projectAssignments(): HasMany
    {
        return $this->hasMany(EmployeeProjectAssignment::class);
    }

    /**
     * Personelin aktif proje atamaları
     */
    public function activeProjectAssignments(): HasMany
    {
        return $this->projectAssignments()->where('status', 'active');
    }

    /**
     * Personelin ana projesi
     */
    public function primaryProjectAssignment()
    {
        return $this->projectAssignments()
                    ->where('is_primary', true)
                    ->where('status', 'active')
                    ->first();
    }

    /**
     * Personelin puantaj kayıtları
     */
    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    /**
     * Personelin izin talepleri
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Personelin izin bakiyeleri
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Personelin bu yılki izin bakiyeleri
     */
    public function currentYearLeaveBalances(): HasMany
    {
        return $this->leaveBalances()->where('year', now()->year);
    }

    /**
     * Personelin evrakları
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Personelin onayladığı puantajlar
     */
    public function approvedTimesheets(): HasMany
    {
        return $this->hasMany(TimesheetApproval::class, 'approver_id');
    }

    /**
     * Personelin onayladığı izinler
     */
    public function approvedLeaves(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'approver_id');
    }

    /**
     * Personelin maaş geçmişi
     */
    public function salaryHistory(): HasMany
    {
        return $this->hasMany(EmployeeSalaryHistory::class);
    }

    /**
     * Son maaş değişiklikleri
     */
    public function recentSalaryChanges(): HasMany
    {
        return $this->hasMany(EmployeeSalaryHistory::class)
                   ->orderBy('effective_date', 'desc')
                   ->limit(5);
    }

    // Accessor ve Mutator'lar

    /**
     * Tam adı döndür
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Hire date accessor - start_date'in alias'ı
     */
    public function getHireDateAttribute()
    {
        return $this->start_date;
    }

    /**
     * Initials accessor - Vue component için
     */
    public function getInitialsAttribute(): string
    {
        $firstInitial = $this->first_name ? strtoupper(substr($this->first_name, 0, 1)) : '';
        $lastInitial = $this->last_name ? strtoupper(substr($this->last_name, 0, 1)) : '';
        return $firstInitial . $lastInitial;
    }

    /**
     * Avatar URL accessor
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }
        return null;
    }

    /**
     * Kalan izin günlerini hesapla
     */
    public function getRemainingLeaveDaysAttribute(): int
    {
        return $this->annual_leave_days - $this->used_leave_days;
    }

    /**
     * Aktif durumda mı?
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    // Scope'lar

    /**
     * Aktif personelleri getir
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }


    /**
     * Belirli projedeki personelleri getir
     */
    public function scopeInProject($query, $projectId)
    {
        return $query->where('current_project_id', $projectId);
    }

    /**
     * Belirli departmandaki personelleri getir
     */
    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Helper metodlar

    /**
     * QR kod oluştur
     */
    public function generateQrCode(): string
    {
        $this->qr_code = 'EMP_' . $this->id . '_' . now()->format('Ymd');
        $this->save();
        return $this->qr_code;
    }

    /**
     * Personelin günlük ücretini hesapla
     */
    public function calculateDailyWage(): float
    {
        return match($this->wage_type) {
            'daily' => $this->daily_wage ?? 0,
            'hourly' => ($this->hourly_wage ?? 0) * 8, // 8 saat varsayılan
            'monthly' => ($this->monthly_salary ?? 0) / 30, // 30 gün varsayılan
            default => 0,
        };
    }

    /**
     * Günlük ücreti hesapla (calculateDailyWage alias)
     */
    public function calculateDailyRate(): float
    {
        return $this->calculateDailyWage();
    }

    /**
     * İzin hakkı var mı kontrol et
     */
    public function hasLeaveBalance(int $requestedDays): bool
    {
        return $this->remaining_leave_days >= $requestedDays;
    }

    /**
     * Personel kategorisine göre proje rolünü döndür
     */
    public function getProjectRole(): string
    {
        return $this->getProjectRoleFromCategory($this->category);
    }

    /**
     * Kategori koduna göre proje rolünü döndür
     */
    private function getProjectRoleFromCategory(string $category): string
    {
        $roles = [
            'worker' => 'İşçi',
            'foreman' => 'Forman',
            'engineer' => 'Mühendis',
            'manager' => 'Proje Yöneticisi',
            'system_admin' => 'Sistem Yöneticisi', // YENİ EKLENEN
        ];

        return $roles[$category] ?? 'İşçi';
    }

    /**
     * Personel kategorisine göre renk kodu döndür (UI için)
     */
    public function getCategoryColor(): string
    {
        $colors = [
            'worker' => 'blue',
            'foreman' => 'green',
            'engineer' => 'purple',
            'manager' => 'orange',
            'system_admin' => 'red', // YENİ EKLENEN
        ];

        return $colors[$this->category] ?? 'gray';
    }

    /**
     * Personel kategorisine göre ikon döndür (UI için)
     */
    public function getCategoryIcon(): string
    {
        $icons = [
            'worker' => 'user',
            'foreman' => 'user-group',
            'engineer' => 'academic-cap',
            'manager' => 'briefcase',
            'system_admin' => 'cog-6-tooth', // YENİ EKLENEN
        ];

        return $icons[$this->category] ?? 'user';
    }

    /**
     * Sistem yöneticisi mi kontrol et
     */
    public function isSystemAdmin(): bool
    {
        return $this->category === 'system_admin';
    }

    /**
     * Yönetici mi kontrol et (manager veya system_admin)
     */
    public function isManager(): bool
    {
        return in_array($this->category, ['manager', 'system_admin']);
    }

    /**
     * İzin yönetimi yetkisi var mı?
     */
    public function hasLeaveManagementAccess(): bool
    {
        return in_array($this->category, ['system_admin', 'manager']) || 
               ($this->user && $this->user->hasRole(['admin', 'hr']));
    }

    // Scope'lar

    /**
     * Sistem yöneticilerini getir
     */
    public function scopeSystemAdmins($query)
    {
        return $query->where('category', 'system_admin');
    }

    /**
     * Yöneticileri getir (manager + system_admin)
     */
    public function scopeManagers($query)
    {
        return $query->whereIn('category', ['manager', 'system_admin']);
    }

    /**
     * Belirli kategoriye göre filtrele
     */
    public function scopeByCategory($query, $category)
    {
        if (is_array($category)) {
            return $query->whereIn('category', $category);
        }
        
        return $query->where('category', $category);
    }
}