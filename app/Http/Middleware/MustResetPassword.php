<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MustResetPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Allow the user to reach the password change page and logout even when forced.
        if ($request->routeIs(
            'password.change',
            'password.change.update',
            'logout',
        )) {
            return $next($request);
        }

        if ((bool) $user->must_reset_password) {
            return redirect()
                ->route('password.change')
                ->with('info', 'For security, please set a new password before continuing.');
        }

        return $next($request);
    }
}

