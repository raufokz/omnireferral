<?php

namespace App\Providers;

use App\Models\Enquiry;
use App\Models\Lead;
use App\Models\MailSetting;
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

            $adminAbilities = [
                'admin.access',
                'audit.view',
                'settings.manage',
                'integrations.manage',
                'users.view',
                'users.create',
                'users.update',
                'users.suspend',
                'users.delete',
                'users.export',
                'realtor_profiles.view',
                'realtor_profiles.update',
                'realtor_profiles.approve',
                'realtor_profiles.reject',
                'properties.view',
                'properties.create',
                'properties.update',
                'properties.delete',
                'properties.review',
                'properties.publish',
                'properties.unpublish',
                'properties.feature',
                'leads.view',
                'leads.update',
                'leads.assign',
                'leads.export',
                'leads.import',
                'enquiries.view',
                'enquiries.reply',
                'enquiries.export',
                'contacts.view',
                'contacts.moderate',
                'packages.manage',
                'affiliates.manage',
                'webhook_events.view',
                'webhook_events.replay',
                'blog.manage',
                'testimonials.manage',
                'partners.manage',
                'team.manage',
                'media.manage',
            ];

            $staffAbilities = [
                'admin.access',
                'audit.view',
                'users.view',
                'users.update',
                'users.export',
                'realtor_profiles.view',
                'properties.view',
                'properties.review',
                'leads.view',
                'leads.update',
                'leads.assign',
                'leads.export',
                'leads.import',
                'enquiries.view',
                'enquiries.reply',
                'enquiries.export',
                'contacts.view',
                'packages.manage',
                'affiliates.manage',
                'webhook_events.view',
            ];

            if ($user->isAdmin() && in_array($ability, $adminAbilities, true)) {
                return true;
            }

            if ($user->role === 'staff' && in_array($ability, $staffAbilities, true)) {
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

        Gate::define('super-admin.access', function (?User $user): bool {
            return (bool) ($user?->isSuperAdmin() ?? false);
        });

        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Enquiry::class, EnquiryPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(RealtorProfile::class, RealtorProfilePolicy::class);

        User::observe(UserObserver::class);

        // NOTE: the MessageSent → email_logs listener (App\Listeners\LogSentEmail) is
        // auto-registered by Laravel 11's listener auto-discovery; do not register it
        // explicitly here or every email would be logged twice.

        // Apply DB-stored mail settings at boot so Mail:: uses them for the whole request.
        try {
            $mail = MailSetting::instance();
            if ($mail->exists && $mail->isConfigured()) {
                $driver = $mail->mailer ?: 'smtp';
                config(['mail.default' => $driver]);

                if ($mail->host) { config(["mail.mailers.{$driver}.host" => $mail->host]); }
                if ($mail->port) { config(["mail.mailers.{$driver}.port" => (int) $mail->port]); }
                if ($mail->encryption !== null) { config(["mail.mailers.{$driver}.encryption" => $mail->encryption ?: null]); }
                if ($mail->username) { config(["mail.mailers.{$driver}.username" => $mail->username]); }
                if ($mail->password) { config(["mail.mailers.{$driver}.password" => $mail->password]); }
                if ($mail->from_address) { config(['mail.from.address' => $mail->from_address]); }
                if ($mail->from_name) { config(['mail.from.name' => $mail->from_name]); }
                if ($mail->credentials_from_address) { config(['mail.credentials_from.address' => $mail->credentials_from_address]); }
                if ($mail->credentials_from_name) { config(['mail.credentials_from.name' => $mail->credentials_from_name]); }
            }
        } catch (\Throwable $e) {
            // Table may not exist during migrations. Silently skip.
        }

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
