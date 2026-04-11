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

        // Allow the user to reach the security page (and logout) even when forced.
        if ($request->routeIs('account.security', 'account.password.update', 'logout')) {
            return $next($request);
        }

        if ((bool) $user->must_reset_password) {
            return redirect()
                ->route('account.security')
                ->with('info', 'For security, please set a new password before continuing.');
        }

        return $next($request);
    }
}

