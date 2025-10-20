<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveParameter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'parameter_key',
        'description',
        'type',
        'category',
        'default_value',
        'min_value',
        'max_value',
        'validation_rules',
        'status',
        'is_system',
        'is_editable',
        'applies_to_all',
        'employee_categories',
        'created_by',
        'sort_order',
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'is_system' => 'boolean',
        'is_editable' => 'boolean',
        'applies_to_all' => 'boolean',
        'employee_categories' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    /**
     * Creator of the parameter
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */

    /**
     * Scope to get only active parameters
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get system parameters
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope to get custom parameters
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope to get editable parameters
     */
    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    /**
     * Scope to get parameters by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get parameters applicable to employee category
     */
    public function scopeForEmployeeCategory($query, $employeeCategory)
    {
        return $query->where(function ($q) use ($employeeCategory) {
            $q->where('applies_to_all', true)
              ->orWhereJsonContains('employee_categories', $employeeCategory);
        });
    }

    /**
     * Scope to get parameters by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Accessors & Mutators
     */

    /**
     * Get parameter value with proper type casting
     */
    public function getTypedValueAttribute()
    {
        return $this->castValue($this->default_value);
    }

    /**
     * Get category display name
     */
    public function getCategoryDisplayAttribute(): string
    {
        $categories = [
            'annual_leave' => 'Yıllık İzin',
            'sick_leave' => 'Hastalık İzni',
            'maternity_leave' => 'Doğum İzni',
            'paternity_leave' => 'Babalık İzni',
            'unpaid_leave' => 'Ücretsiz İzin',
            'calculation' => 'Hesaplama',
            'eligibility' => 'Uygunluk',
            'restrictions' => 'Kısıtlamalar',
        ];

        return $categories[$this->category] ?? $this->category;
    }

    /**
     * Get type display name
     */
    public function getTypeDisplayAttribute(): string
    {
        $types = [
            'integer' => 'Tam Sayı',
            'decimal' => 'Ondalık Sayı',
            'boolean' => 'Evet/Hayır',
            'string' => 'Metin',
            'date' => 'Tarih',
            'json' => 'JSON Veri',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'active' => 'Aktif',
            'inactive' => 'Pasif',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        $classes = [
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
        ];

        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get employee categories display names
     */
    public function getEmployeeCategoriesDisplayAttribute(): array
    {
        if ($this->applies_to_all || !$this->employee_categories) {
            return ['Tüm Çalışanlar'];
        }

        $categories = [
            'worker' => 'İşçi',
            'foreman' => 'Forman',
            'engineer' => 'Mühendis',
            'manager' => 'Proje Yöneticisi',
            'system_admin' => 'Sistem Yöneticisi',
        ];

        return array_map(function ($category) use ($categories) {
            return $categories[$category] ?? $category;
        }, $this->employee_categories);
    }

    /**
     * Get parameter icon based on category
     */
    public function getParameterIconAttribute(): string
    {
        $icons = [
            'annual_leave' => '🏖️',
            'sick_leave' => '🏥',
            'maternity_leave' => '👶',
            'paternity_leave' => '👨‍👶',
            'unpaid_leave' => '💸',
            'calculation' => '🧮',
            'eligibility' => '✅',
            'restrictions' => '🚫',
        ];

        return $icons[$this->category] ?? '⚙️';
    }

    /**
     * Helper Methods
     */

    /**
     * Cast value to proper type
     */
    public function castValue($value)
    {
        if ($value === null) {
            return null;
        }

        switch ($this->type) {
            case 'integer':
                return (int) $value;
            case 'decimal':
                return (float) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'date':
                return \Carbon\Carbon::parse($value);
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'string':
            default:
                return (string) $value;
        }
    }

    /**
     * Validate value against parameter constraints
     */
    public function validateValue($value): array
    {
        $errors = [];
        $typedValue = $this->castValue($value);

        // Type validation
        if (!$this->isValidType($value)) {
            $errors[] = "Değer {$this->type_display} türünde olmalıdır.";
        }

        // Range validation for numeric types
        if (in_array($this->type, ['integer', 'decimal']) && $typedValue !== null) {
            if ($this->min_value !== null && $typedValue < $this->min_value) {
                $errors[] = "Değer {$this->min_value} değerinden küçük olamaz.";
            }
            if ($this->max_value !== null && $typedValue > $this->max_value) {
                $errors[] = "Değer {$this->max_value} değerinden büyük olamaz.";
            }
        }

        // Custom validation rules
        if ($this->validation_rules) {
            $customErrors = $this->applyCustomValidation($value);
            $errors = array_merge($errors, $customErrors);
        }

        return $errors;
    }

    /**
     * Check if value is of correct type
     */
    private function isValidType($value): bool
    {
        switch ($this->type) {
            case 'integer':
                return is_numeric($value) && (int) $value == $value;
            case 'decimal':
                return is_numeric($value);
            case 'boolean':
                return is_bool($value) || in_array(strtolower($value), ['true', 'false', '1', '0', 'yes', 'no']);
            case 'date':
                try {
                    \Carbon\Carbon::parse($value);
                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            case 'json':
                if (is_array($value) || is_object($value)) {
                    return true;
                }
                json_decode($value);
                return json_last_error() === JSON_ERROR_NONE;
            case 'string':
            default:
                return is_string($value) || is_numeric($value);
        }
    }

    /**
     * Apply custom validation rules
     */
    private function applyCustomValidation($value): array
    {
        $errors = [];
        
        // This would implement custom validation logic
        // For now, return empty array
        
        return $errors;
    }

    /**
     * Check if parameter applies to employee
     */
    public function appliesTo(Employee $employee): bool
    {
        if ($this->applies_to_all) {
            return true;
        }

        if (!$this->employee_categories) {
            return false;
        }

        return in_array($employee->category, $this->employee_categories);
    }

    /**
     * Get parameter value for specific employee (can be overridden per employee)
     */
    public function getValueForEmployee(Employee $employee)
    {
        // This can be extended to support employee-specific parameter values
        // For now, return default value
        return $this->typed_value;
    }

    /**
     * Static helper methods
     */

    /**
     * Get all available categories
     */
    public static function getAvailableCategories(): array
    {
        return [
            'annual_leave' => 'Yıllık İzin',
            'sick_leave' => 'Hastalık İzni',
            'maternity_leave' => 'Doğum İzni',
            'paternity_leave' => 'Babalık İzni',
            'unpaid_leave' => 'Ücretsiz İzin',
            'calculation' => 'Hesaplama',
            'eligibility' => 'Uygunluk',
            'restrictions' => 'Kısıtlamalar',
        ];
    }

    /**
     * Get all available types
     */
    public static function getAvailableTypes(): array
    {
        return [
            'integer' => 'Tam Sayı',
            'decimal' => 'Ondalık Sayı',
            'boolean' => 'Evet/Hayır',
            'string' => 'Metin',
            'date' => 'Tarih',
            'json' => 'JSON Veri',
        ];
    }

    /**
     * Get all available employee categories
     */
    public static function getAvailableEmployeeCategories(): array
    {
        return [
            'worker' => 'İşçi',
            'foreman' => 'Forman',
            'engineer' => 'Mühendis',
            'manager' => 'Proje Yöneticisi',
            'system_admin' => 'Sistem Yöneticisi',
        ];
    }

    /**
     * Create default system parameters
     */
    public static function createDefaultParameters(): void
    {
        $defaultParameters = [
            // Annual Leave Parameters
            [
                'name' => 'Yıllık İzin Gün Sayısı',
                'parameter_key' => 'annual_leave_days',
                'description' => 'Çalışanın yıllık hak ettiği izin gün sayısı',
                'type' => 'integer',
                'category' => 'annual_leave',
                'default_value' => '20',
                'min_value' => 0,
                'max_value' => 365,
                'is_system' => true,
                'applies_to_all' => true,
            ],
            [
                'name' => 'Minimum Çalışma Süresi (Gün)',
                'parameter_key' => 'min_employment_days_for_annual_leave',
                'description' => 'Yıllık izin hakkı kazanmak için minimum çalışma gün sayısı',
                'type' => 'integer',
                'category' => 'annual_leave',
                'default_value' => '365',
                'min_value' => 0,
                'is_system' => true,
                'applies_to_all' => true,
            ],
            
            // Sick Leave Parameters
            [
                'name' => 'Yıllık Hastalık İzni Gün Sayısı',
                'parameter_key' => 'sick_leave_days_per_year',
                'description' => 'Çalışanın yıllık hastalık izni gün sayısı',
                'type' => 'integer',
                'category' => 'sick_leave',
                'default_value' => '15',
                'min_value' => 0,
                'max_value' => 365,
                'is_system' => true,
                'applies_to_all' => true,
            ],
            [
                'name' => 'Rapor Gerektiren Minimum Gün',
                'parameter_key' => 'sick_leave_report_required_days',
                'description' => 'Kaç günden sonra doktor raporu gerekir',
                'type' => 'integer',
                'category' => 'sick_leave',
                'default_value' => '3',
                'min_value' => 1,
                'is_system' => true,
                'applies_to_all' => true,
            ],
            
            // Calculation Parameters
            [
                'name' => 'Hafta Sonu Günleri',
                'parameter_key' => 'weekend_days',
                'description' => 'Hafta sonu günleri (0=Pazar, 6=Cumartesi)',
                'type' => 'json',
                'category' => 'calculation',
                'default_value' => '[0, 6]',
                'is_system' => true,
                'applies_to_all' => true,
            ],
            [
                'name' => 'Pro-rata Hesaplama',
                'parameter_key' => 'enable_prorata_calculation',
                'description' => 'Yıl içi başlayan çalışanlar için pro-rata hesaplama',
                'type' => 'boolean',
                'category' => 'calculation',
                'default_value' => 'true',
                'is_system' => true,
                'applies_to_all' => true,
            ],
        ];

        foreach ($defaultParameters as $param) {
            if (!self::where('parameter_key', $param['parameter_key'])->exists()) {
                self::create($param);
            }
        }
    }
}