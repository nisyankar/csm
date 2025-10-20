<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'unit',
        'estimated_unit_price',
        'specification',
        'material_code',
        'is_active',
    ];

    protected $casts = [
        'estimated_unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Malzemenin kullanıldığı satınalma talep kalemleri
     */
    public function purchasingItems(): HasMany
    {
        return $this->hasMany(PurchasingItem::class);
    }

    /**
     * Sadece aktif malzemeleri getir
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Kategoriye göre filtrele
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
