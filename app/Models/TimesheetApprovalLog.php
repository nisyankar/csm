<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetApprovalLog extends Model
{
    protected $fillable = [
        'timesheet_v2_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'notes',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Puantaj kaydı
     */
    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(TimesheetV2::class, 'timesheet_v2_id');
    }

    /**
     * İşlemi yapan kullanıcı
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log kaydı oluştur (static helper)
     */
    public static function logAction(
        TimesheetV2 $timesheet,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $notes = null
    ): self {
        return self::logActionWithUser(
            $timesheet,
            auth()->id() ?? 0,
            $action,
            $oldValues,
            $newValues,
            $notes
        );
    }

    /**
     * Log kaydı oluştur - belirli kullanıcı ile
     */
    public static function logActionWithUser(
        TimesheetV2 $timesheet,
        int $userId,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $notes = null
    ): self {
        return self::create([
            'timesheet_v2_id' => $timesheet->id,
            'user_id' => $userId,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'notes' => $notes,
            'ip_address' => request()->ip() ?? '127.0.0.1',
        ]);
    }
}
