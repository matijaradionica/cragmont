<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'route_id',
        'parent_id',
        'content',
        'photo_path',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
        'is_reported' => 'boolean',
    ];

    /**
     * Get the user who created this comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the route this comment belongs to.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the parent comment (for replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get all replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->with('user', 'replies')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get all votes for this comment.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(CommentVote::class);
    }

    /**
     * Check if this is a top-level comment.
     */
    public function isTopLevel(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if user can edit (within 30 minutes).
     */
    public function canBeEditedBy(User $user): bool
    {
        if ($user->id !== $this->user_id) {
            return false;
        }

        // Can edit within 30 minutes of creation
        return $this->created_at->diffInMinutes(now()) < 30;
    }

    /**
     * Check if user has upvoted this comment.
     */
    public function isUpvotedBy(User $user): bool
    {
        return $this->votes()
            ->where('user_id', $user->id)
            ->where('vote_type', 'upvote')
            ->exists();
    }

    /**
     * Check if user has downvoted this comment.
     */
    public function isDownvotedBy(User $user): bool
    {
        return $this->votes()
            ->where('user_id', $user->id)
            ->where('vote_type', 'downvote')
            ->exists();
    }

    /**
     * Check if user marked this as helpful.
     */
    public function isMarkedHelpfulBy(User $user): bool
    {
        return $this->votes()
            ->where('user_id', $user->id)
            ->where('vote_type', 'helpful')
            ->exists();
    }

    /**
     * Get net vote score (upvotes - downvotes).
     */
    public function getNetScore(): int
    {
        return $this->upvote_count - $this->downvote_count;
    }

    /**
     * Scope to get only top-level comments.
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get comments sorted by most helpful.
     */
    public function scopeMostHelpful($query)
    {
        return $query->orderBy('helpful_count', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope to filter comments by users who climbed the route.
     */
    public function scopeFromClimbers($query, $routeId)
    {
        return $query->whereHas('user.ascents', function ($q) use ($routeId) {
            $q->where('route_id', $routeId);
        });
    }
}
