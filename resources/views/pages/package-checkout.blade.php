@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container-sm">
        <span class="eyebrow">Package Checkout</span>
        <h1>{{ $package->name }}</h1>
        <p>Choose your billing path, complete payment through Stripe, then continue directly into GoHighLevel onboarding.</p>
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
            <div class="focus-list" style="margin-top:1rem;">
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
            <div class="dashboard-surface" style="margin-bottom:1rem;">
                <div class="dashboard-surface__header">
                    <div>
                        <span class="eyebrow">Checkout Options</span>
                        <h3>Launch payment</h3>
                    </div>
                </div>
                @if($stripeEnabled)
                    <div class="hero__actions" style="justify-content:flex-start;">
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
                    <p>Stripe is not configured in this environment yet. You can still use the embedded GoHighLevel form below to model the onboarding flow.</p>
                @endif
            </div>

            <div class="embed-card">
                <iframe src="{{ $packageEmbed['src'] ?? '' }}" title="{{ $packageEmbed['title'] ?? $package->name }} form" loading="lazy"></iframe>
            </div>
            <div class="hero__actions" style="margin-top:1rem;">
                <a href="{{ $onboardingUrl }}" class="button button--orange">Continue to Onboarding</a>
                <a href="{{ route('packages.success', $package) }}" class="button button--ghost-blue">Go to Success Page</a>
            </div>
        </div>
    </div>
</section>
<script src="https://link.msgsndr.com/js/form_embed.js"></script>
@endsection
