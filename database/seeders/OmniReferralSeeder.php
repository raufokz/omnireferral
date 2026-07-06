<?php

namespace Database\Seeders;

use App\Models\AffiliateProfile;
use App\Models\Blog;
use App\Models\Lead;
use App\Models\LeadMatch;
use App\Models\Package;
use App\Models\Partner;
use App\Models\Property;
use App\Models\RealtorProfile;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OmniReferralSeeder extends Seeder
{
    public function run(): void
    {
        $realtorImages = collect(glob(public_path('images/realtors/*.png')))
            ->map(fn ($path) => 'images/realtors/'.basename($path))
            ->filter(fn ($path) => ! str_contains($path, 'admin-ajax'))
            ->values();

        $defaultHeadshot = \App\Support\AgentAvatar::defaultStorageHeadshot();

        $packages = collect([
            [
                'name' => 'Cold Calling / ISA',
                'slug' => 'cold-calling-isa',
                'description' => 'Dedicated ISA Sales Agent',
                'category' => 'virtual_assistant',
                'billing_type' => 'monthly',
                'is_featured' => false,
                'is_active' => true,
                'one_time_price' => null,
                'monthly_price' => 1999,
                'hourly_price' => null,
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'ghl_form_url' => 'https://api.leadconnectorhq.com/widget/survey/DAYWVBJkNiVLEfoW740d',
                'ghl_pipeline_stage' => 'cold-calling-isa',
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
                'cta_label' => 'Explore Plan',
                'duration_days' => 30,
                'sort_order' => 1,
            ],
            [
                'name' => 'Social Media Mgmt',
                'slug' => 'social-media-mgmt',
                'description' => 'Daily Long + Short Form Videos',
                'category' => 'virtual_assistant',
                'billing_type' => 'monthly',
                'is_featured' => true,
                'is_active' => true,
                'one_time_price' => null,
                'monthly_price' => 1499,
                'hourly_price' => null,
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'ghl_form_url' => 'https://api.leadconnectorhq.com/widget/survey/NiEcLMPWI084aKiAaNsi',
                'ghl_pipeline_stage' => 'social-media-mgmt',
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
                'cta_label' => 'Explore Plan',
                'duration_days' => 30,
                'sort_order' => 2,
            ],
            [
                'name' => 'Individual VA',
                'slug' => 'individual-va',
                'description' => 'Flexible Hourly VA Pricing',
                'category' => 'virtual_assistant',
                'billing_type' => 'hourly',
                'is_featured' => false,
                'is_active' => true,
                'one_time_price' => null,
                'monthly_price' => null,
                'hourly_price' => 8,
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'ghl_form_url' => 'https://api.leadconnectorhq.com/widget/survey/DAnafQ8CfUsIMsj8Zq4D',
                'ghl_pipeline_stage' => 'individual-va',
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
                'cta_label' => 'Explore Plan',
                'duration_days' => 30,
                'sort_order' => 3,
            ],
        ])->map(fn ($plan) => Package::updateOrCreate(['slug' => $plan['slug']], $plan));

        $coldCallingPlan = $packages->firstWhere('slug', 'cold-calling-isa');
        $socialMediaPlan = $packages->firstWhere('slug', 'social-media-mgmt');
        $individualVaPlan = $packages->firstWhere('slug', 'individual-va');

        $admin = User::updateOrCreate(['email' => 'admin@omnireferrals.com'], [
            'name' => 'Olivia Parker',
            'password' => 'password',
            'phone' => '8005551000',
            'role' => 'admin',
            'status' => 'active',
            'affiliate_code' => 'ADMIN100',
        ]);

        $isa = User::updateOrCreate(['email' => 'isa@omnireferrals.com'], [
            'name' => 'Lena Brooks',
            'password' => 'password',
            'phone' => '8005551011',
            'role' => 'staff',
            'staff_team' => 'isa',
            'status' => 'active',
            'affiliate_code' => 'ISA10011',
        ]);

        $sales = User::updateOrCreate(['email' => 'sales@omnireferrals.com'], [
            'name' => 'Daniel Cruz',
            'password' => 'password',
            'phone' => '8005551012',
            'role' => 'staff',
            'staff_team' => 'sales',
            'status' => 'active',
            'affiliate_code' => 'SAL10012',
        ]);

        User::updateOrCreate(['email' => 'marketing@omnireferrals.com'], [
            'name' => 'Mia Roberts',
            'password' => 'password',
            'phone' => '8005551013',
            'role' => 'staff',
            'staff_team' => 'marketing',
            'status' => 'active',
            'affiliate_code' => 'MKT10013',
        ]);

        User::updateOrCreate(['email' => 'webdev@omnireferrals.com'], [
            'name' => 'Noah Bennett',
            'password' => 'password',
            'phone' => '8005551014',
            'role' => 'staff',
            'staff_team' => 'web_dev',
            'status' => 'active',
            'affiliate_code' => 'WEB10014',
        ]);

        User::updateOrCreate(['email' => 'buyer@omnireferrals.com'], [
            'name' => 'Taylor Morgan',
            'password' => 'password',
            'phone' => '8005552001',
            'role' => 'buyer',
            'status' => 'active',
            'affiliate_code' => 'BUY20001',
        ]);

        User::updateOrCreate(['email' => 'seller@omnireferrals.com'], [
            'name' => 'Jamie Carter',
            'password' => 'password',
            'phone' => '8005552002',
            'role' => 'seller',
            'status' => 'active',
            'affiliate_code' => 'SEL20002',
        ]);

        $agents = collect([
            ['name' => 'Mason Reed', 'email' => 'mason@omnireferrals.com', 'city' => 'Dallas', 'state' => 'TX', 'zip' => '75201', 'brokerage' => 'Reed & Co Realty', 'plan' => $coldCallingPlan?->id],
            ['name' => 'Ava Collins', 'email' => 'ava@omnireferrals.com', 'city' => 'Miami', 'state' => 'FL', 'zip' => '33101', 'brokerage' => 'Collins Coastal Realty', 'plan' => $socialMediaPlan?->id],
            ['name' => 'Ethan Brooks', 'email' => 'ethan@omnireferrals.com', 'city' => 'Phoenix', 'state' => 'AZ', 'zip' => '85001', 'brokerage' => 'Brooks Property Group', 'plan' => $individualVaPlan?->id],
            ['name' => 'Sophia Hayes', 'email' => 'sophia@omnireferrals.com', 'city' => 'Atlanta', 'state' => 'GA', 'zip' => '30301', 'brokerage' => 'Hayes Urban Homes', 'plan' => $socialMediaPlan?->id],
            ['name' => 'Liam Foster', 'email' => 'liam@omnireferrals.com', 'city' => 'Austin', 'state' => 'TX', 'zip' => '73301', 'brokerage' => 'Foster Urban Realty', 'plan' => $coldCallingPlan?->id],
            ['name' => 'Emma Hart', 'email' => 'emma@omnireferrals.com', 'city' => 'Nashville', 'state' => 'TN', 'zip' => '37201', 'brokerage' => 'Hart & Home Advisors', 'plan' => $individualVaPlan?->id],
        ])->map(function ($agent, $index) use ($realtorImages, $defaultHeadshot, $admin) {
            $user = User::updateOrCreate(['email' => $agent['email']], [
                'name' => $agent['name'],
                'password' => 'password',
                'phone' => '80055510'.($index + 20),
                'role' => 'agent',
                'status' => 'active',
                'city' => $agent['city'],
                'state' => $agent['state'],
                'zip_code' => $agent['zip'],
                'current_plan_id' => $agent['plan'],
                'affiliate_code' => strtoupper('AGN'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT)),
                'onboarding_completed_at' => now()->subDays(10 - $index),
            ]);

            AffiliateProfile::updateOrCreate(['user_id' => $user->id], [
                'slug' => Str::slug($agent['name']).'-partner',
                'referral_code' => $user->affiliate_code,
                'payout_email' => $user->email,
                'commission_rate' => 10 + $index,
                'click_count' => 40 + ($index * 12),
                'conversion_count' => 4 + $index,
                'pending_payout_cents' => 15000 + ($index * 6500),
                'status' => 'active',
            ]);

            return RealtorProfile::updateOrCreate(['user_id' => $user->id], [
                'slug' => Str::slug($agent['name']),
                'brokerage_name' => $agent['brokerage'],
                'service_city' => $agent['city'],
                'service_state' => $agent['state'],
                'service_zip_code' => $agent['zip'],
                'rating' => max(3.0, 4.8 + ($index * 0.02)),
                'review_count' => 24 + ($index * 8),
                'leads_closed' => 12 + ($index * 5),
                'specialties' => 'Luxury, Relocation, First-Time Buyers',
                'bio' => 'Helping clients navigate competitive markets with clear communication, local expertise, and a qualification-first approach to every buyer and seller introduction.',
                'headshot' => $realtorImages[$index % max($realtorImages->count(), 1)] ?? $defaultHeadshot,
                'profile_status' => $index < 2 ? RealtorProfile::STATUS_FEATURED : RealtorProfile::STATUS_PUBLISHED,
                'created_by_user_id' => $admin->id,
            ]);
        });

        foreach ([
            ['Jordan Miles', 'agent', 'Boca Real Estate', 'Boca Raton, FL', 'The biggest improvement for us was lead quality. Our team started spending more time in real conversations and less time sorting weak inquiries.', true, 'images/companies-logos/boca-real-estate-300x137.png'],
            ['Silvia Zopf', 'agent', 'Zopfteam Silvia', 'Atlanta, GA', 'OmniReferral feels credible from the first interaction. The workflow, onboarding, and support helped our team look more polished with every lead.', true, 'images/companies-logos/Zopfteam-silvia-300x189.png'],
            ['Natalie Stone', 'agent', 'Best American Homes', 'Tampa, FL', 'Growth Leads gave us the right balance of urgency and detail. It feels far more organized than the other lead sources we tested.', false, 'images/companies-logos/best-american-homes-150x150.jpg'],
            ['Chris Everett', 'agent', 'Dallas Real Estate', 'Dallas, TX', 'What stood out immediately was the handoff. The leads arrived with better notes, better context, and a clear next step for our agents.', false, 'images/companies-logos/Dallas-RE-150x150.png'],
            ['Sofia Mercer', 'agent', 'Nevada Real estate', 'Las Vegas, NV', 'The dashboard keeps our team aligned. By the time a lead reaches us, the opportunity already feels structured and worth acting on.', false, 'images/companies-logos/Nevada-Re-e1742235865385-150x150.jpg'],
            ['Caleb Warren', 'agent', 'Coldwell Banker Realty', 'Charlotte, NC', 'We are closing the gap between intake and first contact much faster now. The quality of the first conversation improved almost immediately.', false, 'images/companies-logos/CB-realty-150x150.png'],
            ['Ariana Holt', 'buyer', 'Buyer Client', 'Dallas, TX', 'The buyer experience felt organized and high-touch from the start. Every next step came with more clarity than we expected.', true, null],
            ['Leah Monroe', 'buyer', 'Buyer Client', 'Orlando, FL', 'The intake was simple, but the follow-up felt personal and informed. We always knew who was guiding the next step.', false, null],
            ['Olivia Grant', 'buyer', 'First-Time Buyer', 'Raleigh, NC', 'I never felt like I was filling out a generic form. The communication felt thoughtful, and every update made the process easier to trust.', false, null],
            ['Daniel Brooks', 'buyer', 'Relocation Buyer', 'Scottsdale, AZ', 'We needed quick answers in a new market. OmniReferral made the process feel calm, clear, and much less overwhelming.', false, null],
            ['Marcus Dean', 'seller', 'Seller Client', 'Charlotte, NC', 'We wanted a smoother listing handoff and stronger communication. OmniReferral made the entire seller journey feel much more polished.', true, null],
            ['Nina Foster', 'seller', 'Seller Client', 'Phoenix, AZ', 'Our seller lead was handled quickly and professionally. The team stayed clear, responsive, and easy to trust.', false, null],
            ['Tessa Vaughn', 'seller', 'Home Seller', 'Tampa, FL', 'I appreciated how premium the experience felt before we even got deep into the process. The communication stayed clean and consistent.', false, null],
            ['Ramon Ellis', 'seller', 'Property Owner', 'Atlanta, GA', 'The updates were better than expected, and the handoff never felt messy. That gave us a lot more confidence in the process.', false, null],
        ] as $index => [$name, $audience, $company, $location, $quote, $isFeatured, $logo]) {
            Testimonial::updateOrCreate(['name' => $name], [
                'audience' => $audience,
                'company' => $company,
                'location' => $location,
                'rating' => 5,
                'quote' => $quote,
                'photo' => $logo ?: 'images/reviews/review-'.(($index % 4) + 1).'.svg',
                'is_featured' => $isFeatured,
                'is_published' => true,
                'sort_order' => $index + 1,
            ]);
        }

        foreach (['Zillow', 'Redfin', 'Compass', 'RE/MAX', 'Coldwell Banker', 'HomeLight'] as $index => $partner) {
            Partner::updateOrCreate(['name' => $partner], [
                'logo' => Str::slug($partner),
                'website' => 'https://example.com',
                'sort_order' => $index + 1,
            ]);
        }

        foreach ([
            ['Lead Quality in Real Estate: What Agents Should Look For', 'lead-quality-real-estate-agents', 'Marketing Team', 'Lead Generation'],
            ['How ISA Teams and Agents Can Work Better Together', 'isa-teams-and-agents', 'Operations Team', 'Sales Process'],
            ['Why ZIP Code Targeting Still Matters for Local Agent Growth', 'zip-code-targeting-agent-growth', 'SEO Team', 'Local SEO'],
        ] as $index => [$title, $slug, $author, $category]) {
            Blog::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'author' => $author,
                'category' => $category,
                'image' => 'images/blogs/blog-'.($index + 1).'.svg',
                'excerpt' => 'Actionable advice for buyers, sellers, and real estate professionals who want better workflows and better conversion.',
                'content' => "OmniReferral combines human-first outreach with clear systems for qualification, routing, and follow-up.\n\nThis article explains practical ways to improve real estate referral workflows while building more trust with clients and agent partners.",
                'meta_title' => $title.' | OmniReferral',
                'meta_description' => 'Read OmniReferral insights on lead generation, agent growth, and real estate referral systems.',
            ]);
        }

        foreach ([
            ['Lena Brooks', 'Director of ISA Operations'],
            ['Daniel Cruz', 'Head of Sales Partnerships'],
            ['Mia Roberts', 'Marketing Strategy Lead'],
            ['Noah Bennett', 'Web Development Manager'],
        ] as [$name, $role]) {
            TeamMember::updateOrCreate(['name' => $name], [
                'role' => $role,
                'bio' => 'Focused on building a smoother experience for every handoff inside the real estate referral journey.',
                'photo' => null,
            ]);
        }

        $propertyTitles = ['Modern Lakeview Residence', 'Downtown Skyline Condo', 'Sunny Family Retreat', 'Investor-Ready Corner Lot', 'Move-In Ready Townhome', 'Elegant Suburban Estate'];
        foreach ($propertyTitles as $index => $title) {
            Property::updateOrCreate(['slug' => Str::slug($title)], [
                'title' => $title,
                'description' => 'Marketplace sample listing illustrating how approved properties appear to buyers across search, detail pages, and agent outreach.',
                'status' => $index % 2 === 0 ? 'Active' : 'New Listing',
                'property_type' => $index % 3 === 0 ? 'House' : 'Apartment',
                'price' => 325000 + ($index * 85000),
                'location' => ['Dallas, TX', 'Miami, FL', 'Phoenix, AZ', 'Atlanta, GA', 'Austin, TX', 'Scottsdale, AZ'][$index],
                'zip_code' => ['75201', '33101', '85001', '30301', '73301', '85251'][$index],
                'latitude' => [32.7767, 25.7617, 33.4484, 33.7490, 30.2672, 33.4942][$index],
                'longitude' => [-96.7970, -80.1918, -112.0740, -84.3880, -97.7431, -111.9261][$index],
                'beds' => 2 + ($index % 4),
                'baths' => 2 + ($index % 2) * 0.5,
                'sqft' => 1350 + ($index * 240),
                'image' => [
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=800',
                    'https://images.unsplash.com/photo-1600607687940-477a284e68c6?auto=format&fit=crop&q=80&w=800',
                    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&q=80&w=800',
                    'https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&q=80&w=800',
                    'https://images.unsplash.com/photo-1570129477492-45c003edd2be?auto=format&fit=crop&q=80&w=800',
                    'https://images.unsplash.com/photo-1600566753190-17f0bb2a6c3e?auto=format&fit=crop&q=80&w=800',
                ][$index],
                'images' => [
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=800',
                ],
                'source' => 'OmniReferral Listings',
                'is_featured' => $index < 3,
                'published_at' => now()->subDays($index + 1),
                'realtor_profile_id' => $agents[$index % $agents->count()]->id,
                'owner_user_id' => $agents[$index % $agents->count()]->user_id,
                'listed_by_id' => $agents[$index % $agents->count()]->user_id,
            ]);
        }

        $leads = collect([
            ['buyer', 'Taylor Morgan', 'taylor@example.com', '8005552001', '75201', 'House', 450000, null, '0-30 days', 'Pre-approved', 'Phone', $coldCallingPlan?->id, 'qualified', optional($agents[0] ?? null)->user_id],
            ['seller', 'Jamie Carter', 'jamie@example.com', '8005552002', '33101', 'Apartment', null, 625000, '1-3 months', 'Need pricing help', 'Email', $socialMediaPlan?->id, 'assigned', optional($agents[1] ?? null)->user_id],
            ['buyer', 'Chris Allen', 'chris@example.com', '8005552003', '85001', 'Commercial', 950000, null, 'ASAP', 'Cash buyer', 'Text', $individualVaPlan?->id, 'contacted', optional($agents[2] ?? null)->user_id],
        ])->map(function ($leadData, $index) use ($isa, $packages) {
            [$intent, $name, $email, $phone, $zip, $type, $budget, $asking, $timeline, $financingStatus, $contactPreference, $packageId, $status, $assignedAgentId] = $leadData;
            $package = $packages->firstWhere('id', $packageId);

            return Lead::updateOrCreate(['email' => $email], [
                'lead_number' => 'OMNI-20260401-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'intent' => $intent,
                'package_type' => $package ? str($package->slug)->before('-')->toString() : ['cold', 'social', 'individual'][$index],
                'package_id' => $packageId,
                'status' => $status,
                'source' => 'website',
                'name' => $name,
                'phone' => $phone,
                'zip_code' => $zip,
                'property_type' => $type,
                'budget' => $budget,
                'asking_price' => $asking,
                'timeline' => $timeline,
                'financing_status' => $financingStatus,
                'contact_preference' => $contactPreference,
                'preferences' => 'Prefers responsive communication and local expertise.',
                'form_data' => [
                    'budget' => $budget,
                    'timeline' => $timeline,
                    'financing_status' => $financingStatus,
                    'contact_preference' => $contactPreference,
                ],
                'lead_score' => 74 + ($index * 8),
                'is_priority' => $timeline === 'ASAP',
                'reviewed_by_id' => $isa->id,
                'reviewed_at' => now()->subDays(3 - $index),
                'assigned_agent_id' => $assignedAgentId,
                'assigned_at' => now()->subDays(2 - $index),
                'contacted_at' => in_array($status, ['contacted', 'closed'], true) ? now()->subDay() : null,
                'closed_at' => $status === 'closed' ? now()->subHours(6) : null,
                'route_notes' => 'Qualified through ISA review and ready for the next routing action.',
            ]);
        });

        foreach ($leads as $index => $lead) {
            $matchedAgentId = $lead->assigned_agent_id ?: optional($agents[$index % $agents->count()] ?? null)->user_id;
            if (! $matchedAgentId) {
                continue;
            }

            LeadMatch::updateOrCreate([
                'lead_id' => $lead->id,
                'agent_id' => $matchedAgentId,
            ], [
                'matched_by_id' => $sales->id,
                'package_id' => $lead->package_id,
                'status' => $lead->status === 'assigned' ? 'accepted' : 'pending',
                'location_score' => 90 - ($index * 5),
                'plan_score' => 84 + ($index * 4),
                'matched_at' => now()->subDays(2 - $index),
                'responded_at' => $lead->status === 'contacted' ? now()->subDay() : null,
                'notes' => 'Matched by sales team based on ZIP code coverage and plan tier.',
            ]);
        }
    }
}
