@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/pricing.css')
@endpush

@section('content')
@php
    $pricingPlans = $pricingPlans ?? \App\Support\PricingContent::plans();
    $sourceLeadPlans = collect(array_values($leadPlans ?? ($pricingPlans['real_estate'] ?? [])))->keyBy(fn ($plan) => $plan['slug'] ?? $plan['name']);
    $sourceVaPlans = collect(array_values($vaPlans ?? ($pricingPlans['virtual_assistance'] ?? [])))->keyBy(fn ($plan) => $plan['slug'] ?? $plan['name']);

    $planUrl = function (array $plan): string {
        $slug = (string) ($plan['slug'] ?? '');

        if ($slug === '') {
            return route('pricing');
        }

        return route('packages.checkout', ['packageSlug' => $slug]);
    };

    $leadCards = collect([
        [
            'slug' => 'quick-leads',
            'fallback_name' => 'Quick Lead',
            'badge' => 'Starter',
            'price' => 369,
            'billing' => '/ Yearly',
            'subtitle' => 'Starter-friendly. City-focused.',
            'description' => 'Perfect for agents entering new markets who need verified referrals, local visibility, and predictable lead opportunities.',
            'featured' => false,
        ],
        [
            'slug' => 'power-leads',
            'fallback_name' => 'Power Lead',
            'badge' => 'Most Popular',
            'price' => 697,
            'billing' => '/ One-Time',
            'subtitle' => 'Multi-city. Scalable support.',
            'description' => 'Designed for growing teams that need more referrals, broader coverage, virtual assistance, and stronger lead qualification.',
            'featured' => true,
        ],
        [
            'slug' => 'prime-leads',
            'fallback_name' => 'Prime Lead',
            'badge' => 'Premium',
            'price' => 1979,
            'billing' => '/ One-Time',
            'subtitle' => 'Premium reach. Full-service.',
            'description' => 'Built for top-producing agents seeking maximum exposure, priority support, premium placement, and advanced lead qualification.',
            'featured' => false,
        ],
    ])->map(function ($card) use ($sourceLeadPlans, $planUrl) {
        $plan = $sourceLeadPlans->get($card['slug'], []);
        $card['name'] = $plan['name'] ?? $card['fallback_name'];
        $card['badge'] = $plan['badge'] ?? $plan['card_tag'] ?? $card['badge'];
        $card['price'] = (int) ($plan['price'] ?? $card['price']);
        $card['billing'] = $plan['price_note'] ?? $card['billing'];
        $card['subtitle'] = $plan['value_statement'] ?? $plan['summary'] ?? $card['subtitle'];
        $card['description'] = $plan['card_description'] ?? $card['description'];
        $card['featured'] = (bool) ($plan['is_featured'] ?? $card['featured']);
        $card['url'] = $planUrl(array_merge($plan, ['slug' => $card['slug']]));

        return $card;
    })->all();

    $vaCards = collect([
        [
            'slug' => 'cold-calling-isa',
            'fallback_name' => 'Cold Calling / ISA',
            'badge' => 'Dedicated ISA',
            'price' => 1999,
            'billing' => '/ Month',
            'subtitle' => 'Dedicated ISA Sales Agent',
            'description' => 'Professional outbound support focused on appointment setting, lead nurturing, follow-ups, and pipeline growth.',
            'featured' => false,
        ],
        [
            'slug' => 'social-media-mgmt',
            'fallback_name' => 'Social Media Mgmt',
            'badge' => 'Most Popular',
            'price' => 1499,
            'billing' => '/ Month',
            'subtitle' => 'Daily Long + Short Form Videos',
            'description' => 'Done-for-you content creation, audience growth, engagement management, and brand visibility.',
            'featured' => true,
        ],
        [
            'slug' => 'individual-va',
            'fallback_name' => 'Individual VA',
            'badge' => 'Flexible Support',
            'price' => 8,
            'billing' => '/ Hour',
            'subtitle' => 'Flexible Hourly Billing.',
            'description' => 'Dedicated virtual support for CRM updates, scheduling, administrative tasks, and daily business operations.',
            'featured' => false,
        ],
    ])->map(function ($card) use ($sourceVaPlans, $planUrl) {
        $plan = $sourceVaPlans->get($card['slug'])
            ?? $sourceVaPlans->get($card['fallback_name'])
            ?? $sourceVaPlans->firstWhere('name', $card['fallback_name'])
            ?? [];
        $card['name'] = $plan['name'] ?? $card['fallback_name'];
        $card['badge'] = $plan['badge'] ?? $plan['card_tag'] ?? $card['badge'];
        $card['price'] = (int) ($plan['price'] ?? $card['price']);
        $card['billing'] = $plan['price_note'] ?? $card['billing'];
        $card['subtitle'] = $plan['value_statement'] ?? $plan['summary'] ?? $card['subtitle'];
        $card['description'] = $plan['card_description'] ?? $card['description'];
        $card['featured'] = (bool) ($plan['is_featured'] ?? $card['featured']);
        $card['url'] = $planUrl(array_merge($plan, ['slug' => $card['slug']]));

        return $card;
    })->all();

    $formatPrice = fn (int $price): string => '$'.number_format($price);
@endphp

<div class="omni-pricing-page">
    <section class="omni-pricing-hero">
        <div class="omni-pricing-hero__bg" aria-hidden="true">
            <img src="{{ asset('images/home/hero_backdrop_v2.png') }}" alt="">
        </div>
        <div class="container omni-pricing-hero__inner">
            <div class="omni-pricing-hero__copy" data-animate="left">
                <span class="omni-kicker">OmniReferral Pricing</span>
                <h1>Packages built to turn verified demand into closed deals</h1>
                <p>Choose a growth path for cleaner referral handoffs, stronger agent visibility, and support that keeps every buyer or seller opportunity moving.</p>

                <div class="omni-pricing-focus-list" aria-label="Growth platform focus areas">
                    <span>Verified buyer and seller referrals</span>
                    <span>Featured agent profile options</span>
                    <span>Market-matched routing</span>
                    <span>Follow-up support</span>
                    <span>VA services available</span>
                </div>

                <div class="omni-pricing-hero__actions">
                    <a href="#pricing-packages" class="omni-btn omni-btn--orange">Compare Packages</a>
                    <a href="{{ route('contact') }}" class="omni-btn omni-btn--outline">Build My Plan</a>
                </div>

                <div class="omni-pricing-trust" aria-label="OmniReferral trust metrics">
                    <div>
                        <strong>10,000+</strong>
                        <span>Leads Delivered</span>
                    </div>
                    <div>
                        <strong>3,200+</strong>
                        <span>Verified Agents</span>
                    </div>
                    <div>
                        <strong>24/7</strong>
                        <span>Support</span>
                    </div>
                    <div>
                        <strong>50+</strong>
                        <span>Markets Covered</span>
                    </div>
                </div>
            </div>

            <aside class="omni-growth-challenge-card" data-animate="right" aria-label="10X Growth Challenge overview">
                <span class="omni-growth-challenge-card__ribbon">Premium Growth System</span>
                <div class="omni-growth-challenge-card__head">
                    <span>10X Growth Challenge</span>
                    <h2>One operating layer for referrals, visibility, assistant support, and faster market follow-up.</h2>
                </div>
                <div class="omni-growth-challenge-card__flow">
                    <div>
                        <strong>Verified Referral Engine</strong>
                        <span>Qualified buyer and seller opportunities routed by market fit.</span>
                    </div>
                    <div>
                        <strong>VA Operations Layer</strong>
                        <span>Cold calling, admin support, social media, and response coverage.</span>
                    </div>
                    <div>
                        <strong>Marketplace Visibility</strong>
                        <span>Agent placement designed to increase discovery and trust.</span>
                    </div>
                </div>
                <div class="omni-growth-challenge-card__meter" aria-label="Growth workflow stages">
                    <span>Route</span>
                    <span>Nurture</span>
                    <span>Convert</span>
                </div>
            </aside>
        </div>
    </section>

    <section class="omni-pricing-section" id="pricing-packages">
        <div class="container">
            <div class="omni-package-switcher" role="tablist" aria-label="Pricing package groups" data-animate="up">
                <button class="omni-package-switcher__button is-active" id="pricing-tab-real-estate" type="button" role="tab" aria-selected="true" aria-controls="pricing-panel-real-estate" tabindex="0" data-tab-trigger="real-estate">
                    Real Estate Plans
                </button>
                <button class="omni-package-switcher__button" id="pricing-tab-va-services" type="button" role="tab" aria-selected="false" aria-controls="pricing-panel-va-services" tabindex="-1" data-tab-trigger="va-services">
                    VA Services
                </button>
            </div>

            <div class="omni-package-panel is-active" id="pricing-panel-real-estate" role="tabpanel" aria-labelledby="pricing-tab-real-estate" data-tab-panel="real-estate">
                <div class="omni-package-panel__intro">
                    <h3>Real Estate Lead Packages</h3>
                    <p>Verified referral opportunities for agents who want cleaner intake, faster routing, and predictable market coverage.</p>
                </div>
                <div class="omni-plan-grid omni-plan-grid--primary" data-stagger>
                    @foreach($leadCards as $card)
                        <article class="omni-plan-card {{ $card['featured'] ? 'omni-plan-card--featured' : '' }}">
                            <span class="omni-plan-card__badge">{{ $card['badge'] }}</span>
                            <h3>{{ $card['name'] }}</h3>
                            <p class="omni-plan-card__subtitle">{{ $card['subtitle'] }}</p>
                            <div class="omni-plan-card__price">
                                <strong>{{ $formatPrice($card['price']) }}</strong>
                                <span>{{ $card['billing'] }}</span>
                            </div>
                            <p class="omni-plan-card__description">{{ $card['description'] }}</p>
                            <a href="{{ $card['url'] }}" class="omni-plan-card__button">Explore Plan <span aria-hidden="true">&rarr;</span></a>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="omni-package-panel" id="pricing-panel-va-services" role="tabpanel" aria-labelledby="pricing-tab-va-services" data-tab-panel="va-services" hidden>
                <div class="omni-package-panel__intro">
                    <h3>Virtual Assistant Services</h3>
                    <p>Dedicated operational support for follow-up, prospecting, content, and daily real estate workflow coverage.</p>
                </div>
                <div class="omni-plan-grid omni-plan-grid--services" data-stagger>
                    @foreach($vaCards as $card)
                        <article class="omni-plan-card omni-plan-card--service {{ $card['featured'] ? 'omni-plan-card--featured' : '' }}">
                            <span class="omni-plan-card__badge">{{ $card['badge'] }}</span>
                            <h3>{{ $card['name'] }}</h3>
                            <p class="omni-plan-card__subtitle">{{ $card['subtitle'] }}</p>
                            <div class="omni-plan-card__price">
                                <strong>{{ $formatPrice($card['price']) }}</strong>
                                <span>{{ $card['billing'] }}</span>
                            </div>
                            <p class="omni-plan-card__description">{{ $card['description'] }}</p>
                            <a href="{{ $card['url'] }}" class="omni-plan-card__button">Explore Plan <span aria-hidden="true">&rarr;</span></a>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

<section class="omni-comparison-section" aria-labelledby="pricing-comparison-heading">
    <div class="container">
        <div class="omni-section-head" data-animate="up">
            <span class="omni-kicker">Compare Plans</span>
            <h2 id="pricing-comparison-heading">Compare real estate lead packages</h2>
            <p>Power Lead is highlighted for agents who want the strongest balance of lead volume, speed, and support.</p>
        </div>

    <div class="omni-comparison-card" data-animate="up">
        <table class="omni-comparison-table">
            <caption class="visually-hidden">Real estate lead package comparison</caption>
            <thead>
                <tr>
                    <th scope="col">Features</th>
                    <th scope="col">Quick Lead</th>
                    <th scope="col" class="is-featured">
                        Power Lead
                        <span class="omni-comparison-plan-badge">Recommended</span>
                    </th>
                    <th scope="col">Prime Lead</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">Exclusive Leads</th>
                    <td>2</td>
                    <td class="is-featured">5</td>
                    <td>10</td>
                </tr>

                <tr>
                    <th scope="row">Lead Quality</th>
                    <td>High</td>
                    <td class="is-featured">Very High</td>
                    <td>Highest</td>
                </tr>

                <tr>
                    <th scope="row">Response Time</th>
                    <td>24-48 hrs</td>
                    <td class="is-featured">12-24 hrs</td>
                    <td>Within 12 hrs</td>
                </tr>

                <tr>
                    <th scope="row">Follow-up Support</th>
                    <td>Email</td>
                    <td class="is-featured">Phone + Email</td>
                    <td>Priority Phone</td>
                </tr>

                <tr>
                    <th scope="row">Replacement Guarantee</th>
                    <td>Basic</td>
                    <td class="is-featured">Yes</td>
                    <td>Yes</td>
                </tr>

                <tr>
                    <th scope="row">Lead Match Score</th>
                    <td>Good</td>
                    <td class="is-featured">Better</td>
                    <td>Best</td>
                </tr>

                <tr>
                    <th scope="row">Marketplace Exposure</th>
                    <td>Standard</td>
                    <td class="is-featured">Enhanced</td>
                    <td>Premium</td>
                </tr>

                <tr>
                    <th scope="row">Portal Access</th>
                    <td>❌ Not Included</td>
                    <td class="is-featured">✅ Included</td>
                    <td>✅ Included</td>
                </tr>

                <tr>
                    <th scope="row">Property Listings</th>
                    <td>❌ Not Included</td>
                    <td class="is-featured">✅ Up to 5 Listings / Month</td>
                    <td>✅ Up to 10 Listings / Month</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</section>


    <section class="omni-process-section">
        <div class="container">
            <div class="omni-section-head" data-animate="up">
                <span class="omni-kicker">How It Works</span>
                <h2>A cleaner path from package to pipeline</h2>
                <p>Every plan is designed around quick onboarding, useful lead context, and support that helps agents act while intent is fresh.</p>
            </div>
            <div class="omni-process-grid" data-stagger>
                @foreach([
                    ['01', 'Choose Your Plan', 'Pick the lead or VA package that fits your current market, capacity, and growth target.'],
                    ['02', 'Confirm Your Market', 'Share your target cities, ZIP codes, buyer profile, and preferred follow-up workflow.'],
                    ['03', 'Activate Routing', 'OmniReferral configures the handoff so qualified opportunities reach the right place quickly.'],
                    ['04', 'Follow Up Faster', 'Use cleaner context, assistant support, and visibility tools to keep prospects moving.'],
                    ['05', 'Scale What Works', 'Add markets, upgrade support, or pair leads with VA services as your pipeline grows.'],
                ] as [$number, $title, $copy])
                    <article class="omni-process-step">
                        <span>{{ $number }}</span>
                        <h3>{{ $title }}</h3>
                        <p>{{ $copy }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="omni-success-section">
        <div class="container">
            <div class="omni-section-head" data-animate="up">
                <span class="omni-kicker">Successes</span>
                <h2>Success Stories</h2>
                <p>Real estate teams use OmniReferral to open new markets, increase response speed, and add operational support without adding complexity.</p>
            </div>
            <div class="omni-success-grid" data-stagger>
                @foreach([
                    ['name' => 'Jessica Miller', 'market' => 'Miami, FL', 'image' => 'images/realtors/12.png', 'result' => 'Closed 3 referrals in 45 days', 'copy' => 'Power Lead helped Jessica capture high-intent waterfront buyers without adding another internal assistant.'],
                    ['name' => 'David Martinez', 'market' => 'Dallas, TX', 'image' => 'images/realtors/10.png', 'result' => 'Expanded into 4 new ZIP codes', 'copy' => 'Quick Lead gave David a predictable launch path in a new market with verified referral flow.'],
                    ['name' => 'Amanda Taylor', 'market' => 'Nashville, TN', 'image' => 'images/realtors/14.png', 'result' => 'Reached top local placement', 'copy' => 'Prime Lead paired premium profile visibility with faster routing and stronger follow-up.'],
                ] as $story)
                    <article class="omni-success-card">
                        <div class="omni-success-card__avatar">
                            <img src="{{ asset($story['image']) }}" alt="{{ $story['name'] }}" loading="lazy" width="80" height="80">
                        </div>
                        <div>
                            <h3>{{ $story['name'] }}</h3>
                            <span>{{ $story['market'] }}</span>
                        </div>
                        <strong>{{ $story['result'] }}</strong>
                        <p>{{ $story['copy'] }}</p>
                        <span class="omni-success-card__tag">Verified growth story</span>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="omni-trust-section">
        <div class="container">
            <div class="omni-trust-panel" data-animate="up">
                <div class="omni-trust-panel__copy">
                    <span class="omni-kicker">Trust Layer</span>
                    <h2>Built to feel organized before, during, and after every handoff.</h2>
                    <p>OmniReferral keeps the experience consistent across lead generation, assistant support, agent visibility, and buyer or seller routing.</p>
                </div>
                <div class="omni-trust-grid">
                    @foreach([
                        ['Verified intake', 'Requests are reviewed for useful context before they move into the agent workflow.'],
                        ['Human support', 'Sales and support coverage help agents choose the right package and resolve delivery questions.'],
                        ['Market coverage', 'The platform supports local discovery across growing real estate markets.'],
                    ] as [$title, $copy])
                        <article>
                            <strong>{{ $title }}</strong>
                            <span>{{ $copy }}</span>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="omni-faq-section">
        <div class="container">
            <div class="omni-faq-layout" data-animate="up">
                <div class="omni-faq-intro">
                    <span class="omni-kicker">FAQ</span>
                    <h2>Frequently Asked Questions</h2>
                    <p>Clear answers for package fit, onboarding, verification, and support expectations.</p>
                    <a href="{{ route('contact') }}" class="omni-btn omni-btn--orange">Talk To Sales</a>
                </div>
                <div class="omni-faq-list">
                    @foreach([
                        'Are the leads exclusive?' => 'Lead exclusivity depends on the package selected. Higher tiers receive stronger exclusivity, faster routing, and deeper follow-up support.',
                        'How fast will I receive my leads?' => 'Most package onboarding starts immediately after checkout. Routing is configured once your target markets and package details are confirmed.',
                        'Can I pair lead packages with VA services?' => 'Yes. Agents can use real estate lead packages and VA services together for prospecting, social media, follow-up, and daily operations.',
                        'Can I get a replacement?' => 'Replacement support is available based on lead quality review and the plan selected. Our team will help review fit and delivery details.',
                        'Are leads verified?' => 'OmniReferral uses a qualification workflow before handoff so agents receive cleaner referral opportunities with more useful context.',
                        'Do you offer refunds?' => 'Package terms are confirmed at checkout, and our team can help resolve fit or delivery questions through support.',
                    ] as $question => $answer)
                        <details class="omni-faq-item">
                            <summary>
                                <span>{{ $question }}</span>
                                <i aria-hidden="true">+</i>
                            </summary>
                            <p>{{ $answer }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="omni-final-cta">
        <div class="container">
            <div class="omni-final-cta__panel" data-animate="up">
                <div>
                    <span class="omni-kicker">Get Started</span>
                    <h2>Ready to grow your referral pipeline?</h2>
                    <p>Choose the plan that matches your next stage, or talk to sales for help mapping your market strategy.</p>
                </div>
                <div class="omni-final-cta__actions">
                    <a href="#pricing-packages" class="omni-btn omni-btn--orange">View Packages</a>
                    <a href="{{ route('contact') }}" class="omni-btn omni-btn--outline">Talk To Sales</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
