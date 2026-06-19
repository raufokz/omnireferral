<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->status === 'active') {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = $user->status === 'pending'
            ? 'Your portal access is not active yet. Please purchase a plan and complete onboarding to activate your account.'
            : 'This account is not active. Contact OmniReferral support if you believe this is a mistake.';

        return redirect()
            ->route('login')
            ->withErrors(['email' => $message]);
    }
}
