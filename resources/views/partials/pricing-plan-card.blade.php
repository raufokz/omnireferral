<article class="pricing-card pricing-card--interactive homepage-pricing-card pricing-card--premium pricing-card--saas {{ $card['is_featured'] ? 'pricing-card--featured' : '' }} {{ $category === 'virtual_assistance' ? 'pricing-card--va' : '' }}">
    @if($card['is_featured'])
        <span class="pricing-card__popular-ribbon" aria-hidden="true">Most Popular</span>
    @endif

    <div class="pricing-card__topline">
        <span class="pricing-label">{{ $card['tag'] }}</span>
        <span class="pricing-card__icon pricing-card__icon--{{ $card['icon'] }}" aria-hidden="true">
            @include('partials.pricing-card-icon', ['icon' => $card['icon']])
        </span>
    </div>

    <div class="homepage-pricing-card__header">
        <h3>{{ $plan['name'] }}</h3>
        @if($card['value_statement'] !== '')
            <p class="pricing-card__value-statement">{{ $card['value_statement'] }}</p>
        @endif
    </div>

    <div class="pricing-card__price-block pricing-card__price-block--saas">
        <div class="pricing-card__price-row">
            <strong class="pricing-card__price-only">${{ number_format((int) ($plan['price'] ?? 0)) }}</strong>
            <span class="pricing-card__billing-label">{{ $card['billing'] }}</span>
        </div>
    </div>

    <p class="pricing-card__description">{{ $card['description'] }}</p>

    <div class="pricing-card__actions-row">
        <a href="{{ $card['cta_url'] }}" class="button button--pricing-cta {{ $card['is_featured'] ? 'button--orange' : 'button--blue' }}" data-explore-plan="1">
            <span>EXPLORE PLAN</span>
            <svg class="pricing-card__cta-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    </div>
</article>
