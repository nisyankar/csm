<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'module',
        'template_path',
        'parameters_json',
        'description',
        'created_by',
    ];

    protected $casts = [
        'parameters_json' => 'array',
    ];

    protected $appends = ['type_label', 'module_label'];

    /**
     * İlişkiler
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scheduledReports(): HasMany
    {
        return $this->hasMany(ScheduledReport::class, 'template_id');
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'pdf' => 'PDF',
            'excel' => 'Excel',
            default => (string) $this->type
        };
    }

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
            default => (string) $this->module
        };
    }

    /**
     * Scopes
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
