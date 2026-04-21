<?php

namespace App\Http\Middleware;

use App\Models\AffiliateProfile;
use App\Models\AffiliateReferralClick;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
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
            $code = strtoupper(trim((string) $request->query('ref', '')));

            if ($code !== '') {
                $profile = AffiliateProfile::query()->where('referral_code', $code)->first();

                if ($profile) {
                    $sessionKey = 'affiliate_ref_click_' . $code;
                    if (! $request->session()->has($sessionKey)) {
                        $request->session()->put($sessionKey, true);
                        $profile->increment('click_count');
                        AffiliateReferralClick::query()->create([
                            'affiliate_profile_id' => $profile->id,
                            'referral_code' => $code,
                            'ip_hash' => hash('sha256', (string) ($request->ip() ?? '')),
                            'user_agent_hash' => hash('sha256', substr((string) $request->userAgent(), 0, 512)),
                        ]);
                    }
                }

                Cookie::queue('omnireferral_affiliate', $code, 60 * 24 * 60);
            }
        }

        return $next($request);
    }
}
