<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DwgImport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'uploaded_by',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_size',
        'mime_type',
        'import_type',
        'status',
        'started_at',
        'completed_at',
        'processing_duration_seconds',
        'parsed_data',
        'detected_layers',
        'layer_mappings',
        'created_structures',
        'structures_count',
        'floors_count',
        'units_count',
        'error_message',
        'error_details',
        'notes',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'processing_duration_seconds' => 'integer',
        'parsed_data' => 'array',
        'detected_layers' => 'array',
        'layer_mappings' => 'array',
        'created_structures' => 'array',
        'error_details' => 'array',
        'structures_count' => 'integer',
        'floors_count' => 'integer',
        'units_count' => 'integer',
    ];

    // Relationships

    /**
     * DWG dosyasının ait olduğu proje
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Dosyayı yükleyen kullanıcı
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes

    /**
     * Bekleyen importlar
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * İşlenen importlar
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Tamamlanan importlar
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Başarısız importlar
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * İnceleme bekleyen importlar
     */
    public function scopeReadyForReview($query)
    {
        return $query->where('status', 'ready_for_review');
    }

    // Accessors

    /**
     * Durum label (Türkçe)
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Beklemede',
            'processing' => 'İşleniyor',
            'ready_for_review' => 'İnceleme Bekliyor',
            'completed' => 'Tamamlandı',
            'failed' => 'Başarısız',
            default => ucfirst($this->status),
        };
    }

    /**
     * Import tipi label (Türkçe)
     */
    public function getImportTypeLabelAttribute(): string
    {
        return match($this->import_type) {
            'comprehensive' => 'Toplu İçe Aktarım (Yapı/Kat/Birim)',
            'structures_only' => 'Sadece Yapılar',
            'floors_only' => 'Sadece Katlar',
            'units_only' => 'Sadece Birimler',
            default => ucfirst($this->import_type),
        };
    }

    /**
     * Dosya boyutu (human readable)
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * İşlem tamamlandı mı?
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * İşlem başarısız mı?
     */
    public function getIsFailedAttribute(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * İşlem devam ediyor mu?
     */
    public function getIsProcessingAttribute(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * İnceleme bekliyor mu?
     */
    public function getIsReadyForReviewAttribute(): bool
    {
        return $this->status === 'ready_for_review';
    }

    /**
     * Toplam oluşturulan kayıt sayısı
     */
    public function getTotalCreatedCountAttribute(): int
    {
        return $this->structures_count + $this->floors_count + $this->units_count;
    }

    // Helper Methods

    /**
     * İşlemi başlat
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * İnceleme için hazır olarak işaretle
     */
    public function markAsReadyForReview(array $parsedData, array $detectedLayers): void
    {
        $this->update([
            'status' => 'ready_for_review',
            'parsed_data' => $parsedData,
            'detected_layers' => $detectedLayers,
            'processing_duration_seconds' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
        ]);
    }

    /**
     * İşlemi başarılı tamamla (onaylandıktan sonra)
     */
    public function markAsCompleted(array $parsedData, array $createdStructures, array $counts): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'processing_duration_seconds' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
            'parsed_data' => $parsedData,
            'created_structures' => $createdStructures,
            'structures_count' => $counts['structures'] ?? 0,
            'floors_count' => $counts['floors'] ?? 0,
            'units_count' => $counts['units'] ?? 0,
        ]);
    }

    /**
     * İşlemi başarısız olarak işaretle
     */
    public function markAsFailed(string $errorMessage, array $errorDetails = []): void
    {
        // Sanitize error message to prevent encoding issues
        $sanitizedMessage = mb_convert_encoding($errorMessage, 'UTF-8', 'UTF-8');
        $sanitizedMessage = preg_replace('/[\x00-\x1F\x7F]/u', '', $sanitizedMessage); // Remove control characters

        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'processing_duration_seconds' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
            'error_message' => $sanitizedMessage,
            'error_details' => $errorDetails,
        ]);
    }
}
