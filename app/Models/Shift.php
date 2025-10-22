<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'shift_type',
        'daily_hours',
        'overtime_multiplier',
        'is_paid',
        'counts_as_work_day',
        'is_active',
        'sort_order',
        'description',
        'metadata',
    ];

    protected $casts = [
        'daily_hours' => 'decimal:2',
        'overtime_multiplier' => 'decimal:2',
        'is_paid' => 'boolean',
        'counts_as_work_day' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Scope: Sadece aktif vardiyalar
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Tipe göre
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('shift_type', $type);
    }

    /**
     * Relationship: Timesheets
     */
    public function timesheets(): HasMany
    {
        return $this->hasMany(TimesheetV2::class, 'shift_id');
    }

    /**
     * Normal çalışma günü mü?
     */
    public function isNormalWorkDay(): bool
    {
        return $this->shift_type === 'normal' && $this->counts_as_work_day;
    }

    /**
     * Hafta sonu/bayram mü?
     */
    public function isOvertime(): bool
    {
        return in_array($this->shift_type, ['weekend', 'holiday', 'rest_day']);
    }

    /**
     * İzin türü mü?
     */
    public function isLeave(): bool
    {
        return in_array($this->shift_type, [
            'annual_leave',
            'sick_leave',
            'unpaid_leave',
            'excused_leave',
            'maternity_leave',
        ]);
    }

    /**
     * Kanuni izin hakkından düşülür mü?
     */
    public function countsAgainstLeaveEntitlement(): bool
    {
        return in_array($this->shift_type, ['annual_leave', 'sick_leave']);
    }
}
