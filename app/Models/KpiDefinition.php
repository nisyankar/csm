<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiDefinition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'module',
        'formula',
        'target_value',
        'warning_threshold',
        'unit',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'warning_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = ['module_label'];

    /**
     * İlişkiler
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessors
     */
    public function getModuleLabelAttribute(): string
    {
        return match($this->module) {
            'progress_payments' => 'Hakediş',
            'timesheets' => 'Puantaj',
            'financials' => 'Finansal',
            'safety' => 'İSG',
            'equipment' => 'Ekipman',
            'stock' => 'Stok',
            'quantities' => 'Metraj',
            'projects' => 'Projeler',
            'general' => 'Genel',
            default => (string) $this->module
        };
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }
}
