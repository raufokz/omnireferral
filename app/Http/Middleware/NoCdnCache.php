<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prevent CDN / edge-proxy caching for web responses that carry
 * session state or CSRF tokens.  Without this, Hostinger hcdn
 * (or any upstream cache) may serve a stale HTML page whose
 * embedded CSRF token no longer matches the visitor's session,
 * producing a 419 "Page Expired" on form submission.
 */
class NoCdnCache
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('CDN-Cache-Control', 'no-store');
        $response->headers->set('Surrogate-Control', 'no-store');

        return $response;
    }
}
