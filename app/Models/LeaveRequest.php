<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'approver_id',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'working_days',
        'is_half_day',
        'half_day_period',
        'reason',
        'description',
        'emergency_contact',
        'handover_notes',
        'status',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'approval_notes',
        'rejection_reason',
        'substitute_employee_id',
        'substitute_notes',
        'attached_documents',
        'requires_document',
        'document_status',
        'balance_impact',
        'is_paid',
        'salary_impact',
        'conflicting_leaves',
        'conflict_resolved',
        'auto_applied_to_timesheet',
        'applied_to_timesheet_at',
        'timesheet_entries',
        'employee_notified',
        'manager_notified',
        'hr_notified',
        'notifications_sent_at',
        'priority',
        'urgency_reason',
        'is_recurring',
        'parent_request_id',
        'approval_duration_hours',
        'return_date',
        'returned_early',
        'return_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'integer',
        'working_days' => 'integer',
        'is_half_day' => 'boolean',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'attached_documents' => 'array',
        'requires_document' => 'boolean',
        'balance_impact' => 'integer',
        'is_paid' => 'boolean',
        'salary_impact' => 'decimal:2',
        'conflicting_leaves' => 'array',
        'conflict_resolved' => 'boolean',
        'auto_applied_to_timesheet' => 'boolean',
        'applied_to_timesheet_at' => 'datetime',
        'timesheet_entries' => 'array',
        'employee_notified' => 'boolean',
        'manager_notified' => 'boolean',
        'hr_notified' => 'boolean',
        'notifications_sent_at' => 'datetime',
        'approval_duration_hours' => 'integer',
        'return_date' => 'date',
        'returned_early' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    // İlişkiler

    /**
     * İzin talebinde bulunan personel
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * İzni onaylayan yönetici
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    /**
     * Vekil personel
     */
    public function substituteEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'substitute_employee_id');
    }

    /**
     * Ana izin talebi (recurring ise)
     */
    public function parentRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class, 'parent_request_id');
    }

    /**
     * Alt izin talepleri (recurring ise)
     */
    public function childRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'parent_request_id');
    }

    /**
     * İzin için oluşturulan puantaj kayıtları
     */
    public function relatedTimesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'employee_id', 'employee_id')
                    ->whereBetween('work_date', [$this->start_date, $this->end_date])
                    ->whereIn('attendance_type', ['sick_leave', 'annual_leave', 'excuse_leave', 'unpaid_leave']);
    }

    // Accessor ve Mutator'lar

    /**
     * İzin türü human-readable
     */
    public function getLeaveTypeDisplayAttribute(): string
    {
        return match($this->leave_type) {
            'annual' => 'Yıllık İzin',
            'sick' => 'Hastalık İzni',
            'maternity' => 'Doğum İzni',
            'paternity' => 'Babalık İzni',
            'marriage' => 'Evlilik İzni',
            'funeral' => 'Cenaze İzni',
            'military' => 'Askerlik İzni',
            'unpaid' => 'Ücretsiz İzin',
            'emergency' => 'Acil Durum İzni',
            'study' => 'Eğitim İzni',
            'other' => 'Diğer',
            default => ucfirst($this->leave_type),
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
            'cancelled' => 'İptal Edildi',
            'withdrawn' => 'Geri Çekildi',
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
            'cancelled' => 'badge-secondary',
            'withdrawn' => 'badge-info',
            default => 'badge-light',
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
     * İzin onaylanmış mı?
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * İzin aktif mi? (şu anda devam ediyor mu?)
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->is_approved && 
               now()->between($this->start_date, $this->end_date);
    }

    /**
     * İzin geçmiş mi?
     */
    public function getIsPastAttribute(): bool
    {
        return now() > $this->end_date;
    }

    /**
     * İzin gelecek mi?
     */
    public function getIsFutureAttribute(): bool
    {
        return now() < $this->start_date;
    }

    /**
     * İzin süresi (gün cinsinden)
     */
    public function getDurationInDaysAttribute(): int
    {
        return $this->total_days;
    }

    /**
     * Çalışma günü sayısı (hafta sonu hariç)
     */
    public function getWorkingDaysCountAttribute(): int
    {
        return $this->working_days;
    }

    /**
     * İzin için belge gerekli mi?
     */
    public function getRequiresDocumentationAttribute(): bool
    {
        return $this->requires_document || 
               in_array($this->leave_type, ['sick', 'maternity', 'military']);
    }

    /**
     * Belgeler tamamlandı mı?
     */
    public function getDocumentsCompletedAttribute(): bool
    {
        return !$this->requires_documentation || 
               $this->document_status === 'verified';
    }

    /**
     * Çakışma var mı?
     */
    public function getHasConflictAttribute(): bool
    {
        return !empty($this->conflicting_leaves) && !$this->conflict_resolved;
    }

    /**
     * Puantaja yansıdı mı?
     */
    public function getIsAppliedToTimesheetAttribute(): bool
    {
        return $this->auto_applied_to_timesheet;
    }

    // Scope'lar

    /**
     * Beklemedeki talepler
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Onaylanmış talepler
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Aktif izinler (şu anda devam eden)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'approved')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    /**
     * Belirli türdeki izinler
     */
    public function scopeByType($query, $type)
    {
        return $query->where('leave_type', $type);
    }

    /**
     * Belirli personelin izinleri
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Belirli tarih aralığındaki izinler
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function($sq) use ($startDate, $endDate) {
                  $sq->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    }

    /**
     * Bu ayın izinleri
     */
    public function scopeThisMonth($query)
    {
        return $query->betweenDates(now()->startOfMonth(), now()->endOfMonth());
    }

    /**
     * Acil izinler
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Belge gerektiren izinler
     */
    public function scopeRequiringDocuments($query)
    {
        return $query->where('requires_document', true);
    }

    // Helper metodlar

    /**
     * İzin talebini onayla
     */
    public function approve(Employee $approver, string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approver_id' => $approver->id,
            'approved_at' => now(),
            'reviewed_at' => now(),
            'approval_notes' => $notes,
            'approval_duration_hours' => $this->submitted_at->diffInHours(now()),
        ]);

        // İzin bakiyesini güncelle
        $this->updateEmployeeLeaveBalance();
        
        // Puantaja otomatik yansıt
        $this->applyToTimesheet();
        
        // Bildirimleri gönder
        $this->sendNotifications();
    }

    /**
     * İzin talebini reddet
     */
    public function reject(Employee $approver, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approver_id' => $approver->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
            'approval_duration_hours' => $this->submitted_at->diffInHours(now()),
        ]);

        $this->sendNotifications();
    }

    /**
     * İzin talebini iptal et
     */
    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'rejection_reason' => $reason,
        ]);

        // Eğer onaylanmış ise bakiyeyi geri ver
        if ($this->is_approved) {
            $this->restoreEmployeeLeaveBalance();
            $this->removeFromTimesheet();
        }
    }

    /**
     * Çalışma günü sayısını hesapla
     */
    public function calculateWorkingDays(): void
    {
        $workingDays = 0;
        $current = $this->start_date->copy();
        
        while ($current->lte($this->end_date)) {
            // Hafta sonu kontrolü (Cumartesi=6, Pazar=0)
            if (!in_array($current->dayOfWeek, [0, 6])) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        // Yarım gün ise yarım say
        if ($this->is_half_day) {
            $workingDays = 0.5;
        }
        
        $this->working_days = $workingDays;
        $this->total_days = $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Çakışan izinleri kontrol et
     */
    public function checkConflicts(): array
    {
        $conflicts = LeaveRequest::where('employee_id', $this->employee_id)
            ->where('id', '!=', $this->id)
            ->where('status', 'approved')
            ->betweenDates($this->start_date, $this->end_date)
            ->get();

        $conflictData = [];
        foreach ($conflicts as $conflict) {
            $conflictData[] = [
                'id' => $conflict->id,
                'type' => $conflict->leave_type,
                'start_date' => $conflict->start_date,
                'end_date' => $conflict->end_date,
                'overlap_days' => $this->calculateOverlapDays($conflict),
            ];
        }

        $this->conflicting_leaves = $conflictData;
        $this->conflict_resolved = empty($conflictData);
        
        return $conflictData;
    }

    /**
     * Çakışma günlerini hesapla
     */
    private function calculateOverlapDays(LeaveRequest $other): int
    {
        $start = max($this->start_date, $other->start_date);
        $end = min($this->end_date, $other->end_date);
        
        return $start->lte($end) ? $start->diffInDays($end) + 1 : 0;
    }

    /**
     * Personelin izin bakiyesini güncelle
     */
    private function updateEmployeeLeaveBalance(): void
    {
        if ($this->leave_type === 'annual' && $this->is_paid) {
            $this->employee->increment('used_leave_days', $this->working_days);
            $this->balance_impact = $this->working_days;
            $this->save();
        }
    }

    /**
     * İzin bakiyesini geri ver
     */
    private function restoreEmployeeLeaveBalance(): void
    {
        if ($this->balance_impact > 0) {
            $this->employee->decrement('used_leave_days', $this->balance_impact);
        }
    }

    /**
     * Puantaja otomatik yansıt
     */
    public function applyToTimesheet(): void
    {
        if ($this->auto_applied_to_timesheet) {
            return;
        }

        $timesheetEntries = [];
        $current = $this->start_date->copy();
        
        while ($current->lte($this->end_date)) {
            // Sadece çalışma günlerinde puantaj oluştur
            if (!in_array($current->dayOfWeek, [0, 6])) {
                $timesheet = Timesheet::updateOrCreate([
                    'employee_id' => $this->employee_id,
                    'work_date' => $current,
                ], [
                    'project_id' => $this->employee->current_project_id,
                    'attendance_type' => $this->getTimesheetAttendanceType(),
                    'entry_method' => 'system',
                    'approval_status' => 'approved',
                    'is_paid' => $this->is_paid,
                    'notes' => "İzin: {$this->leave_type_display}",
                ]);
                
                $timesheetEntries[] = $timesheet->id;
            }
            $current->addDay();
        }
        
        $this->update([
            'auto_applied_to_timesheet' => true,
            'applied_to_timesheet_at' => now(),
            'timesheet_entries' => $timesheetEntries,
        ]);
    }

    /**
     * Puantajdan kaldır
     */
    private function removeFromTimesheet(): void
    {
        if (!empty($this->timesheet_entries)) {
            Timesheet::whereIn('id', $this->timesheet_entries)->delete();
        }
    }

    /**
     * İzin türüne göre puantaj devam türünü belirle
     */
    private function getTimesheetAttendanceType(): string
    {
        return match($this->leave_type) {
            'annual' => 'annual_leave',
            'sick' => 'sick_leave',
            'unpaid' => 'unpaid_leave',
            default => 'excuse_leave',
        };
    }

    /**
     * Bildirimleri gönder
     */
    public function sendNotifications(): void
    {
        // Personele bildirim
        if (!$this->employee_notified) {
            // Mail/SMS gönder
            $this->employee_notified = true;
        }

        // Yöneticiye bildirim
        if (!$this->manager_notified && $this->approver) {
            // Mail/SMS gönder
            $this->manager_notified = true;
        }

        // İK'ya bildirim
        if (!$this->hr_notified) {
            // Mail/SMS gönder
            $this->hr_notified = true;
        }

        $this->notifications_sent_at = now();
        $this->save();
    }

    /**
     * İzin için uygun vekil bul
     */
    public function suggestSubstitutes(): \Illuminate\Database\Eloquent\Collection
    {
        return Employee::where('current_project_id', $this->employee->current_project_id)
                      ->where('category', $this->employee->category)
                      ->where('status', 'active')
                      ->where('id', '!=', $this->employee_id)
                      ->whereDoesntHave('leaveRequests', function($query) {
                          $query->where('status', 'approved')
                                ->betweenDates($this->start_date, $this->end_date);
                      })
                      ->get();
    }

    /**
     * İzin talebini onaya gönder
     */
    public function submit(): void
    {
        // Çakışmaları kontrol et
        $this->checkConflicts();
        
        // Çalışma günlerini hesapla
        $this->calculateWorkingDays();
        
        // Yöneticiyi belirle
        if (!$this->approver_id) {
            $this->approver_id = $this->employee->manager?->id;
        }
        
        $this->update([
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
        
        $this->sendNotifications();
    }
}