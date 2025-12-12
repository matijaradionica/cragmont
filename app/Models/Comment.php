<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Delete photo when comment is force deleted
        static::forceDeleting(function ($comment) {
            if ($comment->photo_path) {
                Storage::disk('public')->delete($comment->photo_path);
            }
        });
    }

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
     * Get all mentions in this comment.
     */
    public function mentions(): HasMany
    {
        return $this->hasMany(CommentMention::class);
    }

    /**
     * Get all mentioned users.
     */
    public function mentionedUsers()
    {
        return $this->belongsToMany(User::class, 'comment_mentions', 'comment_id', 'mentioned_user_id')
            ->withTimestamps();
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
     * Parse @mentions from content and return array of mentioned user IDs.
     */
    public function parseMentions(): array
    {
        // Match @username patterns (alphanumeric, underscores, hyphens)
        preg_match_all('/@([a-zA-Z0-9_-]+)/', $this->content, $matches);

        if (empty($matches[1])) {
            return [];
        }

        // Find users by name
        $usernames = array_unique($matches[1]);
        $users = User::whereIn('name', $usernames)->pluck('id')->toArray();

        return $users;
    }

    /**
     * Save mentions for this comment.
     */
    public function saveMentions(): void
    {
        $mentionedUserIds = $this->parseMentions();

        // Delete old mentions
        $this->mentions()->delete();

        // Create new mentions
        foreach ($mentionedUserIds as $userId) {
            CommentMention::create([
                'comment_id' => $this->id,
                'mentioned_user_id' => $userId,
            ]);
        }
    }

    /**
     * Get formatted content with highlighted mentions.
     */
    public function getFormattedContent(): string
    {
        $content = e($this->content); // Escape HTML first

        // Get all mentioned users
        $mentionedUsers = User::whereIn('id', $this->parseMentions())->get();

        // Replace @mentions with highlighted spans
        foreach ($mentionedUsers as $user) {
            $pattern = '/@' . preg_quote($user->name, '/') . '\b/';
            $replacement = '<span class="font-semibold text-indigo-600">@' . e($user->name) . '</span>';
            $content = preg_replace($pattern, $replacement, $content);
        }

        return nl2br($content);
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
