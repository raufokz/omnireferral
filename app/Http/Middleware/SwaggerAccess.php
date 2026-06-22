<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SwaggerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment(['local', 'testing', 'development'])) {
            $user = $request->user();

            abort_unless(
                $user && ($user->isAdmin() || $user->isStaff()),
                403,
                'Swagger UI is restricted to admin users in production.'
            );
        }

        return $next($request);
    }
}
