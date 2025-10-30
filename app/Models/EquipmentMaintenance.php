<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentMaintenance extends Model
{
    use SoftDeletes;

    protected $table = 'equipment_maintenance';

    protected $fillable = [
        'equipment_id',
        'maintenance_code',
        'type',
        'maintenance_date',
        'start_time',
        'end_time',
        'duration_hours',
        'description',
        'findings',
        'work_performed',
        'parts_replaced',
        'service_provider',
        'service_company',
        'technician_name',
        'technician_phone',
        'labor_cost',
        'parts_cost',
        'external_service_cost',
        'total_cost',
        'meter_reading',
        'status',
        'next_maintenance_date',
        'next_maintenance_meter',
        'documents',
        'photos',
        'under_warranty',
        'warranty_claim_number',
        'notes',
        'recommendations',
        'cost_recorded',
        'financial_transaction_id',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_hours' => 'decimal:2',
        'work_performed' => 'array',
        'parts_replaced' => 'array',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'external_service_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'meter_reading' => 'integer',
        'next_maintenance_date' => 'date',
        'next_maintenance_meter' => 'integer',
        'documents' => 'array',
        'photos' => 'array',
        'under_warranty' => 'boolean',
        'cost_recorded' => 'boolean',
    ];

    protected $appends = ['type_label', 'status_label', 'status_color', 'service_provider_label'];

    /**
     * İlişkiler
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function financialTransaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class);
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'routine' => 'Rutin Bakım',
            'preventive' => 'Önleyici Bakım',
            'corrective' => 'Onarım Bakımı',
            'breakdown' => 'Arıza',
            'inspection' => 'Muayene',
            'calibration' => 'Kalibrasyon',
            'overhaul' => 'Kapsamlı Revizyon',
            'seasonal' => 'Sezonluk Bakım',
            'other' => 'Diğer',
            default => $this->type
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'Planlandı',
            'in_progress' => 'Devam Ediyor',
            'completed' => 'Tamamlandı',
            'cancelled' => 'İptal Edildi',
            default => $this->status
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'blue',
            'in_progress' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    public function getServiceProviderLabelAttribute(): string
    {
        return match($this->service_provider) {
            'internal' => 'İç Ekip',
            'external' => 'Dış Servis',
            default => $this->service_provider
        };
    }

    /**
     * Scopes
     */
    public function scopeForEquipment($query, $equipmentId)
    {
        return $query->where('equipment_id', $equipmentId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('status', 'scheduled')
            ->whereBetween('maintenance_date', [now(), now()->addDays($days)]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'scheduled')
            ->where('maintenance_date', '<', now());
    }

    public function scopeCostNotRecorded($query)
    {
        return $query->where('cost_recorded', false)
            ->where('status', 'completed')
            ->where('total_cost', '>', 0);
    }

    /**
     * Helper Methods
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'scheduled' && $this->maintenance_date < now();
    }

    public function calculateTotalCost(): void
    {
        $this->total_cost =
            ($this->labor_cost ?? 0) +
            ($this->parts_cost ?? 0) +
            ($this->external_service_cost ?? 0);
    }

    public function calculateDuration(): void
    {
        if ($this->start_time && $this->end_time) {
            $start = \Carbon\Carbon::parse($this->start_time);
            $end = \Carbon\Carbon::parse($this->end_time);
            $this->duration_hours = $end->diffInHours($start, true);
        }
    }
}
