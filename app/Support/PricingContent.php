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
        $normalizedSlug = self::enhancementSlug($slug);

        foreach (self::plans() as $category => $plans) {
            foreach ($plans as $plan) {
                $planSlug = (string) ($plan['slug'] ?? '');
                if (self::enhancementSlug($planSlug) === $normalizedSlug) {
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
            $seenSlugs = [];
            foreach ($plans as $plan) {
                $enrichedPlan = self::enrichPlan([
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
                $normalizedSlug = (string) ($enrichedPlan['slug'] ?? $plan->slug);

                if (isset($seenSlugs[$normalizedSlug])) {
                    continue;
                }

                $seenSlugs[$normalizedSlug] = true;
                $grouped[$plan->category][] = $enrichedPlan;
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
                    'slug' => 'starter-leads',
                    'name' => 'Starter Lead',
                    'tier' => 'Starter',
                    'value_price' => null,
                    'price' => 499,
                    'price_note' => '/ Yearly',
                    'summary' => 'Starter-friendly. City-focused.',
                    'features' => [
                        '20% Referral Fee',
                        '16-20 Referrals / Year',
                        'First TWO (2) Referrals FREE',
                        'Better-qualified referrals',
                        'Select up to 2 Cities or ZIP Codes',
                        'AI Bots + Callers Captured Referrals',
                        '1-Step Verified Prospects',
                        'Basic + Organic Marketing',
                        'Basic Profile Showcase',
                        'Email Support',
                    ],
                    'cta_label' => 'EXPLORE PLAN',
                    'is_featured' => false,
                ],
                [
                    'slug' => 'growth-leads',
                    'name' => 'Growth Lead',
                    'tier' => 'Growth',
                    'value_price' => null,
                    'price' => 797,
                    'price_note' => '/ One-Time',
                    'summary' => 'Multi-city. Scalable support.',
                    'features' => [
                        'Access to Open Enrollment & 2x Referrals (Q3-Q4, 2026)',
                        '15% Referral Fee',
                        'First FIVE (5) Referrals FREE',
                        '30+ Referrals Till Dec, 2026',
                        'Select up to 5 Cities or ZIP Codes',
                        '3 Hrs / Week of Virtual Assistance',
                        'Email + Text Support',
                        '2-Steps Verified Prospects',
                        'AI + Human Powered Referrals',
                        'Organic + Business Network Marketing',
                        'Virtual pre-screening before showings',
                        'Showcase 5 Listings On Websites',
                        'Premium Profile Showcase',
                        'Quarterly Profile Scorecard',
                        '3 High Revenue Ads / Quarter',
                    ],
                    'cta_label' => 'EXPLORE PLAN',
                    'is_featured' => true,
                ],
                [
                    'slug' => 'elite-leads',
                    'name' => 'Elite Lead',
                    'tier' => 'Elite',
                    'value_price' => null,
                    'price' => 2299,
                    'price_note' => '/ One-Time',
                    'summary' => 'Premium reach. Full-service.',
                    'features' => [
                        'Priority Access to Open Enrollment & 2x Referrals (Q3-Q4, 2026)',
                        '10% Referral Fee',
                        'First NINE (9) Referrals FREE',
                        '50+ Referrals Till Dec, 2026',
                        'Select up to 10 Cities or ZIP Codes',
                        '15 Hrs / Week of Virtual Assistance',
                        'Priority Support on Call + Text + Email',
                        '3-Steps Verified Prospects',
                        'AI + Human + Live Connected Referrals',
                        'Full Marketing Toolkit + Templates + Premium SEO',
                        'Showcase 5 Listings on Website',
                        'SEO Optimized Premium Realtor Profile',
                        'Monthly Profile Scorecard',
                        '5 High Revenue Ads / Quarter',
                        'Virtual pre-screening before showings',
                        'Area-specific leads',
                        'Appointment-ready buyers/sellers',
                        'Lead Strategy Planning',
                        'Lead Forecasting & Optimization',
                        'Lead Reporting & Strategy Call - Monthly',
                        'Workflow Tips + Templates to Boost Socials',
                        'Dedicated Accounts Manager',
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
                    'price_note' => '/ Month',
                    'summary' => 'Dedicated ISA Sales Agent',
                    'features' => [
                        'Data Scraping & Skip Tracing',
                        'Up to 5 Cities / 2 to Callers',
                        'Daily Scheduling + VoIP Config',
                        'CRM Setup + KPI Automation',
                        'Email, Text & Voiceflow Follow-ups',
                        'Weekly Performance Reports',
                        'Key Area Territory Manager',
                        'Social Media Management',
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
                    'price_note' => '/ Month',
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
                        'No Cost Calling Support',
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
                    'price_note' => '/ Hour',
                    'summary' => 'Flexible Hourly VA Pricing',
                    'features' => [
                        'Needs Assessment + Onboarding',
                        '24/7 Discord Priority Support',
                        'Appointment Setting + Calendar',
                        'CRM Support + Email Management',
                        'WordPress / Shopify + SEO & ASO',
                        'Graphic Design + Logo Creation',
                        'Data Entry + Lead Generation',
                        'Automations, Workflows + Templates',
                    ],
                    'cta_label' => 'EXPLORE PLAN',
                    'cta_url' => null,
                    'is_featured' => false,
                ],
            ]),
        ];
    }

    private static function enrichPlan(array $plan): array
    {
        $plan = self::normalizeLegacyPlan($plan);
        $slug = (string) ($plan['slug'] ?? '');
        $enhancementKey = self::enhancementSlug($slug);
        $enhancement = self::planEnhancements()[$enhancementKey] ?? null;

        if (! $enhancement) {
            return $plan;
        }

        return array_merge($plan, $enhancement);
    }

    private static function normalizeLegacyPlan(array $plan): array
    {
        $slug = (string) ($plan['slug'] ?? '');
        $name = (string) ($plan['name'] ?? '');
        $tier = (string) ($plan['tier'] ?? '');

        return match ($slug) {
            'quick-leads' => array_merge($plan, [
                'slug' => 'starter-leads',
                'name' => $name === '' ? 'Starter Lead' : str_replace('Quick Lead', 'Starter Lead', $name),
                'tier' => $tier === '' || $tier === 'Quick' ? 'Starter' : $tier,
            ]),
            'power-leads' => array_merge($plan, [
                'slug' => 'growth-leads',
                'name' => $name === '' ? 'Growth Lead' : str_replace('Power Lead', 'Growth Lead', $name),
                'tier' => in_array($tier, ['', 'Power', 'Most Popular', 'Growth'], true) ? 'Growth' : $tier,
            ]),
            'prime-leads' => array_merge($plan, [
                'slug' => 'elite-leads',
                'name' => $name === '' ? 'Elite Lead' : str_replace('Prime Lead', 'Elite Lead', $name),
                'tier' => in_array($tier, ['', 'Prime', 'Premium', 'Elite'], true) ? 'Elite' : $tier,
            ]),
            default => $plan,
        };
    }

    private static function enhancementSlug(string $slug): string
    {
        return match ($slug) {
            'starter-leads', 'quick-leads' => 'starter-leads',
            'growth-leads', 'power-leads' => 'growth-leads',
            'elite-leads', 'prime-leads' => 'elite-leads',
            'va-calling', 'va-starter', 'cold-calling-isa' => 'cold-calling-isa',
            'va-social', 'va-growth', 'social-media-mgmt' => 'social-media-mgmt',
            'va-individual', 'individual-va' => 'individual-va',
            default => $slug,
        };
    }

    private static function planEnhancements(): array
    {
        return [
            'starter-leads' => [
                'is_featured' => false,
                'badge' => 'Starter',
                'card_tag' => 'Starter',
                'ribbon_label' => 'Starter',
                'price' => 369,
                'price_note' => '/ Yearly',
                'value_statement' => 'Starter-friendly. City-focused.',
                'card_best_for' => 'Starter-friendly. City-focused.',
                'billing_label' => '/ Yearly',
                'summary' => 'Starter-friendly. City-focused.',
                'card_description' => 'Perfect for agents entering new markets who need verified referrals, local visibility, and predictable lead opportunities.',
                'features' => [
                    '7x Your Referrals in 3rd & 4th Quarter of 2026',
                    '20% Referral Fee',
                    '1-2 Exclusive Referrals / Mo',
                    'Exclusive Area Coverage (2 Cities or ZIP Codes)',
                    '1 Hour / Week of Virtual Assistance by Assigned Accounts Manager',
                    'Email Support',
                    '1-Step Verified Prospects',
                    'AI Powered Verification',
                    'Basic + Organic Marketing',
                    'Virtual pre-screening before showings',
                    'Basic Listings Portal Access',
                    'Basic Profile Optimization',
                    'Quarterly Profile Health Update and Tips',
                    'Accept or Reject Referrals in Portal',
                ],
                'highlights' => ['20% Referral Fee', '1-2 Exclusive Referrals / Mo', '1 Hr / Week of Virtual Assistance'],
                'best_for' => 'Starter-friendly. City-focused.',
                'what_you_get' => 'Starter-friendly. City-focused.',
                'package_benefits' => [
                    'Access to Open Enrollment & 2x Referrals (Q3-Q4, 2026)',
                    '20% Referral Fee',
                    '1-2 Exclusive Referrals / Mo',
                    '15+ Referrals Till Dec, 2026',
                    'Select up to 2 Cities or ZIP Codes',
                    '1 Hr / Week of Virtual Assistance',
                    'Email Support',
                    '1-Step Verified Prospects',
                    'AI Powered Verification',
                    'Basic + Organic Marketing',
                    'Virtual pre-screening before showings',
                    'Basic Listings Portal Access',
                    'Basic Profile Optimization',
                    'Quarterly Profile Health Update and Tips',
                ],
                'guarantee_label' => [
                    'Closing Guarantee Under 150 Days',
                    'First TWO (2) Referrals for FREE',
                    '150 Day Money Back Guarantee',
                    'Fee credited back toward your first referral payment',
                ],
                'savings_label' => [
                    'Fee credited back toward your first referral payment',
                ],
                'cta_label' => 'GO STARTER',
                'feature_groups' => [
                    ['title' => 'Features', 'items' => [
                        'Access to Open Enrollment & 2x Referrals (Q3-Q4, 2026)',
                        '20% Referral Fee',
                        '1-2 Exclusive Referrals / Mo',
                        '15+ Referrals Till Dec, 2026',
                        'Select up to 2 Cities or ZIP Codes',
                        '1 Hr / Week of Virtual Assistance',
                        'Email Support',
                        '1-Step Verified Prospects',
                        'AI + Human Powered Verification',
                        'Basic + Organic Marketing',
                        'Virtual pre-screening before showings',
                        'Basic Listings Portal Access',
                        'Basic Profile Optimization',
                        'Quarterly Profile Health Update and Tips',
                    ]],
                ],
                'after_submission' => [
                    'Your survey is reviewed for Active License Status, territory, offer type, and service-area fit.',
                    'Your selected areas and routing rules are confirmed.',
                    'A growth-focused handoff workflow is prepared for you.',
                ],
                'support_details' => 'Email Support',
                'trust_indicators' => ['Closing Guarantee Under 150 Days', '1-Step Verified Prospects', 'AI + Human Powered Verification'],
                'trust_note' => 'Closing Guarantee Under 150 Days',
            ],
            'growth-leads' => [
                'is_featured' => true,
                'badge' => 'Most Popular',
                'card_tag' => 'Most Popular',
                'ribbon_label' => 'Growth Tier',
                'price' => 697,
                'price_note' => '/ One-Time',
                'value_statement' => 'Multi-city. Scalable support.',
                'card_best_for' => 'Multi-city. Scalable support.',
                'billing_label' => '/ One-Time',
                'summary' => 'Multi-city. Scalable support.',
                'card_description' => 'Designed for growing agents & teams that need more referrals, broader coverage, virtual assistance, and stronger lead qualification.',
                'features' => [
                    '7x Your Referrals in 3rd & 4th Quarter of 2026',
                    '15% Referral Fee',
                    '2-5 Exclusive Referrals / Mo',
                    'Exclusive Area Coverage (5 Cities or ZIP Codes)',
                    '3 Hours / Week of Virtual Assistance by Assigned Accounts Manager',
                    'Email + Text Support',
                    '2-Steps Verified Prospects',
                    'AI + Human Powered Verification',
                    'Organic + Business Network Marketing',
                    'Virtual pre-screening before showings',
                    'Premium Listings Portal Access',
                    'Preferred Agents Profile Optimization & SEO',
                    'Quarterly Profile Health Update and Tips',
                    'Exclusive Lead Pool Portal Access',
                    'Accept, Nurture, Reject or Re-Verify Referrals in Portal',
                ],
                'highlights' => ['15% Referral Fee', 'First FIVE (5) Referrals FREE', '3 Hrs / Week of Virtual Assistance'],
                'best_for' => 'Multi-city. Scalable support.',
                'what_you_get' => 'Multi-city. Scalable support.',
                'package_benefits' => [
                    'Access to Open Enrollment & 2x Referrals (Q3-Q4, 2026)',
                    '15% Referral Fee',
                    'First FIVE (5) Referrals FREE',
                    '30+ Referrals Till Dec, 2026',
                    'Select up to 5 Cities or ZIP Codes',
                    '3 Hrs / Week of Virtual Assistance',
                    'Email + Text Support',
                    '2-Steps Verified Prospects',
                    'AI + Human Powered Referrals',
                    'Organic + Business Network Marketing',
                    'Virtual pre-screening before showings',
                    'Showcase 5 Listings On Websites',
                    'Premium Profile Showcase',
                    'Quarterly Profile Scorecard',
                    '3 High Revenue Ads / Quarter',
                ],
                'guarantee_label' => [
                    'Closing Guarantee Under 120 Days',
                    'First FIVE (5) Referrals for FREE',
                    '150 Day Money Back Guarantee',
                ],
                'savings_label' => [
                    'Save $400 on Yearly',
                    'Fee credited back toward your first referral payment',
                ],
                'cta_label' => 'GO GROWTH',
                'feature_groups' => [
                    ['title' => 'Features', 'items' => [
                        'Access to Open Enrollment & 2x Referrals (Q3-Q4, 2026)',
                        '15% Referral Fee',
                        'First FIVE (5) Referrals FREE',
                        '30+ Referrals Till Dec, 2026',
                        'Select up to 5 Cities or ZIP Codes',
                        '3 Hrs / Week of Virtual Assistance',
                        'Email + Text Support',
                        '2-Steps Verified Prospects',
                        'AI + Human Powered Referrals',
                        'Organic + Business Network Marketing',
                        'Virtual pre-screening before showings',
                        'Showcase 5 Listings On Websites',
                        'Premium Profile Showcase',
                        'Quarterly Profile Scorecard',
                        '3 High Revenue Ads / Quarter',
                    ]],
                ],
                'after_submission' => [
                    'Your survey is reviewed for Active License Status, territory, offer type, and service-area fit.',
                    'Your selected areas and routing rules are confirmed.',
                    'A growth-focused handoff workflow is prepared for you.',
                ],
                'support_details' => 'Email + Text Support',
                'trust_indicators' => ['Closing Guarantee Under 120 Days', '2-Steps Verified Prospects', 'AI + Human Powered Referrals'],
                'trust_note' => 'Closing Guarantee Under 120 Days',
            ],
            'elite-leads' => [
                'badge' => 'Elite',
                'card_tag' => 'Elite',
                'ribbon_label' => 'Elite',
                'price' => 1979,
                'price_note' => '/ One-Time',
                'value_statement' => 'Premium reach. Full-service.',
                'card_best_for' => 'Premium reach. Full-service.',
                'billing_label' => '/ One-Time',
                'summary' => 'Premium reach. Full-service.',
                'card_description' => 'Built for top-producing agents seeking maximum exposure, priority support, premium placement, and advanced lead qualification.',
                'features' => [
                    '10x Your Referrals in 3rd & 4th Quarter of 2026',
                    '10% Referral Fee',
                    '5-10 Exclusive Referrals / Mo',
                    'Exclusive Area Coverage (10 Cities or ZIP Codes)',
                    '15 Hours / Week of Virtual Assistance by Assigned Accounts Manager',
                    'Priority Support on Call + Text + Email',
                    '3-Steps Verified Prospects',
                    'AI + Human + Live Connected Verification',
                    'Full Marketing Toolkit + Templates + Premium SEO',
                    'Virtual pre-screening before showings',
                    'Premium Listings Portal Access',
                    'Premium Profile Optimization & SEO',
                    'Monthly Profile Health Update and Tips',
                    'Exclusive Lead Pool Portal Access',
                    'Accept, Nurture, Reject or Re-Verify Referrals in Portal',
                    'Dedicated Accounts Manager',
                ],
                'highlights' => ['10% Referral Fee', 'First NINE (9) Referrals FREE', '15 Hrs / Week of Virtual Assistance'],
                'best_for' => 'Premium reach. Full-service.',
                'what_you_get' => 'Premium reach. Full-service.',
                'package_benefits' => [
                    'Priority Access to Open Enrollment & 2x Referrals (Q3-Q4, 2026)',
                    '10% Referral Fee',
                    '50+ Referrals Till Dec, 2026',
                    'Select up to 10 Cities or ZIP Codes',
                    '15 Hrs / Week of Virtual Assistance',
                    'Priority Support on Call + Text + Email',
                    '3-Steps Verified Prospects',
                    'AI + Human Powered Verification',
                    'Full Marketing Toolkit + Templates + Premium SEO',
                    'Virtual pre-screening before showings',
                    'Premium Listings Portal Access',
                    'Premium Profile Optimization & SEO',
                    'Monthly Profile Health Update and Tips',
                    'Exclusive Lead Pool Portal Access',
                    'Accept, Nurture, Reject or Re-Verify Referrals in Portal',
                    'Dedicated Accounts Manager',
                ],
                'guarantee_label' => [
                    'Closing Guarantee Under 90 Days',
                    'First NINE Referrals for FREE',
                    '150 Day Money Back Guarantee',
                    'Fee credited back toward your first referral payment',
                ],
                'savings_label' => [],
                'cta_label' => 'GO ELITE',
                'feature_groups' => [
                    ['title' => 'Features', 'items' => [
                        'Priority Access to Open Enrollment & 2x Referrals (Q3-Q4, 2026)',
                        '10% Referral Fee',
                        '50+ Referrals Till Dec, 2026',
                        'Select up to 10 Cities or ZIP Codes',
                        '15 Hrs / Week of Virtual Assistance',
                        'Priority Support on Call + Text + Email',
                        '3-Steps Verified Prospects',
                        'AI + Human Powered Verification',
                        'Full Marketing Toolkit + Templates + Premium SEO',
                        'Virtual pre-screening before showings',
                        'Premium Listings Portal Access',
                        'Premium Profile Optimization & SEO',
                        'Monthly Profile Health Update and Tips',
                        'Exclusive Lead Pool Portal Access',
                        'Accept, Nurture, Reject or Re-Verify Referrals in Portal',
                        'Dedicated Accounts Manager',
                    ]],
                ],
                'after_submission' => [
                    'Your survey is reviewed for Active License Status, territory, offer type, and service-area fit.',
                    'Your selected areas and routing rules are confirmed.',
                    'A growth-focused handoff workflow is prepared for you.',
                ],
                'support_details' => 'Priority Support on Call + Text + Email',
                'trust_indicators' => ['Closing Guarantee Under 90 Days', '3-Steps Verified Prospects', 'Live Connected Referrals'],
                'trust_note' => 'Closing Guarantee Under 90 Days',
            ],
            'cold-calling-isa' => [
                'badge' => 'Dedicated ISA',
                'card_tag' => 'Dedicated ISA',
                'price' => 1999,
                'price_note' => '/ Month',
                'value_statement' => 'Dedicated ISA Sales Agent',
                'card_best_for' => 'Dedicated ISA Sales Agent',
                'billing_label' => '/ Month',
                'summary' => 'Dedicated ISA Sales Agent',
                'card_description' => 'Professional outbound support focused on appointment setting, lead nurturing, follow-ups, and pipeline growth.',
                'features' => [
                    'Data Scraping & Skip Tracing',
                    'Up to 5 Cities / 2 to Callers',
                    'Daily Scheduling + VoIP Config',
                    'CRM Setup + KPI Automation',
                    'Email, Text & Voiceflow Follow-ups',
                    'Weekly Performance Reports',
                    'Key Area Territory Manager',
                    'Social Media Management',
                ],
                'highlights' => ['Data Scraping & Skip Tracing', 'Daily Scheduling + VoIP Config', 'Weekly Performance Reports'],
                'best_for' => 'Dedicated ISA Sales Agent',
                'what_you_get' => 'Dedicated ISA Sales Agent',
                'package_benefits' => [
                    'Data Scraping & Skip Tracing',
                    'Up to 5 Cities / 2 to Callers',
                    'Daily Scheduling + VoIP Config',
                    'CRM Setup + KPI Automation',
                    'Email, Text & Voiceflow Follow-ups',
                    'Weekly Performance Reports',
                    'Key Area Territory Manager',
                    'Social Media Management',
                ],
                'cta_label' => 'Explore Plan',
                'feature_groups' => [
                    ['title' => 'Features', 'items' => [
                        'Data Scraping & Skip Tracing',
                        'Up to 5 Cities / 2 to Callers',
                        'Daily Scheduling + VoIP Config',
                        'CRM Setup + KPI Automation',
                        'Email, Text & Voiceflow Follow-ups',
                        'Weekly Performance Reports',
                        'Key Area Territory Manager',
                        'Social Media Management',
                    ]],
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
                'price' => 1499,
                'price_note' => '/ Month',
                'value_statement' => 'Daily Long + Short Form Videos',
                'card_best_for' => 'Daily Long + Short Form Videos',
                'billing_label' => '/ Month',
                'summary' => 'Daily Long + Short Form Videos',
                'card_description' => 'Done-for-you content creation, audience growth, engagement management, and brand visibility.',
                'features' => [
                    'IG, FB, LinkedIn + TikTok',
                    'Dedicated Social Media Strategy',
                    'Stories, Highlights + Reels',
                    'Audience Engagement Management',
                    'Custom Ads Creation + Management',
                    'AI Branding & Lead Body Content',
                    'Brand Development + Web SEO',
                    'Monthly + Quarterly Review',
                    'No Cost Calling Support',
                ],
                'highlights' => ['IG, FB, LinkedIn + TikTok', 'Dedicated Social Media Strategy', 'Monthly + Quarterly Review'],
                'best_for' => 'Daily Long + Short Form Videos',
                'what_you_get' => 'Daily Long + Short Form Videos',
                'package_benefits' => [
                    'IG, FB, LinkedIn + TikTok',
                    'Dedicated Social Media Strategy',
                    'Stories, Highlights + Reels',
                    'Audience Engagement Management',
                    'Custom Ads Creation + Management',
                    'AI Branding & Lead Body Content',
                    'Brand Development + Web SEO',
                    'Monthly + Quarterly Review',
                    'No Cost Calling Support',
                ],
                'cta_label' => 'Explore Plan',
                'feature_groups' => [
                    ['title' => 'Features', 'items' => [
                        'IG, FB, LinkedIn + TikTok',
                        'Dedicated Social Media Strategy',
                        'Stories, Highlights + Reels',
                        'Audience Engagement Management',
                        'Custom Ads Creation + Management',
                        'AI Branding & Lead Body Content',
                        'Brand Development + Web SEO',
                        'Monthly + Quarterly Review',
                        'No Cost Calling Support',
                    ]],
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
                'slug' => 'individual-va',
                'badge' => 'Flexible Support',
                'card_tag' => 'Flexible Support',
                'price' => 8,
                'price_note' => '/ Hour',
                'value_statement' => 'Flexible Hourly Billing.',
                'card_best_for' => 'Flexible Hourly Billing.',
                'billing_label' => '/ Hour',
                'summary' => 'Flexible Hourly VA Pricing',
                'card_description' => 'Dedicated virtual support for CRM updates, scheduling, administrative tasks, and daily business operations.',
                'features' => [
                    'Needs Assessment + Onboarding',
                    '24/7 Discord Priority Support',
                    'Appointment Setting + Calendar',
                    'CRM Support + Email Management',
                    'WordPress / Shopify + SEO & ASO',
                    'Graphic Design + Logo Creation',
                    'Data Entry + Lead Generation',
                    'Automations, Workflows + Templates',
                ],
                'highlights' => ['24/7 Discord Priority Support', 'CRM Support + Email Management', 'No Long-Term Commitment'],
                'best_for' => 'Flexible Hourly VA Pricing',
                'what_you_get' => 'Flexible Hourly VA Pricing',
                'package_benefits' => [
                    'Needs Assessment + Onboarding',
                    '24/7 Discord Priority Support',
                    'Appointment Setting + Calendar',
                    'CRM Support + Email Management',
                    'WordPress / Shopify + SEO & ASO',
                    'Graphic Design + Logo Creation',
                    'Data Entry + Lead Generation',
                    'Automations, Workflows + Templates',
                ],
                'guarantee_label' => 'No Long-Term Commitment',
                'cta_label' => 'Explore Plan',
                'feature_groups' => [
                    ['title' => 'Features', 'items' => [
                        'Needs Assessment + Onboarding',
                        '24/7 Discord Priority Support',
                        'Appointment Setting + Calendar',
                        'CRM Support + Email Management',
                        'WordPress / Shopify + SEO & ASO',
                        'Graphic Design + Logo Creation',
                        'Data Entry + Lead Generation',
                        'Automations, Workflows + Templates',
                    ]],
                ],
                'after_submission' => [
                    'Your support needs and preferred task types are reviewed.',
                    'Scope, hours, and priorities are confirmed.',
                    'Your VA support lane is prepared around your workflow.',
                ],
                'support_details' => 'Individual VA includes needs assessment and flexible hourly support across admin, CRM, and operational tasks.',
                'trust_indicators' => ['No Long-Term Commitment', '24/7 Discord Priority Support', 'Flexible Hourly VA Pricing'],
                'trust_note' => 'No Long-Term Commitment',
            ],
        ];
    }
}
