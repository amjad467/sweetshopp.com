<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        abort_unless($user && in_array($user->role, $roles, true) && $user->is_active, 403);

        return $next($request);
    }
}
