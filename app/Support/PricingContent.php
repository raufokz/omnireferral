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
                $grouped[$plan->category][] = [
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
                ];
            }

            return $grouped;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function fallbackPlans(): array
    {
        return [
            'real_estate' => [
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
            ],
            'virtual_assistance' => [
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
            ],
        ];
    }
}
