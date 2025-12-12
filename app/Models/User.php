<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'avatar_path',
        'bio',
        'climbing_club_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role that the user belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function climbingClub(): BelongsTo
    {
        return $this->belongsTo(ClimbingClub::class);
    }

    /**
     * Get the routes created by the user.
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class, 'created_by_user_id');
    }

    /**
     * Get the photos uploaded by the user.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Get the ascents logged by the user.
     */
    public function ascents(): HasMany
    {
        return $this->hasMany(Ascent::class);
    }

    /**
     * Get the ratings created by the user.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get the comments created by the user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the warnings received by the user.
     */
    public function warnings(): HasMany
    {
        return $this->hasMany(UserWarning::class);
    }

    /**
     * Get the count of unread warnings.
     */
    public function getUnreadWarningsCount(): int
    {
        return $this->warnings()->unread()->count();
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'Admin';
    }

    /**
     * Check if the user is a moderator.
     */
    public function isModerator(): bool
    {
        return $this->role && $this->role->name === 'Moderator';
    }

    /**
     * Check if the user is a club equipper.
     */
    public function isClubEquipper(): bool
    {
        return $this->role && $this->role->name === 'Club/Equipper';
    }

    /**
     * Check if the user can auto-approve routes.
     */
    public function canAutoApproveRoutes(): bool
    {
        return $this->isAdmin() || $this->isClubEquipper();
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (User $user) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
        });
    }
}
