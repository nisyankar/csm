<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class ProjectSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'task_code',
        'task_name',
        'task_description',
        'task_type',
        'start_date',
        'end_date',
        'duration',
        'progress',
        'status',
        'priority',
        'assigned_to',
        'department_id',
        'parent_task_id',
        'predecessors',
        'estimated_cost',
        'actual_cost',
        'actual_start_date',
        'actual_end_date',
        'completion_percentage',
        'reference_type',
        'reference_id',
        'notes',
        'color',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'duration' => 'integer',
        'progress' => 'integer',
        'completion_percentage' => 'integer',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'predecessors' => 'array',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(ProjectSchedule::class, 'parent_task_id');
    }

    public function subTasks(): HasMany
    {
        return $this->hasMany(ProjectSchedule::class, 'parent_task_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function progressPayments(): HasMany
    {
        return $this->hasMany(ProgressPayment::class);
    }

    // Accessors
    public function getTaskTypeLabelAttribute(): string
    {
        if (!$this->task_type) {
            return '-';
        }

        return match($this->task_type) {
            'phase' => 'Faz',
            'milestone' => 'Kilometre Taşı',
            'activity' => 'Aktivite',
            'deliverable' => 'Teslim Edilebilir',
            'meeting' => 'Toplantı',
            default => $this->task_type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->status) {
            return '-';
        }

        return match($this->status) {
            'not_started' => 'Başlamadı',
            'in_progress' => 'Devam Ediyor',
            'completed' => 'Tamamlandı',
            'delayed' => 'Gecikti',
            'on_hold' => 'Beklemede',
            'cancelled' => 'İptal',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        if (!$this->status) {
            return 'gray';
        }

        return match($this->status) {
            'not_started' => 'gray',
            'in_progress' => 'blue',
            'completed' => 'green',
            'delayed' => 'red',
            'on_hold' => 'yellow',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        if (!$this->priority) {
            return '-';
        }

        return match($this->priority) {
            'low' => 'Düşük',
            'medium' => 'Orta',
            'high' => 'Yüksek',
            'critical' => 'Kritik',
            default => $this->priority,
        };
    }

    public function getPriorityColorAttribute(): string
    {
        if (!$this->priority) {
            return 'gray';
        }

        return match($this->priority) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray',
        };
    }

    public function getIsDelayedAttribute(): bool
    {
        if ($this->status === 'completed' || $this->status === 'cancelled') {
            return false;
        }

        return $this->end_date < now()->startOfDay();
    }

    public function getDelayDaysAttribute(): int
    {
        if (!$this->is_delayed) {
            return 0;
        }

        return now()->startOfDay()->diffInDays($this->end_date);
    }

    public function getPlannedDurationAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getActualDurationAttribute(): ?int
    {
        if (!$this->actual_start_date) {
            return null;
        }

        $endDate = $this->actual_end_date ?? now();
        return $this->actual_start_date->diffInDays($endDate) + 1;
    }

    public function getCostVarianceAttribute(): float
    {
        if (!$this->estimated_cost) {
            return 0;
        }

        return ($this->actual_cost ?? 0) - $this->estimated_cost;
    }

    public function getCostPerformanceIndexAttribute(): float
    {
        if (!$this->actual_cost || $this->actual_cost == 0) {
            return 1.0;
        }

        return $this->estimated_cost / $this->actual_cost;
    }

    // Scopes
    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('task_type', $type);
    }

    public function scopeDelayed($query)
    {
        return $query->where('end_date', '<', now()->startOfDay())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeMainTasks($query)
    {
        return $query->whereNull('parent_task_id');
    }

    public function scopeSubTasks($query)
    {
        return $query->whereNotNull('parent_task_id');
    }

    // Helper Methods
    public function calculateDuration(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function updateDuration(): bool
    {
        $duration = $this->calculateDuration();
        return $this->update(['duration' => $duration]);
    }

    public function markAsStarted(): bool
    {
        return $this->update([
            'status' => 'in_progress',
            'actual_start_date' => $this->actual_start_date ?? now(),
        ]);
    }

    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => 'completed',
            'completion_percentage' => 100,
            'progress' => 100,
            'actual_end_date' => $this->actual_end_date ?? now(),
        ]);
    }

    public function updateProgress(int $percentage): bool
    {
        $percentage = max(0, min(100, $percentage));

        $updates = [
            'completion_percentage' => $percentage,
            'progress' => $percentage,
        ];

        if ($percentage > 0 && !$this->actual_start_date) {
            $updates['actual_start_date'] = now();
            $updates['status'] = 'in_progress';
        }

        if ($percentage === 100) {
            $updates['status'] = 'completed';
            if (!$this->actual_end_date) {
                $updates['actual_end_date'] = now();
            }
        }

        return $this->update($updates);
    }

    public function checkDelayStatus(): void
    {
        if ($this->is_delayed && $this->status !== 'completed' && $this->status !== 'cancelled') {
            $this->update(['status' => 'delayed']);
        }
    }

    public static function generateTaskCode(): string
    {
        $lastTask = static::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastTask ? ((int) substr($lastTask->task_code, -3)) + 1 : 1;

        return 'TASK-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function getPredecessorTasks()
    {
        if (!$this->predecessors || empty($this->predecessors)) {
            return collect();
        }

        $taskIds = collect($this->predecessors)->pluck('task_id')->toArray();

        return static::whereIn('id', $taskIds)->get();
    }

    public function getSuccessorTasks()
    {
        return static::where('project_id', $this->project_id)
            ->whereNotNull('predecessors')
            ->get()
            ->filter(function ($task) {
                if (!$task->predecessors) {
                    return false;
                }
                $predecessorIds = collect($task->predecessors)->pluck('task_id')->toArray();
                return in_array($this->id, $predecessorIds);
            });
    }

    /**
     * Hakediş ödemelerinden ilerlemeyi güncelle
     */
    public function syncProgressFromPayments(): void
    {
        $payments = $this->progressPayments()
            ->where('auto_update_schedule', true)
            ->get();

        if ($payments->isEmpty()) {
            return;
        }

        // Ortalama tamamlanma yüzdesini hesapla
        $avgCompletion = $payments->avg('completion_percentage');

        // Toplam gerçekleşen maliyeti hesapla
        $totalActualCost = $payments->sum(function ($payment) {
            return ($payment->unit_price ?? 0) * ($payment->completed_quantity ?? 0);
        });

        $this->update([
            'completion_percentage' => (int) $avgCompletion,
            'progress' => (int) $avgCompletion,
            'actual_cost' => $totalActualCost,
        ]);

        // Durumu güncelle
        if ($avgCompletion >= 100) {
            $this->markAsCompleted();
        } elseif ($avgCompletion > 0 && $this->status === 'not_started') {
            $this->markAsStarted();
        }
    }
}
