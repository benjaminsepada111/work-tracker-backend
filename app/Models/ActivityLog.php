<?php
// app/Models/ActivityLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    // Fields that can be filled via create() or fill()
    protected $fillable = [
        'user_id',
        'logged_at',
        'status',
        'active_seconds',
    ];

    // Tell Laravel to treat logged_at as a Carbon date object
    protected $casts = [
        'logged_at' => 'datetime',
    ];

    // Each log belongs to one user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
