<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Location extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'gps_lat',
        'gps_lng',
        'description',
        'level',
    ];

    protected $casts = [
        'gps_lat' => 'decimal:8',
        'gps_lng' => 'decimal:8',
        'level' => 'integer',
    ];

    /**
     * Get the parent location.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    /**
     * Get the child locations.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    /**
     * Get all routes at this location.
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    /**
     * Get all ancestors (parent, grandparent, etc.) from top to bottom.
     */
    public function getAncestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Get the full path (Mountain > Cliff > Sector).
     */
    public function getFullPath(): string
    {
        $ancestors = $this->getAncestors();
        $ancestors->push($this);

        return $ancestors->pluck('name')->implode(' > ');
    }

    /**
     * Check if this is a top-level location (no parent).
     */
    public function isTopLevel(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Scope to get only top-level locations.
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get locations by level.
     */
    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }
}
