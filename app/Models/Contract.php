<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contract_type',
        'contract_number',
        'contract_name',
        'project_id',
        'subcontractor_id',
        'work_description',
        'scope_of_work',
        'contract_value',
        'currency',
        'payment_terms',
        'signing_date',
        'start_date',
        'end_date',
        'warranty_amount',
        'warranty_type',
        'warranty_start_date',
        'warranty_end_date',
        'status',
        'termination_date',
        'termination_reason',
        'notes',
        'documents',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'contract_value' => 'decimal:2',
        'warranty_amount' => 'decimal:2',
        'signing_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'warranty_start_date' => 'date',
        'warranty_end_date' => 'date',
        'termination_date' => 'date',
        'documents' => 'array',
    ];

    /**
     * İlişkiler
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function subcontractor(): BelongsTo
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function progressPayments(): HasMany
    {
        return $this->hasMany(ProgressPayment::class);
    }

    public function purchasingRequests(): HasMany
    {
        return $this->hasMany(PurchasingRequest::class);
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
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
            ->whereDate('end_date', '<=', now()->addDays($days))
            ->whereDate('end_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
            ->whereDate('end_date', '<', now());
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForSubcontractor($query, $subcontractorId)
    {
        return $query->where('subcontractor_id', $subcontractorId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('contract_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('contract_number', 'like', "%{$search}%")
                ->orWhere('contract_name', 'like', "%{$search}%")
                ->orWhere('work_description', 'like', "%{$search}%");
        });
    }

    /**
     * Accessor Methods
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'draft' => ['text' => 'Taslak', 'color' => 'gray'],
            'active' => ['text' => 'Aktif', 'color' => 'green'],
            'completed' => ['text' => 'Tamamlandı', 'color' => 'blue'],
            'terminated' => ['text' => 'Feshedildi', 'color' => 'red'],
            'expired' => ['text' => 'Süresi Doldu', 'color' => 'orange'],
            default => ['text' => 'Bilinmiyor', 'color' => 'gray'],
        };
    }

    public function getContractTypeLabelAttribute(): string
    {
        return match($this->contract_type) {
            'subcontractor' => 'Taşeron Sözleşmesi',
            'supplier' => 'Tedarikçi Anlaşması',
            default => 'Bilinmiyor',
        };
    }

    public function getWarrantyTypeLabelAttribute(): string
    {
        return match($this->warranty_type) {
            'bank_letter' => 'Banka Teminat Mektubu',
            'cash' => 'Nakit Teminat',
            'check' => 'Çek',
            'none' => 'Teminatsız',
            default => 'Bilinmiyor',
        };
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return now()->diffInDays($this->end_date, false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        $days = $this->days_until_expiry;
        return $days !== null && $days <= 30 && $days > 0;
    }

    public function getIsExpiredAttribute(): bool
    {
        $days = $this->days_until_expiry;
        return $days !== null && $days < 0;
    }

    public function getTotalPaidAmountAttribute(): float
    {
        return $this->progressPayments()
            ->where('status', 'paid')
            ->sum('total_amount') ?? 0;
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->contract_value - $this->total_paid_amount;
    }

    public function getCompletionPercentageAttribute(): float
    {
        if ($this->contract_value == 0) {
            return 0;
        }

        return round(($this->total_paid_amount / $this->contract_value) * 100, 2);
    }

    /**
     * Helper Methods
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function canBeTerminated(): bool
    {
        return in_array($this->status, ['draft', 'active']);
    }

    public function terminate(string $reason): void
    {
        if (!$this->canBeTerminated()) {
            throw new \Exception('Bu sözleşme feshedilemez.');
        }

        $this->update([
            'status' => 'terminated',
            'termination_date' => now(),
            'termination_reason' => $reason,
        ]);
    }

    public function activate(): void
    {
        if ($this->status !== 'draft') {
            throw new \Exception('Sadece taslak sözleşmeler aktif hale getirilebilir.');
        }

        $this->update(['status' => 'active']);
    }

    public function complete(): void
    {
        if ($this->status !== 'active') {
            throw new \Exception('Sadece aktif sözleşmeler tamamlanabilir.');
        }

        $this->update(['status' => 'completed']);
    }

    /**
     * Check and update expired contracts
     */
    public static function updateExpiredContracts(): int
    {
        return self::where('status', 'active')
            ->whereDate('end_date', '<', now())
            ->update(['status' => 'expired']);
    }
}
