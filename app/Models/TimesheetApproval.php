<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'timesheet_id',
        'approver_id',
        'approval_level',
        'status',
        'approved_at',
        'approval_notes',
        'rejection_reason',
        'original_data',
        'approved_data',
        'assigned_at',
        'deadline',
        'is_automatic',
        'notification_sent',
        'notification_sent_at',
        'delegated_from',
        'delegation_reason',
        'approval_ip',
        'approval_location',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'assigned_at' => 'datetime',
        'deadline' => 'datetime',
        'notification_sent_at' => 'datetime',
        'is_automatic' => 'boolean',
        'notification_sent' => 'boolean',
        'original_data' => 'array',
        'approved_data' => 'array',
    ];

    // İlişkiler

    /**
     * Onaylanacak puantaj
     */
    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    /**
     * Onaylayıcı personel
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    /**
     * Yetki devredilen personel (varsa)
     */
    public function delegatedFrom(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'delegated_from');
    }

    // Accessor ve Mutator'lar

    /**
     * Onay seviyesi human-readable
     */
    public function getApprovalLevelDisplayAttribute(): string
    {
        return match($this->approval_level) {
            'first' => '1. Onay',
            'second' => '2. Onay',
            'final' => 'Son Onay',
            default => ucfirst($this->approval_level),
        };
    }

    /**
     * Onay durumu human-readable
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Beklemede',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            'cancelled' => 'İptal Edildi',
            default => ucfirst($this->status),
        };
    }

    /**
     * Onay durumu badge class'ı
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            'cancelled' => 'badge-secondary',
            default => 'badge-light',
        };
    }

    /**
     * Deadline geçmiş mi?
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->deadline && 
               now() > $this->deadline && 
               $this->status === 'pending';
    }

    /**
     * Onay süresi (saat)
     */
    public function getApprovalDurationAttribute(): ?int
    {
        if (!$this->approved_at || !$this->assigned_at) {
            return null;
        }
        return $this->assigned_at->diffInHours($this->approved_at);
    }

    /**
     * Beklemede olan süre (saat)
     */
    public function getPendingDurationAttribute(): int
    {
        if ($this->status !== 'pending') {
            return 0;
        }
        return $this->assigned_at->diffInHours(now());
    }

    /**
     * Delegasyon var mı?
     */
    public function getIsDelegatedAttribute(): bool
    {
        return !is_null($this->delegated_from);
    }

    /**
     * Otomatik onay mı?
     */
    public function getIsAutomaticApprovalAttribute(): bool
    {
        return $this->is_automatic;
    }

    // Scope'lar

    /**
     * Beklemedeki onaylar
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Onaylanmış onaylar
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Reddedilmiş onaylar
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Belirli onaylayıcının onayları
     */
    public function scopeByApprover($query, $approverId)
    {
        return $query->where('approver_id', $approverId);
    }

    /**
     * Belirli seviyedeki onaylar
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('approval_level', $level);
    }

    /**
     * Deadline geçmiş onaylar
     */
    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
                    ->where('status', 'pending');
    }

    /**
     * Bugünkü onaylar
     */
    public function scopeToday($query)
    {
        return $query->whereDate('assigned_at', today());
    }

    /**
     * Delegasyonlu onaylar
     */
    public function scopeDelegated($query)
    {
        return $query->whereNotNull('delegated_from');
    }

    // Helper metodlar

    /**
     * Onayı onayla
     */
    public function approve(string $notes = null, array $approvedData = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approval_notes' => $notes,
            'approved_data' => $approvedData ?? $this->timesheet->toArray(),
            'approval_ip' => request()->ip(),
        ]);

        // Timesheet'i güncelle
        $this->updateTimesheetStatus();
        
        // Bir sonraki onay seviyesini kontrol et
        $this->checkNextApprovalLevel();
    }

    /**
     * Onayı reddet
     */
    public function reject(string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_at' => now(),
            'rejection_reason' => $reason,
            'approval_ip' => request()->ip(),
        ]);

        // Timesheet'i reddet
        $this->timesheet->update([
            'approval_status' => 'rejected'
        ]);
    }

    /**
     * Onayı iptal et
     */
    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Timesheet durumunu güncelle
     */
    private function updateTimesheetStatus(): void
    {
        $timesheet = $this->timesheet;
        
        // Bu seviye onayı tamamlandı
        if ($this->approval_level === 'first') {
            $timesheet->update(['first_approved_at' => now()]);
        } elseif ($this->approval_level === 'second') {
            $timesheet->update(['final_approved_at' => now()]);
        }

        // Tüm gerekli onaylar tamamlandı mı kontrol et
        $requiredLevels = $this->getRequiredApprovalLevels();
        $completedLevels = $timesheet->approvals()
                                   ->where('status', 'approved')
                                   ->pluck('approval_level')
                                   ->toArray();

        if (empty(array_diff($requiredLevels, $completedLevels))) {
            $timesheet->update(['approval_status' => 'approved']);
        }
    }

    /**
     * Gerekli onay seviyelerini getir
     */
    private function getRequiredApprovalLevels(): array
    {
        // Basit kural: Her zaman 1. onay gerekli, 
        // yüksek ücretli işlerde 2. onay da gerekli
        $levels = ['first'];
        
        if ($this->timesheet->calculated_wage > 1000) { // Örnek kural
            $levels[] = 'second';
        }
        
        return $levels;
    }

    /**
     * Bir sonraki onay seviyesini kontrol et
     */
    private function checkNextApprovalLevel(): void
    {
        $requiredLevels = $this->getRequiredApprovalLevels();
        $completedLevels = $this->timesheet->approvals()
                               ->where('status', 'approved')
                               ->pluck('approval_level')
                               ->toArray();

        $nextLevel = null;
        foreach ($requiredLevels as $level) {
            if (!in_array($level, $completedLevels)) {
                $nextLevel = $level;
                break;
            }
        }

        if ($nextLevel) {
            $this->createNextApprovalRecord($nextLevel);
        }
    }

    /**
     * Bir sonraki onay kaydını oluştur
     */
    private function createNextApprovalRecord(string $level): void
    {
        $approver = $this->getApproverForLevel($level);
        
        if ($approver) {
            $this->timesheet->approvals()->create([
                'approver_id' => $approver->id,
                'approval_level' => $level,
                'status' => 'pending',
                'assigned_at' => now(),
                'deadline' => now()->addDays(2), // 2 gün deadline
            ]);
        }
    }

    /**
     * Seviyeye göre onaylayıcı bul
     */
    private function getApproverForLevel(string $level): ?Employee
    {
        return match($level) {
            'first' => $this->timesheet->employee->manager,
            'second' => $this->timesheet->employee->manager?->manager,
            default => null,
        };
    }

    /**
     * Bildirim gönder
     */
    public function sendNotification(): void
    {
        if ($this->notification_sent) {
            return;
        }

        // Burada notification sistemi çağrılacak
        // Mail, SMS veya in-app notification
        
        $this->update([
            'notification_sent' => true,
            'notification_sent_at' => now(),
        ]);
    }

    /**
     * Deadline'ı uzat
     */
    public function extendDeadline(int $days, string $reason = null): void
    {
        $this->update([
            'deadline' => $this->deadline?->addDays($days) ?? now()->addDays($days),
            'approval_notes' => ($this->approval_notes ?? '') . "\nDeadline uzatıldı: {$reason}",
        ]);
    }

    /**
     * Onayı başka birine devret
     */
    public function delegateTo(Employee $newApprover, string $reason): void
    {
        $this->update([
            'delegated_from' => $this->approver_id,
            'approver_id' => $newApprover->id,
            'delegation_reason' => $reason,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Otomatik onay kontrolü
     */
    public function checkAutoApproval(): bool
    {
        // Otomatik onay kuralları
        // Örnek: Düşük ücretli, normal mesai, gecikmesi olmayan kayıtlar
        $timesheet = $this->timesheet;
        
        if ($timesheet->calculated_wage <= 500 && 
            $timesheet->overtime_minutes == 0 && 
            $timesheet->attendance_type === 'present') {
            
            $this->update(['is_automatic' => true]);
            $this->approve('Otomatik onay - Standart kayıt');
            return true;
        }
        
        return false;
    }
}