<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDashboard extends Model
{
    protected $fillable = [
        'user_id',
        'layout_json',
        'widgets_json',
    ];

    protected $casts = [
        'layout_json' => 'array',
        'widgets_json' => 'array',
    ];

    /**
     * İlişkiler
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
