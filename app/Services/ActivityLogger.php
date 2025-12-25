<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Request as RequestFacade;

class ActivityLogger
{
    public static function log(
        Authenticatable|null $user,
        string $action,
        string|null $description = null,
        array $properties = []
    ): ActivityLog {
        if (app()->bound('request')) {
            $request = app('request');
            if ($request instanceof \Illuminate\Http\Request) {
                $request->attributes->set('_activity_logged', true);
            }
        }

        return ActivityLog::create([
            'user_id' => $user?->getAuthIdentifier(),
            'action' => $action,
            'description' => $description,
            'properties' => $properties ?: null,
            'ip_address' => RequestFacade::ip(),
            'user_agent' => RequestFacade::header('User-Agent'),
        ]);
    }
}
