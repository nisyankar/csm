<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgressPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'subcontractor_id',
        'work_item_id',
        'quantity_id',
        'project_structure_id',
        'project_floor_id',
        'project_unit_id',
        'planned_quantity',
        'completed_quantity',
        'unit',
        'unit_price',
        'payment_date',
        'status',
        'is_quantity_overrun',
        'overrun_amount',
        'overrun_notes',
        'period_year',
        'period_month',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:2',
        'completed_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'overrun_amount' => 'decimal:2',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'is_quantity_overrun' => 'boolean',
    ];

    protected $appends = ['completion_percentage'];

    /**
     * İlerleme yüzdesi hesaplama
     */
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->planned_quantity == 0) {
            return 0;
        }

        return round(($this->completed_quantity / $this->planned_quantity) * 100, 2);
    }

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

    public function workItem(): BelongsTo
    {
        return $this->belongsTo(WorkItem::class);
    }

    public function projectStructure(): BelongsTo
    {
        return $this->belongsTo(ProjectStructure::class);
    }

    public function projectFloor(): BelongsTo
    {
        return $this->belongsTo(ProjectFloor::class);
    }

    public function projectUnit(): BelongsTo
    {
        return $this->belongsTo(ProjectUnit::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function quantity(): BelongsTo
    {
        return $this->belongsTo(Quantity::class);
    }

    /**
     * Scopes
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForSubcontractor($query, $subcontractorId)
    {
        return $query->where('subcontractor_id', $subcontractorId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForPeriod($query, $year, $month = null)
    {
        $query->where('period_year', $year);

        if ($month) {
            $query->where('period_month', $month);
        }

        return $query;
    }

    public function scopeCompleted($query)
    {
        return $query->where('completed_quantity', '>=', $this->planned_quantity);
    }

    /**
     * Helper Methods
     */
    public function isCompleted(): bool
    {
        return $this->completed_quantity >= $this->planned_quantity;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved' || $this->status === 'paid';
    }

    /**
     * İlerleme güncelleme
     */
    public function updateProgress(float $completedQuantity): void
    {
        $this->setAttribute('completed_quantity', $completedQuantity);

        // Eğer %100 tamamlandıysa statüyü güncelle
        if ($this->isCompleted() && $this->status === 'in_progress') {
            $this->status = 'completed';
        }

        $this->save();

        // Blok/Kat/Daire statülerini kontrol et ve güncelle
        $this->checkAndUpdateStructureStatus();
    }

    /**
     * Yapı statüsü güncelleme (Blok/Kat/Daire)
     */
    private function checkAndUpdateStructureStatus(): void
    {
        // Eğer bir kat referansı varsa
        if ($this->project_floor_id) {
            $floor = $this->projectFloor;

            // Bu kattaki tüm iş kalemleri tamamlandı mı?
            $allFloorWorkCompleted = ProgressPayment::where('project_floor_id', $this->project_floor_id)
                ->where('completed_quantity', '<', \DB::raw('planned_quantity'))
                ->count() === 0;

            if ($allFloorWorkCompleted && $floor->status !== 'completed') {
                $floor->update(['status' => 'completed']);

                // Kat tamamlandıysa blok kontrolü yap
                $this->checkAndUpdateBlockStatus();
            }
        }

        // Eğer bir blok referansı varsa
        if ($this->project_structure_id && !$this->project_floor_id) {
            $this->checkAndUpdateBlockStatus();
        }
    }

    /**
     * Blok statüsü güncelleme
     */
    private function checkAndUpdateBlockStatus(): void
    {
        if (!$this->project_structure_id) {
            return;
        }

        $structure = $this->projectStructure;

        // Bu bloktaki tüm katlar tamamlandı mı?
        $allFloorsCompleted = ProjectFloor::where('project_structure_id', $this->project_structure_id)
            ->where('status', '!=', 'completed')
            ->count() === 0;

        if ($allFloorsCompleted && $structure->status !== 'completed') {
            $structure->update(['status' => 'completed']);

            // Blok tamamlandıysa proje kontrolü yap
            $this->checkAndUpdateProjectStatus();
        }
    }

    /**
     * Proje statüsü güncelleme
     */
    private function checkAndUpdateProjectStatus(): void
    {
        $project = $this->project;

        // Projedeki tüm bloklar tamamlandı mı?
        $allStructuresCompleted = ProjectStructure::where('project_id', $this->project_id)
            ->where('status', '!=', 'completed')
            ->count() === 0;

        if ($allStructuresCompleted && $project->status !== 'completed') {
            $project->update(['status' => 'completed']);
        }
    }
}