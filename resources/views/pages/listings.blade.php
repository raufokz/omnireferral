@extends('layouts.app')

@section('content')
@php
    $propertyCollection = $properties instanceof \Illuminate\Contracts\Pagination\Paginator
        ? collect($properties->items())
        : collect($properties);
    $listingCount = $properties instanceof \Illuminate\Contracts\Pagination\Paginator
        ? $properties->total()
        : $propertyCollection->count();
    $zipMarketCount = $propertyCollection->pluck('zip_code')->filter()->unique()->count();
    $propertyTypeCount = $propertyCollection->pluck('property_type')->filter()->unique()->count();
    $propertyTypeOptions = collect($propertyTypes ?? [])
        ->filter()
        ->values()
        ->whenEmpty(fn ($collection) => $collection->push('House', 'Apartment', 'Condo', 'Townhome', 'Commercial'));
    $activeFilterCount = collect($filters ?? [])
        ->except(['sort'])
        ->filter(fn ($value) => filled($value))
        ->count();
@endphp

<section class="listings-hero-v2 listings-hero-v2--marketplace">
    <div class="listings-hero-v2__glow" aria-hidden="true"></div>
    <div class="container listings-hero-v2__inner">
        <div class="listings-hero-v2__copy" data-animate="left">
            <span class="eyebrow">Property Marketplace</span>
            <h1>Find a home that feels ready for your next move</h1>
            <p>Browse verified homes, compare essentials at a glance, and connect with the right OmniReferral agent without friction.</p>
            <div class="listings-hero-v2__actions">
                <a href="#listing-results" class="button button--orange">Browse Listings</a>
                <a href="{{ route('contact') }}" class="button button--ghost-light">Request A Match</a>
            </div>
            <div class="listings-hero-v2__pills">
                <span class="lh-pill">Verified inventory</span>
                <span class="lh-pill">ZIP-focused search</span>
                <span class="lh-pill">Agent-backed handoff</span>
            </div>
        </div>

        <aside class="listings-hero-v2__panel listings-hero-search-card" data-animate="right" aria-label="Quick property search">
            <span class="listings-hero-v2__panel-eyebrow">Smart Search</span>
            <h2>Search by city, ZIP, or keyword</h2>
            <form method="GET" action="{{ route('listings') }}" class="listings-hero-search">
                <label>
                    <span>City, ZIP, or keyword</span>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Dallas, 75201, lakeview">
                </label>
                <label>
                    <span>ZIP Code</span>
                    <input type="text" name="zip_code" value="{{ $filters['zip_code'] ?? '' }}" placeholder="75201" maxlength="10">
                </label>
                <button type="submit" class="button button--orange">Search Homes</button>
            </form>
            <div class="listings-hero-v2__stats">
                <div class="lh-stat-card">
                    <span class="lh-stat-card__number">{{ number_format($listingCount) }}</span>
                    <span class="lh-stat-card__label">Active Listings</span>
                </div>
                <div class="lh-stat-card">
                    <span class="lh-stat-card__number">{{ $zipMarketCount }}</span>
                    <span class="lh-stat-card__label">ZIP Markets</span>
                </div>
                <div class="lh-stat-card">
                    <span class="lh-stat-card__number">{{ $propertyTypeCount }}</span>
                    <span class="lh-stat-card__label">Property Types</span>
                </div>
            </div>
        </aside>
    </div>
</section>

<section class="listings-page-v2" id="listing-results">
    <div class="container listings-page-v2__layout">
        <div class="listings-main-v2">
            <div class="listings-results-bar">
                <div>
                    <span class="listings-results-bar__eyebrow">{{ $activeFilterCount }} active {{ \Illuminate\Support\Str::plural('filter', $activeFilterCount) }}</span>
                    <h2 class="listings-results-bar__title">Featured Listings</h2>
                    <p class="listings-results-bar__count" id="visibleCount">Showing {{ number_format($listingCount) }} {{ \Illuminate\Support\Str::plural('property', $listingCount) }}</p>
                </div>
                <form method="GET" action="{{ route('listings') }}" class="listings-sort-row">
                    @foreach(['q', 'zip_code', 'property_type', 'listing_intent', 'price_min', 'price_max', 'beds_min', 'baths_min', 'area_min', 'area_max'] as $filterName)
                        <input type="hidden" name="{{ $filterName }}" value="{{ $filters[$filterName] ?? '' }}">
                    @endforeach
                    <label for="listingSort">Sort</label>
                    <select id="listingSort" class="ls-sort-select" name="sort" onchange="this.form.submit()">
                        <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest</option>
                        <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Price Low → High</option>
                        <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Price High → Low</option>
                    </select>
                </form>
            </div>

            <div class="listing-cards-grid listing-cards-grid--marketplace" id="listingGrid" data-stagger>
                @forelse($propertyCollection as $property)
                    @php
                        $locationLine = $property->city
                            ? trim($property->city . ', ' . ($property->state ?: '') . ' ' . $property->zip_code)
                            : trim($property->location . ($property->zip_code ? ' · ' . $property->zip_code : ''));
                    @endphp
                    <article
                        class="lc-card lc-card--marketplace"
                        data-listing-card
                        data-zip="{{ $property->zip_code }}"
                        data-type="{{ strtolower($property->property_type) }}"
                        data-price="{{ $property->price }}"
                        data-beds="{{ (int) ($property->beds ?? 0) }}"
                        data-baths="{{ (float) ($property->baths ?? 0) }}"
                        data-area="{{ (int) ($property->sqft ?? 0) }}"
                    >
                        <a href="{{ route('properties.show', $property) }}" class="lc-card__media" aria-label="View {{ $property->title }}">
                            <img src="{{ $property->image_url }}" alt="{{ $property->title }}" loading="lazy" decoding="async">
                            <span class="lc-card__status">{{ $property->listingIntentLabel() }}</span>
                            @if($property->is_featured)
                                <span class="lc-card__featured">Featured</span>
                            @endif
                            <div class="lc-card__price">{{ $property->formattedPrice() }}</div>
                        </a>
                        <div class="lc-card__save-group">
                            <form method="POST" action="{{ route('properties.favorite.toggle', $property) }}" class="lc-card__save-form">
                                @csrf
                                <button
                                    type="submit"
                                    class="lc-card__save {{ $property->is_favorited ? 'is-active' : '' }}"
                                    aria-label="{{ $property->is_favorited ? 'Remove listing from favorites' : 'Add listing to favorites' }}"
                                >
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="{{ $property->is_favorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                </button>
                            </form>
                            <span class="lc-card__save-count">{{ number_format($property->favorites_count ?? 0) }}</span>
                        </div>
                        <div class="lc-card__body">
                            <div class="lc-card__type-row">
                                <span class="lc-card__type">{{ \Illuminate\Support\Str::headline($property->property_type) }}</span>
                                @if($property->zip_code)
                                    <span class="lc-card__zip">ZIP {{ $property->zip_code }}</span>
                                @endif
                            </div>
                            <h3 class="lc-card__title">{{ $property->title }}</h3>
                            <p class="lc-card__location">{{ $locationLine }}</p>
                            <div class="lc-card__meta" aria-label="Property facts">
                                <div class="lc-meta-item">
                                    <strong>{{ $property->beds ?? '-' }}</strong>
                                    <span>Beds</span>
                                </div>
                                <div class="lc-meta-item">
                                    <strong>{{ $property->baths ?? '-' }}</strong>
                                    <span>Baths</span>
                                </div>
                                <div class="lc-meta-item">
                                    <strong>{{ $property->sqft ? number_format($property->sqft) : '-' }}</strong>
                                    <span>Sqft</span>
                                </div>
                            </div>
                            <div class="lc-card__footer">
                                <span class="lc-card__agent" title="Listed by {{ $property->listedByLabel() }}">Listed by {{ $property->listedByLabel() }}</span>
                                <div class="lc-card__actions">
                                    <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">Details</a>
                                    <a href="{{ route('properties.show', $property) }}#property-contact" class="button button--orange">Contact</a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="listing-empty-state">
                        <div class="listing-empty-state__icon">⌂</div>
                        <h3>No listings available yet</h3>
                        <p>Check back soon. New properties are added regularly.</p>
                        <a href="{{ route('contact') }}" class="button button--orange">Request a Match</a>
                    </div>
                @endforelse
            </div>

            @if($properties instanceof \Illuminate\Contracts\Pagination\Paginator)
                <div class="listings-pagination">
                    {{ $properties->links() }}
                </div>
            @endif
        </div>

        <aside class="listings-filter-sidebar" id="filterSidebar">
            <div class="lf-sidebar__inner">
                <div class="lf-card lf-card--sticky-search">
                    <div class="lf-card__header">
                        <div class="lf-card__header-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        </div>
                        <div>
                            <span class="lf-card__eyebrow">Advanced Filters</span>
                            <h3 class="lf-card__title">Refine your search</h3>
                        </div>
                    </div>

                    <form class="lf-form" id="listingsFilterForm" method="GET" action="{{ route('listings') }}">
                        <div class="lf-field">
                            <label class="lf-label" for="filterKeywords">Keyword, city, or ZIP</label>
                            <input type="search" id="filterKeywords" name="q" class="lf-input" placeholder="Dallas, lakeview, 75201" value="{{ $filters['q'] ?? '' }}">
                        </div>

                        <div class="lf-field">
                            <label class="lf-label" for="filterIntent">Listing Type</label>
                            <select id="filterIntent" name="listing_intent" class="lf-input">
                                <option value="">For sale or rent</option>
                                <option value="sale" @selected(($filters['listing_intent'] ?? '') === 'sale')>For Sale</option>
                                <option value="rent" @selected(($filters['listing_intent'] ?? '') === 'rent')>For Rent</option>
                            </select>
                        </div>

                        <div class="lf-field">
                            <label class="lf-label" for="filterType">Property Type</label>
                            <select id="filterType" name="property_type" class="lf-input">
                                <option value="">Any type</option>
                                @foreach($propertyTypeOptions as $propertyType)
                                    @php $typeValue = strtolower((string) $propertyType); @endphp
                                    <option value="{{ $typeValue }}" @selected(($filters['property_type'] ?? '') === $typeValue)>{{ \Illuminate\Support\Str::headline((string) $propertyType) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lf-field">
                            <label class="lf-label">Price Range</label>
                            <div class="lf-price-inputs">
                                <input type="number" name="price_min" class="lf-input" placeholder="Min $" min="0" step="10000" value="{{ $filters['price_min'] ?? '' }}">
                                <span class="lf-price-sep">-</span>
                                <input type="number" name="price_max" class="lf-input" placeholder="Max $" min="0" step="10000" value="{{ $filters['price_max'] ?? '' }}">
                            </div>
                        </div>

                        <div class="lf-field">
                            <label class="lf-label">Beds & Baths</label>
                            <div class="lf-split-row">
                                <select name="beds_min" class="lf-input" aria-label="Minimum bedrooms">
                                    <option value="">Beds</option>
                                    @foreach([1, 2, 3, 4, 5] as $bedOption)
                                        <option value="{{ $bedOption }}" @selected((string) ($filters['beds_min'] ?? '') === (string) $bedOption)>{{ $bedOption }}+</option>
                                    @endforeach
                                </select>
                                <select name="baths_min" class="lf-input" aria-label="Minimum bathrooms">
                                    <option value="">Baths</option>
                                    @foreach([1, 1.5, 2, 3, 4] as $bathOption)
                                        <option value="{{ $bathOption }}" @selected((string) ($filters['baths_min'] ?? '') === (string) $bathOption)>{{ $bathOption }}+</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="lf-field">
                            <label class="lf-label">Area</label>
                            <div class="lf-price-inputs">
                                <input type="number" name="area_min" class="lf-input" placeholder="Min sqft" min="0" step="100" value="{{ $filters['area_min'] ?? '' }}">
                                <span class="lf-price-sep">-</span>
                                <input type="number" name="area_max" class="lf-input" placeholder="Max sqft" min="0" step="100" value="{{ $filters['area_max'] ?? '' }}">
                            </div>
                        </div>

                        <input type="hidden" name="sort" value="{{ $filters['sort'] ?? 'newest' }}">

                        <div class="lf-actions">
                            <button class="button button--orange lf-btn-full" type="submit">Apply Filters</button>
                            <a class="button button--ghost lf-btn-full" href="{{ route('listings') }}" id="filterReset">Reset All</a>
                        </div>
                    </form>
                </div>

                <div class="lf-card lf-card--map">
                    <div class="lf-card__map-header">
                        <h4>Market Map</h4>
                        <span class="lf-map-chip">Live</span>
                    </div>
                    <iframe
                        id="listingsMapFrame"
                        src="https://www.google.com/maps?q={{ urlencode($filters['q'] ?: ($filters['zip_code'] ?: 'United States real estate')) }}&output=embed"
                        loading="lazy"
                        title="Listings market map"
                    ></iframe>
                </div>

                <div class="lf-card lf-card--cta">
                    <div class="lf-cta__icon">↗</div>
                    <h4>Want a stronger match?</h4>
                    <p>Tell us the market, budget, and timing. We will route you to the right agent.</p>
                    <a href="{{ route('contact') }}" class="button button--orange lf-btn-full">Request a Match</a>
                </div>
            </div>
        </aside>
    </div>
</section>
@endsection
