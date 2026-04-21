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
                    'price_note' => '/ Yearly - City-Focused Entry',
                    'summary' => 'Launch package built for agents who want active referral flow, outreach support, and clean follow-up.',
                    'features' => [
                        'Qualified buyer and seller referrals per month (active buyers and sellers)',
                        'AI plus human powered outreach (cold calling plus marketing)',
                        'Multi-channel lead generation (Facebook, Google, direct outreach)',
                        '15% referral fee only on closed deals',
                        'Dedicated account manager (weekly updates and follow-ups)',
                        'Priority support (call, SMS, and email)',
                        'List up to 2 active listings on our platform',
                        'Organic exposure to active buyer network',
                        'Basic lead nurturing and follow-up system',
                        'Monthly performance report',
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
                    'price_note' => '/ One-Time - Most Popular Plan',
                    'summary' => 'Expansion package with assigned ISA support, JV-ready opportunities, and stronger routing priority.',
                    'features' => [
                        'Direct connection with buyers, sellers, and investors (warm plus active opportunities)',
                        'Dedicated senior account manager (wholesaler-level expertise)',
                        'JV deal opportunities with assigned wholesaler (terms mutually agreed)',
                        '1 full-time ISA (cold caller) working your territory',
                        'Exclusive lead flow from your selected areas',
                        'Select up to 5 cities or ZIP codes',
                        '7% referral fee only on closed deals',
                        'Priority deal matching (buyer, seller, and investor)',
                        'Higher intent, multi-step verified prospects',
                        'Advanced lead nurturing and follow-up sequences',
                        'Weekly strategy and pipeline updates',
                        'Faster lead response and routing system',
                        'Enhanced profile visibility and listing boost',
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
                    'price_note' => '/ One-Time - Full-Service Premium',
                    'summary' => 'Everything in Growth, plus full-team execution, live transfers, and front-of-queue referral access.',
                    'features' => [
                        'Dedicated senior wholesaler assigned to your account',
                        'Full-time virtual assistant (also your go-to account manager)',
                        '2 full-time ISAs (cold callers) working your market daily',
                        'Direct live call transfers (hot leads instantly connected)',
                        'Priority access to all high-intent referrals (front of queue)',
                        'Advanced JV deal flow plus off-market opportunities',
                        'Full CRM access (GoHighLevel) fully built and automated',
                        'Funnels, pipelines, automations, SMS plus email systems included',
                        'Expanded territory (up to 10 cities or ZIP codes)',
                        '5% referral fee only on closed deals',
                        'Investor network access (cash buyers plus off-market deals)',
                        'Unlimited listings on platform plus featured placement',
                        'AI-powered lead scoring plus priority routing',
                        'Weekly strategy calls plus monthly growth planning',
                        'Performance dashboard plus lead forecasting system',
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
