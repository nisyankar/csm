<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubcontractorCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Bu kategoriye ait taşeronlar
     */
    public function subcontractors()
    {
        return $this->hasMany(Subcontractor::class, 'category_id');
    }

    /**
     * Aktif taşeronlar
     */
    public function activeSubcontractors()
    {
        return $this->hasMany(Subcontractor::class, 'category_id')
            ->where('status', 'active')
            ->where('is_approved', true);
    }

    /**
     * Sadece aktif kategorileri getir
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Bu kategorideki toplam taşeron sayısı
     */
    public function getSubcontractorCountAttribute()
    {
        return $this->subcontractors()->count();
    }

    /**
     * Bu kategorideki aktif taşeron sayısı
     */
    public function getActiveSubcontractorCountAttribute()
    {
        return $this->activeSubcontractors()->count();
    }
}