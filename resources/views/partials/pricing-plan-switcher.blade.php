@php
    $toggleGroup = $toggleGroup ?? 'pricing-shared';
    $leadActionUrl = $leadActionUrl ?? route('contact');
    $showSalesLinks = $showSalesLinks ?? false;
    $gridClass = $gridClass ?? 'pricing-grid pricing-grid--spotlight';
    $cardClass = $cardClass ?? 'pricing-card pricing-card--interactive homepage-pricing-card';
    $featureLimit = $featureLimit ?? 7;
@endphp

<div class="pricing-toggle-row" data-pricing-toggle="{{ $toggleGroup }}">
    <span class="is-active" data-category="real_estate">Real Estate Plans</span>
    <button type="button" class="toggle" aria-label="Toggle pricing category">
        <span class="toggle-thumb is-active"></span>
    </button>
    <span data-category="virtual_assistance">Virtual Assistance</span>
</div>

<div class="{{ $gridClass }}" data-pricing-grid="{{ $toggleGroup }}" data-category="real_estate" data-stagger>
    @foreach(($pricingPlans['real_estate'] ?? []) as $plan)
        @php
            $ctaUrl = ($plan['slug'] ?? null) ? route('packages.checkout', $plan['slug']) : $leadActionUrl;
            $isFeatured = $plan['is_featured'] ?? false;
            $features = $plan['features'] ?? [];
            $visibleFeatures = $featureLimit ? array_slice($features, 0, $featureLimit) : $features;
        @endphp
        <article class="{{ $cardClass }} {{ $isFeatured ? 'pricing-card--featured' : '' }}">
            <div class="homepage-pricing-card__header">
                <div class="homepage-pricing-card__eyebrow-row">
                    <span class="pricing-label">{{ $plan['tier'] }}</span>
                    @if($isFeatured)
                        <div class="pricing-badge-popular">Most Popular</div>
                    @endif
                </div>
                <h3>{{ $plan['name'] }}</h3>
                @if(!empty($plan['value_price']))
                    <span class="pricing-card__value">Value ${{ number_format($plan['value_price']) }}</span>
                @endif
                <p class="homepage-pricing-card__summary">{{ $plan['summary'] }}</p>
            </div>
            <div class="price-row homepage-pricing-card__price">
                <strong>${{ number_format($plan['price']) }}</strong>
                <span>{{ $plan['price_note'] }}</span>
            </div>
            <ul class="feature-check-list homepage-pricing-card__features">
                @foreach($visibleFeatures as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>
            <a href="{{ $ctaUrl }}" class="button {{ $isFeatured ? 'button--orange' : 'button--blue' }}">{{ $plan['cta_label'] ?? 'Get Started' }}</a>
            @if($showSalesLinks)
                <a href="{{ route('contact', ['plan' => $plan['name']]) }}" class="ppc-form-link">Talk to sales about {{ $plan['name'] }}</a>
            @endif
        </article>
    @endforeach
</div>

<div class="{{ $gridClass }}" data-pricing-grid="{{ $toggleGroup }}" data-category="virtual_assistance" style="display:none;" data-stagger>
    @foreach(($pricingPlans['virtual_assistance'] ?? []) as $plan)
        @php
            $ctaUrl = $plan['cta_url'] ?? route('contact', ['plan' => $plan['name']]);
            $isFeatured = $plan['is_featured'] ?? false;
            $features = $plan['features'] ?? [];
            $visibleFeatures = $featureLimit ? array_slice($features, 0, $featureLimit) : $features;
        @endphp
        <article class="{{ $cardClass }} {{ $isFeatured ? 'pricing-card--featured' : '' }}">
            <div class="homepage-pricing-card__header">
                <div class="homepage-pricing-card__eyebrow-row">
                    <span class="pricing-label">{{ $plan['tier'] }}</span>
                    @if($isFeatured)
                        <div class="pricing-badge-popular">Top Pick</div>
                    @endif
                </div>
                <h3>{{ $plan['name'] }}</h3>
                <p class="homepage-pricing-card__summary">{{ $plan['summary'] }}</p>
            </div>
            <div class="price-row homepage-pricing-card__price">
                <strong>${{ number_format($plan['price']) }}</strong>
                <span>{{ $plan['price_note'] }}</span>
            </div>
            <ul class="feature-check-list homepage-pricing-card__features">
                @foreach($visibleFeatures as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>
            <a href="{{ $ctaUrl }}" class="button {{ $isFeatured ? 'button--orange' : 'button--blue' }}">{{ $plan['cta_label'] ?? 'Get Started' }}</a>
            @if($showSalesLinks)
                <a href="{{ route('contact', ['plan' => $plan['name']]) }}" class="ppc-form-link">Talk to sales about {{ $plan['name'] }}</a>
            @endif
        </article>
    @endforeach
</div>
