@extends('layouts.app')

@push('styles')
    <link rel="preconnect" href="https://api.leadconnectorhq.com" crossorigin>
    <link rel="preconnect" href="https://link.msgsndr.com" crossorigin>
    <link rel="dns-prefetch" href="//api.leadconnectorhq.com">
    <link rel="dns-prefetch" href="//link.msgsndr.com">
    @vite('resources/css/modules/pricing.css')
@endpush

@section('content')
@php
    $startingPrice = (int) ($packageDisplay['price'] ?? $package->one_time_price ?? $package->monthly_price ?? $package->hourly_price ?? 0);
    $priceNote = (string) ($packageDisplay['price_note'] ?? '');
    $billingLabel = $packageDisplay['billing_label'] ?? trim(str_replace('/', '', $priceNote));
    $slug = (string) ($packageDisplay['slug'] ?? $package->slug);
    $badge = match ($slug) {
        'quick-leads' => 'Quick',
        'power-leads' => 'Power',
        'prime-leads' => 'Premium',
        'cold-calling-isa', 'social-media-mgmt', 'individual-va', 'va-calling', 'va-social', 'va-individual' => 'VA',
        default => $packageDisplay['badge'] ?? $packageDisplay['tier'] ?? 'Selected plan',
    };
    $savingsLabel = $packageDisplay['savings_label'] ?? null;
    $guaranteeLabel = $packageDisplay['guarantee_label'] ?? null;
    $ctaLabel = $packageDisplay['cta_label'] ?? 'Explore Plan';
    $summary = $packageDisplay['summary'] ?? $package->description;
    $cardDescription = $packageDisplay['card_description'] ?? null;
    $fullFeatures = array_values(array_filter((array) ($packageDisplay['features'] ?? [])));
    $afterSubmission = array_values(array_filter((array) ($packageDisplay['after_submission'] ?? [])));
    $bestFor = $packageDisplay['best_for'] ?? null;
    $whatYouGet = $packageDisplay['what_you_get'] ?? null;
    $quickHighlights = array_values(array_filter([
        $bestFor ? ['label' => 'Best Fit', 'value' => $bestFor] : null,
        $whatYouGet && $whatYouGet !== $bestFor ? ['label' => 'Package Focus', 'value' => $whatYouGet] : null,
        $cardDescription ? ['label' => 'Value Summary', 'value' => $cardDescription] : null,
    ]));

    if (empty($afterSubmission)) {
        $afterSubmission = [
            'Submit the secure form with your package and market details.',
            'OmniReferral reviews your information and confirms the right workflow.',
            'Your package setup is routed to the correct GoHighLevel lane.',
            'The team follows up with next steps for launch and support.',
        ];
    }

    if (empty($quickHighlights)) {
        $quickHighlights = [
            ['label' => 'Package Focus', 'value' => $summary ?: 'OmniReferral package setup and secure onboarding.'],
        ];
    }
@endphp

<section class="package-checkout-hero package-checkout-hero--premium">
    <div class="package-checkout-hero__bg" aria-hidden="true"></div>
    <div class="container package-checkout-hero__inner">
        <div>
            <span class="eyebrow">Package Checkout</span>
            <h1>{{ $packageDisplay['name'] }}</h1>
            <p>{{ $summary }}</p>
        </div>
        <div class="package-checkout-hero__actions">
            <a href="{{ route('pricing') }}" class="button button--ghost-light">Back To Pricing</a>
            <a href="#secure-form" class="button button--orange">Complete Form</a>
        </div>
    </div>
</section>

<section class="section package-checkout-section package-checkout-section--premium">
    <div class="container package-checkout-grid package-checkout-grid--premium">
        <article class="package-plan-detail">
            <div class="package-plan-detail__header">
                <div class="package-plan-detail__eyebrow">
                    <span class="pricing-label">{{ $badge }}</span>
                </div>
                <h2>{{ $packageDisplay['name'] }}</h2>

                <div class="package-plan-detail__price-row">
                    <div class="package-plan-detail__price">
                        <span>Price</span>
                        <strong>${{ number_format($startingPrice) }}</strong>
                        @if($priceNote !== '')
                            <small class="package-plan-detail__price-note">{{ $priceNote }}</small>
                        @endif
                    </div>
                    <div class="package-plan-detail__billing">
                        <span>Billing type</span>
                        <strong>{{ $billingLabel !== '' ? $billingLabel : 'Package billing' }}</strong>
                    </div>
                </div>
            </div>

            <section class="package-plan-detail__section package-plan-detail__section--compact" aria-labelledby="quick-highlights-title">
                <h3 id="quick-highlights-title">Quick Highlights</h3>
                <div class="package-quick-highlights">
                    @foreach($quickHighlights as $highlight)
                        <article class="package-highlight-card">
                            <span>{{ $highlight['label'] }}</span>
                            <p>{{ $highlight['value'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="package-plan-detail__section">
                <h3>Complete Feature List</h3>
                <ul class="package-detail-list package-detail-list--two">
                    @foreach($fullFeatures as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </section>

            @if($guaranteeLabel || $savingsLabel)
                <section class="package-detail-guarantee" aria-label="Package guarantee and value">
                    @if($guaranteeLabel)
                        <div class="package-detail-guarantee__item">
                            <span>Guarantee</span>
                            <strong>{{ $guaranteeLabel }}</strong>
                        </div>
                    @endif
                    @if($savingsLabel)
                        <div class="package-detail-guarantee__item package-detail-guarantee__item--value">
                            <span>Package Value</span>
                            <strong>{{ $savingsLabel }}</strong>
                        </div>
                    @endif
                </section>
            @endif

            <section class="package-plan-detail__section">
                <h3>What Happens After Submission</h3>
                <ol class="package-process-list">
                    @foreach($afterSubmission as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
            </section>

            <a href="#secure-form" class="package-plan-detail__cta">{{ $ctaLabel }}</a>
        </article>

        <aside class="package-checkout-form-panel" id="secure-form">
            <div class="package-checkout-form-panel__inner">
                <span class="eyebrow">Secure GoHighLevel Form</span>
                <h2>Complete your package request</h2>
                <p>{{ $packageEmbed['description'] ?? 'Submit the secure form so OmniReferral can route your package details to the right onboarding workflow.' }}</p>

                @if(!empty($packageEmbed['src']))
                    <div class="ghl-checkout-frame is-loading" data-embed-loader aria-busy="true">
                        <div class="ghl-form-loader" data-embed-loader-indicator role="status" aria-live="polite">
                            <span class="ghl-form-loader__spinner" aria-hidden="true"></span>
                            <span class="ghl-form-loader__text embed-card__loader-copy">Loading secure form</span>
                        </div>
                        <iframe
                            src="{{ $packageEmbed['src'] }}"
                            title="{{ $packageEmbed['title'] ?? $packageDisplay['name'] }} form"
                            loading="eager"
                            fetchpriority="high"
                            data-embed-loader-frame
                            referrerpolicy="no-referrer"
                        ></iframe>
                    </div>
                @else
                    <div class="package-checkout-inline-note">
                        <strong>Secure form unavailable</strong>
                        <p>Please contact OmniReferral support and we will help finish setup for this package manually.</p>
                        <a href="{{ route('contact', ['plan' => $packageDisplay['name']]) }}" class="button button--orange">Contact Support</a>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</section>

@if(!empty($packageEmbed['src']))
    <script src="https://link.msgsndr.com/js/form_embed.js" defer></script>
@endif
@endsection
