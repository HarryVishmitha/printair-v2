<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('contact', function (Request $request) {
            $key = 'contact:'.($request->ip() ?? 'ip').':'.substr((string) $request->input('email'), 0, 120);

            return Limit::perMinutes(10, 5)->by($key);
        });

        // Prevent sporadic Blade compilation failures when `storage/framework/*` folders are missing.
        $fs = $this->app->make(Filesystem::class);
        $fs->ensureDirectoryExists(storage_path('framework/views'));
        $fs->ensureDirectoryExists(storage_path('framework/cache/data'));
        $fs->ensureDirectoryExists(storage_path('framework/sessions'));

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

        /**
         * Ability: manage-products
         * 
         * Only super admins and admins may manage products.
         */

        Gate::define('manage-products', function (User $user) {
            return $user->isAdminOrSuperAdmin();
        });

        /**
         * Ability: manage-rolls
         * 
         * Only super admins and admins may manage rolls.
         */

        Gate::define('manage-rolls', function (User $user) {
            return $user->isAdminOrSuperAdmin();
        });

        Gate::define('manage-product-rolls', function (User $user) {
            return $user->isAdminOrSuperAdmin() || ($user->role?->is_staff ?? false);
        });
    }
}
