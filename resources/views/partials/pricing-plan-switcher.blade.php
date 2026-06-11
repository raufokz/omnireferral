@php
    $toggleGroup = $toggleGroup ?? 'pricing-page';
    $defaultCategory = $defaultCategory ?? 'real_estate';
    $toggleId = preg_replace('/[^A-Za-z0-9_-]/', '-', $toggleGroup);

    $leadPlans = $leadPlans ?? [
        [
            'slug' => 'quick-leads',
            'name' => 'Quick Lead',
            'tier' => 'STARTER',
            'price' => 399,
            'price_note' => '/ month',
            'summary' => 'A focused entry package for agents who want verified referral flow in a smaller service area.',
            'features' => ['16-20 total referrals', 'Up to 2 cities or ZIP codes', 'Email support', '1-step verification'],
            'best_for' => 'New Agents',
            'cta_url' => route('packages.checkout', ['packageSlug' => 'quick-leads']),
            'is_featured' => false,
        ],
        [
            'slug' => 'power-leads',
            'name' => 'Power Lead',
            'tier' => 'MOST POPULAR',
            'price' => 899,
            'price_note' => '/ month',
            'summary' => 'The balanced growth tier with stronger routing priority, more referrals, and included VA support.',
            'features' => ['30+ total referrals', 'Up to 5 cities or ZIP codes', '3 hrs/week virtual assistance', 'Email + text support'],
            'best_for' => 'Growing Teams',
            'cta_url' => route('packages.checkout', ['packageSlug' => 'power-leads']),
            'is_featured' => true,
        ],
        [
            'slug' => 'prime-leads',
            'name' => 'Prime Lead',
            'tier' => 'PREMIUM',
            'price' => 1999,
            'price_note' => '/ month',
            'summary' => 'A high-volume package for teams that need broader coverage, deeper verification, and premium support.',
            'features' => ['50+ total referrals', 'Up to 10 cities or ZIP codes', '15 hrs/week virtual assistance', 'Call + text + email support'],
            'best_for' => 'High Volume Agents',
            'cta_url' => route('packages.checkout', ['packageSlug' => 'prime-leads']),
            'is_featured' => false,
        ],
    ];

    $vaPlans = [
        [
            'slug' => 'cold-calling-isa',
            'name' => 'Cold Calling / ISA',
            'tier' => 'SALES BOOST',
            'price' => 1999,
            'price_note' => '/ month',
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
            'badge' => null,
            'cta_url' => route('packages.checkout', ['packageSlug' => 'cold-calling-isa']),
            'is_featured' => false,
        ],
        [
            'slug' => 'social-media-mgmt',
            'name' => 'Social Media Mgmt',
            'tier' => 'CONTENT ENGINE',
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
            'badge' => 'Most Popular',
            'cta_url' => route('packages.checkout', ['packageSlug' => 'social-media-mgmt']),
            'is_featured' => true,
        ],
        [
            'slug' => 'individual-va',
            'name' => 'Individual VA',
            'tier' => 'FLEXIBLE HOURS',
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
            'badge' => 'No Long-Term Commitment',
            'cta_url' => 'https://omnireferrals.com/contact',
            'is_featured' => false,
        ],
    ];
@endphp

<div class="pricing-toggle-shell pricing-toggle-shell--packages">
    <div class="pricing-toggle-row pricing-toggle-row--segmented" data-pricing-toggle="{{ $toggleGroup }}" data-default="{{ $defaultCategory }}" role="tablist" aria-label="Pricing categories">
        <button
            type="button"
            id="pricing-tab-{{ $toggleId }}-real-estate"
            class="pricing-toggle-option {{ $defaultCategory === 'real_estate' ? 'is-active' : '' }}"
            data-category="real_estate"
            data-helper="Lead packages for qualified referrals, territory routing, and real estate pipeline growth."
            role="tab"
            aria-selected="{{ $defaultCategory === 'real_estate' ? 'true' : 'false' }}"
            aria-controls="pricing-panel-{{ $toggleId }}-real-estate"
        >
            <span>Real Estate Plans</span>
            <small>Quick, Power, Prime</small>
        </button>
        <button
            type="button"
            id="pricing-tab-{{ $toggleId }}-va-services"
            class="pricing-toggle-option {{ $defaultCategory === 'virtual_assistance' ? 'is-active' : '' }}"
            data-category="virtual_assistance"
            data-helper="VA services for outbound calling, social content, operations, and flexible execution support."
            role="tab"
            aria-selected="{{ $defaultCategory === 'virtual_assistance' ? 'true' : 'false' }}"
            aria-controls="pricing-panel-{{ $toggleId }}-va-services"
        >
            <span>VA Services</span>
            <small>ISA, Social, Hourly VA</small>
        </button>
    </div>
    <p class="pricing-toggle-helper" data-pricing-toggle-helper>Lead packages for qualified referrals, territory routing, and real estate pipeline growth.</p>
</div>

<div
    id="pricing-panel-{{ $toggleId }}-real-estate"
    class="pricing-grid pricing-grid--spotlight pricing-grid--lead-only pricing-grid--tabbed"
    data-pricing-grid="{{ $toggleGroup }}"
    data-category="real_estate"
    role="tabpanel"
    aria-labelledby="pricing-tab-{{ $toggleId }}-real-estate"
    @if($defaultCategory !== 'real_estate') hidden @endif
    data-stagger
>
    @foreach($leadPlans as $plan)
        <article class="pricing-card pricing-card--interactive homepage-pricing-card {{ $plan['is_featured'] ? 'pricing-card--featured' : '' }}">
            <div class="pricing-card__topline">
                <span class="pricing-label">{{ $plan['tier'] }}</span>
                @if($plan['is_featured'])
                    <div class="pricing-badge-popular">Most Popular</div>
                @endif
            </div>

            <div class="homepage-pricing-card__header">
                <h3>{{ $plan['name'] }}</h3>
                <p class="homepage-pricing-card__summary">{{ $plan['summary'] }}</p>
            </div>

            <div class="pricing-card__price-block">
                <div class="price-row homepage-pricing-card__price">
                    <strong>${{ number_format($plan['price']) }}</strong>
                    <span>{{ $plan['price_note'] }}</span>
                </div>
                <span class="pricing-card__best-fit">Best for {{ $plan['best_for'] }}</span>
            </div>

            <ul class="pricing-card__quick-list" aria-label="{{ $plan['name'] }} features">
                @foreach($plan['features'] as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>

            <a href="{{ $plan['cta_url'] }}" class="button {{ $plan['is_featured'] ? 'button--orange' : 'button--blue' }}" data-explore-plan="1">EXPLORE PLAN</a>
        </article>
    @endforeach
</div>

<div
    id="pricing-panel-{{ $toggleId }}-va-services"
    class="pricing-grid pricing-grid--spotlight pricing-grid--lead-only pricing-grid--tabbed"
    data-pricing-grid="{{ $toggleGroup }}"
    data-category="virtual_assistance"
    role="tabpanel"
    aria-labelledby="pricing-tab-{{ $toggleId }}-va-services"
    @if($defaultCategory !== 'virtual_assistance') hidden @endif
    data-stagger
>
    @foreach($vaPlans as $plan)
        <article class="pricing-card pricing-card--interactive homepage-pricing-card pricing-card--va {{ $plan['is_featured'] ? 'pricing-card--featured' : '' }}">
            <div class="pricing-card__topline">
                <span class="pricing-label">{{ $plan['tier'] }}</span>
                @if($plan['badge'])
                    <div class="pricing-badge-popular">{{ $plan['badge'] }}</div>
                @endif
            </div>

            <div class="homepage-pricing-card__header">
                <h3>{{ $plan['name'] }}</h3>
                <p class="homepage-pricing-card__summary">{{ $plan['summary'] }}</p>
            </div>

            <div class="pricing-card__price-block">
                <div class="price-row homepage-pricing-card__price">
                    <strong>${{ number_format($plan['price']) }}</strong>
                    <span>{{ $plan['price_note'] }}</span>
                </div>
            </div>

            <ul class="pricing-card__quick-list" aria-label="{{ $plan['name'] }} features">
                @foreach($plan['features'] as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>

            <a href="{{ $plan['cta_url'] }}" class="button {{ $plan['is_featured'] ? 'button--orange' : 'button--blue' }}" data-explore-plan="1">EXPLORE PLAN</a>
        </article>
    @endforeach
</div>
