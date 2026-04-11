@extends('layouts.app')

@section('content')
<section class="page-hero dashboard-page-hero dashboard-page-hero--agent">
    <div class="container page-hero__content">
        <span class="eyebrow">Listings Workspace</span>
        <h1>Publish listings based on your active package access</h1>
        <p>Your package controls how many active listings you can keep live at one time. Once a listing is sold or taken off market, that slot opens back up.</p>
    </div>
</section>

<section class="section dashboard-page agent-portal-shell">
    <div class="container agent-portal-grid">
        @include('pages.dashboards.partials.agent-portal-sidebar')

        <div class="agent-portal-main">
            <div class="cockpit-kpi-row">
                <article class="cockpit-kpi-card">
                    <span class="eyebrow">Package</span>
                    <strong>{{ $activePlan?->name ?: 'No Plan' }}</strong>
                    <p>{{ $listingLimitLabel }}</p>
                </article>
                <article class="cockpit-kpi-card">
                    <span class="eyebrow">Live Listings</span>
                    <strong>{{ $activeListingCount }}</strong>
                    <p>Approved properties visible in the marketplace</p>
                </article>
                <article class="cockpit-kpi-card">
                    <span class="eyebrow">Remaining Slots</span>
                    <strong>{{ $remainingListingSlots }}</strong>
                    <p>Available space before you hit your cap</p>
                </article>
                <article class="cockpit-kpi-card">
                    <span class="eyebrow">Pending Review</span>
                    <strong>{{ $pendingReviewCount }}</strong>
                    <p>Listings waiting for admin approval</p>
                </article>
            </div>

            <div class="agent-portal-content-grid">
                <section class="cockpit-table-card agent-portal-section">
                    <div class="agent-portal-section__header">
                        <div>
                            <span class="eyebrow">Publish Listing</span>
                            <h2>Add a property for admin review</h2>
                        </div>
                    </div>

                    @if(! $activePlan || $listingLimit < 1)
                        <div class="agent-portal-warning">
                            <strong>No listing access yet.</strong>
                            <p>Your current account does not include property publishing. Choose a lead package to unlock listing access.</p>
                            <a href="{{ route('pricing') }}" class="button button--orange">View Packages</a>
                        </div>
                    @elseif(! $canCreateListings)
                        <div class="agent-portal-warning">
                            <strong>Listing cap reached.</strong>
                            <p>You are currently using all {{ $listingLimit }} active listing slots in your {{ $activePlan->name }} package. Mark one sold or off-market to free up space.</p>
                        </div>
                    @endif

                    @if($pendingReviewCount > 0)
                        <div class="agent-portal-warning" style="margin-bottom: 1.25rem;">
                            <strong>{{ $pendingReviewCount }} listing {{ $pendingReviewCount === 1 ? 'is' : 'are' }} awaiting review.</strong>
                            <p>Admin approval is required before new submissions appear on the public listings page.</p>
                        </div>
                    @endif

                    <form class="agent-portal-form" method="POST" action="{{ route('agent.listings.store') }}" enctype="multipart/form-data">
                        @csrf
                        <fieldset {{ $canCreateListings ? '' : 'disabled' }}>
                            <div class="form-grid-2">
                                <label class="form-full-row">
                                    <span>Property Title</span>
                                    <input type="text" name="title" value="{{ old('title') }}" required>
                                </label>
                                <label>
                                    <span>Location</span>
                                    <input type="text" name="location" value="{{ old('location') }}" required>
                                </label>
                                <label>
                                    <span>ZIP Code</span>
                                    <input type="text" name="zip_code" value="{{ old('zip_code') }}" required>
                                </label>
                                <label>
                                    <span>Property Type</span>
                                    <select name="property_type" required>
                                        @foreach(['house' => 'House', 'apartment' => 'Apartment', 'condo' => 'Condo', 'commercial' => 'Commercial'] as $value => $label)
                                            <option value="{{ $value }}" {{ old('property_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>
                                    <span>Price</span>
                                    <input type="number" name="price" value="{{ old('price') }}" min="0" required>
                                </label>
                                <label>
                                    <span>Beds</span>
                                    <input type="number" name="beds" value="{{ old('beds') }}" min="0">
                                </label>
                                <label>
                                    <span>Baths</span>
                                    <input type="number" name="baths" value="{{ old('baths') }}" min="0" step="0.5">
                                </label>
                                <label>
                                    <span>Square Feet</span>
                                    <input type="number" name="sqft" value="{{ old('sqft') }}" min="0">
                                </label>
                                <label class="form-full-row">
                                    <span>Description</span>
                                    <textarea name="description" rows="5">{{ old('description') }}</textarea>
                                </label>
                                <label class="form-full-row">
                                    <span>Listing Image</span>
                                    <input type="file" name="image" accept="image/*">
                                </label>
                            </div>
                        </fieldset>

                        <div class="agent-portal-form__actions">
                            <button type="submit" class="button" {{ $canCreateListings ? '' : 'disabled' }}>Submit For Review</button>
                            <a href="{{ route('pricing') }}" class="button button--ghost-blue">Review Package Limits</a>
                        </div>
                    </form>
                </section>

                <section class="cockpit-table-card agent-portal-section">
                    <div class="agent-portal-section__header">
                        <div>
                            <span class="eyebrow">Current Listings</span>
                            <h2>Manage your published inventory</h2>
                        </div>
                    </div>

                    <div class="agent-portal-listing-grid">
                        @forelse($properties as $property)
                            <article class="listing-card listing-card--showcase">
                                <div class="listing-card__media">
                                    <img src="{{ $property->image_url }}" alt="{{ $property->title }}" loading="lazy">
                                    <span class="listing-badge">{{ $property->status }}</span>
                                </div>
                                <div class="listing-card__body">
                                    <div class="listing-card__top">
                                        <strong>${{ number_format($property->price) }}</strong>
                                        <span class="listing-type">{{ $property->property_type }}</span>
                                    </div>
                                    <h3>{{ $property->title }}</h3>
                                    <p class="listing-location">{{ $property->location }}</p>
                                    <div class="listing-card__meta listing-card__meta--pills">
                                        <span>{{ $property->beds }} bd</span>
                                        <span>{{ $property->baths }} ba</span>
                                        <span>{{ number_format($property->sqft) }} sqft</span>
                                    </div>
                                    <div class="agent-portal-listing-card__status" style="display:flex; gap:.5rem; flex-wrap:wrap; margin-top: .75rem;">
                                        <span class="status-pill status-pill--{{ $property->approvalStatusTone() }}">{{ $property->approvalStatusLabel() }}</span>
                                        <span class="status-pill status-pill--qualified">{{ number_format($property->favorites_count ?? 0) }} saves</span>
                                        @if($property->approval_notes)
                                            <span class="status-pill status-pill--pending">{{ \Illuminate\Support\Str::limit($property->approval_notes, 40) }}</span>
                                        @endif
                                    </div>
                                    <div class="agent-portal-listing-card__actions">
                                        <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">View</a>
                                        <a href="{{ route('properties.edit', $property) }}" class="button button--blue">Edit</a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="cockpit-empty-state">
                                <h3>No listings yet</h3>
                                <p class="text-gray-500">Your published properties will appear here after you add the first one.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="agent-portal-pagination">
                        {{ $properties->links() }}
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
@endsection
