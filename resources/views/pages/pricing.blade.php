@extends('layouts.app')

@section('content')
<section class="page-hero pricing-page-hero pricing-page-hero--premium" style="background-image: linear-gradient(rgba(0, 28, 72, 0.86), rgba(0, 28, 72, 0.86)), url('{{ asset('images/hero/bg.jpg') }}'); background-size: cover; background-position: center;">
    <div class="container pricing-page-hero__content" data-reveal>
        <span class="eyebrow" style="color: var(--color-primary);">Transparent Pricing</span>
        <h1>Choose the lead engine that matches your growth stage</h1>
        <p>From first-touch qualification to premium-intent routing, OmniReferral packages are built to help agents move faster with more confidence.</p>
        <div class="hero-chip-row hero-chip-row--pricing">
            <span>ISA-qualified leads</span>
            <span>Sales-backed packaging</span>
            <span>Optional VA support</span>
        </div>
        <div class="pricing-hero-cta">
            <a href="#lead-packages" class="button button--orange">Compare Packages</a>
            <a href="{{ route('contact') }}" class="button button--ghost-light">Talk to Sales</a>
        </div>
    </div>
</section>

<section class="section pricing-page-section pricing-page-section--premium" x-data="{ plan: 'onetime', category: 'leads' }">
    <div class="container">
        <div class="pricing-header-toggle" role="tablist">
            <button :class="category === 'leads' ? 'is-active' : ''" @click="category = 'leads'" type="button">Lead Packages</button>
            <button :class="category === 'va' ? 'is-active' : ''" @click="category = 'va'" type="button">Virtual Assistance</button>
        </div>

        <div class="pricing-toggle-row" aria-label="Billing toggle">
            <span :class="plan === 'onetime' ? 'is-active' : ''">One-Time Purchase</span>
            <button type="button" class="toggle" @click="plan = plan === 'onetime' ? 'monthly' : 'onetime'" :aria-pressed="plan === 'monthly'">
                <span class="toggle-thumb" :class="plan === 'monthly' ? 'is-active' : ''"></span>
            </button>
            <span :class="plan === 'monthly' ? 'is-active' : ''">Monthly Plan</span>
            <span class="badge badge--pill badge--save">Save 15%</span>
        </div>

        <div x-show="category === 'leads'" data-reveal id="lead-packages">
            <div class="section-heading pricing-page-heading">
                <span class="eyebrow">Real Estate Lead Packages</span>
                <h2>High-conversion packages for modern teams</h2>
                <p>Quick for momentum, Power for stronger qualification, Prime for priority routing, and VA support when you need more operational help.</p>
            </div>

            <div class="pricing-grid pricing-grid--page" data-stagger>
                @foreach($leadPackages as $package)
                    <article class="pricing-card pricing-page-card {{ $package->is_featured ? 'pricing-card--featured' : '' }}" data-reveal data-package-card data-package-name="{{ strtolower($package->name) }}" data-package-features="{{ implode(' ', $package->features ?? []) }}" data-package-featured="{{ $package->is_featured ? 'true' : 'false' }}">
                        @if($package->is_featured)
                            <div class="pricing-card__badge">Recommended</div>
                        @endif
                        <div class="pricing-card__head">
                            <span class="eyebrow">{{ $package->is_featured ? 'Growth Tier' : 'Starting Tier' }}</span>
                            <h3>{{ $package->name }}</h3>
                            <p class="pricing-card__summary">{{ $package->description ?? 'ISA-qualified, sales-backed property leads.' }}</p>
                        </div>

                        <div class="pricing-card__price">
                            <div x-show="plan === 'onetime'"><strong>${{ number_format($package->one_time_price) }}</strong><span>one-time</span></div>
                            <div x-show="plan === 'monthly'"><strong>${{ number_format($package->monthly_price) }}</strong><span>/ month</span></div>
                        </div>

                        <ul class="feature-check-list">
                            @foreach($package->features as $feature)
                                <li>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="pricing-card__actions">
                            <a class="button {{ $package->is_featured ? 'button--orange' : 'button--blue' }}" href="{{ route('packages.checkout', $package) }}">Select Plan</a>
                        </div>
                        <p class="pricing-card__micro">Secure checkout • Guided onboarding • Stripe + GoHighLevel ready</p>
                    </article>
                @endforeach
            </div>
        </div>

        <div x-show="category === 'va'" data-reveal style="display: none;" :style="category === 'va' ? 'display: block;' : ''">
            <div class="section-heading pricing-page-heading">
                <span class="eyebrow">Virtual Support</span>
                <h2>Delegate the follow-up, keep the commission</h2>
                <p>VA services for response, scheduling, and CRM hygiene when your pipeline heats up.</p>
            </div>
            <div class="pricing-grid pricing-grid--page" data-stagger>
                @foreach($assistantPackages as $package)
                    <article class="pricing-card pricing-page-card" data-reveal>
                        <div class="pricing-card__head">
                            <span class="eyebrow">Support Layer</span>
                            <h3>{{ $package->name }}</h3>
                        </div>
                        <div class="pricing-card__price">
                            <strong>${{ number_format($package->monthly_price) }}</strong><span>/ month</span>
                        </div>
                        <ul class="feature-check-list">
                            @foreach($package->features as $feature)
                                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>{{ $feature }}</span></li>
                            @endforeach
                        </ul>
                        <div class="pricing-card__actions">
                            <a href="{{ route('packages.checkout', $package) }}" class="button button--ghost-blue">Select Plan</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="comparison-block" data-reveal>
            <div class="section-heading pricing-page-heading" style="text-align: center;">
                <span class="eyebrow">Breakdown</span>
                <h2>Engine capability comparison</h2>
            </div>
            <div class="comparison-table-wrap">
                <table class="cockpit-table comparison-table">
                    <thead>
                        <tr>
                            <th>Capability</th>
                            <th class="text-center">Quick</th>
                            <th class="text-center">Power</th>
                            <th class="text-center">Prime</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comparison as $capability => $tiers)
                            <tr>
                                <td>{{ $capability }}</td>
                                <td class="text-center">{!! $tiers['quick'] !!}</td>
                                <td class="text-center">{!! $tiers['power'] !!}</td>
                                <td class="text-center">{!! $tiers['prime'] !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <section class="section pricing-cta-band" data-reveal>
            <div class="container pricing-cta-band__inner">
                <div>
                    <span class="eyebrow">Need a guided walkthrough?</span>
                    <h3>Get a 3-minute package recording or talk to sales now.</h3>
                    <p>We’ll show you how Quick, Power, Prime, and VA fit into your territory and response plan.</p>
                </div>
                <div class="pricing-cta-band__actions">
                    <a href="{{ route('contact') }}" class="button button--orange">Talk to Sales</a>
                    <a href="{{ route('contact') }}?type=recording" class="button button--ghost-light">Get Recording</a>
                </div>
            </div>
        </section>
    </div>
</section>
@endsection
