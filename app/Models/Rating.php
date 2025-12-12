<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'route_id',
        'is_positive',
    ];

    protected $casts = [
        'is_positive' => 'boolean',
    ];

    /**
     * Get the user who created this rating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the route being rated.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Check if this is a positive rating (thumbs up).
     */
    public function isPositive(): bool
    {
        return $this->is_positive;
    }

    /**
     * Check if this is a negative rating (thumbs down).
     */
    public function isNegative(): bool
    {
        return !$this->is_positive;
    }
}
