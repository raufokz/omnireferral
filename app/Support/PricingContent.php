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
                    'name' => 'Starter',
                    'tier' => 'Starter Tier',
                    'value_price' => 999,
                    'price' => 399,
                    'price_note' => '/ month - 2 Areas',
                    'summary' => 'Launch package built for agents who want active referral flow, outreach support, and clean follow-up.',
                    'features' => [
                        'Qualified Buyer/Seller Referrals Per Month (Active Buyers & Sellers)',
                        'AI + Human Powered Outreach (Cold Calling + Marketing)',
                        'Multi-Channel Lead Generation (Facebook, Google, Direct Outreach)',
                        'Select up to 5 cities or ZIP codes',
                        '15% Referral Fee Only on Closed Deals',
                        'Dedicated Account Manager (Weekly Updates & Follow-Ups)',
                        'Priority Support (Call + SMS + Email)',
                        'List Up to 2 Active Listings on Our Platform ',
                        'Organic Exposure to Active Buyer Network',
                        'Basic Lead Nurturing & Follow-Up System',
                        'Monthly Performance Report',
                    ],
                    'cta_label' => 'Get Started',
                    'is_featured' => false,
                ],
                [
                    'slug' => 'power-leads',
                    'name' => 'Growth',
                    'tier' => 'Growth Tier',
                    'value_price' => 1497,
                    'price' => 899,
                    'price_note' => '/ month - 5 Areas',
                    'summary' => 'Everything in Starter, plus warm opportunities, JV support, and stronger routing priority.',
                    'features' => [
                        'Direct Connection with Buyers, Sellers & Investors (Warm + Active Opportunities)',
                        'Dedicated Senior Account Manager (Wholesaler-Level Expertise)',
                        'Joint Venture (JV) Opportunities with Assigned Wholesaler (Terms Mutually Agreed) ',
                        '1 Full-Time ISA (Cold Caller) Working Your Territory',
                        'Exclusive Lead Flow from Your Selected Areas',
                        'Select up to 10 cities or ZIP codes',
                        '7% Referral Fee Only on Closed Deals',
                        'Priority Deal Matching (Buyer ↔ Seller ↔ Investor)',
                        'Higher Intent, Multi-Step Verified Prospects',
                        'Advanced Lead Nurturing + Follow-Up Sequences',
                        'Weekly Strategy + Pipeline Updates',
                        'Faster Lead Response & Routing System',
                        'Enhanced Profile Visibility + Listing Boost',
                    ],
                    'cta_label' => 'Get Started',
                    'is_featured' => true,
                ],
                [
                    'slug' => 'prime-leads',
                    'name' => 'Elite',
                    'tier' => 'Elite Tier',
                    'value_price' => 3299,
                    'price' => 1999,
                    'price_note' => '/ month - 10 Areas',
                    'summary' => 'Everything in Growth, plus full-team execution, live transfers, and front-of-queue referral access.',
                    'features' => [
                        'Dedicated Senior Wholesaler Assigned to Your Account',
                        'Full-Time Virtual Assistant (Also Your Go-To Account Manager)',
                        '2 Full-Time ISAs (Cold Callers) Working Your Market Daily',
                        'Direct Live Call Transfers (Hot Leads Instantly Connected) ',
                        'Priority Access to All High-Intent Referrals (Front of Queue)',
                        'Advanced JV Deal Flow + Off-Market Opportunities ',
                        'Full CRM Access (GoHighLevel) — Fully Built & Automated',
                        'Funnels, Pipelines, Automations, SMS + Email Systems Included',
                        'Expanded territory (up to 15 cities or ZIP codes)',
                        '5% Referral Fee Only on Closed Deals',
                        'Investor Network Access (Cash Buyers + Off-Market Deals)',
                        'Unlimited Listings on Platform + Featured Placement ',
                        'AI-Powered Lead Scoring + Priority Routing',
                        'Weekly Strategy Calls + Monthly Growth Planning',
                        'Performance Dashboard + Lead Forecasting System',
                    ],
                    'cta_label' => 'Get Started',
                    'is_featured' => false,
                ],
            ]),
            'virtual_assistance' => array_map([self::class, 'enrichPlan'], [
                [
                    'slug' => 'cold-calling-isa',
                    'name' => 'Cold Calling / ISA',
                    'tier' => 'SALES BOOST',
                    'value_price' => null,
                    'price' => 1999,
                    'price_note' => '/month',
                    'summary' => 'Dedicated ISA Sales Agent',
                    'features' => [
                        'Data Scraping & Skip Tracing',
                        'Up to 5 Cities / Zip Codes',
                        'Daily Scheduling + VoIP Config',
                        'CRM Setup + KPI Automation',
                        'Email, Text & VoiceFlow Follow-ups',
                        'Weekly Performance Reports',
                        'Key Area Territory Manager',
                        'Social Media Management',
                    ],
                    'cta_label' => 'GET ISA SUPPORT',
                    'cta_url' => null,
                    'is_featured' => false,
                ],
                [
                    'slug' => 'social-media-mgmt',
                    'name' => 'Social Media Mgmt',
                    'tier' => 'MOST POPULAR',
                    'value_price' => null,
                    'price' => 1499,
                    'price_note' => '/ month',
                    'summary' => 'Daily Long + Short Form Videos',
                    'features' => [
                        'IG, FB, LinkedIn + TikTok',
                        'Dedicated Social Media Strategy',
                        'Stories, Highlights + Reels',
                        'Audience Engagement Management',
                        'Custom Ads Creation + Management',
                        'AI Branding & Lead Body Content',
                        'Brand Development + Web SEO',
                        'Monthly + Quarterly Review',
                        'ISA Cold Calling Support',
                    ],
                    'cta_label' => 'GET SOCIAL',
                    'cta_url' => null,
                    'is_featured' => true,
                ],
                [
                    'slug' => 'individual-va',
                    'name' => 'Individual VA',
                    'tier' => 'FLEXIBLE HOURS',
                    'value_price' => null,
                    'price' => 8,
                    'price_note' => '/ hour',
                    'summary' => 'Flexible Hourly Billing',
                    'features' => [
                        'Needs Assessment + Onboarding',
                        '24/7 Discord Priority Support',
                        'Appointment Setting + Calendar',
                        'CRM Support + Email Management',
                        'WordPress / Shopify + SEO & AEO',
                        'Graphic Design + Logo Creation',
                        'Data Entry + Lead Generation',
                        'Automations, Workflows + Templates',
                    ],
                    'cta_label' => 'HIRE VA',
                    'cta_url' => null,
                    'is_featured' => false,
                ],
            ]),
        ];
    }

    private static function enrichPlan(array $plan): array
    {
        $slug = (string) ($plan['slug'] ?? '');
        $enhancement = self::planEnhancements()[$slug] ?? null;

        if (! $enhancement) {
            return $plan;
        }

        return array_merge($plan, $enhancement);
    }

    private static function planEnhancements(): array
    {
        return [
            'cold-calling-isa' => [
                'summary' => 'Put a dedicated ISA on your outbound pipeline without recruiting, training, or managing another in-house hire.',
                'highlights' => [
                    'Dedicated caller capacity',
                    'Territory-focused prospecting',
                    'CRM-ready handoff',
                ],
                'best_for' => 'Agents and teams that need consistent outbound conversations in up to 5 target markets.',
                'what_you_get' => 'A managed outbound lane that sources contact data, configures calling tools, keeps follow-up moving, and reports weekly performance signals.',
                'feature_groups' => [
                    [
                        'title' => 'Prospecting setup',
                        'items' => [
                            'Data Scraping & Skip Tracing',
                            'Up to 5 Cities / Zip Codes',
                            'Daily Scheduling + VoIP Config',
                        ],
                    ],
                    [
                        'title' => 'Follow-up engine',
                        'items' => [
                            'CRM Setup + KPI Automation',
                            'Email, Text & VoiceFlow Follow-ups',
                            'Weekly Performance Reports',
                        ],
                    ],
                    [
                        'title' => 'Growth support',
                        'items' => [
                            'Key Area Territory Manager',
                            'Social Media Management',
                        ],
                    ],
                ],
                'trust_note' => 'Built for teams that want more qualified conversations before spending time on appointments.',
            ],
            'social-media-mgmt' => [
                'summary' => 'Turn your real estate brand into a daily content engine across video, engagement, ads, and local visibility.',
                'highlights' => [
                    'Daily content rhythm',
                    'Cross-channel coverage',
                    'Review cadence included',
                ],
                'best_for' => 'Agents who need consistent social presence, campaign execution, and lead-focused brand support.',
                'what_you_get' => 'A social operations package that plans, produces, publishes, engages, and reviews your content so your brand stays visible between client conversations.',
                'feature_groups' => [
                    [
                        'title' => 'Content production',
                        'items' => [
                            'IG, FB, LinkedIn + TikTok',
                            'Dedicated Social Media Strategy',
                            'Stories, Highlights + Reels',
                        ],
                    ],
                    [
                        'title' => 'Audience growth',
                        'items' => [
                            'Audience Engagement Management',
                            'Custom Ads Creation + Management',
                            'Monthly + Quarterly Review',
                        ],
                    ],
                    [
                        'title' => 'Brand and lead support',
                        'items' => [
                            'AI Branding & Lead Body Content',
                            'Brand Development + Web SEO',
                            'ISA Cold Calling Support',
                        ],
                    ],
                ],
                'trust_note' => 'Designed to keep your channels active, credible, and aligned with the way real estate clients research agents.',
            ],
            'individual-va' => [
                'summary' => 'Flexible VA capacity for admin, CRM, design, data, and automation tasks without a long-term commitment.',
                'highlights' => [
                    'Hourly flexibility',
                    'Task-based delegation',
                    'Priority support',
                ],
                'best_for' => 'Solo agents, lean teams, and operators who need skilled help on demand instead of another fixed seat.',
                'what_you_get' => 'A flexible assistant lane for recurring admin work, one-off projects, CRM cleanup, creative tasks, and workflow support as your workload changes.',
                'feature_groups' => [
                    [
                        'title' => 'Admin support',
                        'items' => [
                            'Needs Assessment + Onboarding',
                            '24/7 Discord Priority Support',
                            'Appointment Setting + Calendar',
                            'CRM Support + Email Management',
                        ],
                    ],
                    [
                        'title' => 'Web and creative help',
                        'items' => [
                            'WordPress / Shopify + SEO & AEO',
                            'Graphic Design + Logo Creation',
                        ],
                    ],
                    [
                        'title' => 'Data and workflows',
                        'items' => [
                            'Data Entry + Lead Generation',
                            'Automations, Workflows + Templates',
                        ],
                    ],
                ],
                'trust_note' => 'A clean way to add capacity when the work is real, but the need is not always full time.',
            ],
        ];
    }
}
