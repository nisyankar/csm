<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchasingRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_code',
        'title',
        'description',
        'requested_by',
        'project_id',
        'department_id',
        'status',
        'urgency',
        'category',
        'required_date',
        'submitted_at',
        'approved_at',
        'ordered_at',
        'delivered_at',
        'approved_by_supervisor',
        'approved_by_manager',
        'supervisor_notes',
        'manager_notes',
        'rejection_reason',
        'estimated_total',
        'actual_total',
    ];

    protected $casts = [
        'required_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'ordered_at' => 'datetime',
        'delivered_at' => 'datetime',
        'estimated_total' => 'decimal:2',
        'actual_total' => 'decimal:2',
    ];

    // İlişkiler

    /**
     * Talebi oluşturan kullanıcı
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Talep ile ilişkili proje
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Talep ile ilişkili departman
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Şef onayı
     */
    public function supervisorApproval(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_supervisor');
    }

    /**
     * Yönetici onayı
     */
    public function managerApproval(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_manager');
    }

    /**
     * Talep kalemleri
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchasingItem::class);
    }

    /**
     * Tedarikçi teklifleri
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(SupplierQuotation::class);
    }

    /**
     * Sipariş
     */
    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    // Scopes

    /**
     * Belirli durumdaki talepleri getir
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Bekleyen talepleri getir
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Onaylanmış talepleri getir
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Acil talepleri getir
     */
    public function scopeUrgent($query)
    {
        return $query->where('urgency', 'urgent');
    }

    /**
     * Belirli kategorideki talepleri getir
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Belirli projedeki talepleri getir
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    // Helper metodlar

    /**
     * Talep kodu oluştur (PR-2025-0001)
     */
    public static function generateRequestCode(): string
    {
        $year = now()->year;
        $lastRequest = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastRequest ? ((int) substr($lastRequest->request_code, -4)) + 1 : 1;

        return 'PR-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Onaya gönder
     */
    public function submit(): bool
    {
        $this->status = 'pending';
        $this->submitted_at = now();
        return $this->save();
    }

    /**
     * Şef onayı
     */
    public function approveBySupervisor(int $userId, ?string $notes = null): bool
    {
        $this->approved_by_supervisor = $userId;
        $this->supervisor_notes = $notes;

        // Eğer yönetici onayı da varsa, durumu approved yap
        if ($this->approved_by_manager) {
            $this->status = 'approved';
            $this->approved_at = now();
        }

        return $this->save();
    }

    /**
     * Yönetici onayı
     */
    public function approveByManager(int $userId, ?string $notes = null): bool
    {
        $this->approved_by_manager = $userId;
        $this->manager_notes = $notes;

        // Eğer şef onayı da varsa, durumu approved yap
        if ($this->approved_by_supervisor) {
            $this->status = 'approved';
            $this->approved_at = now();
        }

        return $this->save();
    }

    /**
     * Reddet
     */
    public function reject(string $reason): bool
    {
        $this->status = 'rejected';
        $this->rejection_reason = $reason;
        return $this->save();
    }

    /**
     * İptal et
     */
    public function cancel(): bool
    {
        $this->status = 'cancelled';
        return $this->save();
    }

    /**
     * Siparişe çevir
     */
    public function markAsOrdered(): bool
    {
        $this->status = 'ordered';
        $this->ordered_at = now();
        return $this->save();
    }

    /**
     * Teslim edildi olarak işaretle
     */
    public function markAsDelivered(): bool
    {
        $this->status = 'delivered';
        $this->delivered_at = now();
        return $this->save();
    }

    /**
     * Tahmini toplam tutarı hesapla
     */
    public function calculateEstimatedTotal(): float
    {
        return $this->items()->sum('estimated_total_price');
    }

    /**
     * Gerçekleşen toplam tutarı hesapla
     */
    public function calculateActualTotal(): float
    {
        return $this->items()->sum('actual_total_price');
    }

    /**
     * Onay bekliyor mu?
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Onaylandı mı?
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Reddedildi mi?
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Durum badge rengi (UI için)
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'pending' => 'yellow',
            'approved' => 'green',
            'ordered' => 'blue',
            'delivered' => 'green',
            'cancelled' => 'red',
            'rejected' => 'red',
            default => 'gray',
        };
    }

    /**
     * Aciliyet badge rengi (UI için)
     */
    public function getUrgencyColor(): string
    {
        return match($this->urgency) {
            'low' => 'gray',
            'normal' => 'blue',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray',
        };
    }
}