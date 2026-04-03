@extends('layouts.app')
@section('content')

{{-- ============================================================
     LISTINGS HERO
============================================================ --}}
<section class="listings-hero-v2">
    <div class="container listings-hero-v2__inner">
        <div class="listings-hero-v2__copy" data-animate="up">
            <span class="eyebrow">Property Marketplace</span>
            <h1>Find your perfect match — agent-backed, ZIP-focused listings</h1>
            <p>Search by ZIP code, budget, and property type with a faster, marketplace-style experience built for buyers, sellers, and agents.</p>
            <div class="listings-hero-v2__pills">
                <span class="lh-pill">🔒 ISA-Qualified</span>
                <span class="lh-pill">📍 ZIP-Based Routing</span>
                <span class="lh-pill">⚡ 48hr Avg. Delivery</span>
            </div>
        </div>
        <div class="listings-hero-v2__stats" data-animate="right">
            <div class="lh-stat-card">
                <span class="lh-stat-card__number">{{ $properties->count() }}</span>
                <span class="lh-stat-card__label">Active Listings</span>
            </div>
            <div class="lh-stat-card">
                <span class="lh-stat-card__number">48</span>
                <span class="lh-stat-card__label">ZIP Markets</span>
            </div>
            <div class="lh-stat-card">
                <span class="lh-stat-card__number">450+</span>
                <span class="lh-stat-card__label">Agent Partners</span>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     MAIN LISTINGS LAYOUT: Cards LEFT, Fixed Filter RIGHT
============================================================ --}}
<section class="listings-page-v2">
    <div class="container listings-page-v2__layout">

        {{-- ── LEFT: Property Grid ── --}}
        <div class="listings-main-v2">

            {{-- Results header --}}
            <div class="listings-results-bar">
                <div>
                    <h2 class="listings-results-bar__title">Featured Listings</h2>
                    <p class="listings-results-bar__count" id="visibleCount">Showing {{ $properties->count() }} properties</p>
                </div>
                <div class="listings-sort-row">
                    <label for="listingSort">Sort:</label>
                    <select id="listingSort" class="ls-sort-select">
                        <option value="relevant">Most Relevant</option>
                        <option value="price-asc">Price: Low → High</option>
                        <option value="price-desc">Price: High → Low</option>
                    </select>
                </div>
            </div>

            {{-- Cards grid --}}
            <div class="listing-cards-grid" id="listingGrid" data-stagger>
                @forelse($properties as $property)
                    <article
                        class="lc-card"
                        data-listing-card
                        data-zip="{{ $property->zip_code }}"
                        data-type="{{ strtolower($property->property_type) }}"
                        data-price="{{ $property->price }}"
                    >
                        <div class="lc-card__media">
                            <img src="{{ $property->image_url }}" alt="{{ $property->title }}" loading="lazy">
                            <span class="lc-card__status">{{ $property->status ?? 'Active' }}</span>
                            <div class="lc-card__price">${{ number_format($property->price) }}</div>
                            <button type="button" class="lc-card__save" aria-label="Save listing">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                            </button>
                        </div>
                        <div class="lc-card__body">
                            <div class="lc-card__type-row">
                                <span class="lc-card__type">{{ $property->property_type }}</span>
                                @if($property->zip_code)
                                    <span class="lc-card__zip">📍 {{ $property->zip_code }}</span>
                                @endif
                            </div>
                            <h3 class="lc-card__title">{{ $property->title }}</h3>
                            <p class="lc-card__location">{{ $property->location }}</p>
                            <div class="lc-card__meta">
                                <div class="lc-meta-item">
                                    <strong>{{ $property->beds ?? '—' }}</strong>
                                    <span>Beds</span>
                                </div>
                                <div class="lc-meta-item">
                                    <strong>{{ $property->baths ?? '—' }}</strong>
                                    <span>Baths</span>
                                </div>
                                <div class="lc-meta-item">
                                    <strong>{{ $property->sqft ? number_format($property->sqft) : '—' }}</strong>
                                    <span>Sqft</span>
                                </div>
                            </div>
                            <div class="lc-card__footer">
                                <span class="lc-card__agent">Listed by {{ optional(optional($property->realtorProfile)->user)->name ?? 'OmniPartner' }}</span>
                                <div class="lc-card__actions">
                                    <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">Details</a>
                                    <a href="{{ route('contact') }}?property={{ urlencode($property->title) }}" class="button button--orange">Contact</a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="listing-empty-state">
                        <div class="listing-empty-state__icon">🏘️</div>
                        <h3>No listings available yet</h3>
                        <p>Check back soon — new properties are added regularly.</p>
                        <a href="{{ route('contact') }}" class="button button--orange">Request a Match</a>
                    </div>
                @endforelse
            </div>

            {{-- Empty state after filter --}}
            <div class="listing-empty-state" id="listingEmptyState" hidden>
                <div class="listing-empty-state__icon">🔍</div>
                <h3>No listings match your filters</h3>
                <p>Adjust your ZIP code, property type, or price range to see more opportunities.</p>
                <button class="button button--orange" onclick="document.getElementById('filterReset').click()">Clear Filters</button>
            </div>
        </div>

        {{-- ── RIGHT: Fixed/Sticky Filter Sidebar ── --}}
        <aside class="listings-filter-sidebar" id="filterSidebar">
            <div class="lf-sidebar__inner">

                {{-- Filter card --}}
                <div class="lf-card">
                    <div class="lf-card__header">
                        <div class="lf-card__header-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        </div>
                        <div>
                            <span class="lf-card__eyebrow">Smart Filters</span>
                            <h3 class="lf-card__title">Search Listings</h3>
                        </div>
                    </div>

                    <form class="lf-form" id="listingsFilterForm" novalidate>
                        <div class="lf-field">
                            <label class="lf-label" for="filterZip">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                ZIP Code
                            </label>
                            <input type="text" id="filterZip" class="lf-input" placeholder="e.g. 75201, 90210" maxlength="10">
                        </div>

                        <div class="lf-field">
                            <label class="lf-label" for="filterType">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                Property Type
                            </label>
                            <select id="filterType" class="lf-input">
                                <option value="">Any type</option>
                                <option value="house">House</option>
                                <option value="apartment">Apartment</option>
                                <option value="condo">Condo</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>

                        <div class="lf-field">
                            <label class="lf-label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H6"/></svg>
                                Price Range
                            </label>
                            <div class="lf-price-inputs">
                                <input type="number" id="filterPriceMin" class="lf-input" placeholder="Min $" min="0" step="10000">
                                <span class="lf-price-sep">–</span>
                                <input type="number" id="filterPriceMax" class="lf-input" placeholder="Max $" min="0" step="10000">
                            </div>
                        </div>

                        <div class="lf-field">
                            <label class="lf-label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                Min Bedrooms
                            </label>
                            <div class="lf-beds-row">
                                <button type="button" class="lf-bed-btn" data-beds="0">Any</button>
                                <button type="button" class="lf-bed-btn" data-beds="1">1+</button>
                                <button type="button" class="lf-bed-btn" data-beds="2">2+</button>
                                <button type="button" class="lf-bed-btn" data-beds="3">3+</button>
                                <button type="button" class="lf-bed-btn" data-beds="4">4+</button>
                            </div>
                        </div>

                        <input type="hidden" id="filterBedsVal" value="0">

                        <div class="lf-actions">
                            <button class="button button--orange lf-btn-full" type="button" id="filterApply">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                Apply Filters
                            </button>
                            <button class="button button--ghost lf-btn-full" type="button" id="filterReset">Reset All</button>
                        </div>
                    </form>
                </div>

                {{-- Map preview --}}
                <div class="lf-card lf-card--map">
                    <div class="lf-card__map-header">
                        <h4>Market Map</h4>
                        <span class="lf-map-chip">Live</span>
                    </div>
                    <iframe
                        id="listingsMapFrame"
                        src="https://www.google.com/maps?q=United%20States&output=embed"
                        loading="lazy"
                        title="Listings market map"
                    ></iframe>
                </div>

                {{-- CTA card --}}
                <div class="lf-card lf-card--cta">
                    <div class="lf-cta__icon">🏆</div>
                    <h4>Can't find what you need?</h4>
                    <p>Our ISA team qualifies leads personally and routes to top local agents.</p>
                    <a href="{{ route('contact') }}" class="button button--orange lf-btn-full">Request a Match</a>
                </div>
            </div>
        </aside>

    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('[data-listing-card]');
    const emptyState = document.getElementById('listingEmptyState');
    const countEl = document.getElementById('visibleCount');
    const bedBtns = document.querySelectorAll('.lf-bed-btn');
    const bedsInput = document.getElementById('filterBedsVal');
    let activeBeds = 0;

    // Bedroom buttons
    bedBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            bedBtns.forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            activeBeds = parseInt(btn.dataset.beds);
            bedsInput.value = activeBeds;
        });
    });
    bedBtns[0]?.classList.add('is-active');

    // Apply filter
    document.getElementById('filterApply')?.addEventListener('click', () => {
        const zip = document.getElementById('filterZip').value.trim().toLowerCase();
        const type = document.getElementById('filterType').value.toLowerCase();
        const minPrice = parseFloat(document.getElementById('filterPriceMin').value) || 0;
        const maxPrice = parseFloat(document.getElementById('filterPriceMax').value) || Infinity;
        const beds = parseInt(bedsInput.value) || 0;

        let visible = 0;
        cards.forEach(card => {
            const cardZip = (card.dataset.zip || '').toLowerCase();
            const cardType = (card.dataset.type || '').toLowerCase();
            const cardPrice = parseFloat(card.dataset.price) || 0;
            const cardBeds = parseInt(card.dataset.beds) || 0;

            const matchZip = !zip || cardZip.includes(zip);
            const matchType = !type || cardType.includes(type);
            const matchPrice = cardPrice >= minPrice && cardPrice <= maxPrice;
            const matchBeds = beds === 0 || cardBeds >= beds;

            const show = matchZip && matchType && matchPrice && matchBeds;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (emptyState) emptyState.hidden = visible > 0;
        if (countEl) countEl.textContent = `Showing ${visible} propert${visible === 1 ? 'y' : 'ies'}`;
    });

    // Reset
    document.getElementById('filterReset')?.addEventListener('click', () => {
        document.getElementById('filterZip').value = '';
        document.getElementById('filterType').value = '';
        document.getElementById('filterPriceMin').value = '';
        document.getElementById('filterPriceMax').value = '';
        activeBeds = 0;
        bedsInput.value = 0;
        bedBtns.forEach(b => b.classList.remove('is-active'));
        bedBtns[0]?.classList.add('is-active');
        cards.forEach(c => c.style.display = '');
        if (emptyState) emptyState.hidden = true;
        if (countEl) countEl.textContent = `Showing ${cards.length} properties`;
    });

    // Sort
    document.getElementById('listingSort')?.addEventListener('change', (e) => {
        const grid = document.getElementById('listingGrid');
        const items = [...grid.querySelectorAll('[data-listing-card]')];
        items.sort((a, b) => {
            const pa = parseFloat(a.dataset.price) || 0;
            const pb = parseFloat(b.dataset.price) || 0;
            if (e.target.value === 'price-asc') return pa - pb;
            if (e.target.value === 'price-desc') return pb - pa;
            return 0;
        });
        items.forEach(item => grid.appendChild(item));
    });

    // Sticky sidebar
    const sidebar = document.getElementById('filterSidebar');
    const headerH = document.getElementById('siteHeader')?.offsetHeight || 80;
    if (sidebar) {
        sidebar.style.top = (headerH + 16) + 'px';
    }
});
</script>
@endpush
@endsection
