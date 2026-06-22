<?php

namespace App\Services;

use App\Models\BuyerProfile;
use App\Models\OnboardingLog;
use App\Models\Package;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Support\AgentAvatar;
use Illuminate\Support\Str;

class OnboardingSyncService
{
    public function __construct(
        private readonly PasswordProvisioningService $passwordService,
    ) {}

    /**
     * Process a GHL onboarding webhook payload.
     *
     * Returns an array with:
     *   - user              (User)
     *   - isNewUser         (bool)
     *   - isFirstOnboarding (bool)
     *   - shouldSendSetup   (bool)  – true means email a password-setup link
     *   - onboardingLog     (OnboardingLog)
     */
    public function sync(array $payload, ?int $explicitUserId = null): array
    {
        $email  = $this->scalar($payload, ['email', 'contact.email']);
        $phone  = $this->scalar($payload, ['phone', 'contact.phone', 'phone_number']);
        $role   = $this->normalizeRole($this->scalar($payload, ['role', 'user_type', 'contact.customField.role']) ?? '');

        $packageSlug = $this->scalar($payload, ['package_slug', 'package.slug']);
        $packageId   = (int) ($payload['package_id'] ?? data_get($payload, 'package.id') ?? 0) ?: null;
        $staffTeam   = $this->scalar($payload, ['staff_team']);
        $contactId   = $this->scalar($payload, ['contact_id', 'id', 'contact.id']);

        $user = null;

        // Prefer explicit user_id hint (passed as hidden field from onboarding form URL)
        if ($explicitUserId) {
            $user = User::find($explicitUserId);
        }

        if (! $user && $email) {
            $user = User::firstOrNew(['email' => $email]);
        }

        if (! $user) {
            throw new \RuntimeException('Cannot identify user: no email or user_id in webhook payload.');
        }

        $isNewUser        = ! $user->exists;
        $isFirstOnboarding = $isNewUser || is_null($user->onboarding_completed_at);

        // --- Resolve name: prefer payload, fall back to existing name or generic default ---
        $payloadName = $this->scalar($payload, ['name', 'contact.name', 'full_name', 'first_name']);
        $name        = $payloadName ?: ($user->name ?: 'New OmniReferral User');

        // --- Address fields ---
        $city     = $this->scalar($payload, ['city', 'contact.city'])     ?: $user->city;
        $state    = strtoupper($this->scalar($payload, ['state', 'contact.state']) ?: ($user->state ?? ''));
        $zipCode  = $this->scalar($payload, ['zip_code', 'postal_code', 'contact.postalCode']) ?: $user->zip_code;
        $address1 = $this->scalar($payload, ['address', 'address_line_1', 'contact.address1']) ?: $user->address_line_1;
        $address2 = $this->scalar($payload, ['address_line_2', 'contact.address2']) ?: $user->address_line_2;

        $user->fill([
            'name'                    => $name,
            'phone'                   => $phone,
            'role'                    => $role,
            'staff_team'              => $staffTeam,
            'status'                  => 'active',
            'ghl_contact_id'          => $contactId ?: ($user->ghl_contact_id ?? null),
            'onboarding_completed_at' => now(),
            'email_verified_at'       => $user->email_verified_at ?? now(),
            'address_line_1'          => $address1,
            'address_line_2'          => $address2,
            'city'                    => $city,
            'state'                   => $state,
            'zip_code'                => $zipCode,
        ]);

        if (! $user->affiliate_code) {
            $user->affiliate_code = strtoupper(Str::random(8));
        }

        // --- Plan ---
        $package = $packageId
            ? Package::find($packageId)
            : ($packageSlug ? Package::where('slug', $packageSlug)->first() : null);

        if ($package) {
            $user->current_plan_id = $package->id;
        }

        // --- Password setup (no plaintext password is ever generated or emailed) ---
        // On first onboarding (or for a user who still owes a password reset) we set a
        // random, unknown password so the account is unusable until they activate it via
        // the one-time setup link. The link itself is dispatched by the caller.
        $shouldSendSetup = $isFirstOnboarding || ($user->exists && $user->must_reset_password);
        if ($shouldSendSetup) {
            $user->password            = Str::password(32, true, true, true, false); // hashed by cast; discarded
            $user->must_reset_password = true;
        }

        $user->save();

        // --- Role-based profile upsert ---
        $profileAction = null;
        if ($role === 'agent') {
            $profileAction = $this->upsertRealtorProfile($user, $payload, $city, $state, $zipCode);
        } elseif (in_array($role, ['buyer', 'seller'], true)) {
            $profileAction = $this->upsertBuyerProfile($user, $payload, $city, $zipCode);
        }

        $portalAccessEnabled = in_array($user->status, ['active', 'approved'], true);

        // --- Audit log ---
        $safePayload = $this->stripSensitiveKeys($payload);
        $log = OnboardingLog::create([
            'user_id'               => $user->id,
            'source'                => 'ghl',
            'event_type'            => 'onboarding_completed',
            'triggered_by'          => $email,
            'user_action'           => $isNewUser ? 'created' : 'updated',
            'profile_action'        => $profileAction,
            'portal_access_enabled' => $portalAccessEnabled,
            'email_status'          => $shouldSendSetup ? 'pending' : 'skipped',
            'form_name'             => $this->scalar($payload, ['form_name', 'form.name']),
            'form_id'               => $this->scalar($payload, ['form_id', 'form.id']),
            'ghl_contact_id'        => $contactId ?: $user->ghl_contact_id,
            'contact_name'          => $name,
            'contact_phone'         => $phone,
            'payload'               => $safePayload,
            'processed_at'          => now(),
            'email_sent'            => false,
        ]);

        return [
            'user'              => $user,
            'isNewUser'         => $isNewUser,
            'isFirstOnboarding' => $isFirstOnboarding,
            'shouldSendSetup'   => $shouldSendSetup,
            'onboardingLog'     => $log,
        ];
    }

    /**
     * Normalise role values from GoHighLevel to Laravel roles.
     * All agent/realtor aliases → 'agent'.
     */
    public function normalizeRole(string $raw): string
    {
        $lower = strtolower(trim($raw));

        $agentAliases = [
            'agent', 'realtor', 'real estate agent', 'real_estate_agent',
            'realestate agent', 'realestate_agent', 'real-estate-agent',
            'listing agent', 'buyer agent', 'buyers agent',
        ];

        if (in_array($lower, $agentAliases, true)) {
            return 'agent';
        }

        $allowed = ['buyer', 'seller', 'admin', 'staff'];

        return in_array($lower, $allowed, true) ? $lower : 'agent';
    }

    private function upsertRealtorProfile(User $user, array $payload, ?string $city, ?string $state, ?string $zipCode): string
    {
        $existing = RealtorProfile::where('user_id', $user->id)->first();
        // The UserObserver auto-creates a blank stub profile when an agent user is saved, so a
        // row may already exist. Treat a stub (never populated by an onboarding submission) as
        // "created"; only a profile previously populated via onboarding counts as "updated".
        $action = ($existing && filled($existing->submission_source)) ? 'updated' : 'created';

        $slug = $existing?->slug
            ?: Str::slug($user->name . '-' . Str::lower(Str::random(6)));

        $brokerage = $this->scalar($payload, ['brokerage_name', 'brokerage']) ?: 'OmniReferral Partner';
        $license   = $this->scalar($payload, ['license_number', 'license_no', 'real_estate_license_number']);
        $bio       = $this->scalar($payload, ['bio', 'agent_bio', 'about']) ?: 'Agent profile generated from GoHighLevel onboarding.';
        $specialties = $this->scalar($payload, ['specialties', 'specialty']) ?: 'Buyer Representation, Seller Strategy, Lead Conversion';
        $yearsExp  = isset($payload['years_of_experience']) ? (int) $payload['years_of_experience'] : null;
        $languages = $this->scalar($payload, ['languages', 'language']);
        $marketAreas = $this->scalar($payload, ['market_areas', 'market_area', 'markets_served']);
        $socialLinks = null;
        if (isset($payload['social_links']) && is_array($payload['social_links'])) {
            $socialLinks = $payload['social_links'];
        } elseif (isset($payload['linkedin']) || isset($payload['facebook']) || isset($payload['instagram'])) {
            $socialLinks = array_filter([
                'linkedin'  => $payload['linkedin'] ?? null,
                'facebook'  => $payload['facebook'] ?? null,
                'instagram' => $payload['instagram'] ?? null,
            ]);
        }

        $updates = array_filter([
            'slug'                => $slug,
            'brokerage_name'      => $brokerage,
            'license_number'      => $license,
            'service_city'        => $city ?: 'Dallas',
            'service_state'       => $state ?: 'TX',
            'service_zip_code'    => $zipCode ?: '75201',
            'specialties'         => $specialties,
            'bio'                 => $bio,
            'years_of_experience' => $yearsExp,
            'languages'           => $languages,
            'market_areas'        => $marketAreas,
            'social_links'        => $socialLinks,
            'headshot'            => AgentAvatar::defaultStorageHeadshot(),
        ], fn ($v) => $v !== null && $v !== '');

        // Onboarding approves the profile for public visibility (profile_status=published +
        // approved_at) and marks the agent active. approved_at is only stamped once.
        $updates['profile_status']    = RealtorProfile::STATUS_PUBLISHED;
        $updates['submission_source'] = 'gohighlevel';
        $updates['is_active_agent']   = $this->boolFrom($payload, ['is_active_agent', 'active_agent', 'is_active']) ?? true;
        $updates['approved_at']       = $existing?->approved_at ?? now();

        // Preserve existing slug on update
        RealtorProfile::updateOrCreate(
            ['user_id' => $user->id],
            $updates,
        );

        return $action;
    }

    private function upsertBuyerProfile(User $user, array $payload, ?string $city, ?string $zipCode): string
    {
        $action = BuyerProfile::where('user_id', $user->id)->exists() ? 'updated' : 'created';

        $preferredLocations = array_values(array_filter([
            $this->scalar($payload, ['preferred_location', 'city']) ?: $city,
            $this->scalar($payload, ['zip_code', 'postal_code']) ?: $zipCode,
        ]));

        $safePayload = $this->stripSensitiveKeys($payload);

        BuyerProfile::updateOrCreate(['user_id' => $user->id], [
            'budget_min'           => isset($payload['budget_min']) ? (int) $payload['budget_min'] : null,
            'budget_max'           => isset($payload['budget_max']) ? (int) $payload['budget_max'] : null,
            'preferred_locations'  => $preferredLocations ?: null,
            'financing_status'     => $this->scalar($payload, ['financing_status']) ?: null,
            'timeline'             => $this->scalar($payload, ['timeline', 'purchase_timeline']) ?: null,
            'notes'                => $this->scalar($payload, ['notes', 'buyer_notes']) ?: null,
            'onboarding_data'      => $safePayload,
        ]);

        return $action;
    }

    /** Parse a boolean-ish value from the payload using dot-notation key candidates. */
    private function boolFrom(array $payload, array $keys): ?bool
    {
        foreach ($keys as $key) {
            $val = data_get($payload, $key);
            if ($val === null || $val === '') {
                continue;
            }
            if (is_bool($val)) {
                return $val;
            }

            return in_array(strtolower(trim((string) $val)), ['1', 'true', 'yes', 'y', 'on', 'active'], true);
        }

        return null;
    }

    /** Extract a scalar value from nested payload using dot-notation key candidates. */
    private function scalar(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $val = data_get($payload, $key);
            if (is_string($val) && trim($val) !== '') {
                return trim($val);
            }
        }

        return null;
    }

    private function stripSensitiveKeys(array $payload): array
    {
        $blocked = ['password', 'token', 'webhook_secret', 'api_key', 'secret'];

        return array_filter(
            $payload,
            fn ($key) => ! in_array(strtolower((string) $key), $blocked, true),
            ARRAY_FILTER_USE_KEY
        );
    }
}
