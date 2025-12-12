<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define Gate for admin access
        Gate::define('viewAdmin', function (User $user) {
            return $user->isAdmin();
        });

        // Define Gate for moderation access
        Gate::define('moderate', function (User $user) {
            return $user->isAdmin() || $user->isModerator();
        });
    }
}
