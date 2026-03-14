<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson() || in_array('api', $request->route()?->gatherMiddleware() ?? [], true)) {
            return null;
        }

        return '/app/login';
    }
}
