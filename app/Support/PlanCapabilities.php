<?php

namespace App\Support;

use App\Models\Package;
use Illuminate\Support\Str;

/**
 * Single source of truth for what every package unlocks across the platform —
 * backed entirely by the `packages` table so an admin can change any plan's
 * permissions from the Package Management screen with zero code deploys.
 *
 * Plans are keyed by their *canonical* slug (starter-leads / growth-leads /
 * elite-leads / cold-calling-isa / social-media-mgmt / individual-va). Legacy
 * and marketing aliases (quick-leads / power-leads / prime-leads, va-*) are
 * normalised via {@see self::canonicalize()} so every caller resolves to the
 * same package row regardless of which historical slug is passed in.
 *
 * Enforcement everywhere should read from here — never from parsed feature
 * text, ad-hoc slug matches, or a hardcoded permissions array.
 */
class PlanCapabilities
{
    /**
     * Baseline: everything off. An unknown/expired/cancelled plan (no matching
     * package row) safely resolves to zero access.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'category' => 'lead',
            'portal_access' => false,
            'property_listings' => false,
            'listing_limit' => 0,
            'virtual_assistant' => false,
            'priority_routing' => false,
            'featured_placement' => false,
            'premium_seo' => false,
            'advanced_reporting' => false,
            'dashboard_access' => false,
            'advanced_qualification' => false,
            'dedicated_account_manager' => false,
            'verified_referral_access' => false,
            'referral_fee_pct' => null,
            'city_limit' => 0,
            'free_referrals' => 0,
            'referral_capacity' => null,
            'verification_steps' => 0,
            'marketing_tier' => 'none',   // none|basic|better|premium
            'profile_tier' => 'none',     // none|basic|premium
            'support_tier' => 'none',     // none|email|email_sms|priority
            'analytics_level' => 'none',  // none|basic|enhanced|advanced
            'services' => [],
            'monthly_lead_quota' => 0,
            'lead_priority' => 0,
        ];
    }

    /**
     * Resolve any stored/marketing slug to its canonical definition key. Kept
     * as code — this is a naming/alias concern, not a permission decision.
     */
    public static function canonicalize(?string $slug): string
    {
        $slug = strtolower(trim((string) $slug));

        return match ($slug) {
            'starter-leads', 'quick-leads', 'quick-lead' => 'starter-leads',
            'growth-leads', 'power-leads', 'power-lead' => 'growth-leads',
            'elite-leads', 'prime-leads', 'prime-lead' => 'elite-leads',
            'cold-calling-isa', 'va-calling', 'va-starter', 'cold-calling', 'isa' => 'cold-calling-isa',
            'social-media-mgmt', 'va-social', 'va-growth', 'social-media-management' => 'social-media-mgmt',
            'individual-va', 'va-individual', 'individual' => 'individual-va',
            default => $slug,
        };
    }

    /**
     * Merged capability array for a package slug (defaults + live DB row).
     *
     * @return array<string, mixed>
     */
    public static function for(?string $slug): array
    {
        $lookup = strtolower(trim((string) $slug));
        if ($lookup === '') {
            return self::defaults();
        }

        $package = self::resolvePackage($lookup);

        return $package ? array_merge(self::defaults(), self::rowToCapabilities($package)) : self::defaults();
    }

    /**
     * True when the given feature flag is enabled for the slug.
     */
    public static function allows(?string $slug, string $feature): bool
    {
        return (bool) (self::for($slug)[$feature] ?? false);
    }

    /**
     * Numeric limit (listing_limit, city_limit, free_referrals, referral_fee_pct...).
     */
    public static function limit(?string $slug, string $key): int
    {
        return (int) (self::for($slug)[$key] ?? 0);
    }

    /**
     * Admin-facing display label using the marketing (Quick/Power/Prime) naming.
     * Kept as code — display copy, not a permission.
     */
    public static function label(?string $slug): string
    {
        return match (self::canonicalize($slug)) {
            'starter-leads' => 'Quick Lead',
            'growth-leads' => 'Power Lead',
            'elite-leads' => 'Prime Lead',
            'cold-calling-isa' => 'Cold Calling / ISA',
            'social-media-mgmt' => 'Social Media Management',
            'individual-va' => 'Individual VA',
            default => 'No Plan',
        };
    }

    /**
     * True when the slug resolves to an actual package row.
     */
    public static function isKnown(?string $slug): bool
    {
        $lookup = strtolower(trim((string) $slug));

        return $lookup !== '' && self::resolvePackage($lookup) !== null;
    }

    /**
     * True for virtual-assistant category packages (non-lead behaviour).
     */
    public static function isVaPlan(?string $slug): bool
    {
        return (self::for($slug)['category'] ?? 'lead') === 'virtual_assistant';
    }

    /**
     * Ordered enable/disable checklist for the admin UI — built live from the
     * package row so it always mirrors whatever an admin last saved.
     *
     * @return array<int, array{label: string, enabled: bool}>
     */
    public static function checklist(?string $slug): array
    {
        $caps = self::for($slug);

        if (($caps['category'] ?? 'lead') === 'virtual_assistant') {
            return array_map(
                static fn (string $service) => ['label' => $service, 'enabled' => true],
                (array) ($caps['services'] ?? [])
            );
        }

        $item = static fn (string $label, bool $enabled) => ['label' => $label, 'enabled' => $enabled];

        $list = [
            $item('Verified Referral Access', (bool) $caps['verified_referral_access']),
        ];

        if ($caps['referral_fee_pct'] !== null) {
            $list[] = $item($caps['referral_fee_pct'] . '% Referral Fee', true);
        }
        if ($caps['city_limit'] > 0) {
            $list[] = $item('Up to ' . $caps['city_limit'] . ' Cities / ZIP Codes', true);
        }
        if ($caps['property_listings']) {
            $list[] = $item('Up to ' . $caps['listing_limit'] . ' Active Property Listings / Month', true);
        } else {
            $list[] = $item('Property Listings', false);
        }
        if ($caps['free_referrals'] > 0) {
            $list[] = $item('Up to ' . $caps['free_referrals'] . ' Free Referrals', true);
        }
        if (! empty($caps['referral_capacity'])) {
            $list[] = $item($caps['referral_capacity'] . ' Referral Capacity', true);
        }
        if ($caps['verification_steps'] > 0) {
            $list[] = $item($caps['verification_steps'] . '-Step Lead Verification', true);
        }

        $list[] = $item('Support: ' . Str::headline(str_replace('_', ' ', $caps['support_tier'])), $caps['support_tier'] !== 'none');
        $list[] = $item('Profile: ' . Str::headline($caps['profile_tier']), $caps['profile_tier'] !== 'none');
        $list[] = $item('Marketing: ' . Str::headline($caps['marketing_tier']), $caps['marketing_tier'] !== 'none');
        $list[] = $item('Analytics: ' . Str::headline($caps['analytics_level']), $caps['analytics_level'] !== 'none');

        $list[] = $item('Portal Access', (bool) $caps['portal_access']);
        $list[] = $item('Virtual Assistant', (bool) $caps['virtual_assistant']);
        $list[] = $item('Priority Routing', (bool) $caps['priority_routing']);
        $list[] = $item('Featured Placement', (bool) $caps['featured_placement']);
        $list[] = $item('Premium SEO', (bool) $caps['premium_seo']);
        $list[] = $item('Advanced Reporting', (bool) $caps['advanced_reporting']);
        $list[] = $item('Advanced Qualification', (bool) $caps['advanced_qualification']);
        $list[] = $item('Dedicated Account Manager', (bool) $caps['dedicated_account_manager']);

        return $list;
    }

    /**
     * Every active plan's capabilities keyed by canonical slug, for the live
     * admin/agent-profile UI payload. Iterates real package rows, so any new
     * package an admin creates appears automatically.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        $out = [];

        foreach (Package::query()->active()->orderBy('category')->orderBy('sort_order')->get() as $package) {
            $out[$package->slug] = [
                'label' => self::label($package->slug),
                'capabilities' => self::for($package->slug),
                'checklist' => self::checklist($package->slug),
            ];
        }

        return $out;
    }

    private static function resolvePackage(string $lookup): ?Package
    {
        $package = Package::query()->where('slug', $lookup)->first();
        if ($package) {
            return $package;
        }

        $canonical = self::canonicalize($lookup);
        if ($canonical === $lookup) {
            return null;
        }

        return Package::query()->where('slug', $canonical)->first();
    }

    /**
     * @return array<string, mixed>
     */
    private static function rowToCapabilities(Package $package): array
    {
        return [
            'category' => $package->category,
            'portal_access' => (bool) $package->portal_access,
            'property_listings' => (bool) $package->property_listings,
            'listing_limit' => (int) $package->listing_limit,
            'virtual_assistant' => (bool) $package->virtual_assistant,
            'priority_routing' => (bool) $package->priority_routing,
            'featured_placement' => (bool) $package->featured_placement,
            'premium_seo' => (bool) $package->premium_seo,
            'advanced_reporting' => (bool) $package->advanced_reporting,
            'dashboard_access' => (bool) $package->dashboard_access,
            'advanced_qualification' => (bool) $package->advanced_qualification,
            'dedicated_account_manager' => (bool) $package->dedicated_account_manager,
            'verified_referral_access' => (bool) $package->verified_referral_access,
            'referral_fee_pct' => $package->referral_fee_pct,
            'city_limit' => (int) $package->city_limit,
            'free_referrals' => (int) $package->free_referrals,
            'referral_capacity' => $package->referral_capacity,
            'verification_steps' => (int) $package->verification_steps,
            'marketing_tier' => $package->marketing_tier ?: 'none',
            'profile_tier' => $package->profile_tier ?: 'none',
            'support_tier' => $package->support_tier ?: 'none',
            'analytics_level' => $package->analytics_level ?: 'none',
            'services' => $package->services ?? [],
            'monthly_lead_quota' => (int) $package->monthly_lead_quota,
            'lead_priority' => (int) $package->lead_priority,
        ];
    }
}
