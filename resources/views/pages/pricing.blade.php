@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/pricing.css')
@endpush

@section('content')
@php
    $featuredHeroPlan = collect($pricingPlans['real_estate'] ?? [])->firstWhere('is_featured', true)
        ?? collect($pricingPlans['real_estate'] ?? [])->first();
@endphp
<section class="pricing-hero-band">
    <div class="pricing-hero-band__bg" aria-hidden="true"></div>
    <div class="container pricing-hero-band__inner pricing-hero-band__inner--split" data-animate="up">
        <div class="phb-copy phb-copy--split">
            <span class="eyebrow phb-eyebrow">Premium Real Estate Lead Engine</span>
            <h1 class="phb-copy__headline">Simple, transparent pricing for serious agents</h1>
            <p class="phb-copy__sub">ISA-qualified, sales-backed leads with clear packages, optional virtual assistance, and a smoother GoHighLevel handoff.</p>
            <div class="phb-copy__ctas">
                <a href="#pricing-plans" class="button button--orange">View Packages</a>
                <a href="{{ route('contact') }}" class="button button--ghost-light">Talk to Sales</a>
            </div>
            <div class="phb-copy__badges">
                <span class="phb-badge">90-day satisfaction focus</span>
                <span class="phb-badge">Fast routing</span>
                <span class="phb-badge">High-intent buyers and sellers</span>
            </div>
        </div>

        @if($featuredHeroPlan)
            <aside class="pricing-hero-band__panel">
                <span class="pricing-hero-band__panel-eyebrow">Most Chosen Plan</span>
                <h2>{{ $featuredHeroPlan['name'] }}</h2>
                <p>{{ $featuredHeroPlan['summary'] }}</p>

                <div class="pricing-hero-band__panel-price">
                    <strong>${{ number_format($featuredHeroPlan['price']) }}</strong>
                    <span>{{ $featuredHeroPlan['price_note'] }}</span>
                </div>

                <ul class="pricing-hero-band__panel-list">
                    @foreach(array_slice($featuredHeroPlan['features'] ?? [], 0, 4) as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>

                <div class="pricing-hero-band__metrics">
                    <div class="pricing-hero-band__metric">
                        <strong>{{ count($pricingPlans['real_estate'] ?? []) }}</strong>
                        <span>Lead paths</span>
                    </div>
                    <div class="pricing-hero-band__metric">
                        <strong>{{ count($pricingPlans['virtual_assistance'] ?? []) }}</strong>
                        <span>VA plans</span>
                    </div>
                    <div class="pricing-hero-band__metric">
                        <strong>48 hr</strong>
                        <span>Avg. routing</span>
                    </div>
                </div>
            </aside>
        @endif
    </div>
</section>

<section class="pricing-trust-strip">
    <div class="container">
        <div class="pts-grid">
            <div class="pts-item">
                <div class="pts-item__val">12,700+</div>
                <div class="pts-item__label">Agents and Teams</div>
            </div>
            <div class="pts-item">
                <div class="pts-item__val">97%+</div>
                <div class="pts-item__label">Client Satisfaction</div>
            </div>
            <div class="pts-item">
                <div class="pts-item__val">$20k</div>
                <div class="pts-item__label">Avg. Closed Deal</div>
            </div>
            <div class="pts-item">
                <div class="pts-item__val">90-Day</div>
                <div class="pts-item__label">Guarantee Mindset</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--gray homepage-section homepage-section--pricing pricing-packages-section" id="pricing-plans">
    <div class="container">
        <div class="section-heading homepage-section__heading" data-animate="left">
            <span class="eyebrow">Pricing Snapshot</span>
            <h2>Choose the package that matches your growth stage</h2>
            <p class="pricing-section-head__sub">Each tier delivers meaningful ROI, whether testing a new market or scaling a high-performing team.</p>
        </div>

        @include('partials.pricing-plan-switcher', [
            'pricingPlans' => $pricingPlans,
            'toggleGroup' => 'pricing-page',
            'leadActionUrl' => $primaryActionUrl,
            'featureLimit' => 8,
        ])
    </div>
</section>

<section class="section pricing-why-strip">
    <div class="container">
        <div class="section-heading" data-animate="up">
            <span class="eyebrow">Why OmniReferral</span>
            <h2>What makes our leads different</h2>
        </div>
        <div class="pricing-why-grid">
            <div class="pwy-feature" data-animate="up">
                <div class="pwy-feature__icon">&#127919;</div>
                <h3>ISA-Qualified Leads</h3>
                <p>Every request is verified by our inside sales team before it reaches you.</p>
            </div>
            <div class="pwy-feature" data-animate="up">
                <div class="pwy-feature__icon">&#128205;</div>
                <h3>ZIP-Based Routing</h3>
                <p>Leads are matched to agents based on the specific ZIP codes they serve.</p>
            </div>
            <div class="pwy-feature" data-animate="up">
                <div class="pwy-feature__icon">&#9889;</div>
                <h3>48-Hour Delivery</h3>
                <p>Qualified opportunities are packaged and delivered within 48 hours on average.</p>
            </div>
            <div class="pwy-feature" data-animate="up">
                <div class="pwy-feature__icon">&#128202;</div>
                <h3>Dashboard Access</h3>
                <p>Track your leads, submissions, and status in a dedicated agent cockpit.</p>
            </div>
        </div>
    </div>
</section>

@if(!empty($comparison) && count($comparison))
<section class="section pricing-comparison-section">
    <div class="container">
        <div class="section-heading" data-animate="up">
            <span class="eyebrow">Compare Plans</span>
            <h2>What is included in each package</h2>
        </div>
        <div class="pricing-comparison-wrap">
            <table class="pricing-comparison-table">
                <thead>
                    <tr>
                        <th>Feature</th>
                        @foreach($comparison['headers'] ?? [] as $header)
                        <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($comparison['rows'] ?? [] as $row)
                        @if(($row['type'] ?? null) === 'group')
                            <tr class="pricing-comparison-group">
                                <td colspan="{{ count($comparison['headers'] ?? []) + 1 }}">{{ $row['label'] ?? '' }}</td>
                            </tr>
                        @else
                            <tr>
                                <td>{{ $row['feature'] }}</td>
                                @foreach($row['values'] ?? [] as $val)
                                <td class="pct-cell">
                                    @if($val === true || $val === 'yes')<span class="pct-check">&#10003;</span>
                                    @elseif($val === false || $val === 'no')<span class="pct-cross">--</span>
                                    @else{{ $val }}@endif
                                </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endif

<section class="section pricing-final-cta">
    <div class="container">
        <div class="pfc-inner" data-animate="up">
            <div class="pfc-copy">
                <h2>Ready to grow your book of business?</h2>
                <p>Pick a package above, or talk to our sales team for a personalized recommendation. GoHighLevel handles the post-purchase setup automatically.</p>
            </div>
            <div class="pfc-actions">
                <a href="{{ $primaryActionUrl }}" class="button button--orange">{{ $primaryActionLabel }}</a>
                <a href="{{ route('contact') }}" class="button button--ghost-light">Talk to Sales</a>
            </div>
        </div>
    </div>
</section>

@push('scripts')
    @include('partials.pricing-toggle-script')
@endpush
@endsection
