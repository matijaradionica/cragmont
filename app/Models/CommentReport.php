<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentReport extends Model
{
    protected $fillable = [
        'comment_id',
        'reported_by_user_id',
        'reason',
        'description',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the comment that was reported.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Get the user who reported the comment.
     */
    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    /**
     * Get the admin who reviewed the report.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
