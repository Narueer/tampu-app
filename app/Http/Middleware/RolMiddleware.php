<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RolMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (! in_array($request->user()->rol, $roles)) {
            abort(403, 'No tienes permiso para entrar a esta página.');
        }

        return $next($request);
    }
}
