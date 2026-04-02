@extends('layouts.app')
@section('content')
<section class="page-hero listings-hero">
    <div class="container listings-hero__inner">
        <div>
            <span class="eyebrow">Listings</span>
            <h1>Explore listings, locations, and agent-backed opportunities</h1>
            <p>Search by ZIP code, budget, and property type with a faster, marketplace-style experience built for buyers, sellers, and agents.</p>
        </div>
        <div class="listings-hero__stats">
            <div class="listings-stat-card">
                <strong>{{ $properties->count() }}</strong>
                <span>active opportunities</span>
            </div>
            <div class="listings-stat-card">
                <strong>48</strong>
                <span>ZIP-focused markets</span>
            </div>
        </div>
    </div>
</section>

<section class="section listings-page">
    <div class="container">
        <div class="listings-layout">
            <aside class="listings-sidebar">
                <div class="sidebar-card" data-animate="left">
                    <span class="eyebrow">Find Matches</span>
                    <h2>Search smarter</h2>
                    <p>Refine by ZIP, type, and price to see the best opportunities.</p>
                    <form class="listings-filter-form" id="listingsFilterForm">
                        <label>
                            <span>ZIP code</span>
                            <input type="text" placeholder="e.g. 75201" id="filterZip">
                        </label>
                        <label>
                            <span>Property type</span>
                            <select id="filterType">
                                <option value="">Any type</option>
                                <option value="house">House</option>
                                <option value="apartment">Apartment</option>
                            </select>
                        </label>
                        <label>
                            <span>Price range</span>
                            <input type="text" placeholder="$250k - $850k" id="filterPrice">
                        </label>
                        <div class="listings-filter-actions">
                            <button class="button" type="button" id="filterApply">Filter</button>
                            <button class="button button--ghost" type="button" id="filterReset">Reset</button>
                        </div>
                    </form>
                </div>
                <div class="sidebar-card" data-animate="left">
                    <div class="sidebar-card__header">
                        <h3>Map Preview</h3>
                        <span class="map-chip">Ready</span>
                    </div>
                    <iframe id="listingsMapFrame" style="border-radius: 12px; border: none; height: 160px; width: 100%;" src="https://www.google.com/maps?q=United%20States&output=embed" loading="lazy"></iframe>
                </div>
            </aside>

            <div class="listings-main">
                <div class="listings-results-head" data-animate="right">
                    <div>
                        <span class="eyebrow">Results</span>
                        <h2>Featured property matches</h2>
                    </div>
                    <div class="listings-results-tools">
                        <div class="results-sort">
                            <span>Sort by</span>
                            <select id="listingSort">
                                <option value="relevant">Most Relevant</option>
                                <option value="price-asc">Price: Low to High</option>
                                <option value="price-desc">Price: High to Low</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="listing-grid listing-grid--showcase" data-stagger id="listingGrid">
                    @foreach($properties as $index => $property)
                        <article 
                            class="listing-card listing-card--showcase"
                            data-listing-card
                            data-zip="{{ $property->zip_code }}"
                            data-type="{{ strtolower($property->property_type) }}"
                            data-price="{{ $property->price }}"
                        >
                            <div class="listing-card__media">
                                <img src="{{ $property->image_url }}" alt="{{ $property->title }} property image" loading="lazy">
                                <span class="listing-card__badge">{{ $property->status ?? 'Active' }}</span>
                                <div class="listing-card__price-badge">${{ number_format($property->price) }}</div>
                                <button type="button" class="listing-card__save" aria-label="Save">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                </button>
                            </div>
                            <div class="listing-card__body">
                                <span class="listing-card__type">{{ $property->property_type }}</span>
                                <h3>{{ $property->title }}</h3>
                                <p class="listing-location">{{ $property->location }}</p>
                                
                                <div class="listing-card__meta-grid">
                                    <div class="listing-meta-chip">
                                        <strong>{{ $property->beds }}</strong>
                                        <span>Beds</span>
                                    </div>
                                    <div class="listing-meta-chip">
                                        <strong>{{ $property->baths }}</strong>
                                        <span>Baths</span>
                                    </div>
                                    <div class="listing-meta-chip">
                                        <strong>{{ number_format($property->sqft) }}</strong>
                                        <span>Sqft</span>
                                    </div>
                                </div>

                                <div class="listing-card__footer">
                                    <div class="listing-agent-mini">
                                        <div>
                                            <p>{{ optional(optional($property->realtorProfile)->user)->name ?? 'OmniPartner' }}</p>
                                        </div>
                                    </div>
                                    <div class="listing-card__actions">
                                        <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">Details</a>
                                        <a href="{{ route('contact') }}" class="button button--orange">Contact</a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="listing-empty-state" id="listingEmptyState" hidden>
                    <h3>No listings match your filters right now.</h3>
                    <p>Adjust the ZIP, property type, or price range to see more opportunities.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection


