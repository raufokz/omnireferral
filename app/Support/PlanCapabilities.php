<?php

namespace App\Support;

/**
 * Single source of truth for what every package unlocks across the platform.
 *
 * Plans are keyed by their *canonical* slug (starter-leads / growth-leads /
 * elite-leads / cold-calling-isa / social-media-mgmt / individual-va). Legacy
 * and marketing aliases (quick-leads / power-leads / prime-leads, va-*) are
 * normalised via {@see self::canonicalize()} so every caller resolves to one
 * definition regardless of which historical slug is stored on the package.
 *
 * Enforcement everywhere should read from here — never from parsed feature
 * text or ad-hoc slug matches.
 */
class PlanCapabilities
{
    /**
     * Baseline: everything off. Per-plan definitions override only what they grant,
     * so an unknown/expired/cancelled plan safely resolves to zero access.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'category' => 'lead',
            // Feature switches
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
            // Numeric / tiered limits
            'referral_fee_pct' => null,
            'city_limit' => 0,
            'free_referrals' => 0,
            'referral_capacity' => null,
            'verification_steps' => 0,
            'marketing_tier' => 'none',   // none|basic|better|premium
            'profile_tier' => 'none',     // none|basic|premium
            'support_tier' => 'none',     // none|email|email_sms|priority
            // VA service deliverables (informational feature list)
            'services' => [],
        ];
    }

    /**
     * Resolve any stored/marketing slug to its canonical definition key.
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
     * Merged capability array for a package slug (defaults + plan overrides).
     *
     * @return array<string, mixed>
     */
    public static function for(?string $slug): array
    {
        $canonical = self::canonicalize($slug);
        $definition = self::definitions()[$canonical] ?? [];

        return array_merge(self::defaults(), $definition);
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
     * True when the slug maps to one of the defined canonical plans.
     */
    public static function isKnown(?string $slug): bool
    {
        return array_key_exists(self::canonicalize($slug), self::definitions());
    }

    /**
     * True for the three VA service packages (non-lead behaviour).
     */
    public static function isVaPlan(?string $slug): bool
    {
        return (self::for($slug)['category'] ?? 'lead') === 'virtual_assistant';
    }

    /**
     * Ordered enable/disable checklist for the admin UI — mirrors the published
     * package matrix so admins see exactly what a plan turns on and off.
     *
     * @return array<int, array{label: string, enabled: bool}>
     */
    public static function checklist(?string $slug): array
    {
        $canonical = self::canonicalize($slug);
        $list = self::checklists()[$canonical] ?? null;

        if ($list !== null) {
            return $list;
        }

        // VA plans: present their service deliverables as an all-on list.
        if (self::isVaPlan($canonical)) {
            return array_map(
                static fn (string $service) => ['label' => $service, 'enabled' => true],
                self::for($canonical)['services']
            );
        }

        return [];
    }

    /**
     * Every plan's capabilities keyed by canonical slug, for the live UI payload.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        $out = [];
        foreach (array_keys(self::definitions()) as $slug) {
            $out[$slug] = [
                'label' => self::label($slug),
                'capabilities' => self::for($slug),
                'checklist' => self::checklist($slug),
            ];
        }

        return $out;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function definitions(): array
    {
        return [
            'starter-leads' => [
                'category' => 'lead',
                'verified_referral_access' => true,
                'referral_fee_pct' => 20,
                'city_limit' => 2,
                'free_referrals' => 2,
                'referral_capacity' => '16-20',
                'verification_steps' => 1,
                'marketing_tier' => 'basic',
                'profile_tier' => 'basic',
                'support_tier' => 'email',
                // Everything below stays off (defaults): portal, listings, VA,
                // priority routing, featured, premium SEO, reporting, dashboard.
            ],
            'growth-leads' => [
                'category' => 'lead',
                'verified_referral_access' => true,
                'portal_access' => true,
                'property_listings' => true,
                'listing_limit' => 5,
                'virtual_assistant' => true,
                'priority_routing' => true,
                'dashboard_access' => true,
                'referral_fee_pct' => 15,
                'city_limit' => 5,
                'free_referrals' => 5,
                'referral_capacity' => '30+',
                'verification_steps' => 2,
                'marketing_tier' => 'better',
                'profile_tier' => 'premium',
                'support_tier' => 'email_sms',
                // Off: premium SEO, featured placement, dedicated account manager.
            ],
            'elite-leads' => [
                'category' => 'lead',
                'verified_referral_access' => true,
                'portal_access' => true,
                'property_listings' => true,
                'listing_limit' => 10,
                'virtual_assistant' => true,
                'priority_routing' => true,
                'featured_placement' => true,
                'premium_seo' => true,
                'advanced_reporting' => true,
                'dashboard_access' => true,
                'advanced_qualification' => true,
                'dedicated_account_manager' => true,
                'referral_fee_pct' => 10,
                'city_limit' => 10,
                'free_referrals' => 9,
                'referral_capacity' => '50+',
                'verification_steps' => 3,
                'marketing_tier' => 'premium',
                'profile_tier' => 'premium',
                'support_tier' => 'priority',
            ],
            'cold-calling-isa' => [
                'category' => 'virtual_assistant',
                'support_tier' => 'priority',
                'services' => [
                    'Dedicated ISA',
                    'Appointment Setting',
                    'Lead Follow-up',
                    'CRM Updates',
                    'KPI Reporting',
                    'Territory Management',
                ],
            ],
            'social-media-mgmt' => [
                'category' => 'virtual_assistant',
                'support_tier' => 'priority',
                'services' => [
                    'Daily Content',
                    'Reels',
                    'Shorts',
                    'Facebook',
                    'Instagram',
                    'LinkedIn',
                    'TikTok',
                    'Brand Strategy',
                    'Monthly Review',
                ],
            ],
            'individual-va' => [
                'category' => 'virtual_assistant',
                'support_tier' => 'email',
                'services' => [
                    'CRM Support',
                    'Scheduling',
                    'Email Management',
                    'Data Entry',
                    'Administrative Tasks',
                    'WordPress Support',
                    'Shopify Support',
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<int, array{label: string, enabled: bool}>>
     */
    private static function checklists(): array
    {
        return [
            'starter-leads' => [
                ['label' => 'Verified Referral Access', 'enabled' => true],
                ['label' => 'Referral Fee = 20%', 'enabled' => true],
                ['label' => 'Up to 2 Cities / ZIP Codes', 'enabled' => true],
                ['label' => 'Up to 2 Free Referrals', 'enabled' => true],
                ['label' => '16–20 Referral Capacity', 'enabled' => true],
                ['label' => 'Email Support', 'enabled' => true],
                ['label' => '1-Step Lead Verification', 'enabled' => true],
                ['label' => 'Basic Marketing', 'enabled' => true],
                ['label' => 'Basic Profile Showcase', 'enabled' => true],
                ['label' => 'Portal Access', 'enabled' => false],
                ['label' => 'Property Listings', 'enabled' => false],
                ['label' => 'Virtual Assistant', 'enabled' => false],
                ['label' => 'Priority Routing', 'enabled' => false],
                ['label' => 'Featured Placement', 'enabled' => false],
                ['label' => 'Premium SEO', 'enabled' => false],
                ['label' => 'Advanced Reporting', 'enabled' => false],
            ],
            'growth-leads' => [
                ['label' => 'Portal Access', 'enabled' => true],
                ['label' => 'Referral Fee = 15%', 'enabled' => true],
                ['label' => 'Up to 5 Cities / ZIP Codes', 'enabled' => true],
                ['label' => 'Up to 5 Property Listings', 'enabled' => true],
                ['label' => 'Up to 5 Free Referrals', 'enabled' => true],
                ['label' => '30+ Referrals', 'enabled' => true],
                ['label' => 'Virtual Assistance', 'enabled' => true],
                ['label' => 'Email + SMS Support', 'enabled' => true],
                ['label' => 'Priority Routing', 'enabled' => true],
                ['label' => 'Premium Profile', 'enabled' => true],
                ['label' => 'Better Marketing', 'enabled' => true],
                ['label' => '2-Step Verification', 'enabled' => true],
                ['label' => 'Dashboard Access', 'enabled' => true],
                ['label' => 'Premium SEO', 'enabled' => false],
                ['label' => 'Featured Homepage Placement', 'enabled' => false],
                ['label' => 'Dedicated Account Manager', 'enabled' => false],
            ],
            'elite-leads' => [
                ['label' => 'Portal Access', 'enabled' => true],
                ['label' => 'Referral Fee = 10%', 'enabled' => true],
                ['label' => 'Up to 10 Cities / ZIP Codes', 'enabled' => true],
                ['label' => 'Up to 10 Property Listings', 'enabled' => true],
                ['label' => 'Up to 9 Free Referrals', 'enabled' => true],
                ['label' => '50+ Referrals', 'enabled' => true],
                ['label' => 'Premium Profile', 'enabled' => true],
                ['label' => 'Priority Routing', 'enabled' => true],
                ['label' => 'Priority Support', 'enabled' => true],
                ['label' => 'Virtual Assistance', 'enabled' => true],
                ['label' => 'Advanced Qualification', 'enabled' => true],
                ['label' => 'Premium Marketing', 'enabled' => true],
                ['label' => 'SEO Optimized Profile', 'enabled' => true],
                ['label' => 'Dashboard Analytics', 'enabled' => true],
                ['label' => 'Featured Placement', 'enabled' => true],
                ['label' => 'Dedicated Account Manager', 'enabled' => true],
            ],
        ];
    }
}
