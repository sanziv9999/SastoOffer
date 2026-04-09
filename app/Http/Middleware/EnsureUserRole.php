<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Usage: ->middleware('role:admin') or ->middleware('role:vendor') etc.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        if (! $user->hasRole($role)) {
            abort(403);
        }

        return $next($request);
    }
}

