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
            ->map(fn ($path) => 'images/realtors/' . basename($path))
            ->filter(fn ($path) => ! str_contains($path, 'admin-ajax'))
            ->values();

        $packages = collect([
            [
                'name' => 'Quick Leads',
                'slug' => 'quick-leads',
                'description' => 'Entry-level verified leads for agents who want to test a new market or add lighter-volume opportunities.',
                'category' => 'lead',
                'billing_type' => 'hybrid',
                'is_featured' => false,
                'is_active' => true,
                'one_time_price' => 199,
                'monthly_price' => 149,
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'ghl_form_url' => 'https://api.leadconnectorhq.com/widget/survey/6VrZG7vbNueWG6hoqYru',
                'ghl_pipeline_stage' => 'quick-intake',
                'features' => ['Verified contact details', 'ZIP-based routing', 'Friendly intake support', 'Starter-level opportunity tracking'],
                'cta_label' => 'Get Quick Leads',
                'duration_days' => 30,
                'sort_order' => 1,
            ],
            [
                'name' => 'Power Leads',
                'slug' => 'power-leads',
                'description' => 'The best balance of detail, urgency, and routing support for teams that want reliable monthly growth.',
                'category' => 'lead',
                'billing_type' => 'hybrid',
                'is_featured' => true,
                'is_active' => true,
                'one_time_price' => 349,
                'monthly_price' => 289,
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'ghl_form_url' => 'https://api.leadconnectorhq.com/widget/survey/NmuErgwOkT4c83tl1k12',
                'ghl_pipeline_stage' => 'power-qualification',
                'features' => ['Higher-intent screening', 'Budget and timing notes', 'Priority assignment workflow', 'Most popular package for growth-minded agents'],
                'cta_label' => 'Choose Power',
                'duration_days' => 30,
                'sort_order' => 2,
            ],
            [
                'name' => 'Prime Leads',
                'slug' => 'prime-leads',
                'description' => 'Premium qualification and priority routing for agents who want the highest-intent opportunities first.',
                'category' => 'lead',
                'billing_type' => 'hybrid',
                'is_featured' => false,
                'is_active' => true,
                'one_time_price' => 549,
                'monthly_price' => 449,
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'ghl_form_url' => 'https://api.leadconnectorhq.com/widget/survey/DAnafQ8CfUsIMsj8Zq4D',
                'ghl_pipeline_stage' => 'prime-priority',
                'features' => ['Premium qualification', 'Closer-to-action opportunities', 'Priority support and routing', 'Best for fast-moving teams'],
                'cta_label' => 'Get Prime Leads',
                'duration_days' => 30,
                'sort_order' => 3,
            ],
            [
                'name' => 'VA Starter',
                'slug' => 'va-starter',
                'description' => 'A monthly cold-calling and CRM support layer for teams that need more top-of-funnel consistency.',
                'category' => 'virtual_assistant',
                'billing_type' => 'monthly',
                'is_featured' => false,
                'is_active' => true,
                'one_time_price' => null,
                'monthly_price' => 299,
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'ghl_form_url' => 'https://api.leadconnectorhq.com/widget/survey/CV8WmfWmoDlJ5GEO9B99',
                'ghl_pipeline_stage' => 'va-starter',
                'features' => ['20 support hours per month', 'CRM organization', 'Inbox and follow-up help', 'Task queue visibility'],
                'cta_label' => 'Add VA Support',
                'duration_days' => 30,
                'sort_order' => 4,
            ],
            [
                'name' => 'VA Growth',
                'slug' => 'va-growth',
                'description' => 'A monthly social and coordination layer for scaling teams that need more consistent nurture and campaign support.',
                'category' => 'virtual_assistant',
                'billing_type' => 'monthly',
                'is_featured' => false,
                'is_active' => true,
                'one_time_price' => null,
                'monthly_price' => 599,
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'ghl_form_url' => 'https://api.leadconnectorhq.com/widget/survey/ye7sDOoYsZaiCNjWRARI',
                'ghl_pipeline_stage' => 'va-growth',
                'features' => ['40 support hours per month', 'Listing coordination', 'Lead nurture assistance', 'Reporting support'],
                'cta_label' => 'Scale Support',
                'duration_days' => 30,
                'sort_order' => 5,
            ],
        ])->map(fn ($plan) => Package::updateOrCreate(['slug' => $plan['slug']], $plan));

        $quickPlan = $packages->firstWhere('slug', 'quick-leads');
        $powerPlan = $packages->firstWhere('slug', 'power-leads');
        $primePlan = $packages->firstWhere('slug', 'prime-leads');

        $admin = User::updateOrCreate(['email' => 'admin@omnireferral.us'], [
            'name' => 'Olivia Parker',
            'password' => 'password',
            'phone' => '8005551000',
            'role' => 'admin',
            'status' => 'active',
            'affiliate_code' => 'ADMIN100',
        ]);

        $isa = User::updateOrCreate(['email' => 'isa@omnireferral.us'], [
            'name' => 'Lena Brooks',
            'password' => 'password',
            'phone' => '8005551011',
            'role' => 'staff',
            'staff_team' => 'isa',
            'status' => 'active',
            'affiliate_code' => 'ISA10011',
        ]);

        $sales = User::updateOrCreate(['email' => 'sales@omnireferral.us'], [
            'name' => 'Daniel Cruz',
            'password' => 'password',
            'phone' => '8005551012',
            'role' => 'staff',
            'staff_team' => 'sales',
            'status' => 'active',
            'affiliate_code' => 'SAL10012',
        ]);

        User::updateOrCreate(['email' => 'marketing@omnireferral.us'], [
            'name' => 'Mia Roberts',
            'password' => 'password',
            'phone' => '8005551013',
            'role' => 'staff',
            'staff_team' => 'marketing',
            'status' => 'active',
            'affiliate_code' => 'MKT10013',
        ]);

        User::updateOrCreate(['email' => 'webdev@omnireferral.us'], [
            'name' => 'Noah Bennett',
            'password' => 'password',
            'phone' => '8005551014',
            'role' => 'staff',
            'staff_team' => 'web_dev',
            'status' => 'active',
            'affiliate_code' => 'WEB10014',
        ]);

        User::updateOrCreate(['email' => 'buyer@omnireferral.us'], [
            'name' => 'Taylor Morgan',
            'password' => 'password',
            'phone' => '8005552001',
            'role' => 'buyer',
            'status' => 'active',
            'affiliate_code' => 'BUY20001',
        ]);

        User::updateOrCreate(['email' => 'seller@omnireferral.us'], [
            'name' => 'Jamie Carter',
            'password' => 'password',
            'phone' => '8005552002',
            'role' => 'seller',
            'status' => 'active',
            'affiliate_code' => 'SEL20002',
        ]);

        $agents = collect([
            ['name' => 'Mason Reed', 'email' => 'mason@omnireferral.us', 'city' => 'Dallas', 'state' => 'TX', 'zip' => '75201', 'brokerage' => 'Reed & Co Realty', 'plan' => $primePlan?->id],
            ['name' => 'Ava Collins', 'email' => 'ava@omnireferral.us', 'city' => 'Miami', 'state' => 'FL', 'zip' => '33101', 'brokerage' => 'Collins Coastal Realty', 'plan' => $powerPlan?->id],
            ['name' => 'Ethan Brooks', 'email' => 'ethan@omnireferral.us', 'city' => 'Phoenix', 'state' => 'AZ', 'zip' => '85001', 'brokerage' => 'Brooks Property Group', 'plan' => $quickPlan?->id],
            ['name' => 'Sophia Hayes', 'email' => 'sophia@omnireferral.us', 'city' => 'Atlanta', 'state' => 'GA', 'zip' => '30301', 'brokerage' => 'Hayes Urban Homes', 'plan' => $powerPlan?->id],
            ['name' => 'Liam Foster', 'email' => 'liam@omnireferral.us', 'city' => 'Austin', 'state' => 'TX', 'zip' => '73301', 'brokerage' => 'Foster Urban Realty', 'plan' => $primePlan?->id],
            ['name' => 'Emma Hart', 'email' => 'emma@omnireferral.us', 'city' => 'Nashville', 'state' => 'TN', 'zip' => '37201', 'brokerage' => 'Hart & Home Advisors', 'plan' => $quickPlan?->id],
        ])->map(function ($agent, $index) use ($realtorImages) {
            $user = User::updateOrCreate(['email' => $agent['email']], [
                'name' => $agent['name'],
                'password' => 'password',
                'phone' => '80055510' . ($index + 20),
                'role' => 'agent',
                'status' => 'active',
                'current_plan_id' => $agent['plan'],
                'affiliate_code' => strtoupper('AGN' . str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT)),
                'onboarding_completed_at' => now()->subDays(10 - $index),
            ]);

            AffiliateProfile::updateOrCreate(['user_id' => $user->id], [
                'slug' => Str::slug($agent['name']) . '-partner',
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
                'city' => $agent['city'],
                'state' => $agent['state'],
                'zip_code' => $agent['zip'],
                'rating' => 4.8 + ($index * 0.02),
                'review_count' => 24 + ($index * 8),
                'leads_closed' => 12 + ($index * 5),
                'specialties' => 'Luxury, Relocation, First-Time Buyers',
                'bio' => 'Helping clients navigate competitive markets with clear communication and local expertise.',
                'headshot' => $realtorImages[$index % max($realtorImages->count(), 1)] ?? 'images/realtors/3.png',
            ]);
        });

        foreach ([
            ['Jordan Miles', 'agent', 'Broker | Miles Realty Group', 'Austin, TX', 'The biggest improvement for us was lead quality. Our team started spending more time in real conversations and less time sorting weak inquiries.', true],
            ['Lauren Price', 'agent', 'Team Lead | Price Residential', 'Atlanta, GA', 'OmniReferral feels credible from the first interaction. The workflow, onboarding, and support helped our team look more polished with every lead.', true],
            ['Natalie Stone', 'agent', 'Realtor | Stone & Key Partners', 'Tampa, FL', 'Power Leads gave us the right balance of urgency and detail. It feels far more organized than the other lead sources we tested.', false],
            ['Chris Everett', 'agent', 'Investor Advisor | Everett Homes', 'Phoenix, AZ', 'What stood out immediately was the handoff. The leads arrived with better notes, better context, and a clear next step for our agents.', false],
            ['Sofia Mercer', 'agent', 'Principal Broker | Mercer Lane Realty', 'Nashville, TN', 'The dashboard keeps our team aligned. By the time a lead reaches us, the opportunity already feels structured and worth acting on.', false],
            ['Caleb Warren', 'agent', 'Growth Agent | Warren Residential', 'Charlotte, NC', 'We are closing the gap between intake and first contact much faster now. The quality of the first conversation improved almost immediately.', false],
            ['Ariana Holt', 'buyer', 'Buyer Client', 'Dallas, TX', 'The buyer experience felt organized and high-touch from the start. Every next step came with more clarity than we expected.', true],
            ['Leah Monroe', 'buyer', 'Buyer Client', 'Orlando, FL', 'The intake was simple, but the follow-up felt personal and informed. We always knew who was guiding the next step.', false],
            ['Olivia Grant', 'buyer', 'First-Time Buyer', 'Raleigh, NC', 'I never felt like I was filling out a generic form. The communication felt thoughtful, and every update made the process easier to trust.', false],
            ['Daniel Brooks', 'buyer', 'Relocation Buyer', 'Scottsdale, AZ', 'We needed quick answers in a new market. OmniReferral made the process feel calm, clear, and much less overwhelming.', false],
            ['Marcus Dean', 'seller', 'Seller Client', 'Charlotte, NC', 'We wanted a smoother listing handoff and stronger communication. OmniReferral made the entire seller journey feel much more polished.', true],
            ['Nina Foster', 'seller', 'Seller Client', 'Phoenix, AZ', 'Our seller lead was handled quickly and professionally. The team stayed clear, responsive, and easy to trust.', false],
            ['Tessa Vaughn', 'seller', 'Home Seller', 'Tampa, FL', 'I appreciated how premium the experience felt before we even got deep into the process. The communication stayed clean and consistent.', false],
            ['Ramon Ellis', 'seller', 'Property Owner', 'Atlanta, GA', 'The updates were better than expected, and the handoff never felt messy. That gave us a lot more confidence in the process.', false],
        ] as $index => [$name, $audience, $company, $location, $quote, $isFeatured]) {
            Testimonial::updateOrCreate(['name' => $name], [
                'audience' => $audience,
                'company' => $company,
                'location' => $location,
                'rating' => 5,
                'quote' => $quote,
                'photo' => 'images/reviews/review-' . (($index % 4) + 1) . '.svg',
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
                'image' => 'images/blogs/blog-' . ($index + 1) . '.svg',
                'excerpt' => 'Actionable advice for buyers, sellers, and real estate professionals who want better workflows and better conversion.',
                'content' => "OmniReferral combines human-first outreach with clear systems for qualification, routing, and follow-up.\n\nThis article explains practical ways to improve real estate referral workflows while building more trust with clients and agent partners.",
                'meta_title' => $title . ' | OmniReferral',
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
                'description' => 'A premium demo listing seeded for the OmniReferral marketplace and agent dashboards.',
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
                    'https://images.unsplash.com/photo-1600566753190-17f0bb2a6c3e?auto=format&fit=crop&q=80&w=800'
                ][$index],
                'images' => [
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=800'
                ],
                'source' => 'OmniReferral Listings',
                'is_featured' => $index < 3,
                'published_at' => now()->subDays($index + 1),
                'realtor_profile_id' => $agents[$index % $agents->count()]->id,
            ]);
        }

        $leads = collect([
            ['buyer', 'Taylor Morgan', 'taylor@example.com', '8005552001', '75201', 'House', 450000, null, '0-30 days', 'Pre-approved', 'Phone', $quickPlan?->id, 'qualified', optional($agents[0] ?? null)->user_id],
            ['seller', 'Jamie Carter', 'jamie@example.com', '8005552002', '33101', 'Apartment', null, 625000, '1-3 months', 'Need pricing help', 'Email', $powerPlan?->id, 'assigned', optional($agents[1] ?? null)->user_id],
            ['buyer', 'Chris Allen', 'chris@example.com', '8005552003', '85001', 'Commercial', 950000, null, 'ASAP', 'Cash buyer', 'Text', $primePlan?->id, 'contacted', optional($agents[2] ?? null)->user_id],
        ])->map(function ($leadData, $index) use ($isa, $packages) {
            [$intent, $name, $email, $phone, $zip, $type, $budget, $asking, $timeline, $financingStatus, $contactPreference, $packageId, $status, $assignedAgentId] = $leadData;
            $package = $packages->firstWhere('id', $packageId);

            return Lead::updateOrCreate(['email' => $email], [
                'lead_number' => 'OMNI-20260401-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'intent' => $intent,
                'package_type' => $package ? str($package->slug)->before('-')->toString() : ['quick', 'power', 'prime'][$index],
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
