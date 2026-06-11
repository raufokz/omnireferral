@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/pricing.css')
@endpush

@section('content')
@php
    $leadPlans = [
        [
            'slug' => 'quick-leads',
            'name' => 'Quick Lead',
            'tier' => 'STARTER',
            'price' => 399,
            'price_note' => '/ month',
            'summary' => 'A focused entry package for agents who want verified referral flow in a smaller service area.',
            'features' => [
                '16-20 total referrals',
                'Up to 2 cities or ZIP codes',
                'Email support',
                '1-step verification',
            ],
            'best_for' => 'New Agents',
            'cta_url' => route('packages.checkout', ['packageSlug' => 'quick-leads']),
            'is_featured' => false,
        ],
        [
            'slug' => 'power-leads',
            'name' => 'Power Lead',
            'tier' => 'MOST POPULAR',
            'price' => 899,
            'price_note' => '/ month',
            'summary' => 'The balanced growth tier with stronger routing priority, more referrals, and included VA support.',
            'features' => [
                '30+ total referrals',
                'Up to 5 cities or ZIP codes',
                '3 hrs/week virtual assistance',
                'Email + text support',
            ],
            'best_for' => 'Growing Teams',
            'cta_url' => route('packages.checkout', ['packageSlug' => 'power-leads']),
            'is_featured' => true,
        ],
        [
            'slug' => 'prime-leads',
            'name' => 'Prime Lead',
            'tier' => 'PREMIUM',
            'price' => 1999,
            'price_note' => '/ month',
            'summary' => 'A high-volume package for teams that need broader coverage, deeper verification, and premium support.',
            'features' => [
                '50+ total referrals',
                'Up to 10 cities or ZIP codes',
                '15 hrs/week virtual assistance',
                'Call + text + email support',
            ],
            'best_for' => 'High Volume Agents',
            'cta_url' => route('packages.checkout', ['packageSlug' => 'prime-leads']),
            'is_featured' => false,
        ],
    ];

    $featuredHeroPlan = $leadPlans[1];
    $heroMetrics = [
        ['value' => '3', 'label' => 'Lead packages'],
        ['value' => '5', 'label' => 'Launch steps'],
        ['value' => '24/7', 'label' => 'Dashboard access'],
    ];
@endphp

<section class="pricing-hero-band pricing-hero-band--reference">
    <div class="pricing-hero-band__bg" aria-hidden="true"></div>
    <div class="container pricing-hero-band__inner pricing-hero-band__inner--split" data-animate="up">
        <div class="phb-copy phb-copy--split">
            <span class="eyebrow phb-eyebrow">OmniReferral Lead Packages</span>
            <h1 class="phb-copy__headline">Qualified real estate referrals without the chase</h1>
            <p class="phb-copy__sub">Choose a lead package built for real estate teams that want verified opportunities, clear routing, and follow-up workflows that move fast after checkout.</p>

            <div class="phb-copy__ctas">
                <a href="#pricing-plans" class="button button--orange">View Packages</a>
                <a href="{{ route('contact') }}" class="button button--ghost-light">Talk To Sales</a>
            </div>

            <div class="phb-copy__badges" aria-label="Service highlights">
                <span class="phb-badge">ISA qualified leads</span>
                <span class="phb-badge">ZIP based routing</span>
                <span class="phb-badge">Fast onboarding</span>
            </div>
        </div>

        <aside class="pricing-hero-band__panel pricing-hero-band__panel--featured" aria-label="Featured pricing plan">
            <div class="pricing-hero-band__panel-top">
                <span class="pricing-hero-band__panel-eyebrow">Most Popular</span>
                <span class="pricing-hero-band__panel-chip">Power Lead</span>
            </div>

            <h2>{{ $featuredHeroPlan['name'] }}</h2>
            <p>{{ $featuredHeroPlan['summary'] }}</p>

            <div class="pricing-hero-band__panel-price">
                <strong>${{ number_format($featuredHeroPlan['price']) }}</strong>
                <span>{{ $featuredHeroPlan['price_note'] }}</span>
            </div>

            <ul class="pricing-hero-band__panel-list">
                @foreach($featuredHeroPlan['features'] as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>

            <a href="{{ $featuredHeroPlan['cta_url'] }}" class="button button--orange">EXPLORE PLAN</a>

            <div class="pricing-hero-band__metrics">
                @foreach($heroMetrics as $metric)
                    <div class="pricing-hero-band__metric">
                        <strong>{{ $metric['value'] }}</strong>
                        <span>{{ $metric['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </aside>
    </div>
</section>

<section class="section section--gray homepage-section homepage-section--pricing pricing-packages-section" id="pricing-plans">
    <div class="container">
        <div class="section-heading homepage-section__heading pricing-section-head" data-animate="left">
            <span class="eyebrow">Pricing</span>
            <h2>Choose your growth lane</h2>
            <p class="pricing-section-head__sub">Switch between real estate referral packages and VA execution services without leaving the page.</p>
        </div>

        @include('partials.pricing-plan-switcher', ['leadPlans' => $leadPlans])
    </div>
</section>

<section class="section pricing-why-strip">
    <div class="container">
        <div class="section-heading" data-animate="up">
            <span class="eyebrow">Why OmniReferral</span>
            <h2>Why our leads are different</h2>
        </div>

        <div class="pricing-why-grid">
            <div class="pwy-feature" data-animate="up">
                <div class="pwy-feature__icon">ISA</div>
                <h3>ISA Qualified Leads</h3>
                <p>Every request is reviewed by our inside sales workflow before it reaches your pipeline.</p>
            </div>
            <div class="pwy-feature" data-animate="up">
                <div class="pwy-feature__icon">ZIP</div>
                <h3>ZIP Based Routing</h3>
                <p>Opportunities are matched to the areas your team actually serves.</p>
            </div>
            <div class="pwy-feature" data-animate="up">
                <div class="pwy-feature__icon">FAST</div>
                <h3>Fast Handoff</h3>
                <p>Qualified referrals move into your follow-up workflow quickly after intake.</p>
            </div>
            <div class="pwy-feature" data-animate="up">
                <div class="pwy-feature__icon">CRM</div>
                <h3>Dashboard Access</h3>
                <p>Track lead status, follow-up, and activity from a dedicated agent dashboard.</p>
            </div>
        </div>
    </div>
</section>

@include('partials.pricing-comparison-table')

<section class="section pricing-benefits">
    <div class="container">
        <div class="section-heading" data-animate="up">
            <span class="eyebrow">How it works</span>
            <h2>From package selection to measurable follow-up</h2>
            <p class="pricing-section-head__sub">A compact launch process designed to get your market, routing, and support workflow moving without extra friction.</p>
        </div>

        <div class="pricing-how-grid">
            <div class="how-step" data-animate="up">
                <div class="how-step__num">1</div>
                <h3>Choose Your Package</h3>
                <p>Select the referral tier that matches your market coverage and growth target.</p>
            </div>
            <div class="how-step" data-animate="up">
                <div class="how-step__num">2</div>
                <h3>Complete Onboarding</h3>
                <p>Share your service areas, lead preferences, and routing details.</p>
            </div>
            <div class="how-step" data-animate="up">
                <div class="how-step__num">3</div>
                <h3>We Launch Your System</h3>
                <p>Our team configures your referral flow and follow-up workflow.</p>
            </div>
            <div class="how-step" data-animate="up">
                <div class="how-step__num">4</div>
                <h3>Receive Leads & Support</h3>
                <p>Qualified referrals and support updates move into your operating rhythm.</p>
            </div>
            <div class="how-step how-step--wide" data-animate="up">
                <div class="how-step__num">5</div>
                <h3>Track Performance</h3>
                <p>Monitor activity and adjust your focus as your pipeline grows.</p>
            </div>
        </div>
    </div>
</section>

<section class="section pricing-qualification">
    <div class="container">
        <div class="section-heading" data-animate="up">
            <span class="eyebrow">Benefits</span>
            <h2>More qualified conversations, less chasing</h2>
        </div>

        <div class="pricing-qual-grid">
            <div class="qual-card" data-animate="up">
                <div class="qual-card__icon">OPS</div>
                <h3>ISA + Ops Verification</h3>
                <p>Reduce wasted follow-up by prioritizing prospects that have already passed through a real workflow.</p>
            </div>
            <div class="qual-card" data-animate="up">
                <div class="qual-card__icon">ZIP</div>
                <h3>ZIP Territory Routing</h3>
                <p>Keep referrals aligned to the cities and ZIP codes where your team can respond quickly.</p>
            </div>
            <div class="qual-card" data-animate="up">
                <div class="qual-card__icon">FLOW</div>
                <h3>Workflow First Follow-up</h3>
                <p>Use structured timing, next steps, and reminders so fewer leads slip through the cracks.</p>
            </div>
        </div>
    </div>
</section>

<section class="section pricing-trust">
    <div class="container">
        <div class="section-heading" data-animate="up">
            <span class="eyebrow">Trust</span>
            <h2>Built for real estate teams that value execution</h2>
        </div>

        <div class="pricing-trust-grid">
            @foreach(['Vetted ISA service', 'Fast onboarding', 'Transparent pricing', 'Dedicated support', 'Real operational workflows'] as $trustItem)
                <div class="trust-pill" data-animate="up">
                    <span class="trust-pill__check" aria-hidden="true">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </span>
                    {{ $trustItem }}
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section pricing-faq">
    <div class="container">
        <div class="section-heading" data-animate="up">
            <span class="eyebrow">FAQs</span>
            <h2>Everything you need to know</h2>
        </div>

        <div class="faq-accordion">
            @php
                $faqs = [
                    'How do referrals work?' => 'After checkout, onboarding confirms your target areas and workflow. Referrals are qualified, routed, and organized so your team can focus on the right conversations.',
                    'What happens after purchase?' => 'You complete onboarding, we configure your package workflow, and your lead support process begins based on the tier you selected.',
                    'Can I upgrade later?' => 'Yes. You can move from Quick Lead to Power Lead or Prime Lead as your volume and coverage needs grow.',
                    'Are there any recurring fees?' => 'Each package is billed according to the pricing shown at checkout. Your checkout screen confirms the exact recurring amount before purchase.',
                    'How quickly can I get started?' => 'You can start onboarding immediately after checkout. Launch timing depends on how quickly your market and routing details are completed.',
                    'What support is included?' => 'Support scales by package, from email support to text, call, and dedicated execution support on higher tiers.',
                    'What makes Omnireferral different?' => 'OmniReferral combines qualification, routing, dashboard visibility, and real operational follow-up instead of simply handing over raw names.',
                    'Do you offer VA support too?' => 'Yes. Power Lead includes 3 hrs/week of virtual assistance and Prime Lead includes 15 hrs/week. Additional VA support can be discussed with sales.',
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

<section class="section pricing-final-cta">
    <div class="container">
        <div class="pfc-inner" data-animate="up">
            <div class="pfc-copy">
                <h2>Ready to choose your referral package?</h2>
                <p>Explore a package above to start checkout, or talk to sales if you want help choosing the right tier.</p>
            </div>
            <div class="pfc-actions">
                <a href="#pricing-plans" class="button button--orange">View Packages</a>
                <a href="{{ route('contact') }}" class="button button--ghost-light">Talk To Sales</a>
            </div>
        </div>
    </div>
</section>

@push('scripts')
    @include('partials.pricing-toggle-script')
@endpush
@endsection
