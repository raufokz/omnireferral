@php
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
@endphp

<div class="pricing-grid pricing-grid--spotlight pricing-grid--lead-only" data-stagger>
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
