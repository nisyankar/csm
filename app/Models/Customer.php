<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'tc_number',
        'passport_number',
        'customer_type',
        'company_name',
        'tax_office',
        'tax_number',
        'email',
        'phone',
        'mobile_phone',
        'work_phone',
        'city',
        'district',
        'address',
        'postal_code',
        'country',
        'birth_date',
        'birth_place',
        'gender',
        'marital_status',
        'nationality',
        'reference_source',
        'reference_person',
        'customer_status',
        'satisfaction_score',
        'notes',
        'documents',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'documents' => 'array',
        'satisfaction_score' => 'integer',
    ];

    protected $appends = ['full_name', 'status_badge'];

    /**
     * İlişkiler
     */
    public function unitSales(): HasMany
    {
        return $this->hasMany(UnitSale::class);
    }

    public function salePayments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Accessor Methods
     */
    public function getFullNameAttribute(): string
    {
        if ($this->customer_type === 'corporate') {
            return $this->company_name ?? '';
        }
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->customer_status) {
            'potential' => ['text' => 'Potansiyel', 'color' => 'gray'],
            'interested' => ['text' => 'İlgileniyor', 'color' => 'blue'],
            'active' => ['text' => 'Aktif', 'color' => 'green'],
            'inactive' => ['text' => 'Pasif', 'color' => 'orange'],
            'blacklisted' => ['text' => 'Kara Liste', 'color' => 'red'],
            default => ['text' => 'Bilinmiyor', 'color' => 'gray'],
        };
    }

    public function getCustomerTypeLabelAttribute(): string
    {
        return match($this->customer_type) {
            'individual' => 'Bireysel',
            'corporate' => 'Kurumsal',
            default => 'Bilinmiyor',
        };
    }

    public function getGenderLabelAttribute(): ?string
    {
        return match($this->gender) {
            'male' => 'Erkek',
            'female' => 'Kadın',
            'other' => 'Diğer',
            default => null,
        };
    }

    public function getMaritalStatusLabelAttribute(): ?string
    {
        return match($this->marital_status) {
            'single' => 'Bekar',
            'married' => 'Evli',
            'divorced' => 'Boşanmış',
            'widowed' => 'Dul',
            default => null,
        };
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('customer_status', 'active');
    }

    public function scopePotential($query)
    {
        return $query->where('customer_status', 'potential');
    }

    public function scopeInterested($query)
    {
        return $query->where('customer_status', 'interested');
    }

    public function scopeIndividual($query)
    {
        return $query->where('customer_type', 'individual');
    }

    public function scopeCorporate($query)
    {
        return $query->where('customer_type', 'corporate');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('tc_number', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%");
        });
    }

    /**
     * Helper Methods
     */
    public function isActive(): bool
    {
        return $this->customer_status === 'active';
    }

    public function isPotential(): bool
    {
        return $this->customer_status === 'potential';
    }

    public function isBlacklisted(): bool
    {
        return $this->customer_status === 'blacklisted';
    }

    public function getTotalPurchaseAmount(): float
    {
        return $this->unitSales()
            ->whereNotIn('status', ['cancelled'])
            ->sum('final_price') ?? 0;
    }

    public function getTotalPaidAmount(): float
    {
        return $this->salePayments()
            ->where('status', 'paid')
            ->sum('paid_amount') ?? 0;
    }

    public function getTotalOutstandingAmount(): float
    {
        return $this->salePayments()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('remaining_amount') ?? 0;
    }

    public function hasOverduePayments(): bool
    {
        return $this->salePayments()
            ->where('status', 'overdue')
            ->exists();
    }

    public function activate(): void
    {
        $this->update(['customer_status' => 'active']);
    }

    public function deactivate(): void
    {
        $this->update(['customer_status' => 'inactive']);
    }

    public function blacklist(): void
    {
        $this->update(['customer_status' => 'blacklisted']);
    }
}
