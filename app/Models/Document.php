<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'uploaded_by',
        'title',
        'description',
        'file_name',
        'file_path',
        'file_extension',
        'file_size',
        'mime_type',
        'file_hash',
        'document_type',
        'document_subtype',
        'issue_date',
        'expiry_date',
        'has_expiry',
        'expiry_warning_days',
        'status',
        'verified_by',
        'verified_at',
        'verification_notes',
        'rejection_reason',
        'is_mandatory',
        'priority',
        'privacy_level',
        'access_permissions',
        'version',
        'parent_document_id',
        'is_latest_version',
        'requires_signature',
        'signed_at',
        'signature_hash',
        'auto_reminder_enabled',
        'reminder_schedule',
        'last_reminder_sent',
        'is_archived',
        'archived_at',
        'archived_by',
        'archive_reason',
        'metadata',
        'tags',
        'download_count',
        'last_downloaded_at',
        'last_downloaded_by',
        'is_backed_up',
        'backed_up_at',
        'backup_location',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'has_expiry' => 'boolean',
        'expiry_warning_days' => 'integer',
        'verified_at' => 'datetime',
        'is_mandatory' => 'boolean',
        'access_permissions' => 'array',
        'version' => 'integer',
        'is_latest_version' => 'boolean',
        'requires_signature' => 'boolean',
        'signed_at' => 'datetime',
        'auto_reminder_enabled' => 'boolean',
        'reminder_schedule' => 'array',
        'last_reminder_sent' => 'datetime',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
        'metadata' => 'array',
        'tags' => 'array',
        'download_count' => 'integer',
        'last_downloaded_at' => 'datetime',
        'is_backed_up' => 'boolean',
        'backed_up_at' => 'datetime',
        'file_size' => 'integer',
    ];

    // İlişkiler

    /**
     * Belgenin ait olduğu personel
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Belgeyi yükleyen kullanıcı
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Belgeyi doğrulayan kullanıcı
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Arşivleyen kullanıcı
     */
    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * Son indiren kullanıcı
     */
    public function lastDownloadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_downloaded_by');
    }

    /**
     * Ana belge (versiyonlama için)
     */
    public function parentDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'parent_document_id');
    }

    /**
     * Belge versiyonları
     */
    public function versions()
    {
        return $this->hasMany(Document::class, 'parent_document_id');
    }

    // Accessor ve Mutator'lar

    /**
     * Belge türü human-readable
     */
    public function getDocumentTypeDisplayAttribute(): string
    {
        return match($this->document_type) {
            'identity' => 'Kimlik Belgeleri',
            'education' => 'Eğitim Belgeleri',
            'health' => 'Sağlık Raporları',
            'insurance' => 'Sigorta Belgeleri',
            'contract' => 'İş Sözleşmesi',
            'certificate' => 'Sertifikalar',
            'photo' => 'Fotoğraflar',
            'medical_report' => 'Tıbbi Raporlar',
            'safety_training' => 'İş Güvenliği Eğitimleri',
            'bank_info' => 'Banka Bilgileri',
            'reference' => 'Referans Mektupları',
            'other' => 'Diğer',
            default => ucfirst($this->document_type),
        };
    }

    /**
     * Durum human-readable
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Beklemede',
            'verified' => 'Doğrulandı',
            'rejected' => 'Reddedildi',
            'expired' => 'Süresi Doldu',
            'archived' => 'Arşivlendi',
            default => ucfirst($this->status),
        };
    }

    /**
     * Durum badge class'ı
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'verified' => 'badge-success',
            'rejected' => 'badge-danger',
            'expired' => 'badge-secondary',
            'archived' => 'badge-info',
            default => 'badge-light',
        };
    }

    /**
     * Gizlilik seviyesi human-readable
     */
    public function getPrivacyLevelDisplayAttribute(): string
    {
        return match($this->privacy_level) {
            'public' => 'Herkese Açık',
            'internal' => 'Şirket İçi',
            'hr_only' => 'Sadece İK',
            'confidential' => 'Gizli',
            default => ucfirst($this->privacy_level),
        };
    }

    /**
     * Dosya boyutu human-readable
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Dosya tam URL'i
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Süresi dolmuş mu?
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->has_expiry && 
               $this->expiry_date && 
               now() > $this->expiry_date;
    }

    /**
     * Süre dolma uyarısı verilmeli mi?
     */
    public function getNeedsExpiryWarningAttribute(): bool
    {
        if (!$this->has_expiry || !$this->expiry_date || $this->is_expired) {
            return false;
        }
        
        $warningDate = $this->expiry_date->subDays($this->expiry_warning_days ?? 30);
        return now() >= $warningDate;
    }

    /**
     * Kalan gün sayısı
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->has_expiry || !$this->expiry_date) {
            return null;
        }
        
        $days = now()->diffInDays($this->expiry_date, false);
        return $days > 0 ? $days : 0;
    }

    /**
     * Doğrulanmış mı?
     */
    public function getIsVerifiedAttribute(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * İmzalanmış mı?
     */
    public function getIsSignedAttribute(): bool
    {
        return !is_null($this->signed_at);
    }

    /**
     * En son versiyon mu?
     */
    public function getIsCurrentVersionAttribute(): bool
    {
        return $this->is_latest_version;
    }

    // Scope'lar

    /**
     * Doğrulanmış belgeler
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Beklemedeki belgeler
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Süresi dolmuş belgeler
     */
    public function scopeExpired($query)
    {
        return $query->where('has_expiry', true)
                    ->where('expiry_date', '<', now());
    }

    /**
     * Süre dolma uyarısı verilecek belgeler
     */
    public function scopeExpiryWarning($query, $days = 30)
    {
        return $query->where('has_expiry', true)
                    ->where('expiry_date', '>', now())
                    ->whereRaw('expiry_date <= DATE_ADD(NOW(), INTERVAL ? DAY)', [$days]);
    }

    /**
     * Zorunlu belgeler
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Belirli türdeki belgeler
     */
    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Belirli personelin belgeleri
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * En son versiyonlar
     */
    public function scopeLatestVersions($query)
    {
        return $query->where('is_latest_version', true);
    }

    /**
     * Arşivlenmemiş belgeler
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    // Helper metodlar

    /**
     * Belgeyi doğrula
     */
    public function verify(User $verifier, string $notes = null): void
    {
        $this->update([
            'status' => 'verified',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }

    /**
     * Belgeyi reddet
     */
    public function reject(User $verifier, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Belgeyi arşivle
     */
    public function archive(User $archiver, string $reason = null): void
    {
        $this->update([
            'status' => 'archived',
            'is_archived' => true,
            'archived_by' => $archiver->id,
            'archived_at' => now(),
            'archive_reason' => $reason,
        ]);
    }

    /**
     * Belge indir ve sayacı artır
     */
    public function download(User $downloader = null): string
    {
        $this->increment('download_count');
        $this->update([
            'last_downloaded_at' => now(),
            'last_downloaded_by' => $downloader?->id,
        ]);
        
        return Storage::path($this->file_path);
    }

    /**
     * Dosya hash'i oluştur
     */
    public function generateFileHash(): void
    {
        if (Storage::exists($this->file_path)) {
            $this->file_hash = hash_file('sha256', Storage::path($this->file_path));
            $this->save();
        }
    }

    /**
     * Yeni versiyon oluştur
     */
    public function createNewVersion(array $fileData): self
    {
        // Mevcut versiyonu eski olarak işaretle
        $this->update(['is_latest_version' => false]);
        
        // Yeni versiyon oluştur
        $newVersion = $this->replicate();
        $newVersion->version = $this->version + 1;
        $newVersion->parent_document_id = $this->parent_document_id ?? $this->id;
        $newVersion->is_latest_version = true;
        $newVersion->status = 'pending';
        $newVersion->verified_by = null;
        $newVersion->verified_at = null;
        
        // Dosya bilgilerini güncelle
        foreach ($fileData as $key => $value) {
            $newVersion->{$key} = $value;
        }
        
        $newVersion->save();
        
        return $newVersion;
    }

    /**
     * Dijital imza ekle
     */
    public function addDigitalSignature(string $signatureData): void
    {
        $this->update([
            'signed_at' => now(),
            'signature_hash' => hash('sha256', $signatureData),
        ]);
    }

    /**
     * Hatırlatma gönder
     */
    public function sendReminder(): void
    {
        if (!$this->auto_reminder_enabled || !$this->needs_expiry_warning) {
            return;
        }
        
        // Burada email/SMS gönderilecek
        
        $this->update(['last_reminder_sent' => now()]);
    }

    /**
     * Backup durumunu güncelle
     */
    public function markAsBackedUp(string $location): void
    {
        $this->update([
            'is_backed_up' => true,
            'backed_up_at' => now(),
            'backup_location' => $location,
        ]);
    }

    /**
     * Dosyayı sil
     */
    public function deleteFile(): bool
    {
        if (Storage::exists($this->file_path)) {
            return Storage::delete($this->file_path);
        }
        return true;
    }

    /**
     * OCR/Metadata çıkar
     */
    public function extractMetadata(): array
    {
        $metadata = [];
        
        // Dosya türüne göre metadata çıkarma
        if (in_array($this->file_extension, ['pdf', 'jpg', 'jpeg', 'png'])) {
            // OCR işlemi burada yapılabilir
            $metadata['ocr_text'] = 'OCR sonucu...'; // Placeholder
        }
        
        // Exif bilgileri (resimler için)
        if (in_array($this->file_extension, ['jpg', 'jpeg'])) {
            $metadata['exif'] = []; // Exif bilgileri
        }
        
        // Metadata'yı kaydet
        $this->metadata = array_merge($this->metadata ?? [], $metadata);
        $this->save();
        
        return $metadata;
    }

    /**
     * Erişim izni kontrol et
     */
    public function canAccess(User $user): bool
    {
        // Admin her şeye erişebilir
        if ($user->user_type === 'admin') {
            return true;
        }
        
        // Gizlilik seviyesine göre kontrol
        return match($this->privacy_level) {
            'public' => true,
            'internal' => $user->user_type !== 'viewer',
            'hr_only' => in_array($user->user_type, ['hr', 'admin']),
            'confidential' => $user->id === $this->employee->user_id || 
                             in_array($user->user_type, ['hr', 'admin']),
            default => false,
        };
    }

    /**
     * Eksik zorunlu belgeleri bul
     */
    public static function getMissingMandatoryDocuments(Employee $employee): array
    {
        $mandatoryTypes = [
            'identity', 'health', 'insurance', 'contract', 'safety_training'
        ];
        
        $existingTypes = $employee->documents()
                                 ->verified()
                                 ->active()
                                 ->pluck('document_type')
                                 ->unique()
                                 ->toArray();
        
        return array_diff($mandatoryTypes, $existingTypes);
    }

    /**
     * Süre dolacak belgeleri getir
     */
    public static function getExpiringDocuments(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return self::expiryWarning($days)
                   ->with(['employee', 'verifiedBy'])
                   ->get();
    }
}