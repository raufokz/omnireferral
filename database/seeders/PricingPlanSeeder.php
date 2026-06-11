<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use Illuminate\Database\Seeder;

class PricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'category' => 'real_estate',
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
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'category' => 'real_estate',
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
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category' => 'real_estate',
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
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'category' => 'virtual_assistance',
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
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'category' => 'virtual_assistance',
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
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category' => 'virtual_assistance',
                'slug' => 'va-individual',
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
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($plans as $plan) {
            PricingPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
