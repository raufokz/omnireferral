@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/pricing.css')
@endpush

@section('content')
@php
    $startingPrice = (int) ($packageDisplay['price'] ?? $package->one_time_price ?? $package->monthly_price ?? 0);
    $priceNote = (string) ($packageDisplay['price_note'] ?? '');
    $billingLabel = $packageDisplay['billing_label'] ?? trim(str_replace('/', '', $priceNote));
    $badge = $packageDisplay['badge'] ?? $packageDisplay['tier'] ?? 'Selected plan';
    $summary = $packageDisplay['summary'] ?? $package->description;
    $fullFeatures = array_values(array_filter((array) ($packageDisplay['features'] ?? [])));
    $benefits = array_values(array_filter((array) ($packageDisplay['package_benefits'] ?? [])));
    $featureGroups = array_values(array_filter((array) ($packageDisplay['feature_groups'] ?? [])));
    $afterSubmission = array_values(array_filter((array) ($packageDisplay['after_submission'] ?? [])));
    $trustIndicators = array_values(array_filter((array) ($packageDisplay['trust_indicators'] ?? [])));
    $bestFor = $packageDisplay['best_for'] ?? null;
    $supportDetails = $packageDisplay['support_details'] ?? null;
    $whatYouGet = $packageDisplay['what_you_get'] ?? null;

    if (empty($benefits)) {
        $benefits = array_slice($fullFeatures, 0, 4);
    }

    if (empty($afterSubmission)) {
        $afterSubmission = [
            'Submit the secure form with your package and market details.',
            'OmniReferral reviews your information and confirms the right workflow.',
            'Your package setup is routed to the correct GoHighLevel lane.',
            'The team follows up with next steps for launch and support.',
        ];
    }

    if (empty($trustIndicators)) {
        $trustIndicators = [
            'Secure GoHighLevel survey',
            'Real estate focused operations',
            'Clear package handoff',
            'Dedicated support workflow',
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
                    @if(!empty($packageDisplay['is_featured']))
                        <span class="pricing-badge-popular">Most Popular</span>
                    @endif
                </div>
                <h2>{{ $packageDisplay['name'] }}</h2>
                <p>{{ $summary }}</p>

                <div class="package-plan-detail__price-row">
                    <div class="package-plan-detail__price">
                        <span>Price</span>
                        <strong>${{ number_format($startingPrice) }}</strong>
                    </div>
                    <div class="package-plan-detail__billing">
                        <span>Billing type</span>
                        <strong>{{ $billingLabel !== '' ? $billingLabel : 'Package billing' }}</strong>
                    </div>
                </div>
            </div>

            <div class="package-plan-detail__meta-grid">
                <div>
                    <span>Badge</span>
                    <strong>{{ $badge }}</strong>
                </div>
                <div>
                    <span>Best for</span>
                    <strong>{{ $bestFor ?: 'Agents and teams ready to grow' }}</strong>
                </div>
            </div>

            @if($whatYouGet)
                <section class="package-plan-detail__section">
                    <h3>Full Description</h3>
                    <p>{{ $whatYouGet }}</p>
                </section>
            @endif

            <section class="package-plan-detail__section">
                <h3>Complete Feature List</h3>
                <ul class="package-detail-list package-detail-list--two">
                    @foreach($fullFeatures as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </section>

            <section class="package-plan-detail__section">
                <h3>Benefits</h3>
                <ul class="package-detail-list">
                    @foreach($benefits as $benefit)
                        <li>{{ $benefit }}</li>
                    @endforeach
                </ul>
            </section>

            <section class="package-plan-detail__section">
                <h3>What Is Included</h3>
                @if(!empty($featureGroups))
                    <div class="package-included-grid">
                        @foreach($featureGroups as $group)
                            <div class="package-included-group">
                                <strong>{{ $group['title'] ?? 'Included support' }}</strong>
                                <ul>
                                    @foreach(($group['items'] ?? []) as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @else
                    <ul class="package-detail-list">
                        @foreach(array_slice($fullFeatures, 0, 6) as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="package-plan-detail__section">
                <h3>What Happens After Submission</h3>
                <ol class="package-process-list">
                    @foreach($afterSubmission as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
            </section>

            <section class="package-plan-detail__section package-plan-detail__section--support">
                <h3>Support Information</h3>
                <p>{{ $supportDetails ?: 'OmniReferral support reviews your submission, confirms package details, and helps guide your next setup step.' }}</p>
            </section>

            <section class="package-plan-detail__section">
                <h3>Trust Indicators</h3>
                <div class="package-trust-pills">
                    @foreach($trustIndicators as $indicator)
                        <span>{{ $indicator }}</span>
                    @endforeach
                </div>
            </section>
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
                            <span class="ghl-form-loader__text">Loading secure form...</span>
                        </div>
                        <iframe
                            src="{{ $packageEmbed['src'] }}"
                            title="{{ $packageEmbed['title'] ?? $packageDisplay['name'] }} form"
                            loading="lazy"
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
    <script src="https://link.msgsndr.com/js/form_embed.js"></script>
@endif
@endsection
