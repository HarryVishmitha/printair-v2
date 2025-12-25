<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogControllerActivity
{
    /**
     * Handle an incoming request.
     *
     * Logs a default "controller action executed" activity log entry after the
     * controller runs, unless the request has already logged activity explicitly.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (Throwable $throwable) {
            $this->logIfControllerAction($request, null, $throwable);
            throw $throwable;
        }

        $this->logIfControllerAction($request, $response);

        return $response;
    }

    private function logIfControllerAction(Request $request, Response|null $response, Throwable|null $throwable = null): void
    {
        if ($request->attributes->get('_activity_logged') === true) {
            return;
        }

        $route = $request->route();
        if (! $route) {
            return;
        }

        $actionName = $route->getActionName();
        if (! is_string($actionName) || $actionName === 'Closure' || str_contains($actionName, 'Closure')) {
            return;
        }

        $controller = $actionName;
        $method = '__invoke';
        if (str_contains($actionName, '@')) {
            [$controller, $method] = explode('@', $actionName, 2);
        }

        $routeName = $route->getName();
        $action = $routeName ?: $actionName;

        $description = sprintf('%s %s', $request->method(), '/'.$request->path());

        $properties = [
            'route' => $routeName,
            'controller' => $controller,
            'controller_method' => $method,
            'http_method' => $request->method(),
            'path' => '/'.$request->path(),
            'url' => $request->fullUrl(),
            'route_parameters' => $route->parameters(),
        ];

        if ($response) {
            $properties['response_status'] = $response->getStatusCode();
        }

        if ($throwable) {
            $properties['exception_class'] = $throwable::class;
            $properties['exception_message'] = $throwable->getMessage();
        }

        ActivityLogger::log(
            $request->user(),
            $action,
            $description,
            $properties
        );
    }
}

