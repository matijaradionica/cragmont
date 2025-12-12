<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConditionReport extends Model
{
    protected $fillable = [
        'route_id',
        'user_id',
        'type',
        'category',
        'categories',
        'content',
        'expires_at',
        'archived_at',
        'is_approved',
        'moderator_id',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'categories' => 'array',
        'expires_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
}
