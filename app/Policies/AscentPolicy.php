<?php

namespace App\Policies;

use App\Models\Ascent;
use App\Models\User;

class AscentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ascent $ascent): bool
    {
        // Users can view their own ascents, admins can view all
        return $user->id === $ascent->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create ascents
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ascent $ascent): bool
    {
        // Users can only update their own ascents
        return $user->id === $ascent->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ascent $ascent): bool
    {
        // Users can only delete their own ascents, admins can delete any
        return $user->id === $ascent->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ascent $ascent): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ascent $ascent): bool
    {
        return $user->isAdmin();
    }
}
