<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SubcontractorCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcontractor_id',
        'certificate_type',
        'certificate_name',
        'certificate_number',
        'issuing_authority',
        'issue_date',
        'expiry_date',
        'file_path',
        'file_name',
        'file_size',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'is_expired',
        'days_until_expiry',
        'status_badge',
        'type_label',
        'file_url',
    ];

    /**
     * Belge türü etiketleri
     */
    public static $typeLabels = [
        'kapasite_raporu' => 'Kapasite Raporu',
        'deneyim_belgesi' => 'Deneyim Belgesi',
        'iso_9001' => 'ISO 9001',
        'iso_14001' => 'ISO 14001',
        'iso_45001' => 'ISO 45001',
        'sgk_borcu_yok' => 'SGK Borcu Yok Belgesi',
        'vergi_borcu_yok' => 'Vergi Borcu Yok Belgesi',
        'ticaret_sicil' => 'Ticaret Sicil Gazetesi',
        'imza_sirküleri' => 'İmza Sirküleri',
        'other' => 'Diğer',
    ];

    /**
     * Belgenin sahibi taşeron
     */
    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }

    /**
     * Süresi dolmuş belgeler
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<', now());
            });
    }

    /**
     * Geçerli belgeler
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'valid')
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', now());
            });
    }

    /**
     * Süresi yakında dolacak belgeler
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'valid')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($days))
            ->whereDate('expiry_date', '>', now());
    }

    /**
     * Belge türüne göre filtrele
     */
    public function scopeByType($query, $type)
    {
        return $query->where('certificate_type', $type);
    }

    /**
     * Belge süresi dolmuş mu?
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    /**
     * Son kullanma tarihine kaç gün kaldı
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        $days = now()->diffInDays($this->expiry_date, false);
        return (int) $days;
    }

    /**
     * Durum badge'i
     */
    public function getStatusBadgeAttribute(): array
    {
        if ($this->is_expired) {
            return ['text' => 'Süresi Dolmuş', 'color' => 'red'];
        }

        if ($this->days_until_expiry !== null && $this->days_until_expiry <= 30) {
            return ['text' => 'Süresi Yakında Dolacak', 'color' => 'orange'];
        }

        return match($this->status) {
            'valid' => ['text' => 'Geçerli', 'color' => 'green'],
            'expired' => ['text' => 'Süresi Dolmuş', 'color' => 'red'],
            'pending' => ['text' => 'Onay Bekliyor', 'color' => 'yellow'],
            default => ['text' => 'Bilinmiyor', 'color' => 'gray'],
        };
    }

    /**
     * Belge türü etiketi
     */
    public function getTypeLabelAttribute(): string
    {
        return self::$typeLabels[$this->certificate_type] ?? 'Bilinmiyor';
    }

    /**
     * Dosya URL'si
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    /**
     * Belge durumunu otomatik güncelle
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($certification) {
            // Eğer son kullanma tarihi geçmişse, durumu otomatik güncelle
            if ($certification->expiry_date && $certification->expiry_date->isPast()) {
                $certification->status = 'expired';
            }
        });

        static::deleting(function ($certification) {
            // Belge silinirken dosyayı da sil
            if ($certification->file_path) {
                Storage::delete($certification->file_path);
            }
        });
    }
}
