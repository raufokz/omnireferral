<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureListingDeviceCookie
{
    public const COOKIE_NAME = 'or_listing_device';

    public function handle(Request $request, Closure $next): Response
    {
        $existing = $request->cookie(self::COOKIE_NAME);
        if (is_string($existing) && preg_match('/^[a-f0-9]{64}$/', $existing)) {
            $request->attributes->set('listing_device_id', $existing);

            return $next($request);
        }

        $id = bin2hex(random_bytes(32));
        $request->attributes->set('listing_device_id', $id);

        $response = $next($request);

        return $response->withCookie(cookie(
            self::COOKIE_NAME,
            $id,
            minutes: 60 * 24 * 365 * 5,
            path: '/',
            domain: null,
            secure: config('session.secure', false),
            httpOnly: true,
            raw: false,
            sameSite: 'lax',
        ));
    }
}
