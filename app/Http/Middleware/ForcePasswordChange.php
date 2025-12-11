<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->force_password_change) {
            // Allow logout
            if ($request->routeIs('logout')) {
                return $next($request);
            }
            // If user is on the password change page, allow it
            if ($request->routeIs('password.force-change')) {
                return $next($request);
            }
            // Otherwise, redirect to the password change page
            return redirect()->route('password.force-change');
        }

        return $next($request);
    }
}
