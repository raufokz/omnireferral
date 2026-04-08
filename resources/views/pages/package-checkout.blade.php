@extends('layouts.app')

@section('content')
    <section class="page-hero">
        <div class="container-sm">
            <span class="eyebrow">Package Checkout</span>
            <h1>{{ $package->name }}</h1>
            <p>Choose your billing path, complete payment through Stripe, then continue directly into GoHighLevel
                onboarding.</p>
        </div>
    </section>

    <section class="section">
        <div class="container two-column">
            <div class="pricing-card pricing-card--featured">
                <span class="pricing-label">Selected Package</span>
                <h2>{{ $package->name }}</h2>
                <div class="price-row">
                    <strong>${{ number_format($package->one_time_price ?? $package->monthly_price ?? 0) }}</strong>
                    <span>{{ $package->one_time_price ? 'starting one-time' : 'monthly plan' }}</span>
                </div>
                @if($package->description)
                    <p>{{ $package->description }}</p>
                @endif
                <ul class="feature-list compact">
                    @foreach($package->features as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
                <div class="focus-list package-focus-list">
                    <article>
                        <strong>Stripe-first checkout</strong>
                        <p>Use secure hosted checkout for one-time or recurring billing where available.</p>
                    </article>
                    <article>
                        <strong>GoHighLevel onboarding</strong>
                        <p>After payment, complete the onboarding form to trigger account provisioning and CRM sync.</p>
                    </article>
                </div>
            </div>

            <div class="contact-card">
                <div class="dashboard-surface checkout-options-surface">
                    <div class="dashboard-surface__header">
                        <div>
                            <span class="eyebrow">Checkout Options</span>
                            <h3>Launch payment</h3>
                        </div>
                    </div>
                    @if($stripeEnabled)
                        <div class="hero__actions checkout-actions">
                            @if($package->one_time_price)
                                <form method="POST" action="{{ route('packages.checkout.start', $package) }}">
                                    @csrf
                                    <input type="hidden" name="billing" value="one_time">
                                    <button type="submit" class="button button--orange">Pay One-Time</button>
                                </form>
                            @endif
                            @if($package->monthly_price)
                                <form method="POST" action="{{ route('packages.checkout.start', $package) }}">
                                    @csrf
                                    <input type="hidden" name="billing" value="monthly">
                                    <button type="submit" class="button button--ghost-blue">Subscribe Monthly</button>
                                </form>
                            @endif
                        </div>
                    @else
                        <p>Stripe is not configured in this environment yet. You can still use the embedded GoHighLevel form
                            below to model the onboarding flow.</p>
                    @endif
                </div>

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
                        src="{{ $packageEmbed['src'] ?? '' }}"
                        title="{{ $packageEmbed['title'] ?? $package->name }} form"
                        loading="lazy"
                        data-embed-loader-frame
                    ></iframe>
                </div>


            </div>
        </div>
    </section>
    <script src="https://link.msgsndr.com/js/form_embed.js"></script>
@endsection
