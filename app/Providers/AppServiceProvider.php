<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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
        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\UserLoginSubscriber::class);

        /**
         * Ability: manage-working-groups
         *
         * Only Super Admins and Admins may manage working groups.
         */
        Gate::define('manage-working-groups', function (User $user) {
            return $user->isAdminOrSuperAdmin();
        });

        /**
         * Ability: manage-users
         *
         * Only Super Admins and Admins may manage system users.
         */
        Gate::define('manage-users', function (User $user) {
            return $user->isAdminOrSuperAdmin();
        });

        /**
         * Ability: manage-customers
         *
         * Super Admins, Admins, and staff members may manage customers.
         */
        Gate::define('manage-customers', function (User $user) {
            return $user->isAdminOrSuperAdmin() || ($user->role?->is_staff ?? false);
        });

        /**
         * Ability: manage-categories
         *
         * Only Super Admins and Admins may manage categories.
         */
        Gate::define('manage-categories', function (User $user) {
            return $user->isAdminOrSuperAdmin();
        });
    }
}
