@extends('layouts.app')
@section('content')
<section class="pricing-hero-band">
    <div class="pricing-hero-band__bg" aria-hidden="true"></div>
    <div class="container pricing-hero-band__inner" data-animate="up">
        <div class="phb-copy">
            <span class="eyebrow phb-eyebrow">Premium Real Estate Lead Engine</span>
            <h1 class="phb-copy__headline">Simple, transparent pricing for serious agents</h1>
            <p class="phb-copy__sub">ISA-qualified, sales-backed leads with clear packages, optional virtual assistance, and rapid onboarding.</p>
            <div class="phb-copy__ctas">
                <a href="{{ $onboardingUrl }}" class="button button--orange">Get Started Today</a>
                <a href="{{ route('contact') }}" class="button button--ghost-light">Talk to Sales</a>
            </div>
            <div class="phb-copy__badges">
                <span class="phb-badge">90-day satisfaction focus</span>
                <span class="phb-badge">Fast routing</span>
                <span class="phb-badge">High-intent buyers and sellers</span>
            </div>
        </div>
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

<section class="section pricing-packages-section">
    <div class="container">
        <div class="pricing-section-head" data-animate="up">
            <span class="eyebrow">Lead Packages</span>
            <h2>Choose the package that matches your growth stage</h2>
            <p class="pricing-section-head__sub">Each tier delivers meaningful ROI, whether testing a new market or scaling a high-performing team.</p>
        </div>

        <div class="pricing-category-toggle" id="pricingToggle">
            <button class="pct-btn is-active" data-category="lead" type="button">Referrals / Lead Engine</button>
            <button class="pct-btn" data-category="va" type="button">Virtual Assistance</button>
        </div>

        <div class="pricing-cards-grid" id="leadPackagesGrid" data-stagger>
            @foreach($leadPackages as $pkg)
            <article class="pricing-pkg-card {{ $pkg->is_featured ? 'pricing-pkg-card--featured' : '' }}">
                @if($pkg->is_featured)
                <div class="pricing-pkg-card__badge">Most Popular</div>
                @endif
                <div class="pricing-pkg-card__head">
                    <h3 class="pricing-pkg-card__name">{{ $pkg->name }}</h3>
                    <p class="pricing-pkg-card__tagline">
                        @if(str_contains(strtolower($pkg->name), 'quick'))Entry-point leads with fast setup and verified intent.
                        @elseif(str_contains(strtolower($pkg->name), 'power'))Balanced lead depth, quality, and market reach.
                        @else Top-tier opportunities, highest intent, priority routing.@endif
                    </p>
                </div>
                <div class="pricing-pkg-card__price">
                    @if($pkg->one_time_price)
                    <strong class="ppc-price-amount">${{ number_format($pkg->one_time_price) }}</strong>
                    <span class="ppc-price-period">one-time</span>
                    @elseif($pkg->monthly_price)
                    <strong class="ppc-price-amount">${{ number_format($pkg->monthly_price) }}</strong>
                    <span class="ppc-price-period">per month</span>
                    @endif
                </div>
                <ul class="pricing-pkg-card__features">
                    @foreach($pkg->features as $feature)
                    <li><span class="ppf-check">&#10003;</span> {{ $feature }}</li>
                    @endforeach
                </ul>
                <div class="pricing-pkg-card__actions">
                    <a href="{{ route('packages.checkout', $pkg) }}" class="button {{ $pkg->is_featured ? 'button--orange' : 'button--blue' }} w-full">Get {{ $pkg->name }}</a>
                    <a href="{{ $onboardingUrl }}" class="ppc-form-link">Apply via form instead</a>
                </div>
            </article>
            @endforeach
        </div>

        <div class="pricing-cards-grid" id="vaPackagesGrid" style="display:none;" data-stagger>
            @foreach($assistantPackages as $pkg)
            <article class="pricing-pkg-card {{ $pkg->is_featured ? 'pricing-pkg-card--featured' : '' }}">
                @if($pkg->is_featured)
                <div class="pricing-pkg-card__badge">Top Pick</div>
                @endif
                <div class="pricing-pkg-card__head">
                    <h3 class="pricing-pkg-card__name">{{ $pkg->name }}</h3>
                    <p class="pricing-pkg-card__tagline">{{ $pkg->description ?? 'Dedicated support built for busy real estate teams.' }}</p>
                </div>
                <div class="pricing-pkg-card__price">
                    @if($pkg->monthly_price)
                    <strong class="ppc-price-amount">${{ number_format($pkg->monthly_price) }}</strong>
                    <span class="ppc-price-period">per month</span>
                    @elseif($pkg->one_time_price)
                    <strong class="ppc-price-amount">${{ number_format($pkg->one_time_price) }}</strong>
                    <span class="ppc-price-period">one-time</span>
                    @endif
                </div>
                <ul class="pricing-pkg-card__features">
                    @foreach($pkg->features as $feature)
                    <li><span class="ppf-check">&#10003;</span> {{ $feature }}</li>
                    @endforeach
                </ul>
                <div class="pricing-pkg-card__actions">
                    <a href="{{ route('packages.checkout', $pkg) }}" class="button {{ $pkg->is_featured ? 'button--orange' : 'button--blue' }} w-full">Get Started</a>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>

<section class="section pricing-why-strip">
    <div class="container">
        <div class="pricing-section-head" data-animate="up">
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
        <div class="pricing-section-head" data-animate="up">
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
                <p>Pick a package above, or talk to our sales team for a personalized recommendation.</p>
            </div>
            <div class="pfc-actions">
                <a href="{{ $onboardingUrl }}" class="button button--orange">Start Today</a>
                <a href="{{ route('contact') }}" class="button button--ghost-light">Talk to Sales</a>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var catBtns = document.querySelectorAll('.pct-btn');
    var leadGrid = document.getElementById('leadPackagesGrid');
    var vaGrid = document.getElementById('vaPackagesGrid');
    catBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            catBtns.forEach(function(b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            var cat = btn.getAttribute('data-category');
            if (leadGrid) leadGrid.style.display = cat === 'lead' ? '' : 'none';
            if (vaGrid) vaGrid.style.display = cat === 'va' ? '' : 'none';
        });
    });
});
</script>
@endpush
@endsection
