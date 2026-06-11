@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/pricing.css')
@endpush

@section('content')
    @php
        $planSlug = 'prime-leads';
        $pricingPlan = \App\Support\PricingContent::planBySlug($planSlug);
    @endphp

    <section class="pricing-detail-hero">
        <div class="container">
            <div class="pdh-inner" data-animate="up">
                <div class="pdh-breadcrumbs">
                    <a href="{{ route('pricing') }}">Pricing</a>
                    <span aria-hidden="true">/</span>
                    <span>Prime Lead</span>
                </div>

                <h1 class="pdh-title">{{ $pricingPlan['name'] ?? 'Prime Lead' }}</h1>
                <p class="pdh-subtitle">
                    {{ $pricingPlan['summary'] ?? 'The Elite tier with full-team execution, live transfers, and priority access to premium opportunities.' }}
                </p>

                <div class="pdh-actions">
                    <a class="button button--orange" href="{{ route('packages.checkout', ['packageSlug' => $planSlug]) }}">GET STARTED</a>
                    <a class="button button--ghost-light" href="{{ route('contact') }}">TALK TO SALES</a>
                </div>

                <div class="pdh-meta">
                    <span class="pdh-pill">Priority front-of-queue access</span>
                    <span class="pdh-pill">Live transfers for hot leads</span>
                    <span class="pdh-pill">Full GoHighLevel automation</span>
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
                            {{ $pricingPlan['what_you_get'] ?? 'You get full-team execution that combines priority lead flow, automated GoHighLevel workflows, and live transfers so your team can respond instantly and convert faster.' }}
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
                                    <div class="outcome-card__text">More high-intent referrals that convert</div>
                                </div>
                                <div class="outcome-card">
                                    <div class="outcome-card__icon" aria-hidden="true">✓</div>
                                    <div class="outcome-card__text">Instant response with live transfer flow</div>
                                </div>
                                <div class="outcome-card">
                                    <div class="outcome-card__icon" aria-hidden="true">✓</div>
                                    <div class="outcome-card__text">Better pipeline predictability with reporting</div>
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
                                {{ $pricingPlan['onboarding'] ?? 'We configure your premium workflows, integrate GoHighLevel automation, and ensure your hot leads flow into the right pipeline stage immediately.' }}
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
                            <li><strong>We launch</strong> your premium workflow</li>
                            <li><strong>Receive</strong> hot leads + support</li>
                            <li><strong>Track</strong> performance</li>
                        </ol>
                    </div>

                    <div class="panel panel--premium">
                        <h3 class="panel__subtitle">Trust indicators</h3>
                        <ul class="trust-list">
                            <li>Priority workflow for high-intent referrals</li>
                            <li>Performance tracking & reporting</li>
                            <li>Fast response times for hot leads</li>
                            <li>Real estate operations team execution</li>
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
            @include('partials.pricing-saas-comparison-table', ['activeSlug' => $planSlug])
        </div>
    </section>

    <section class="section pricing-final-cta">
        <div class="container">
            <div class="pfc-inner" data-animate="up">
                <div class="pfc-copy">
                    <h2>Ready To Grow Your Business?</h2>
                    <p>Choose Prime Lead for premium, high-intent workflows designed to convert faster and scale with predictability.</p>
                </div>
                <div class="pfc-actions">
                    <a href="{{ route('packages.checkout', ['packageSlug' => $planSlug]) }}" class="button button--orange">GET STARTED</a>
                    <a href="{{ route('contact') }}" class="button button--ghost-light">TALK TO SALES</a>
                </div>
            </div>
        </div>
    </section>
@endsection
