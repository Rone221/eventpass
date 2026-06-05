<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint l'accès d'une route à un ou plusieurs rôles.
 *
 * Usage : Route::...->middleware('role:organizer')
 *         Route::...->middleware('role:organizer,participant')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role->value, $roles, true)) {
            abort(403, "Accès réservé aux rôles : ".implode(', ', $roles));
        }

        return $next($request);
    }
}
