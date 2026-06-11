@php
    $toggleGroup = $toggleGroup ?? 'pricing-shared';
    $leadActionUrl = $leadActionUrl ?? route('contact');
    $showSalesLinks = $showSalesLinks ?? false;
    $gridClass = $gridClass ?? 'pricing-grid pricing-grid--spotlight';
    $cardClass = $cardClass ?? 'pricing-card pricing-card--interactive homepage-pricing-card';
    $featureLimit = $featureLimit ?? 7;
    $showPackageDetails = $showPackageDetails ?? false;
    $defaultCategory = $defaultCategory ?? 'real_estate';
@endphp

<div class="pricing-toggle-row" data-pricing-toggle="{{ $toggleGroup }}" data-default="{{ $defaultCategory }}">
    <span class="{{ $defaultCategory === 'real_estate' ? 'is-active' : '' }}" data-category="real_estate">Real Estate Plans</span>
    <button type="button" class="toggle" aria-label="Toggle pricing category">
        <span class="toggle-thumb {{ $defaultCategory === 'virtual_assistance' ? 'is-active' : '' }}"></span>
    </button>
    <span class="{{ $defaultCategory === 'virtual_assistance' ? 'is-active' : '' }}" data-category="virtual_assistance">VA Services</span>
</div>

<div class="{{ $gridClass }}" data-pricing-grid="{{ $toggleGroup }}" data-category="real_estate" style="{{ $defaultCategory === 'real_estate' ? '' : 'display:none;' }}" data-stagger>
    @foreach(($pricingPlans['real_estate'] ?? []) as $plan)
        @php
            $slug = (string) ($plan['slug'] ?? '');
            $ctaUrl = match ($slug) {
                'quick-leads' => route('pricing.quick-lead'),
                'power-leads' => route('pricing.power-lead'),
                'prime-leads' => route('pricing.prime-lead'),
                default => $leadActionUrl,
            };
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
                @if($showPackageDetails && !empty($plan['highlights']))
                    <div class="pricing-card__mini-points">
                        @foreach($plan['highlights'] as $highlight)
                            <span>{{ $highlight }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="price-row homepage-pricing-card__price">
                <strong>${{ number_format($plan['price']) }}</strong>
                <span>{{ $plan['price_note'] }}</span>
            </div>
            @if($showPackageDetails && !empty($plan['what_you_get']))
                <div class="pricing-card__value-block">
                    <span>What you get</span>
                    <p>{{ $plan['what_you_get'] }}</p>
                </div>
            @endif
            @if($showPackageDetails && !empty($plan['feature_groups']))
                <div class="pricing-card__feature-groups">
                    @foreach($plan['feature_groups'] as $group)
                        <div class="pricing-card__feature-group">
                            <strong>{{ $group['title'] ?? 'Included support' }}</strong>
                            <ul class="feature-check-list homepage-pricing-card__features pricing-card__group-list">
                                @foreach(($group['items'] ?? []) as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @else
                <ul class="feature-check-list homepage-pricing-card__features">
                    @foreach($visibleFeatures as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            @endif
            @if($showPackageDetails && !empty($plan['trust_note']))
                <p class="pricing-card__trust-note">{{ $plan['trust_note'] }}</p>
            @endif
            <a href="{{ $ctaUrl }}" class="button {{ $isFeatured ? 'button--orange' : 'button--blue' }}">EXPLORE PLAN</a>
            @if($showSalesLinks)
                <a href="{{ route('contact', ['plan' => $plan['name']]) }}" class="ppc-form-link">Talk to sales about {{ $plan['name'] }}</a>
            @endif
        </article>
    @endforeach
</div>

<div class="{{ $gridClass }}" data-pricing-grid="{{ $toggleGroup }}" data-category="virtual_assistance" style="{{ $defaultCategory === 'virtual_assistance' ? '' : 'display:none;' }}" data-stagger>
    @foreach(($pricingPlans['virtual_assistance'] ?? []) as $plan)
        @php
            $ctaUrl = ($plan['slug'] ?? null) ? route('packages.checkout', ['packageSlug' => $plan['slug']]) : ($plan['cta_url'] ?? route('contact', ['plan' => $plan['name']]));
            $isFeatured = $plan['is_featured'] ?? false;
            $isFlexible = ($plan['slug'] ?? '') === 'individual-va';
            $features = $plan['features'] ?? [];
            $visibleFeatures = $featureLimit ? array_slice($features, 0, $featureLimit) : $features;
        @endphp
        <article class="{{ $cardClass }} {{ $isFeatured ? 'pricing-card--featured' : '' }}" style="position:relative;">
            @if($isFeatured)
                <div class="pricing-card__ribbon" aria-hidden="true">VA SUPPORT</div>
            @endif
            <div class="homepage-pricing-card__header">
                <div class="homepage-pricing-card__eyebrow-row">
                    <span class="pricing-label">{{ $plan['tier'] }}</span>
                    @if($isFeatured)
                        <div class="pricing-badge-popular">MOST POPULAR</div>
                    @endif
                </div>
                <h3>{{ $plan['name'] }}</h3>
                <p class="homepage-pricing-card__summary">{{ $plan['summary'] }}</p>
                @if($showPackageDetails && !empty($plan['highlights']))
                    <div class="pricing-card__mini-points">
                        @foreach($plan['highlights'] as $highlight)
                            <span>{{ $highlight }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="price-row homepage-pricing-card__price">
                <strong>${{ number_format($plan['price']) }}</strong>
                <span>{{ $plan['price_note'] }}</span>
            </div>
            @if($showPackageDetails && !empty($plan['best_for']))
                <p class="pricing-card__best-for"><strong>Best for:</strong> {{ $plan['best_for'] }}</p>
            @endif
            @if($showPackageDetails && !empty($plan['what_you_get']))
                <div class="pricing-card__value-block">
                    <span>What you get</span>
                    <p>{{ $plan['what_you_get'] }}</p>
                </div>
            @endif
            @if($isFlexible)
                <div class="pricing-card__highlight-label" aria-label="Commitment note">No Long-Term Commitment</div>
            @endif
            @if($showPackageDetails && !empty($plan['feature_groups']))
                <div class="pricing-card__feature-groups">
                    @foreach($plan['feature_groups'] as $group)
                        <div class="pricing-card__feature-group">
                            <strong>{{ $group['title'] ?? 'Included support' }}</strong>
                            <ul class="feature-check-list homepage-pricing-card__features pricing-card__group-list">
                                @foreach(($group['items'] ?? []) as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @else
                <ul class="feature-check-list homepage-pricing-card__features">
                    @foreach($visibleFeatures as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            @endif
            @if($showPackageDetails && !empty($plan['trust_note']))
                <p class="pricing-card__trust-note">{{ $plan['trust_note'] }}</p>
            @endif
            <a href="{{ $ctaUrl }}" class="button {{ $isFeatured ? 'button--orange' : 'button--blue' }}">{{ $plan['cta_label'] ?? 'Get Started' }}</a>
            @if($showSalesLinks)
                <a href="{{ route('contact', ['plan' => $plan['name']]) }}" class="ppc-form-link">Talk to sales about {{ $plan['name'] }}</a>
            @endif
        </article>
    @endforeach
</div>
