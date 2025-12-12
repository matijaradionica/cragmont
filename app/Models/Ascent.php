<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ascent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'route_id',
        'ascent_date',
        'partners',
        'status',
        'notes',
    ];

    protected $casts = [
        'ascent_date' => 'date',
    ];

    /**
     * Get the user who logged this ascent.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the route for this ascent.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Scope to get ascents for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get ascents for a specific route.
     */
    public function scopeForRoute($query, $routeId)
    {
        return $query->where('route_id', $routeId);
    }

    /**
     * Scope to get successful ascents only.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'Success');
    }

    /**
     * Check if the ascent was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'Success';
    }

    /**
     * Get a formatted display of the status.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'Success' => 'bg-green-100 text-green-800',
            'Failed' => 'bg-red-100 text-red-800',
            'Attempt' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
