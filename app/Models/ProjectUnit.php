<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'structure_id',
        'floor_id',
        'unit_code',
        'unit_type',
        'room_configuration',
        'gross_area',
        'net_area',
        'balcony_area',
        'status',
        'notes',
    ];

    protected $casts = [
        'gross_area' => 'decimal:2',
        'net_area' => 'decimal:2',
        'balcony_area' => 'decimal:2',
    ];

    protected $appends = [
        'name',
        'unit_display',
        'full_code',
    ];

    /**
     * Relationships
     */
    public function structure(): BelongsTo
    {
        return $this->belongsTo(ProjectStructure::class, 'structure_id');
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(ProjectFloor::class, 'floor_id');
    }

    public function workAssignments(): HasMany
    {
        return $this->hasMany(WorkItemAssignment::class, 'unit_id');
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'unit_id');
    }

    /**
     * Accessors
     */
    public function getNameAttribute(): string
    {
        return $this->unit_code ?? '';
    }

    public function getUnitDisplayAttribute(): string
    {
        $unitCode = $this->unit_code ?? '';

        if ($this->room_configuration) {
            return $unitCode . ' (' . $this->room_configuration . ')';
        }

        return $unitCode;
    }

    public function getFullCodeAttribute(): string
    {
        $parts = [];

        if ($this->structure) {
            $parts[] = $this->structure->code;
        }

        if ($this->floor) {
            $parts[] = $this->floor->floor_display;
        }

        if ($this->unit_code) {
            $parts[] = $this->unit_code;
        }

        return implode(' - ', $parts) ?: '';
    }

    public function getUnitTypeDisplayAttribute(): string
    {
        $types = [
            'apartment' => 'Daire',
            'office' => 'Ofis',
            'shop' => 'Dükkan',
            'parking_space' => 'Otopark',
            'storage' => 'Depo',
            'penthouse' => 'Çatı Dubleks',
            'studio' => 'Stüdyo',
            'duplex' => 'Dubleks',
            'other' => 'Diğer',
        ];

        return $types[$this->unit_type] ?? $this->unit_type;
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'planned' => 'Planlandı',
            'in_progress' => 'Devam Ediyor',
            'completed' => 'Tamamlandı',
            'delivered' => 'Teslim Edildi',
            'on_hold' => 'Beklemede',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Scopes
     */
    public function scopeByStructure($query, $structureId)
    {
        return $query->where('structure_id', $structureId);
    }

    public function scopeByFloor($query, $floorId)
    {
        return $query->where('floor_id', $floorId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('unit_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Helper Methods
     */
    public function getTotalArea(): float
    {
        return ($this->gross_area ?? 0) + ($this->balcony_area ?? 0);
    }

    public function calculateProgress(): float
    {
        $assignments = $this->workAssignments;

        if ($assignments->isEmpty()) {
            return 0;
        }

        return round($assignments->avg('progress_percentage'), 2);
    }

    public function isResidential(): bool
    {
        return in_array($this->unit_type, ['apartment', 'studio', 'penthouse', 'duplex']);
    }

    public function isCommercial(): bool
    {
        return in_array($this->unit_type, ['office', 'shop']);
    }
}
