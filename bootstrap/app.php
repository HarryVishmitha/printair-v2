<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\ForcePasswordChange::class,
            \App\Http\Middleware\LogControllerActivity::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\LogControllerActivity::class,
        ]);

        $middleware->alias([
            // Core Laravel aliases (needed by routes/web.php)
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,

            'staff' => \App\Http\Middleware\EnsureStaffUser::class,
            'portal' => \App\Http\Middleware\EnsurePortalUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
