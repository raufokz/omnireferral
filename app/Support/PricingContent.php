<?php

namespace App\Support;

class PricingContent
{
    public static function plans(): array
    {
        return [
            'real_estate' => [
                [
                    'slug' => 'quick-leads',
                    'name' => 'Starter',
                    'tier' => 'Starter Tier',
                    'value_price' => 999,
                    'price' => 499,
                    'price_note' => '/ month - Up to 5 Cities/ZIP Codes',
                    'summary' => 'Launch package built for agents who want active referral flow, outreach support, and clean follow-up.',
                    'features' => [
                        'Qualified Buyer/Seller Referrals Per Month (Active Buyers & Sellers)',
                        'AI + Human Powered Outreach (Cold Calling + Marketing)',
                        'Multi-Channel Lead Generation (Facebook, Google, Direct Outreach)',
                        'Select up to 5 cities or ZIP codes',
                        '15% Referral Fee Only on Closed Deals',
                        'Dedicated Account Manager (Weekly Updates & Follow-Ups)',
                        'Priority Support (Call + SMS + Email)',
                        'List Up to 2 Active Listings on Our Platform 🏡',
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
                    'price' => 797,
                    'price_note' => '/ month - Up to 10 Cities/ZIP Codes',
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
                    'price' => 2299,
                    'price_note' => '/ month - Up to 15 Cities/ZIP Codes',
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
                    'slug' => 'va-starter',
                    'name' => 'Cold Calling',
                    'tier' => 'ISA Support',
                    'price' => 999,
                    'price_note' => '/ month - Automated Sales',
                    'summary' => 'High-touch ISA outreach with guaranteed seller appointments and live transfers.',
                    'features' => [
                        'Guaranteed seller appointments, live transfers, plus buyer and investor outreach',
                        '1 trained cold caller and sales growth specialist',
                        'Data scraping, skip tracing, homeowner data, FSBOs, and vendor lists',
                        'Strategic profiling across partner networks',
                        'SEO-optimized profiling inside real-estate networks',
                        '2+ years experienced ISA support across wholesaling, real estate, and mortgage',
                        'Select up to 5 cities or ZIP codes',
                        'Unlimited dialer data and VOIP included',
                        'CRM support including text and email follow-ups',
                        'Workflows, follow-up templates, and process tips',
                        'Key accounts manager',
                    ],
                    'cta_label' => 'Get Overview',
                    'cta_url' => null,
                    'is_featured' => false,
                ],
                [
                    'slug' => 'va-growth',
                    'name' => 'Social Media',
                    'tier' => 'Full Package',
                    'price' => 1499,
                    'price_note' => '/ month - Full Social Package',
                    'summary' => 'Premium social content, engagement, and ads management aligned to campaigns.',
                    'features' => [
                        'High-quality posts, reels, carousels, and videos each week',
                        'Organic lead and engagement support with stronger CTAs',
                        'Professional formatting for client-provided content',
                        'Monthly content calendar aligned with campaigns',
                        'Platform-specific planning for Instagram, Facebook, LinkedIn, and TikTok',
                        'Dedicated social media strategy',
                        'Story posting and highlight management',
                        'Audience engagement support',
                        'Custom ad creation and management',
                        'Branding, logo design, and workflow support',
                        'Web development and maintenance for WordPress and Shopify',
                    ],
                    'cta_label' => 'Enroll Now',
                    'cta_url' => null,
                    'is_featured' => true,
                ],
                [
                    'slug' => 'va-individual',
                    'name' => 'Individual VA',
                    'tier' => 'Virtual Assist',
                    'price' => 8,
                    'price_note' => '/ hour - Flexible Engagement',
                    'summary' => 'Flexible hourly support for transactions, outreach, and creative work.',
                    'features' => [
                        'Transaction coordination and social media help',
                        'Outbound and inbound calling specialists',
                        'Receptionist and front-desk support',
                        'Appointment scheduling and chat support',
                        'CRM support for tasks and email follow-ups',
                        'Web development and maintenance for WordPress and Shopify',
                        'AI automations, ads management, and scaling support',
                        'Graphic design and logo design',
                        'Digital marketing and lead generation',
                        'Automations, workflows, and text or email templates',
                    ],
                    'cta_label' => 'Go Prime',
                    'cta_url' => null,
                    'is_featured' => false,
                ],
            ],
        ];
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
}
