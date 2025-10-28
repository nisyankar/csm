<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Inspection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'inspection_company_id',
        'inspection_number',
        'inspector_name',
        'inspection_date',
        'inspection_type',
        'status',
        'findings',
        'non_conformities',
        'corrective_actions',
        'attachments',
        'report_path',
        'next_inspection_date',
        'notes',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'next_inspection_date' => 'date',
        'non_conformities' => 'array',
        'corrective_actions' => 'array',
        'attachments' => 'array',
    ];

    protected $attributes = [
        'non_conformities' => '[]',
        'corrective_actions' => '[]',
        'attachments' => '[]',
    ];

    /**
     * Projeye ait denetim
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Denetim kuruluşuna ait
     */
    public function inspectionCompany(): BelongsTo
    {
        return $this->belongsTo(InspectionCompany::class);
    }

    /**
     * Denetim türü etiketi
     */
    public function getInspectionTypeLabelAttribute(): string
    {
        return match($this->inspection_type) {
            'periodic' => 'Periyodik Denetim',
            'special' => 'Özel Denetim',
            'final' => 'Final Denetim',
            default => $this->inspection_type,
        };
    }

    /**
     * Durum badge'i (HTML)
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'scheduled' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Planlandı</span>',
            'completed' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Tamamlandı</span>',
            'pending_action' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Eylem Bekliyor</span>',
            'closed' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Kapatıldı</span>',
            default => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">' . $this->status . '</span>',
        };
    }

    /**
     * Bekleyen düzeltici faaliyet var mı?
     */
    public function getHasPendingActionsAttribute(): bool
    {
        if (empty($this->corrective_actions)) {
            return false;
        }

        foreach ($this->corrective_actions as $action) {
            if (isset($action['status']) && $action['status'] !== 'completed') {
                return true;
            }
        }

        return false;
    }

    /**
     * Kritik uygunsuzluk sayısı
     */
    public function getCriticalNonConformitiesCountAttribute(): int
    {
        if (empty($this->non_conformities)) {
            return 0;
        }

        return collect($this->non_conformities)
            ->where('severity', 'critical')
            ->count();
    }

    /**
     * Rapor dosya URL'i
     */
    public function getReportUrlAttribute(): ?string
    {
        if (!$this->report_path) {
            return null;
        }

        return Storage::url($this->report_path);
    }

    /**
     * Ek dosyaları URL'leriyle döndür
     */
    public function getAttachmentsWithUrlsAttribute(): array
    {
        if (empty($this->attachments)) {
            return [];
        }

        return collect($this->attachments)->map(function ($attachment) {
            return array_merge($attachment, [
                'url' => Storage::url($attachment['path'] ?? ''),
            ]);
        })->toArray();
    }

    /**
     * Sonraki denetim yaklaşıyor mu?
     */
    public function getIsNextInspectionSoonAttribute(): bool
    {
        if (!$this->next_inspection_date) {
            return false;
        }

        return $this->next_inspection_date->diffInDays(now()) <= 7 && $this->next_inspection_date->isFuture();
    }

    /**
     * Sonraki denetim geçti mi?
     */
    public function getIsNextInspectionOverdueAttribute(): bool
    {
        if (!$this->next_inspection_date) {
            return false;
        }

        return $this->next_inspection_date->isPast();
    }

    /**
     * Otomatik denetim numarası oluştur
     */
    public static function generateInspectionNumber(int $projectId, string $inspectionType): string
    {
        $project = Project::find($projectId);
        $typePrefix = match($inspectionType) {
            'periodic' => 'PER',
            'special' => 'OZL',
            'final' => 'FIN',
            default => 'DEN',
        };

        $year = now()->year;
        $lastInspection = self::where('project_id', $projectId)
            ->where('inspection_type', $inspectionType)
            ->whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $sequence = $lastInspection ? intval(substr($lastInspection->inspection_number, -4)) + 1 : 1;

        return sprintf('%s-%s-%s-%04d', $project->project_code, $typePrefix, $year, $sequence);
    }
}
