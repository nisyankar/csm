<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ConstructionPermit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'project_unit_id',
        'permit_type',
        'permit_number',
        'application_date',
        'approval_date',
        'expiry_date',
        'status',
        'issuing_authority',
        'zoning_status',
        'documents',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'application_date' => 'date',
        'approval_date' => 'date',
        'expiry_date' => 'date',
        'documents' => 'array',
    ];

    protected $attributes = [
        'documents' => '[]',
    ];

    protected $appends = [
        'permit_type_label',
        'status_label',
        'status_badge',
        'is_expiring_soon',
        'days_until_expiry',
        'is_expired',
    ];

    /**
     * Relationships
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectUnit()
    {
        return $this->belongsTo(ProjectUnit::class, 'project_unit_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Accessors
     */
    public function getPermitTypeLabelAttribute()
    {
        return match ($this->permit_type) {
            'building' => 'Yapı Ruhsatı',
            'demolition' => 'Yıkım Ruhsatı',
            'occupancy' => 'İskan İzni',
            'usage' => 'Yapı Kullanma İzni',
            default => $this->permit_type,
        };
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'pending' => 'Beklemede',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            'expired' => 'Süresi Doldu',
            'renewed' => 'Yenilendi',
            default => $this->status,
        };
    }

    public function getStatusBadgeAttribute()
    {
        $classes = match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'approved' => 'bg-green-100 text-green-800 border-green-200',
            'rejected' => 'bg-red-100 text-red-800 border-red-200',
            'expired' => 'bg-gray-100 text-gray-800 border-gray-200',
            'renewed' => 'bg-blue-100 text-blue-800 border-blue-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };

        return [
            'text' => $this->status_label,
            'class' => $classes,
        ];
    }

    public function getIsExpiringSoonAttribute()
    {
        if (!$this->expiry_date || $this->is_expired) {
            return false;
        }

        $daysUntilExpiry = Carbon::today()->diffInDays($this->expiry_date, false);
        return $daysUntilExpiry >= 0 && $daysUntilExpiry <= 30;
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) {
            return null;
        }

        return Carbon::today()->diffInDays($this->expiry_date, false);
    }

    public function getIsExpiredAttribute()
    {
        if (!$this->expiry_date) {
            return false;
        }

        return Carbon::today()->isAfter($this->expiry_date);
    }

    /**
     * Scopes
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->whereRaw('expiry_date > CURDATE()')
            ->whereRaw('DATEDIFF(expiry_date, CURDATE()) <= ?', [$days])
            ->orderBy('expiry_date', 'asc');
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->whereRaw('expiry_date < CURDATE()')
            ->orderBy('expiry_date', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['approved', 'renewed'])
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhereRaw('expiry_date >= CURDATE()');
            });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('permit_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Helper Methods
     */
    public static function generatePermitNumber($projectCode, $permitType)
    {
        // Format: PRJ-CODE-PERMIT-TYPE-YYYY-0001
        $year = date('Y');
        $typeCode = strtoupper(substr($permitType, 0, 3));

        $lastPermit = self::where('permit_number', 'like', "{$projectCode}-{$typeCode}-{$year}-%")
            ->orderBy('permit_number', 'desc')
            ->first();

        if ($lastPermit) {
            $lastNumber = (int) substr($lastPermit->permit_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$projectCode}-{$typeCode}-{$year}-{$newNumber}";
    }

    public function markAsExpired()
    {
        $this->update(['status' => 'expired']);
    }

    public function markAsRenewed()
    {
        $this->update(['status' => 'renewed']);
    }

    public function approve($approvalDate = null)
    {
        $this->update([
            'status' => 'approved',
            'approval_date' => $approvalDate ?? now(),
        ]);
    }

    public function reject()
    {
        $this->update(['status' => 'rejected']);
    }
}
