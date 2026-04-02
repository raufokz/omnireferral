<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAffiliateCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('ref')) {
            \Illuminate\Support\Facades\Cookie::queue('omnireferral_affiliate', $request->query('ref'), 60 * 24 * 60); // 60 days
        }

        return $next($request);
    }
}
