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
        // Anyone can view the routes list (pending routes are still restricted elsewhere)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Route $route): bool
    {
        // Anyone can view approved routes
        if ($route->is_approved) {
            return true;
        }

        // For pending routes, only creator, admin, or moderator can view
        if ($user) {
            return $user->isAdmin()
                || $user->isModerator()
                || $route->created_by_user_id === $user->id;
        }

        return false;
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

    /**
     * Determine whether the user can approve the route.
     */
    public function approve(User $user, Route $route): bool
    {
        // Only admin and moderator can approve routes
        return $user->isAdmin() || $user->isModerator();
    }
}
