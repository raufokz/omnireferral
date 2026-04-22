@extends('layouts.app')

@section('content')
@php
    $startingPrice = (int) ($packageDisplay['price'] ?? $package->one_time_price ?? $package->monthly_price ?? 0);
    $isFeatured    = (bool)($packageDisplay['is_featured'] ?? false);
    $priceNote     = (string)($packageDisplay['price_note'] ?? '');
    $priceNoteSlug = trim(ltrim($priceNote, '/ '));
    $pricePeriod   = explode(' ', $priceNoteSlug)[0] ?? '';
    $pricePeriod   = $pricePeriod !== '' ? $pricePeriod : 'month';
@endphp

{{-- ====================== HERO (matches pricing-hero-band) ====================== --}}
<section class="pricing-hero-band">
    <div class="pricing-hero-band__bg" aria-hidden="true"></div>
    <div class="container pricing-hero-band__inner pricing-hero-band__inner--split" data-animate="up">
        <div class="phb-copy phb-copy--split">
            <span class="eyebrow phb-eyebrow">Package Checkout</span>
            <h1 class="phb-copy__headline">{{ $packageDisplay['name'] }}</h1>
            <p class="phb-copy__sub">{{ $packageDisplay['summary'] }}</p>
            <div class="phb-copy__ctas">
                <a href="{{ route('pricing') }}" class="button button--ghost-light">← Back to Pricing</a>
                <a href="{{ route('contact') }}" class="button button--orange">Talk to Sales</a>
            </div>
            <div class="phb-copy__badges">
                <span class="phb-badge">{{ $packageDisplay['tier'] }}</span>
                <span class="phb-badge">{{ $packageDisplay['price_note'] }}</span>
                @if($package->category === 'lead')
                    <span class="phb-badge">{{ $package->listingLimitLabel() }}</span>
                @else
                    <span class="phb-badge">Virtual assistance support</span>
                @endif
            </div>
        </div>

        <aside class="pricing-hero-band__panel">
            <span class="pricing-hero-band__panel-eyebrow">Selected Package</span>
            <h2>{{ $packageDisplay['name'] }}</h2>
            <p>{{ $packageDisplay['summary'] }}</p>

            <div class="pricing-hero-band__panel-price">
                <strong>${{ number_format($startingPrice) }}</strong>
                <span>{{ $packageDisplay['price_note'] }}</span>
            </div>

            <ul class="pricing-hero-band__panel-list">
                @foreach(array_slice($packageDisplay['features'] ?? [], 0, 4) as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>

            @if($packageDisplay['value_price'])
                <div class="pricing-hero-band__metrics">
                    <div class="pricing-hero-band__metric">
                        <strong>${{ number_format($packageDisplay['value_price']) }}</strong>
                        <span>Est. value</span>
                    </div>
                    <div class="pricing-hero-band__metric">
                        <strong>48 hr</strong>
                        <span>Avg. routing</span>
                    </div>
                    <div class="pricing-hero-band__metric">
                        <strong>90-Day</strong>
                        <span>Satisfaction</span>
                    </div>
                </div>
            @endif
        </aside>
    </div>
</section>

{{-- ====================== CHECKOUT BODY ====================== --}}
<section class="section package-checkout-section">
    <div class="container two-column package-checkout-grid">

        {{-- LEFT: Plan card — identical structure to pricing-pkg-card --}}
        <article class="pricing-pkg-card {{ $isFeatured ? 'pricing-pkg-card--featured' : '' }} package-checkout-card">
            @if($isFeatured)
                <div class="pricing-pkg-card__badge">Most Popular</div>
            @endif

            {{-- White Top Section --}}
            <div class="pricing-pkg-card__top">
                <div class="pricing-pkg-card__head">
                    <div class="pricing-pkg-card__meta">
                        <span class="pricing-label">{{ $packageDisplay['tier'] }}</span>
                        @if(!empty($packageDisplay['value_price']))
                            <span class="pricing-card__value">Value ${{ number_format($packageDisplay['value_price']) }}</span>
                        @endif
                    </div>
                    <h3 class="pricing-pkg-card__name">{{ $packageDisplay['name'] }}</h3>
                    <p class="pricing-pkg-card__tagline">{{ $packageDisplay['summary'] }}</p>
                </div>

                <div class="pricing-pkg-card__price">
                    <strong class="ppc-price-amount">${{ number_format($startingPrice) }}</strong>
                    <span class="ppc-price-period">/{{ $pricePeriod }}</span>
                </div>
            </div>

            {{-- Colored Bottom Section --}}
            <div class="pricing-pkg-card__bottom">
                <ul class="feature-check-list pricing-pkg-card__features">
                    @foreach($packageDisplay['features'] as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>

                <div class="pricing-pkg-card__actions">
                    <a href="{{ route('contact', ['plan' => $packageDisplay['name']]) }}" class="ppc-form-link">
                        Talk to sales about {{ $packageDisplay['name'] }}
                        <span class="ppc-btn-icon">→</span>
                    </a>
                </div>
            </div>
        </article>

        {{-- RIGHT: Checkout options + embed --}}
        <div class="contact-card package-checkout-side">
            <div class="dashboard-surface checkout-options-surface package-checkout-surface">
                <div class="dashboard-surface__header">
                    <div>
                        <span class="eyebrow">Checkout Options</span>
                        <h3>Launch payment</h3>
                    </div>
                </div>

                @if($stripeEnabled)
                    <div class="package-checkout-billing-list">
                        @foreach($billingOptions as $option)
                            <article class="package-checkout-billing-card">
                                <div>
                                    <strong>{{ $option['label'] }}</strong>
                                    <p>${{ number_format($option['amount']) }} {{ $option['key'] === 'monthly' ? 'per month' : 'one-time' }}</p>
                                    <small>{{ $option['note'] }}</small>
                                </div>
                                <form method="POST" action="{{ route('packages.checkout.start', $package) }}">
                                    @csrf
                                    <input type="hidden" name="billing" value="{{ $option['key'] }}">
                                    <button type="submit" class="button {{ $option['button'] }}">{{ $option['label'] }}</button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="package-checkout-inline-note">
                        <strong>Stripe is not configured here yet.</strong>
                        <p>You can still use the onboarding form below to confirm the setup flow and handoff details.</p>
                    </div>
                @endif
            </div>

            @if(!empty($packageEmbed['src']))
                <div class="embed-card embed-card--ghl is-loading" data-embed-loader aria-busy="true">
                    <div class="embed-card__loader" data-embed-loader-indicator role="status" aria-live="polite">
                        <span class="embed-card__loader-badge">Loading Form</span>
                        <h4 class="embed-card__loader-title">Preparing your onboarding form</h4>
                        <p class="embed-card__loader-copy">Secure GoHighLevel fields are connecting now. Your setup form will appear here in a few seconds.</p>
                        <div class="embed-card__loader-skeleton" aria-hidden="true">
                            <span class="embed-card__loader-line embed-card__loader-line--short"></span>
                            <span class="embed-card__loader-line embed-card__loader-line--medium"></span>
                            <div class="embed-card__loader-grid">
                                <span class="embed-card__loader-block"></span>
                                <span class="embed-card__loader-block"></span>
                                <span class="embed-card__loader-block embed-card__loader-block--wide"></span>
                                <span class="embed-card__loader-block embed-card__loader-block--tall"></span>
                            </div>
                        </div>
                    </div>
                    <iframe
                        src="{{ $packageEmbed['src'] }}"
                        title="{{ $packageEmbed['title'] ?? $packageDisplay['name'] }} form"
                        loading="lazy"
                        data-embed-loader-frame
                    ></iframe>
                </div>
            @else
                <div class="dashboard-surface package-checkout-surface">
                    <div class="package-checkout-inline-note">
                        <strong>No onboarding form is attached yet.</strong>
                        <p>Contact OmniReferral support and we will help finish setup for this package manually.</p>
                    </div>
                </div>
            @endif

            <div class="package-modal-card__actions package-checkout-footer-actions">
                <a href="{{ route('pricing') }}" class="button button--ghost-blue">Back To Pricing</a>
                <a href="{{ $postPurchaseActionUrl }}" class="button button--orange">{{ $postPurchaseActionLabel }}</a>
            </div>
        </div>
    </div>
</section>

<script src="https://link.msgsndr.com/js/form_embed.js"></script>
@endsection
