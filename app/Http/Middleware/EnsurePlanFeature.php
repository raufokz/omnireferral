<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route guard for plan-gated features:  ->middleware('plan.feature:portal_access').
 *
 * Delegates to the `plan-feature` Gate so staff/admin bypass and super-admin
 * break-glass stay consistent with the rest of the authorization layer.
 */
class EnsurePlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (Gate::denies('plan-feature', $feature)) {
            abort(403, 'Your current package does not include this feature. Upgrade your plan to unlock it.');
        }

        return $next($request);
    }
}
