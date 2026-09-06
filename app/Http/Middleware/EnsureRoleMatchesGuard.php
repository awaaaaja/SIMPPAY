<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user's role matches the expected guard for the
 * current route group. This is a defense-in-depth layer — the route group
 * already uses `role:admin` (Spatie middleware), but this middleware provides
 * an explicit check that can be tested independently.
 */
class EnsureRoleMatchesGuard
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->hasAnyRole($roles)) {
            abort(403, 'Unauthorized. Your role does not have access to this area.');
        }

        return $next($request);
    }
}
