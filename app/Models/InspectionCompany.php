<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InspectionCompany extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_name',
        'license_number',
        'contact_person',
        'phone',
        'email',
        'address',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Denetim kuruluşuna ait denetimler
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    /**
     * Aktif denetimler
     */
    public function activeInspections(): HasMany
    {
        return $this->inspections()->whereIn('status', ['scheduled', 'completed', 'pending_action']);
    }
}
