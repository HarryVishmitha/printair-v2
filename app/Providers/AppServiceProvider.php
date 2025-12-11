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
    }
}
