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
                    'name' => 'Quick Lead',
                    'tier' => 'Starter Tier',
                    'value_price' => 999,
                    'price' => 499,
                    'price_note' => '/ Yearly - City-Focused Entry',
                    'summary' => 'Entry path built for city-focused agents who want verified contacts and lean marketing.',
                    'features' => [
                        '20% Referral Fee',
                        '16-20 Referrals / Year',
                        'Select up to 2 cities or ZIP codes',
                        'AI bots plus caller-captured referrals',
                        '1-step verified prospects',
                        'Basic organic marketing support',
                        'Basic profile showcase',
                        'Email support',
                        'Closing guarantee under 150 days',
                    ],
                    'cta_label' => 'Get Started',
                    'is_featured' => false,
                ],
                [
                    'slug' => 'power-leads',
                    'name' => 'Power Lead',
                    'tier' => 'Growth Tier',
                    'value_price' => 1497,
                    'price' => 797,
                    'price_note' => '/ One-Time - Most Popular Plan',
                    'summary' => 'Balanced qualification depth, faster routing, and virtual assistance support.',
                    'features' => [
                        'Access to open enrollment and 2x referrals for 2026 campaigns',
                        '15% referral fee',
                        '40+ quality referrals through December 2026',
                        '2-step verified prospects',
                        'AI plus human-powered referrals',
                        'Select up to 5 cities or ZIP codes',
                        '3 hours per week of virtual assistance',
                        'Key accounts manager for support and follow-ups',
                        'Email and text support',
                        'Organic and business-network marketing',
                        'Showcase 3 listings per quarter',
                        'Premium profile showcase',
                        'Quarterly profile scorecard',
                        '3 high-revenue ads per quarter',
                        'Closing guarantee under 120 days',
                    ],
                    'cta_label' => 'Get Started',
                    'is_featured' => true,
                ],
                [
                    'slug' => 'prime-leads',
                    'name' => 'Prime Lead',
                    'tier' => 'Elite Tier',
                    'value_price' => 3299,
                    'price' => 2299,
                    'price_note' => '/ One-Time - Full-Service Premium',
                    'summary' => 'Priority routing, connected referrals, and the deepest marketing and support coverage.',
                    'features' => [
                        'Priority access to open enrollment and 2x referrals for 2026 campaigns',
                        '10% referral fee',
                        '260+ high-quality referrals through December 2026',
                        '3-step verified prospects',
                        'AI plus human plus live connected referrals',
                        'Select up to 10 cities or ZIP codes',
                        '12 hours per week of virtual assistance for cold calling and follow-ups',
                        'Priority support on call, text, and email',
                        'Full marketing toolkit, templates, and premium SEO',
                        'Showcase 5 listings per quarter',
                        'SEO-optimized premium realtor profile',
                        'Monthly profile scorecard',
                        'Closing guarantee under 90 days',
                        '5 high-revenue ads per quarter',
                        'Lead strategy planning',
                        'Lead forecasting and optimization',
                        'Lead reporting and strategy call each month',
                        'Workflow tips and templates to boost socials',
                        'Dedicated accounts manager',
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
