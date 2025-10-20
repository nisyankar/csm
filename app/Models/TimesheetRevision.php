<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'timesheet_id',
        'revised_by',
        'requested_by',
        'revision_number',
        'revision_type',
        'revision_reason',
        'reason_category',
        'changes_made',
        'old_values',
        'new_values',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejection_reason',
        'requested_at',
        'completed_at',
        'processing_time_minutes',
        'priority',
        'affects_payroll',
        'affects_overtime',
        'wage_impact',
        'notified_users',
        'email_sent',
        'email_sent_at',
        'revision_ip',
        'additional_notes',
        'attachments',
        'is_bulk_revision',
        'bulk_revision_id',
    ];

    protected $casts = [
        'changes_made' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'approved_at' => 'datetime',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'processing_time_minutes' => 'integer',
        'affects_payroll' => 'boolean',
        'affects_overtime' => 'boolean',
        'wage_impact' => 'decimal:2',
        'notified_users' => 'array',
        'email_sent' => 'boolean',
        'attachments' => 'array',
        'is_bulk_revision' => 'boolean',
        'revision_number' => 'integer',
    ];

    // İlişkiler

    /**
     * Revize edilen puantaj
     */
    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    /**
     * Revizyonu yapan personel
     */
    public function revisedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'revised_by');
    }

    /**
     * Revizyonu talep eden personel
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    /**
     * Revizyonu onaylayan personel
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Ana toplu revizyon (bulk revision ise)
     */
    public function bulkRevision(): BelongsTo
    {
        return $this->belongsTo(TimesheetRevision::class, 'bulk_revision_id');
    }

    /**
     * Bu revizyonun alt revizyonları (bulk revision ise)
     */
    public function subRevisions()
    {
        return $this->hasMany(TimesheetRevision::class, 'bulk_revision_id');
    }

    // Accessor ve Mutator'lar

    /**
     * Revizyon türü human-readable
     */
    public function getRevisionTypeDisplayAttribute(): string
    {
        return match($this->revision_type) {
            'correction' => 'Düzeltme',
            'addition' => 'Ekleme',
            'deletion' => 'Silme',
            'modification' => 'Değişiklik',
            default => ucfirst($this->revision_type),
        };
    }

    /**
     * Sebep kategorisi human-readable
     */
    public function getReasonCategoryDisplayAttribute(): string
    {
        return match($this->reason_category) {
            'data_error' => 'Veri Hatası',
            'time_correction' => 'Zaman Düzeltmesi',
            'attendance_change' => 'Devam Durumu Değişikliği',
            'management_request' => 'Yönetim Talebi',
            'employee_request' => 'Personel Talebi',
            'system_error' => 'Sistem Hatası',
            'other' => 'Diğer',
            default => ucfirst($this->reason_category),
        };
    }

    /**
     * Durum human-readable
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Beklemede',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            'in_progress' => 'İşleniyor',
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
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            'in_progress' => 'badge-info',
            default => 'badge-secondary',
        };
    }

    /**
     * Öncelik human-readable
     */
    public function getPriorityDisplayAttribute(): string
    {
        return match($this->priority) {
            'low' => 'Düşük',
            'medium' => 'Orta',
            'high' => 'Yüksek',
            'urgent' => 'Acil',
            default => ucfirst($this->priority),
        };
    }

    /**
     * Öncelik badge class'ı
     */
    public function getPriorityBadgeAttribute(): string
    {
        return match($this->priority) {
            'low' => 'badge-secondary',
            'medium' => 'badge-primary',
            'high' => 'badge-warning',
            'urgent' => 'badge-danger',
            default => 'badge-light',
        };
    }

    /**
     * İşlem süresi (saat)
     */
    public function getProcessingTimeHoursAttribute(): ?float
    {
        return $this->processing_time_minutes ? round($this->processing_time_minutes / 60, 2) : null;
    }

    /**
     * Beklemede olan süre (saat)
     */
    public function getPendingDurationAttribute(): int
    {
        if ($this->status !== 'pending') {
            return 0;
        }
        return $this->requested_at->diffInHours(now());
    }

    /**
     * Tamamlanmış mı?
     */
    public function getIsCompletedAttribute(): bool
    {
        return in_array($this->status, ['approved', 'rejected']);
    }

    /**
     * Kritik revizyon mu?
     */
    public function getIsCriticalAttribute(): bool
    {
        return $this->affects_payroll || 
               $this->priority === 'urgent' || 
               abs($this->wage_impact) > 500;
    }

    /**
     * Toplu revizyon mu?
     */
    public function getIsBulkRevisionAttribute(): bool
    {
        return $this->is_bulk_revision;
    }

    // Scope'lar

    /**
     * Beklemedeki revizyonlar
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Onaylanmış revizyonlar
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Reddedilmiş revizyonlar
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Belirli öncelik seviyesindeki revizyonlar
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Acil revizyonlar
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Bordroyu etkileyen revizyonlar
     */
    public function scopeAffectingPayroll($query)
    {
        return $query->where('affects_payroll', true);
    }

    /**
     * Belirli kategorideki revizyonlar
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('reason_category', $category);
    }

    /**
     * Bugünkü revizyonlar
     */
    public function scopeToday($query)
    {
        return $query->whereDate('requested_at', today());
    }

    /**
     * Bu haftaki revizyonlar
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('requested_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Toplu revizyonlar
     */
    public function scopeBulkRevisions($query)
    {
        return $query->where('is_bulk_revision', true);
    }

    // Helper metodlar

    /**
     * Revizyonu onayla
     */
    public function approve(Employee $approver, string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'completed_at' => now(),
            'processing_time_minutes' => $this->requested_at->diffInMinutes(now()),
        ]);

        // Puantajı güncelle
        $this->applyChangesToTimesheet();
    }

    /**
     * Revizyonu reddet
     */
    public function reject(Employee $approver, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
            'completed_at' => now(),
            'processing_time_minutes' => $this->requested_at->diffInMinutes(now()),
        ]);
    }

    /**
     * Revizyonu işleme al
     */
    public function startProcessing(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    /**
     * Değişiklikleri puantaja uygula
     */
    private function applyChangesToTimesheet(): void
    {
        $timesheet = $this->timesheet;
        
        // Eski puantajı revizyon olarak işaretle
        $timesheet->update([
            'is_revised' => true,
            'revision_count' => $timesheet->revision_count + 1,
            'approval_status' => 'revision', // Tekrar onaya gönderilecek
        ]);

        // Yeni değerleri uygula
        foreach ($this->new_values as $field => $value) {
            if (in_array($field, $timesheet->getFillable())) {
                $timesheet->{$field} = $value;
            }
        }

        $timesheet->save();
        
        // Puantajı tekrar hesapla
        $timesheet->calculateWorkingTime();
        $timesheet->calculateWage();
        $timesheet->save();
    }

    /**
     * Ücret etkisini hesapla
     */
    public function calculateWageImpact(): void
    {
        $oldWage = $this->old_values['calculated_wage'] ?? 0;
        $newWage = $this->new_values['calculated_wage'] ?? 0;
        
        $this->wage_impact = $newWage - $oldWage;
        $this->affects_payroll = abs($this->wage_impact) > 0;
        $this->affects_overtime = isset($this->changes_made['overtime_minutes']);
        
        $this->save();
    }

    /**
     * Bildirim gönder
     */
    public function sendNotifications(): void
    {
        if ($this->email_sent) {
            return;
        }

        $notifiedUsers = [];
        
        // İlgili personellere bildirim gönder
        // Employee, Manager, HR vs.
        
        $this->update([
            'email_sent' => true,
            'email_sent_at' => now(),
            'notified_users' => $notifiedUsers,
        ]);
    }

    /**
     * Değişikliklerin özetini al
     */
    public function getChangesSummary(): array
    {
        $summary = [];
        
        foreach ($this->changes_made as $field => $change) {
            $oldValue = $this->old_values[$field] ?? 'N/A';
            $newValue = $this->new_values[$field] ?? 'N/A';
            
            $summary[] = [
                'field' => $field,
                'field_display' => $this->getFieldDisplayName($field),
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'change_type' => $this->getChangeType($oldValue, $newValue),
            ];
        }
        
        return $summary;
    }

    /**
     * Alan adının görüntüleme adını al
     */
    private function getFieldDisplayName(string $field): string
    {
        return match($field) {
            'start_time' => 'Başlangıç Saati',
            'end_time' => 'Bitiş Saati',
            'total_minutes' => 'Toplam Dakika',
            'overtime_minutes' => 'Fazla Mesai Dakika',
            'attendance_type' => 'Devam Durumu',
            'calculated_wage' => 'Hesaplanan Ücret',
            default => ucfirst(str_replace('_', ' ', $field)),
        };
    }

    /**
     * Değişiklik türünü belirle
     */
    private function getChangeType($oldValue, $newValue): string
    {
        if ($oldValue === null || $oldValue === '') {
            return 'added';
        } elseif ($newValue === null || $newValue === '') {
            return 'removed';
        } else {
            return 'modified';
        }
    }

    /**
     * Toplu revizyon oluştur
     */
    public static function createBulkRevision(array $timesheetIds, Employee $revisedBy, array $changes, string $reason): self
    {
        $bulkRevision = self::create([
            'timesheet_id' => $timesheetIds[0], // Ana timesheet
            'revised_by' => $revisedBy->id,
            'revision_number' => 1,
            'revision_type' => 'modification',
            'revision_reason' => $reason,
            'reason_category' => 'management_request',
            'changes_made' => $changes,
            'old_values' => [],
            'new_values' => $changes,
            'requested_at' => now(),
            'is_bulk_revision' => true,
            'priority' => 'high',
        ]);

        // Alt revizyonları oluştur
        foreach ($timesheetIds as $timesheetId) {
            if ($timesheetId !== $timesheetIds[0]) {
                self::create([
                    'timesheet_id' => $timesheetId,
                    'revised_by' => $revisedBy->id,
                    'revision_number' => 1,
                    'revision_type' => 'modification',
                    'revision_reason' => $reason,
                    'reason_category' => 'management_request',
                    'changes_made' => $changes,
                    'old_values' => [],
                    'new_values' => $changes,
                    'requested_at' => now(),
                    'bulk_revision_id' => $bulkRevision->id,
                    'priority' => 'high',
                ]);
            }
        }

        return $bulkRevision;
    }
}