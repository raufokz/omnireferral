<?php

namespace App\Support;

use App\Models\PricingPlan;
use Illuminate\Support\Facades\Schema;

class PricingContent
{
    public static function plans(): array
    {
        $dbPlans = self::loadFromDatabase();
        if ($dbPlans !== null) {
            return $dbPlans;
        }

        return self::fallbackPlans();
    }

    public static function planBySlug(string $slug): ?array
    {
        foreach (self::plans() as $category => $plans) {
            foreach ($plans as $plan) {
                if (($plan['slug'] ?? null) === $slug) {
                    $plan['category'] = $category;

                    return $plan;
                }
            }
        }

        return null;
    }

    private static function loadFromDatabase(): ?array
    {
        try {
            if (! Schema::hasTable('pricing_plans')) {
                return null;
            }

            $plans = PricingPlan::active()->ordered()->get();

            if ($plans->isEmpty()) {
                return null;
            }

            $grouped = [];
            foreach ($plans as $plan) {
                $grouped[$plan->category][] = self::enrichPlan([
                    'slug' => $plan->slug,
                    'name' => $plan->name,
                    'tier' => $plan->tier,
                    'value_price' => $plan->value_price,
                    'price' => $plan->price,
                    'price_note' => $plan->price_note,
                    'summary' => $plan->summary,
                    'features' => $plan->features ?? [],
                    'cta_label' => $plan->cta_label ?? 'Get Started',
                    'cta_url' => $plan->cta_url,
                    'is_featured' => $plan->is_featured,
                ]);
            }

            return $grouped;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function fallbackPlans(): array
    {
        return [
            'real_estate' => array_map([self::class, 'enrichPlan'], [
                [
                    'slug' => 'quick-leads',
                    'name' => 'Quick Lead',
                    'tier' => 'Starter',
                    'value_price' => null,
                    'price' => 499,
                    'price_note' => 'One-Time',
                    'summary' => 'Verified referral growth for agents entering new markets.',
                    'features' => [
                        'Verified referral opportunities',
                        'ZIP-based routing',
                        'Email support',
                        'Local market exposure',
                        'Predictable new-business workflow',
                    ],
                    'cta_label' => 'EXPLORE PLAN',
                    'is_featured' => false,
                ],
                [
                    'slug' => 'power-leads',
                    'name' => 'Power Lead',
                    'tier' => 'Most Popular',
                    'value_price' => null,
                    'price' => 797,
                    'price_note' => 'One-Time',
                    'summary' => 'Balanced growth and visibility for scaling teams.',
                    'features' => [
                        'Priority routing',
                        'Virtual assistance',
                        'Text support',
                        'Increased referral volume',
                        'Stronger market presence',
                    ],
                    'cta_label' => 'EXPLORE PLAN',
                    'is_featured' => true,
                ],
                [
                    'slug' => 'prime-leads',
                    'name' => 'Prime Lead',
                    'tier' => 'Premium',
                    'value_price' => null,
                    'price' => 2299,
                    'price_note' => 'One-Time',
                    'summary' => 'Maximum referral exposure and priority support.',
                    'features' => [
                        'Premium exposure',
                        'Priority support',
                        'Advanced qualification',
                        'Broader market coverage',
                        'Accelerated growth opportunities',
                    ],
                    'cta_label' => 'EXPLORE PLAN',
                    'is_featured' => false,
                ],
            ]),
            'virtual_assistance' => array_map([self::class, 'enrichPlan'], [
                [
                    'slug' => 'cold-calling-isa',
                    'name' => 'Cold Calling / ISA',
                    'tier' => 'Dedicated ISA',
                    'value_price' => null,
                    'price' => 1999,
                    'price_note' => 'Per Month',
                    'summary' => 'Pipeline growth powered by real prospecting.',
                    'features' => [
                        'Data scraping and skip tracing',
                        'Up to 5 cities or ZIP codes',
                        'Daily scheduling and VoIP config',
                        'CRM setup and KPI automation',
                        'Email, text, and VoiceFlow follow-ups',
                        'Weekly performance reports',
                    ],
                    'cta_label' => 'EXPLORE PLAN',
                    'cta_url' => null,
                    'is_featured' => false,
                ],
                [
                    'slug' => 'social-media-mgmt',
                    'name' => 'Social Media Mgmt',
                    'tier' => 'Most Popular',
                    'value_price' => null,
                    'price' => 1499,
                    'price_note' => 'Per Month',
                    'summary' => 'Consistent visibility and audience growth.',
                    'features' => [
                        'IG, FB, LinkedIn, and TikTok',
                        'Dedicated social media strategy',
                        'Stories, highlights, and reels',
                        'Audience engagement management',
                        'Custom ads creation and management',
                        'Monthly and quarterly review',
                    ],
                    'cta_label' => 'EXPLORE PLAN',
                    'cta_url' => null,
                    'is_featured' => true,
                ],
                [
                    'slug' => 'individual-va',
                    'name' => 'Individual VA',
                    'tier' => 'Flexible Support',
                    'value_price' => null,
                    'price' => 8,
                    'price_note' => 'Per Hour',
                    'summary' => 'On-demand operational support when you need it.',
                    'features' => [
                        'Needs assessment and onboarding',
                        '24/7 Discord priority support',
                        'Appointment setting and calendar',
                        'CRM support and email management',
                        'WordPress, Shopify, SEO, and AEO',
                        'Automations, workflows, and templates',
                    ],
                    'cta_label' => 'EXPLORE PLAN',
                    'cta_url' => '/contact',
                    'is_featured' => false,
                ],
            ]),
        ];
    }

    private static function enrichPlan(array $plan): array
    {
        $slug = (string) ($plan['slug'] ?? '');
        $enhancementKey = self::enhancementSlug($slug);
        $enhancement = self::planEnhancements()[$enhancementKey] ?? null;

        if (! $enhancement) {
            return $plan;
        }

        return array_merge($plan, $enhancement);
    }

    private static function enhancementSlug(string $slug): string
    {
        return match ($slug) {
            'va-calling' => 'cold-calling-isa',
            'va-social' => 'social-media-mgmt',
            'va-individual' => 'individual-va',
            default => $slug,
        };
    }
 
    private static function planEnhancements(): array
    {
        return [
            'quick-leads' => [
                'badge' => 'Starter',
                'card_tag' => 'Starter',
                'value_statement' => 'Verified referral growth for agents entering new markets.',
                'card_best_for' => 'New Agents',
                'billing_label' => 'One-Time',
                'card_description' => 'Perfect for agents entering a new market who want verified referral opportunities, local exposure, and a predictable way to generate new business opportunities.',
                'highlights' => ['Verified Referrals', 'ZIP Routing', 'Email Support'],
                'best_for' => 'Agents entering a new market who want verified referral opportunities without a large upfront commitment.',
                'what_you_get' => 'A focused referral workflow with verified opportunities, ZIP routing, and email support to help you build momentum in a new market.',
                'package_benefits' => [
                    'Launch verified referral flow in your target market.',
                    'Route opportunities through ZIP-based matching.',
                    'Get email support for setup and follow-up questions.',
                ],
                'feature_groups' => [
                    ['title' => 'Core value', 'items' => ['Verified Referrals', 'ZIP Routing', 'Email Support']],
                ],
                'after_submission' => [
                    'Your survey details are reviewed by the OmniReferral team.',
                    'We confirm your target cities, ZIP codes, and preferred lead profile.',
                    'Your focused referral workflow is prepared for launch.',
                ],
                'support_details' => 'Quick Lead includes email support for package setup, territory selection, and referral handoff questions.',
                'trust_indicators' => ['Verified referrals', 'ZIP routing', 'Email support', 'One-time package'],
                'trust_note' => 'A clean entry point for agents who want verified opportunities before scaling further.',
            ],
            'power-leads' => [
                'is_featured' => true,
                'badge' => 'Most Popular',
                'card_tag' => 'Most Popular',
                'value_statement' => 'Balanced growth and visibility for scaling teams.',
                'card_best_for' => 'Growing Teams',
                'billing_label' => 'One-Time',
                'card_description' => 'Our most balanced package for agents and teams looking to increase referral volume, improve visibility, strengthen market presence, and receive additional support resources.',
                'highlights' => ['Priority Routing', 'Virtual Assistance', 'Text Support'],
                'best_for' => 'Growing teams that want more referral volume, stronger visibility, and additional support resources.',
                'what_you_get' => 'A balanced growth package with priority routing, virtual assistance, and text support to help your team scale referral flow.',
                'package_benefits' => [
                    'Increase referral volume with priority routing.',
                    'Add virtual assistance without hiring internally.',
                    'Stay responsive with dedicated text support.',
                ],
                'feature_groups' => [
                    ['title' => 'Core value', 'items' => ['Priority Routing', 'Virtual Assistance', 'Text Support']],
                ],
                'after_submission' => [
                    'Your survey is reviewed for territory, offer type, and service-area fit.',
                    'Your selected areas and routing rules are confirmed.',
                    'A growth-focused handoff workflow is prepared for your team.',
                ],
                'support_details' => 'Power Lead includes priority routing support, virtual assistance coordination, and text support for active lead-flow questions.',
                'trust_indicators' => ['Most popular tier', 'Priority routing', 'Virtual assistance', 'Text support'],
                'trust_note' => 'Built for teams that want balanced growth, visibility, and hands-on support.',
            ],
            'prime-leads' => [
                'badge' => 'Premium',
                'card_tag' => 'Premium',
                'value_statement' => 'Maximum referral exposure and priority support.',
                'card_best_for' => 'High Volume Agents',
                'billing_label' => 'One-Time',
                'card_description' => 'Designed for serious producers who want broader market coverage, deeper qualification, premium support, and accelerated growth opportunities.',
                'highlights' => ['Premium Exposure', 'Priority Support', 'Advanced Qualification'],
                'best_for' => 'High-volume agents and teams that need premium exposure, deeper qualification, and priority support.',
                'what_you_get' => 'A premium referral system with broader coverage, advanced qualification, and priority support for serious producers.',
                'package_benefits' => [
                    'Expand referral exposure across a wider footprint.',
                    'Receive priority support for high-intent opportunities.',
                    'Benefit from deeper qualification before handoff.',
                ],
                'feature_groups' => [
                    ['title' => 'Core value', 'items' => ['Premium Exposure', 'Priority Support', 'Advanced Qualification']],
                ],
                'after_submission' => [
                    'Your premium survey is reviewed for territory, capacity, and routing needs.',
                    'Your target market rules and qualification preferences are confirmed.',
                    'Your dedicated support lane is prepared for launch.',
                ],
                'support_details' => 'Prime Lead includes premium support, advanced qualification workflows, and priority routing for high-volume agents.',
                'trust_indicators' => ['Premium exposure', 'Priority support', 'Advanced qualification', 'One-time package'],
                'trust_note' => 'Designed for producers who want maximum referral exposure and premium support.',
            ],
            'cold-calling-isa' => [
                'badge' => 'Dedicated ISA',
                'card_tag' => 'Dedicated ISA',
                'value_statement' => 'Pipeline growth powered by real prospecting.',
                'card_best_for' => 'Busy Agents',
                'billing_label' => 'Per Month',
                'card_description' => 'Dedicated inside sales support focused on prospecting, lead nurturing, appointment setting, follow-ups, and pipeline growth so agents can spend more time closing deals.',
                'highlights' => ['Appointment Setting', 'Lead Follow-Up', 'Pipeline Growth'],
                'best_for' => 'Busy agents who need consistent outbound prospecting and appointment setting without hiring in-house.',
                'what_you_get' => 'A dedicated ISA lane for prospecting, follow-up, appointment setting, and pipeline growth across your target markets.',
                'package_benefits' => [
                    'Add dedicated prospecting without recruiting a new hire.',
                    'Keep follow-up and appointment setting moving daily.',
                    'Grow pipeline with weekly performance visibility.',
                ],
                'feature_groups' => [
                    ['title' => 'Core value', 'items' => ['Appointment Setting', 'Lead Follow-Up', 'Pipeline Growth']],
                ],
                'after_submission' => [
                    'Your territory and outbound goals are reviewed.',
                    'Calling tools, data needs, and handoff rules are confirmed.',
                    'Your ISA workflow is prepared for active prospecting.',
                ],
                'support_details' => 'Cold Calling / ISA includes campaign setup guidance, weekly performance reporting, and CRM handoff coordination.',
                'trust_indicators' => ['Dedicated ISA', 'Appointment setting', 'Pipeline growth', 'Weekly reporting'],
                'trust_note' => 'Built for agents who want more qualified conversations and a stronger outbound pipeline.',
            ],
            'social-media-mgmt' => [
                'is_featured' => true,
                'badge' => 'Most Popular',
                'card_tag' => 'Most Popular',
                'value_statement' => 'Consistent visibility and audience growth.',
                'card_best_for' => 'Brand Growth',
                'billing_label' => 'Per Month',
                'card_description' => 'End-to-end social media management including content creation, audience engagement, brand positioning, short-form videos, and growth strategies designed to generate opportunities.',
                'highlights' => ['Daily Content', 'Audience Growth', 'Brand Visibility'],
                'best_for' => 'Agents and teams focused on brand growth, content consistency, and audience visibility.',
                'what_you_get' => 'Full social media management with daily content, audience growth, and brand visibility across your key channels.',
                'package_benefits' => [
                    'Publish daily content without managing every post.',
                    'Grow audience engagement across key channels.',
                    'Strengthen brand visibility with strategic creative.',
                ],
                'feature_groups' => [
                    ['title' => 'Core value', 'items' => ['Daily Content', 'Audience Growth', 'Brand Visibility']],
                ],
                'after_submission' => [
                    'Your brand, channels, and content goals are reviewed.',
                    'Posting cadence and creative priorities are confirmed.',
                    'Your content workflow is prepared for launch.',
                ],
                'support_details' => 'Social Media Mgmt includes content planning, channel support, engagement coverage, and review checkpoints.',
                'trust_indicators' => ['Daily content', 'Audience growth', 'Brand visibility', 'Most popular VA tier'],
                'trust_note' => 'Designed to keep your brand visible, credible, and consistently generating opportunities.',
            ],
            'individual-va' => [
                'badge' => 'Flexible Support',
                'card_tag' => 'Flexible Support',
                'value_statement' => 'On-demand operational support when you need it.',
                'card_best_for' => 'Lean Teams',
                'billing_label' => 'Per Hour',
                'card_description' => 'Flexible hourly support for administrative tasks, CRM management, scheduling, lead organization, email handling, research, and day-to-day operations.',
                'highlights' => ['CRM Support', 'Scheduling', 'Admin Assistance'],
                'cta_url' => '/contact',
                'best_for' => 'Lean teams that need flexible hourly support without a long-term commitment.',
                'what_you_get' => 'On-demand VA support for CRM, scheduling, admin work, and day-to-day operations billed hourly.',
                'package_benefits' => [
                    'Delegate admin and CRM work on your schedule.',
                    'Add scheduling and operational support as needed.',
                    'Avoid long-term commitment when needs change.',
                ],
                'feature_groups' => [
                    ['title' => 'Core value', 'items' => ['CRM Support', 'Scheduling', 'Admin Assistance']],
                ],
                'after_submission' => [
                    'Your support needs and preferred task types are reviewed.',
                    'Scope, hours, and priorities are confirmed.',
                    'Your VA support lane is prepared around your workflow.',
                ],
                'support_details' => 'Individual VA includes needs assessment and flexible hourly support across admin, CRM, and operational tasks.',
                'trust_indicators' => ['Flexible hourly model', 'CRM support', 'Scheduling help', 'No long-term lock-in'],
                'trust_note' => 'A clean way to add capacity when the work is real but not always full time.',
            ],
        ];
    }
}
