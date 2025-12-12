<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    protected $fillable = [
        'route_id',
        'user_id',
        'path',
        'is_topo',
        'caption',
        'order',
    ];

    protected $casts = [
        'is_topo' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the route the photo belongs to.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the user who uploaded the photo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only topo diagrams.
     */
    public function scopeTopos($query)
    {
        return $query->where('is_topo', true);
    }

    /**
     * Scope to get only regular photos.
     */
    public function scopeRegular($query)
    {
        return $query->where('is_topo', false);
    }

    /**
     * Scope to order photos by their order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get the full URL of the photo.
     */
    public function getUrl(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * Delete the photo file from storage.
     */
    public function delete(): bool
    {
        if ($this->path) {
            Storage::disk('public')->delete($this->path);
        }

        return parent::delete();
    }
}
