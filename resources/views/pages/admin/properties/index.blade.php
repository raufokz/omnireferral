@extends('layouts.dashboard')

@section('dashboard_eyebrow', $isStaffView ? 'Staff Workspace' : 'Admin Workspace')
@section('dashboard_title', 'Zillow Style Property System')
@section('dashboard_description', 'Modern Real Estate Dashboard with advanced filters, map-integrated listing cards, and complete property management controls.')

@section('dashboard_actions')
    <a href="{{ route('admin.dashboard') }}" class="button button--ghost-blue">Overview</a>
    @if($canCreate)
        <a href="{{ route('admin.properties.create') }}" class="button">Add Property</a>
    @endif
@endsection

@section('content')
<div class="workspace-stack zillow-admin">
    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi">
            <span>Total Listings</span>
            <strong>{{ number_format($summary['total']) }}</strong>
            <span>All properties in registry</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Active</span>
            <strong>{{ number_format($summary['active']) }}</strong>
            <span>Visible marketplace inventory</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Pending Review</span>
            <strong>{{ number_format($summary['pendingReview']) }}</strong>
            <span>Awaiting moderation</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>User Listed</span>
            <strong>{{ number_format($summary['userListed']) }}</strong>
            <span>Assigned to user ownership</span>
        </article>
    </section>

    <section class="workspace-card zillow-filters-card">
        <span class="eyebrow">Filters</span>
        <h2>Advanced Property Filters</h2>
        <form method="GET" action="{{ route('admin.properties.index') }}">
            <div class="workspace-form-grid">
                <label class="workspace-field workspace-field--full">
                    <span>Search</span>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Real Estate Listing keyword, city, zip, title...">
                </label>
                <label class="workspace-field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Approval</span>
                    <select name="approval_status">
                        <option value="">All approvals</option>
                        @foreach($approvalStatuses as $status)
                            <option value="{{ $status }}" {{ $filters['approval_status'] === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Type</span>
                    <select name="property_type">
                        <option value="">All types</option>
                        @foreach($propertyTypes as $type)
                            <option value="{{ $type }}" {{ $filters['property_type'] === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Listed By</span>
                    <select name="listed_by">
                        <option value="">All owners</option>
                        <option value="omnireferral" {{ $filters['listed_by'] === 'omnireferral' ? 'selected' : '' }}>OmniReferral/Admin</option>
                        <option value="user" {{ $filters['listed_by'] === 'user' ? 'selected' : '' }}>User</option>
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Min Price</span>
                    <input type="number" min="0" name="price_min" value="{{ $filters['price_min'] }}">
                </label>
                <label class="workspace-field">
                    <span>Max Price</span>
                    <input type="number" min="0" name="price_max" value="{{ $filters['price_max'] }}">
                </label>
                <label class="workspace-field">
                    <span>Bedrooms (min)</span>
                    <input type="number" min="0" name="beds" value="{{ $filters['beds'] }}">
                </label>
                <label class="workspace-field">
                    <span>Bathrooms (min)</span>
                    <input type="number" min="0" step="0.5" name="baths" value="{{ $filters['baths'] }}">
                </label>
                <label class="workspace-field">
                    <span>Area Min</span>
                    <input type="number" min="0" name="area_min" value="{{ $filters['area_min'] }}">
                </label>
                <label class="workspace-field">
                    <span>Area Max</span>
                    <input type="number" min="0" name="area_max" value="{{ $filters['area_max'] }}">
                </label>
                <label class="workspace-field">
                    <span>Sort</span>
                    <select name="sort">
                        <option value="latest" {{ $filters['sort'] === 'latest' ? 'selected' : '' }}>Latest</option>
                        <option value="price_low" {{ $filters['sort'] === 'price_low' ? 'selected' : '' }}>Price Low to High</option>
                        <option value="price_high" {{ $filters['sort'] === 'price_high' ? 'selected' : '' }}>Price High to Low</option>
                    </select>
                </label>
            </div>
            <div class="workspace-actions" style="margin-top: 0.8rem;">
                <button type="submit" class="button">Apply Filters</button>
                <a href="{{ route('admin.properties.index') }}" class="button button--ghost-blue">Reset</a>
            </div>
        </form>
    </section>

    <section class="zillow-grid-layout">
        <div class="zillow-cards-grid">
            @forelse($properties as $property)
                @php
                    $gallery = collect($property->images ?? [])->filter()->values();
                    if ($property->image && ! $gallery->contains($property->image)) {
                        $gallery->prepend($property->image);
                    }
                    $listedBy = $property->listedByLabel();
                    $shareUrl = route('properties.show', $property);
                @endphp
                <article class="workspace-card zillow-card">
                    <div class="zillow-card__media">
                        <img src="{{ $property->image_url }}" alt="{{ $property->title }}" loading="lazy">
                        <span class="listing-badge">{{ $property->status }}</span>
                        @if($property->is_featured)
                            <span class="listing-badge" style="right: 0.8rem; left: auto;">Featured</span>
                        @endif
                    </div>
                    <div class="zillow-card__body">
                        <h3>{{ $property->title }}</h3>
                        <p style="margin: 0.25rem 0 0.5rem; color: #64748b;">{{ $property->fullAddress() }}</p>
                        <strong style="font-size: 1.3rem;">${{ number_format((int) $property->price) }}</strong>
                        <span class="workspace-property__meta">{{ strtoupper($property->price_type ?: 'sale') }} · {{ $property->property_type }}</span>
                        <div class="listing-card__meta listing-card__meta--pills" style="margin-top: 0.65rem;">
                            <span>{{ $property->beds }} bd</span>
                            <span>{{ $property->baths }} ba</span>
                            <span>{{ number_format((int) $property->sqft) }} sqft</span>
                            <span>{{ $gallery->count() }} photos</span>
                        </div>
                        <div class="workspace-property__meta" style="margin-top: 0.55rem;">Listed by {{ $listedBy }}</div>
                        <div class="workspace-actions" style="margin-top: 0.8rem; flex-wrap: wrap;">
                            <a href="{{ route('admin.properties.edit', $property) }}" class="button button--ghost-blue">Edit</a>
                            <a href="{{ $shareUrl }}" class="button button--ghost-blue">View</a>
                            <a href="{{ $shareUrl }}" target="_blank" class="button button--ghost-blue">Share</a>
                            @if($canDelete)
                                <form method="POST" action="{{ route('admin.properties.destroy', $property) }}" onsubmit="return confirm('Delete this listing? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button">Delete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="workspace-card">
                    <div class="workspace-empty">No listings found for the selected filters.</div>
                </div>
            @endforelse
        </div>

        <aside class="workspace-card zillow-map-pane">
            <span class="eyebrow">Property Listing With Map</span>
            <h3 style="margin-top: 0.4rem;">Map View</h3>
            <div class="property-sidebar__map">
                <iframe title="Property map panel" src="https://www.google.com/maps?q={{ urlencode($filters['search'] ?: 'real estate listings') }}&output=embed" loading="lazy"></iframe>
            </div>
            <ul class="workspace-list" style="margin-top: 0.75rem;">
                @foreach($mapPins->take(8) as $pin)
                    <li>
                        <strong>{{ $pin['title'] }}</strong>
                        <small>{{ $pin['address'] }}</small>
                    </li>
                @endforeach
            </ul>
        </aside>
    </section>

    <section class="workspace-card">
        <div class="workspace-pagination">
            {{ $properties->links() }}
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
.zillow-grid-layout{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:1rem;align-items:start}
.zillow-cards-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem}
.zillow-card{overflow:hidden;transition:transform .18s ease,box-shadow .18s ease}
.zillow-card:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(2,18,43,.16)}
.zillow-card__media{position:relative}
.zillow-card__media img{width:100%;height:190px;object-fit:cover;border-radius:12px}
.zillow-card__body{padding-top:.65rem}
.zillow-map-pane{position:sticky;top:1rem}
.zillow-filters-card{position:sticky;top:.75rem;z-index:3}
@media (max-width: 1100px){
    .zillow-grid-layout{grid-template-columns:1fr}
    .zillow-map-pane{position:static}
}
</style>
@endpush
