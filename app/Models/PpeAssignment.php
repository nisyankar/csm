<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpeAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'employee_id',
        'ppe_type',
        'brand',
        'model',
        'size',
        'serial_number',
        'description',
        'assigned_date',
        'assigned_by',
        'return_date',
        'returned_to',
        'return_condition',
        'status',
        'quantity',
        'unit_price',
        'total_price',
        'expiry_date',
        'inspection_required',
        'last_inspection_date',
        'next_inspection_date',
        'inspection_notes',
        'certificate_number',
        'certificate_expiry',
        'notes',
        'replaced_by_assignment_id',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'return_date' => 'date',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'expiry_date' => 'date',
        'inspection_required' => 'boolean',
        'last_inspection_date' => 'date',
        'next_inspection_date' => 'date',
        'certificate_expiry' => 'date',
    ];

    /**
     * İlişkiler
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function returnedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    public function replacedByAssignment(): BelongsTo
    {
        return $this->belongsTo(PpeAssignment::class, 'replaced_by_assignment_id');
    }

    /**
     * Scopes
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('ppe_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    public function scopeRequiringInspection($query)
    {
        return $query->where('inspection_required', true)
            ->where('next_inspection_date', '<=', now());
    }

    public function scopeCertificateExpiring($query, $days = 30)
    {
        return $query->whereNotNull('certificate_expiry')
            ->whereBetween('certificate_expiry', [now(), now()->addDays($days)]);
    }

    /**
     * Helper Methods
     */
    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    public function needsInspection(): bool
    {
        return $this->inspection_required &&
               $this->next_inspection_date &&
               $this->next_inspection_date <= now();
    }

    public function isCertificateExpired(): bool
    {
        return $this->certificate_expiry && $this->certificate_expiry < now();
    }

    public function getPpeTypeLabelAttribute(): string
    {
        return match($this->ppe_type) {
            'helmet' => 'Baret',
            'safety_boots' => 'İş Ayakkabısı',
            'gloves' => 'Eldiven',
            'goggles' => 'Koruyucu Gözlük',
            'vest' => 'Reflektörlü Yelek',
            'harness' => 'Emniyet Kemeri',
            'respirator' => 'Solunum Maskesi',
            'ear_protection' => 'Kulak Koruyucu',
            'face_shield' => 'Yüz Siperi',
            'coverall' => 'Tulum',
            'knee_pads' => 'Dizlik',
            'dust_mask' => 'Toz Maskesi',
            'welding_mask' => 'Kaynak Maskesi',
            'fall_arrest' => 'Düşüş Durdurma Sistemi',
            'other' => 'Diğer',
            default => $this->ppe_type
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'assigned' => 'Atanmış',
            'returned' => 'İade Edildi',
            'lost' => 'Kayıp',
            'damaged' => 'Hasarlı',
            'expired' => 'Süresi Doldu',
            'replaced' => 'Değiştirildi',
            default => $this->status
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'assigned' => 'green',
            'returned' => 'blue',
            'lost' => 'red',
            'damaged' => 'orange',
            'expired' => 'red',
            'replaced' => 'gray',
            default => 'gray'
        };
    }

    public function getReturnConditionLabelAttribute(): ?string
    {
        if (!$this->return_condition) {
            return null;
        }

        return match($this->return_condition) {
            'good' => 'İyi Durumda',
            'fair' => 'Kullanılabilir',
            'poor' => 'Kötü Durumda',
            'damaged' => 'Hasarlı',
            'lost' => 'Kayıp',
            default => $this->return_condition
        };
    }
}
