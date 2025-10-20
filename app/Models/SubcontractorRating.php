<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubcontractorRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcontractor_id',
        'project_id',
        'rated_by',
        'rating_date',
        'quality_score',
        'timeline_score',
        'safety_score',
        'communication_score',
        'cost_score',
        'overall_score',
        'strengths',
        'weaknesses',
        'recommendations',
        'would_rehire',
    ];

    protected $casts = [
        'rating_date' => 'date',
        'quality_score' => 'decimal:2',
        'timeline_score' => 'decimal:2',
        'safety_score' => 'decimal:2',
        'communication_score' => 'decimal:2',
        'cost_score' => 'decimal:2',
        'overall_score' => 'decimal:2',
        'would_rehire' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'overall_rating_stars',
        'performance_level',
        'score_breakdown',
    ];

    /**
     * Değerlendirilen taşeron
     */
    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }

    /**
     * İlgili proje
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Değerlendirmeyi yapan kullanıcı
     */
    public function rater()
    {
        return $this->belongsTo(User::class, 'rated_by');
    }

    /**
     * Yüksek puanlılar (4.0 ve üzeri)
     */
    public function scopeHighRated($query)
    {
        return $query->where('overall_score', '>=', 4.0);
    }

    /**
     * Düşük puanlılar (2.0 ve altı)
     */
    public function scopeLowRated($query)
    {
        return $query->where('overall_score', '<=', 2.0);
    }

    /**
     * Projeye göre filtrele
     */
    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Taşerona göre filtrele
     */
    public function scopeBySubcontractor($query, $subcontractorId)
    {
        return $query->where('subcontractor_id', $subcontractorId);
    }

    /**
     * Tekrar işe alınacaklar
     */
    public function scopeWouldRehire($query)
    {
        return $query->where('would_rehire', true);
    }

    /**
     * Yıldız formatında genel puan
     */
    public function getOverallRatingStarsAttribute(): string
    {
        $fullStars = floor($this->overall_score);
        $halfStar = ($this->overall_score - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;

        return str_repeat('★', (int) $fullStars) .
               str_repeat('⯨', $halfStar) .
               str_repeat('☆', (int) $emptyStars);
    }

    /**
     * Performans seviyesi
     */
    public function getPerformanceLevelAttribute(): array
    {
        $score = $this->overall_score;

        if ($score >= 4.5) {
            return ['level' => 'Mükemmel', 'color' => 'green', 'icon' => '🌟'];
        } elseif ($score >= 4.0) {
            return ['level' => 'Çok İyi', 'color' => 'blue', 'icon' => '👍'];
        } elseif ($score >= 3.0) {
            return ['level' => 'İyi', 'color' => 'yellow', 'icon' => '✓'];
        } elseif ($score >= 2.0) {
            return ['level' => 'Orta', 'color' => 'orange', 'icon' => '⚠'];
        } else {
            return ['level' => 'Zayıf', 'color' => 'red', 'icon' => '⚠'];
        }
    }

    /**
     * Puan dağılımı
     */
    public function getScoreBreakdownAttribute(): array
    {
        return [
            'quality' => [
                'label' => 'Kalite',
                'score' => $this->quality_score,
                'percentage' => $this->quality_score ? ($this->quality_score / 5) * 100 : 0,
            ],
            'timeline' => [
                'label' => 'Zaman Yönetimi',
                'score' => $this->timeline_score,
                'percentage' => $this->timeline_score ? ($this->timeline_score / 5) * 100 : 0,
            ],
            'safety' => [
                'label' => 'İş Güvenliği',
                'score' => $this->safety_score,
                'percentage' => $this->safety_score ? ($this->safety_score / 5) * 100 : 0,
            ],
            'communication' => [
                'label' => 'İletişim',
                'score' => $this->communication_score,
                'percentage' => $this->communication_score ? ($this->communication_score / 5) * 100 : 0,
            ],
            'cost' => [
                'label' => 'Maliyet',
                'score' => $this->cost_score,
                'percentage' => $this->cost_score ? ($this->cost_score / 5) * 100 : 0,
            ],
        ];
    }

    /**
     * Değerlendirme kaydedilirken genel puanı hesapla
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($rating) {
            // Eğer genel puan verilmemişse, diğer puanların ortalamasını al
            if (!$rating->overall_score) {
                $scores = array_filter([
                    $rating->quality_score,
                    $rating->timeline_score,
                    $rating->safety_score,
                    $rating->communication_score,
                    $rating->cost_score,
                ]);

                $rating->overall_score = count($scores) > 0
                    ? round(array_sum($scores) / count($scores), 2)
                    : 0;
            }

            // Değerlendirme tarihi yoksa bugünü kullan
            if (!$rating->rating_date) {
                $rating->rating_date = now();
            }
        });

        static::saved(function ($rating) {
            // Taşeronun genel puanını güncelle
            $rating->subcontractor->updateRating();

            // Tamamlanan proje sayısını güncelle
            $completedProjects = SubcontractorRating::where('subcontractor_id', $rating->subcontractor_id)
                ->distinct('project_id')
                ->count('project_id');

            $rating->subcontractor->update([
                'completed_projects' => $completedProjects,
            ]);
        });
    }
}
