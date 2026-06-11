<?php

namespace App\Providers;

use App\Models\Enquiry;
use App\Models\Lead;
use App\Models\Property;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Observers\UserObserver;
use App\Policies\EnquiryPolicy;
use App\Policies\LeadPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\RealtorProfilePolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Gate::before(function (?User $user, string $ability) {
            if (! $user) {
                return null;
            }

            // Break-glass access: a super-admin can do anything.
            // (Still keep normal Policies/Gates for all other users.)
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }

            // If Spatie roles are enabled and this user is a Super Admin, allow all.
            // Guarded by config so production can disable role-name based escalation.
            if (
                (bool) config('omnireferral.security.allow_spatie_super_admin', false)
                && method_exists($user, 'hasRole')
                && $user->hasRole('Super Admin')
            ) {
                return true;
            }

            return null;
        });

        // Backward-compatible authorization bridge:
        // The admin area is gated by `can:admin.access` in routes. In production we may also grant this
        // via Spatie permissions, but we must always allow role-based admin/staff access.
        Gate::define('admin.access', function (?User $user): bool {
            return (bool) ($user?->isStaff() ?? false);
        });

        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Enquiry::class, EnquiryPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(RealtorProfile::class, RealtorProfilePolicy::class);

        User::observe(UserObserver::class);

        Route::bind('realtor', function (string $value) {
            $profile = RealtorProfile::query()
                ->publicEligible()
                ->where('slug', $value)
                ->with([
                    'user' => fn ($q) => $q->select([
                        'id',
                        'name',
                        'display_name',
                        'email',
                        'phone',
                        'avatar',
                        'city',
                        'state',
                        'zip_code',
                        'role',
                        'status',
                        'created_at',
                        'updated_at',
                    ]),
                ])
                ->firstOrFail();

            $user = $profile->user;
            abort_unless($user, 404);
            $user->setRelation('realtorProfile', $profile);

            return $user;
        });

        RateLimiter::for('leads', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('property-favorite', function (Request $request) {
            $device = (string) $request->attributes->get('listing_device_id', '');

            return Limit::perMinute(40)->by($device !== '' ? $device : $request->ip());
        });

        RateLimiter::for('property-comments', function (Request $request) {
            return Limit::perMinute(8)->by($request->ip());
        });

        RateLimiter::for('reviews', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('auth-login', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));

            return Limit::perMinute(8)->by($email . '|' . $request->ip());
        });

        RateLimiter::for('auth-register', function (Request $request) {
            return Limit::perMinute(4)->by($request->ip());
        });

        RateLimiter::for('agent-join', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('auth-password-reset', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('account-profile', function (Request $request) {
            $userId = (string) ($request->user()?->id ?? 'guest');

            return Limit::perMinute(12)->by($userId . '|' . $request->ip());
        });

        RateLimiter::for('enquiry-replies', function (Request $request) {
            $userId = (string) ($request->user()?->id ?? 'guest');

            return Limit::perMinute(20)->by($userId . '|' . $request->ip());
        });
    }
}
