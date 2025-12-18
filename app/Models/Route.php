<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Route extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'location_id',
        'created_by_user_id',
        'length_m',
        'pitch_count',
        'grade_type',
        'grade_value',
        'risk_rating',
        'approach_description',
        'descent_description',
        'required_gear',
        'route_type',
        'topo_url',
        'topo_data',
        'status',
    ];

    protected $casts = [
        'length_m' => 'integer',
        'pitch_count' => 'integer',
        'topo_data' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($route) {
            // Delete topo file when route is deleted
            if ($route->topo_url) {
                Storage::disk('public')->delete($route->topo_url);
            }

            // Delete route photos (also removes files)
            $route->photos()->get()->each->delete();
        });
    }

    /**
     * Get the location of the route.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the user who created the route.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get all photos for the route.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function conditionReports(): HasMany
    {
        return $this->hasMany(ConditionReport::class);
    }

    /**
     * Get all ascents for the route.
     */
    public function ascents(): HasMany
    {
        return $this->hasMany(Ascent::class);
    }

    /**
     * Get all ratings for the route.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get all comments for the route.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the percentage of positive ratings.
     */
    public function getPositiveRatingPercentage(): int
    {
        $total = $this->ratings()->count();
        if ($total === 0) {
            return 0;
        }

        $positive = $this->ratings()->where('is_positive', true)->count();
        return (int) round(($positive / $total) * 100);
    }

    /**
     * Get user's rating for this route.
     */
    public function getUserRating(User $user): ?Rating
    {
        return $this->ratings()->where('user_id', $user->id)->first();
    }

    /**
     * Scope to filter by grade range.
     */
    public function scopeByGrade($query, $minGrade = null, $maxGrade = null)
    {
        if ($minGrade) {
            $query->where('grade_value', '>=', $minGrade);
        }

        if ($maxGrade) {
            $query->where('grade_value', '<=', $maxGrade);
        }

        return $query;
    }

    /**
     * Scope to filter by route type.
     */
    public function scopeByType($query, $type)
    {
        if ($type) {
            return $query->where('route_type', $type);
        }

        return $query;
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Scope to search routes by name.
     */
    public function scopeSearch($query, $searchTerm)
    {
        if ($searchTerm) {
            return $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        return $query;
    }

    /**
     * Get formatted grade display.
     */
    public function getGradeDisplay(): string
    {
        return "{$this->grade_type}: {$this->grade_value}";
    }

    /**
     * Get the full location path.
     */
    public function getLocationPath(): string
    {
        return $this->location ? $this->location->getFullPath() : 'Unknown';
    }

    /**
     * Check if the route can be edited by a user.
     */
    public function canBeEditedBy(User $user): bool
    {
        return $user->isAdmin()
            || $user->isModerator()
            || $this->created_by_user_id === $user->id;
    }
}
