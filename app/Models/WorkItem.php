<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'code', 'name', 'description', 'unit',
        'estimated_duration_days', 'default_unit_price',
        'requires_approval', 'is_critical', 'order', 'is_active', 'metadata'
    ];

    protected $casts = [
        'estimated_duration_days' => 'integer',
        'default_unit_price' => 'decimal:2',
        'requires_approval' => 'boolean',
        'is_critical' => 'boolean',
        'order' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(WorkCategory::class, 'category_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(WorkItemAssignment::class, 'work_item_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}
