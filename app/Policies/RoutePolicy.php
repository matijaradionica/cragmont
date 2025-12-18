<?php

namespace App\Policies;

use App\Models\Route;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RoutePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view the routes list
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Route $route): bool
    {
        // Anyone can view any route
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create routes
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Route $route): bool
    {
        // Admin and moderator can always update
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        // Owner can update their own route
        return $route->created_by_user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Route $route): bool
    {
        // Only admin can delete routes
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Route $route): bool
    {
        // Only admin can restore routes
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Route $route): bool
    {
        // Only admin can permanently delete routes
        return $user->isAdmin();
    }
}
