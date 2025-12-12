<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWarning extends Model
{
    protected $fillable = [
        'user_id',
        'warned_by_user_id',
        'comment_report_id',
        'reason',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user who received the warning.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who issued the warning.
     */
    public function warnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warned_by_user_id');
    }

    /**
     * Get the comment report that triggered this warning.
     */
    public function commentReport(): BelongsTo
    {
        return $this->belongsTo(CommentReport::class);
    }

    /**
     * Mark this warning as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Scope to get only unread warnings.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get warnings for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
