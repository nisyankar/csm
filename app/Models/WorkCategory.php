<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkCategory extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description', 'icon', 'color', 'order', 'is_active'];
    protected $casts = ['order' => 'integer', 'is_active' => 'boolean'];

    public function workItems(): HasMany
    {
        return $this->hasMany(WorkItem::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}
