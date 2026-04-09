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
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please login to continue.'], 401);
            }

            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        if (! $user->hasRole($role)) {
            $actualRole = $user->getRoleNames()->first() ?? 'customer';
            $actualRoleLabel = ucfirst((string) $actualRole);
            $requiredRoleLabel = ucfirst($role);

            $message = match ($role) {
                'customer' => "{$actualRoleLabel} accounts cannot use this function. Please login as a customer.",
                'vendor' => "{$actualRoleLabel} accounts cannot access the vendor dashboard.",
                'admin' => "{$actualRoleLabel} accounts cannot access the admin dashboard.",
                default => "Your account does not have permission to access this area (requires {$requiredRoleLabel}).",
            };

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            // For GET requests, send them to the correct dashboard instead of a dead-end.
            if ($request->isMethod('get')) {
                if ($actualRole === 'admin') {
                    return redirect()->route('admin.dashboard')->with('error', $message);
                }

                if ($actualRole === 'vendor') {
                    return redirect()->route('vendor.dashboard')->with('error', $message);
                }

                return redirect()->route('dashboard')->with('error', $message);
            }

            // For non-GET requests, go back and show a flash message.
            return back()->with('error', $message);
        }

        return $next($request);
    }
}

