<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcontractor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'trade_title',
        'tax_office',
        'tax_number',
        'address',
        'city',
        'district',
        'postal_code',
        'phone',
        'fax',
        'email',
        'website',
        'authorized_person',
        'authorized_phone',
        'authorized_email',
        'authorized_title',
        'bank_name',
        'bank_branch',
        'branch_name',
        'branch_code',
        'account_number',
        'iban',
        'category_id',
        'rating',
        'completed_projects',
        'total_contract_value',
        'status',
        'is_approved',
        'approval_date',
        'approved_by',
        'notes',
        'tags',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'completed_projects' => 'integer',
        'is_approved' => 'boolean',
        'approval_date' => 'date',
        'tags' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Appends kaldırıldı - performans için sadece gerektiğinde yüklenecek
    // protected $appends = [];

    /**
     * Taşeronun kategorisi
     */
    public function category()
    {
        return $this->belongsTo(SubcontractorCategory::class);
    }

    /**
     * Taşeronun belgeleri
     */
    public function certifications()
    {
        return $this->hasMany(SubcontractorCertification::class);
    }

    /**
     * Geçerli belgeler
     */
    public function validCertifications()
    {
        return $this->hasMany(SubcontractorCertification::class)
            ->where('status', 'valid')
            ->whereDate('expiry_date', '>', now());
    }

    /**
     * Süresi yakında dolacak belgeler (30 gün içinde)
     */
    public function expiringSoonCertifications()
    {
        return $this->hasMany(SubcontractorCertification::class)
            ->where('status', 'valid')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->whereDate('expiry_date', '>', now());
    }

    /**
     * Taşeronun değerlendirmeleri
     */
    public function ratings()
    {
        return $this->hasMany(SubcontractorRating::class);
    }

    /**
     * Onaylayan kullanıcı
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Taşeronun çalıştığı projeler (many-to-many)
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_subcontractor')
            ->withPivot([
                'work_type', 'assigned_date', 'start_date', 'end_date', 'assigned_by',
                'scope_of_work', 'contract_amount', 'status', 'notes'
            ])
            ->withTimestamps();
    }

    /**
     * Aktif projeler
     */
    public function activeProjects()
    {
        return $this->projects()->wherePivot('status', 'active');
    }

    /**
     * Tamamlanmış projeler
     */
    public function completedProjects()
    {
        return $this->projects()->wherePivot('status', 'completed');
    }

    /**
     * Taşeron çalışanları
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Aktif çalışanlar
     */
    public function activeEmployees()
    {
        return $this->hasMany(Employee::class)->where('status', 'active');
    }

    /**
     * Taşeronun sözleşmeleri (ileride eklenecek)
     */
    // public function contracts()
    // {
    //     return $this->hasMany(Contract::class);
    // }

    /**
     * Taşeronun hakediş kayıtları
     */
    public function progressPayments()
    {
        return $this->hasMany(ProgressPayment::class);
    }

    /**
     * Aktif taşeronlar
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Onaylı taşeronlar
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Kara listede olanlar
     */
    public function scopeBlacklisted($query)
    {
        return $query->where('status', 'blacklisted');
    }

    /**
     * Kategoriye göre filtrele
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Şehre göre filtrele
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Puana göre sırala
     */
    public function scopeByRating($query, $order = 'desc')
    {
        return $query->orderBy('rating', $order);
    }

    /**
     * Arama
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('company_name', 'like', "%{$search}%")
                ->orWhere('trade_title', 'like', "%{$search}%")
                ->orWhere('tax_number', 'like', "%{$search}%")
                ->orWhere('authorized_person', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Tam iletişim bilgisi
     */
    public function getFullContactAttribute(): string
    {
        $parts = array_filter([
            $this->authorized_person,
            $this->phone,
            $this->email,
        ]);

        return implode(' | ', $parts) ?: 'İletişim bilgisi yok';
    }

    /**
     * Yıldız formatında puan
     */
    public function getRatingStarsAttribute(): string
    {
        $rating = (float) ($this->rating ?? 0);
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;

        return str_repeat('★', (int) $fullStars) .
               str_repeat('⯨', $halfStar) .
               str_repeat('☆', (int) $emptyStars);
    }

    /**
     * Durum badge'i
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'active' => ['text' => 'Aktif', 'color' => 'green'],
            'inactive' => ['text' => 'Pasif', 'color' => 'gray'],
            'blacklisted' => ['text' => 'Kara Liste', 'color' => 'red'],
            default => ['text' => 'Bilinmiyor', 'color' => 'gray'],
        };
    }

    /**
     * Belgelendirme durumu
     */
    public function getCertificationStatusAttribute(): array
    {
        $total = $this->certifications()->count();
        $valid = $this->validCertifications()->count();
        $expiringSoon = $this->expiringSoonCertifications()->count();
        $expired = $this->certifications()->where('status', 'expired')->count();

        return [
            'total' => $total,
            'valid' => $valid,
            'expiring_soon' => $expiringSoon,
            'expired' => $expired,
            'completion_rate' => $total > 0 ? round(($valid / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Ortalama puanı güncelle
     */
    public function updateRating(): void
    {
        $averageRating = $this->ratings()->avg('overall_score');
        $this->update([
            'rating' => $averageRating ?? 0,
        ]);
    }
}