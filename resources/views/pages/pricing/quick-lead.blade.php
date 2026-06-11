@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/pricing.css')
@endpush

@section('content')
    @php
        $planSlug = 'quick-leads';
        $pricingPlan = \App\Support\PricingContent::planBySlug($planSlug);
    @endphp

    <section class="pricing-detail-hero">
        <div class="container">
            <div class="pdh-inner" data-animate="up">
                <div class="pdh-breadcrumbs">
                    <a href="{{ route('pricing') }}">Pricing</a>
                    <span aria-hidden="true">/</span>
                    <span>Quick Lead</span>
                </div>

                <h1 class="pdh-title">{{ $pricingPlan['name'] ?? 'Quick Lead' }}</h1>
                <p class="pdh-subtitle">
                    {{ $pricingPlan['summary'] ?? 'A launch-ready lead tier designed to start your referral flow with verified opportunities and clear follow-up.' }}
                </p>

                <div class="pdh-actions">
                    <a class="button button--orange" href="{{ route('packages.checkout', ['packageSlug' => $planSlug]) }}">GET STARTED</a>
                    <a class="button button--ghost-light" href="{{ route('contact') }}">TALK TO SALES</a>
                </div>

                <div class="pdh-meta">
                    <span class="pdh-pill">Best ROI for consistent outreach</span>
                    <span class="pdh-pill">No confusing add-ons</span>
                    <span class="pdh-pill">GoHighLevel-ready handoff</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section pricing-section">
        <div class="container">
            <div class="pricing-detail-grid">
                <div class="pdx-col">
                    <div class="panel panel--premium">
                        <h2 class="panel__title">Full package overview</h2>
                        <p class="panel__copy">
                            {{ $pricingPlan['what_you_get'] ?? 'You get a structured lead generation lane that sources referrals, routes them to the right workflow, and keeps follow-up moving so you can close more deals.' }}
                        </p>

                        <div class="pdx-divider"></div>

                        <h3 class="panel__subtitle">Service highlights</h3>
                        <ul class="feature-check-list pdx-list">
                            @foreach(($pricingPlan['service_highlights'] ?? ($pricingPlan['highlights'] ?? [])) as $h)
                                <li>{{ $h }}</li>
                            @endforeach
                            @if(empty($pricingPlan['service_highlights'] ?? []) && empty($pricingPlan['highlights'] ?? []))
                                @foreach(($pricingPlan['features'] ?? []) as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            @endif
                        </ul>
                    </div>

                    <div class="panel panel--premium">
                        <h2 class="panel__title">Expected outcomes</h2>
                        <div class="outcome-grid">
                            @foreach(($pricingPlan['expected_outcomes'] ?? []) as $o)
                                <div class="outcome-card">
                                    <div class="outcome-card__icon" aria-hidden="true">✓</div>
                                    <div class="outcome-card__text">{{ $o }}</div>
                                </div>
                            @endforeach

                            @if(empty($pricingPlan['expected_outcomes'] ?? []))
                                <div class="outcome-card">
                                    <div class="outcome-card__icon" aria-hidden="true">✓</div>
                                    <div class="outcome-card__text">More verified referral opportunities each month</div>
                                </div>
                                <div class="outcome-card">
                                    <div class="outcome-card__icon" aria-hidden="true">✓</div>
                                    <div class="outcome-card__text">Faster routing into your CRM workflow</div>
                                </div>
                                <div class="outcome-card">
                                    <div class="outcome-card__icon" aria-hidden="true">✓</div>
                                    <div class="outcome-card__text">Higher conversion confidence with clear follow-up</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <aside class="pdx-col pdx-aside">
                    <div class="panel panel--premium panel--sticky">
                        <div class="price-row">
                            <strong>${{ number_format((int) ($pricingPlan['price'] ?? 0)) }}</strong>
                            <span>{{ $pricingPlan['price_note'] ?? '/ month' }}</span>
                        </div>

                        @if(!empty($pricingPlan['best_for']))
                            <div class="pdx-block">
                                <span class="pdx-label">Best for</span>
                                <p class="pdx-copy">{{ $pricingPlan['best_for'] }}</p>
                            </div>
                        @endif

                        @if(!empty($pricingPlan['support_level']))
                            <div class="pdx-block">
                                <span class="pdx-label">Support level</span>
                                <p class="pdx-copy">{{ $pricingPlan['support_level'] }}</p>
                            </div>
                        @endif

                        <div class="pdx-block">
                            <span class="pdx-label">Onboarding</span>
                            <p class="pdx-copy">
                                {{ $pricingPlan['onboarding'] ?? 'We confirm your target markets and configure your GoHighLevel workflow so your leads are handled correctly from day one.' }}
                            </p>
                        </div>

                        <div class="pdx-aside-actions">
                            <a class="button button--orange" href="{{ route('packages.checkout', ['packageSlug' => $planSlug]) }}">GET STARTED</a>
                            <a class="button button--ghost-light" href="{{ route('contact') }}">TALK TO SALES</a>
                        </div>

                        @if(!empty($pricingPlan['trust_note']))
                            <p class="pdx-trust">{{ $pricingPlan['trust_note'] }}</p>
                        @endif
                    </div>

                    <div class="panel panel--premium">
                        <h3 class="panel__subtitle">Process overview</h3>
                        <ol class="process-steps">
                            <li><strong>Choose</strong> your package</li>
                            <li><strong>Complete onboarding</strong></li>
                            <li><strong>We launch</strong> your campaign</li>
                            <li><strong>Receive leads</strong> + support</li>
                            <li><strong>Track</strong> performance</li>
                        </ol>
                    </div>

                    <div class="panel panel--premium">
                        <h3 class="panel__subtitle">Trust indicators</h3>
                        <ul class="trust-list">
                            <li>Verified lead workflow</li>
                            <li>Real estate focused operations team</li>
                            <li>Performance tracking & reporting</li>
                            <li>Fast response time when you need support</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="section pricing-section pricing-faq">
        <div class="container">
            <div class="section-heading" data-animate="up">
                <span class="eyebrow">FAQs</span>
                <h2>Everything you need to know</h2>
            </div>

            <div class="faq-accordion">
                @php
                    $faqs = [
                        'How do referrals work?' => 'After purchase, onboarding confirms your markets and workflow. Leads are sourced and routed into your system so you can focus on closing.',
                        'What happens after purchase?' => 'We configure your setup, launch your campaign workflow, and ensure leads move through your pipeline with the correct follow-up timing.',
                        'Can I upgrade later?' => 'Yes. You can upgrade when your volume and growth goals evolve.',
                        'Are there any recurring fees?' => 'Pricing reflects the package billing model. The checkout page confirms the exact amount for your selection.',
                        'How quickly can I get started?' => 'Once onboarding is complete, your lead workflow is launched quickly so you can begin receiving qualified opportunities.',
                        'What support is included?' => 'You receive a dedicated support lane plus performance updates so you always know what’s happening in your pipeline.',
                        'What makes Omnireferral different?' => 'We combine vetted lead sourcing with performance-driven operations and a workflow designed for real estate teams.',
                    ];
                @endphp

                @foreach($faqs as $q => $a)
                    <details class="faq-item">
                        <summary class="faq-summary">
                            <span>{{ $q }}</span>
                            <span class="faq-chevron" aria-hidden="true">+</span>
                        </summary>
                        <div class="faq-body">
                            <p>{{ $a }}</p>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section pricing-section">
        <div class="container">
            @include('partials.pricing-saas-comparison-table', [
                'activeSlug' => $planSlug
            ])
        </div>
    </section>

    <section class="section pricing-final-cta">
        <div class="container">
            <div class="pfc-inner" data-animate="up">
                <div class="pfc-copy">
                    <h2>Ready To Grow Your Business?</h2>
                    <p>Choose Quick Lead to start receiving verified opportunities faster — and get the support to turn them into closed deals.</p>
                </div>
                <div class="pfc-actions">
                    <a href="{{ route('packages.checkout', ['packageSlug' => $planSlug]) }}" class="button button--orange">GET STARTED</a>
                    <a href="{{ route('contact') }}" class="button button--ghost-light">TALK TO SALES</a>
                </div>
            </div>
        </div>
    </section>
@endsection
