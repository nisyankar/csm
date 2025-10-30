<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $table = 'equipments';

    protected $fillable = [
        'code',
        'name',
        'type',
        'brand',
        'model',
        'serial_number',
        'manufacture_year',
        'specifications',
        'description',
        'ownership',
        'rental_company',
        'rental_cost_daily',
        'rental_cost_monthly',
        'purchase_date',
        'purchase_price',
        'supplier',
        'status',
        'current_project_id',
        'current_location',
        'insured',
        'insurance_company',
        'insurance_policy_number',
        'insurance_expiry_date',
        'last_maintenance_date',
        'next_maintenance_date',
        'maintenance_interval_days',
        'documents',
        'certifications',
        'requires_operator_license',
        'required_license_type',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'manufacture_year' => 'integer',
        'specifications' => 'array',
        'rental_cost_daily' => 'decimal:2',
        'rental_cost_monthly' => 'decimal:2',
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'insured' => 'boolean',
        'insurance_expiry_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'maintenance_interval_days' => 'integer',
        'documents' => 'array',
        'certifications' => 'array',
        'requires_operator_license' => 'boolean',
    ];

    protected $appends = ['type_label', 'status_label', 'status_color', 'ownership_label', 'is_maintenance_due'];

    /**
     * İlişkiler
     */
    public function currentProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'current_project_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(EquipmentUsage::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(EquipmentMaintenance::class);
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute(): string
    {
        if (!$this->type) {
            return '-';
        }

        return match($this->type) {
            'excavator' => 'Ekskavatör',
            'bulldozer' => 'Dozer',
            'crane' => 'Vinç',
            'loader' => 'Yükleyici',
            'grader' => 'Greyder',
            'roller' => 'Silindir',
            'forklift' => 'Forklift',
            'concrete_mixer' => 'Beton Mikseri',
            'pump' => 'Pompa',
            'generator' => 'Jeneratör',
            'compressor' => 'Kompresör',
            'welding_machine' => 'Kaynak Makinesi',
            'scaffolding' => 'İskele',
            'vehicle' => 'Araç',
            'tower_crane' => 'Kule Vinç',
            'mobile_crane' => 'Mobil Vinç',
            'other' => 'Diğer',
            default => (string) $this->type
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->status) {
            return '-';
        }

        return match($this->status) {
            'available' => 'Müsait',
            'in_use' => 'Kullanımda',
            'maintenance' => 'Bakımda',
            'repair' => 'Onarımda',
            'out_of_service' => 'Hizmet Dışı',
            'retired' => 'Emekli',
            default => (string) $this->status
        };
    }

    public function getStatusColorAttribute(): string
    {
        if (!$this->status) {
            return 'gray';
        }

        return match($this->status) {
            'available' => 'green',
            'in_use' => 'blue',
            'maintenance' => 'yellow',
            'repair' => 'orange',
            'out_of_service' => 'red',
            'retired' => 'gray',
            default => 'gray'
        };
    }

    public function getOwnershipLabelAttribute(): string
    {
        if (!$this->ownership) {
            return '-';
        }

        return match($this->ownership) {
            'owned' => 'Şirket Malı',
            'rented' => 'Kiralık',
            'leased' => 'Leasingli',
            default => (string) $this->ownership
        };
    }

    public function getIsMaintenanceDueAttribute(): bool
    {
        if (!$this->next_maintenance_date) {
            return false;
        }
        return $this->next_maintenance_date <= now()->addDays(7);
    }

    /**
     * Scopes
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByOwnership($query, $ownership)
    {
        return $query->where('ownership', $ownership);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeInUse($query)
    {
        return $query->where('status', 'in_use');
    }

    public function scopeMaintenanceDue($query, $days = 7)
    {
        return $query->where('next_maintenance_date', '<=', now()->addDays($days))
            ->whereNotNull('next_maintenance_date');
    }

    public function scopeRented($query)
    {
        return $query->where('ownership', 'rented');
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('current_project_id', $projectId);
    }

    /**
     * Helper Methods
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isInUse(): bool
    {
        return $this->status === 'in_use';
    }

    public function isRented(): bool
    {
        return $this->ownership === 'rented';
    }

    public function calculateTotalMaintenanceCost(): float
    {
        return $this->maintenances()
            ->where('status', 'completed')
            ->sum('total_cost');
    }

    public function calculateTotalUsageCost(): float
    {
        return $this->usages()
            ->where('status', 'completed')
            ->sum('rental_cost');
    }

    public function getTotalOperatingCost(): float
    {
        return $this->calculateTotalMaintenanceCost() + $this->calculateTotalUsageCost();
    }
}
