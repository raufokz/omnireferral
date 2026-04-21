@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Agent Workspace')
@section('dashboard_title', 'Agent Listings')
@section('dashboard_description', 'Create listings within your package limits and manage all listing states in one dedicated page.')

@section('dashboard_actions')
    <a href="{{ route('pricing') }}" class="button button--ghost-blue">Package Limits</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi">
            <span>Plan</span>
            <strong>{{ $activePlan?->name ?: 'No Plan' }}</strong>
            <span>{{ $listingLimitLabel }}</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Live Listings</span>
            <strong>{{ number_format($activeListingCount) }}</strong>
            <span>Approved and visible</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Slots Left</span>
            <strong>{{ number_format($remainingListingSlots) }}</strong>
            <span>Available for new uploads</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Pending Review</span>
            <strong>{{ number_format($pendingReviewCount) }}</strong>
            <span>Awaiting admin decision</span>
        </article>
    </section>

    <section class="workspace-card">
        <span class="eyebrow">Publish Listing</span>
        <h2>Add A Property</h2>

        @if(! $activePlan || $listingLimit < 1)
            <div class="workspace-empty" style="margin-bottom: 0.9rem;">Your current package does not include listing access yet.</div>
        @elseif(! $canCreateListings)
            <div class="workspace-empty" style="margin-bottom: 0.9rem;">You have reached your listing cap. Mark a listing sold or off-market to free a slot.</div>
        @endif

        @if($pendingReviewCount > 0)
            <div class="workspace-empty" style="margin-bottom: 0.9rem;">{{ $pendingReviewCount }} listing {{ $pendingReviewCount === 1 ? 'is' : 'are' }} waiting for admin review.</div>
        @endif

        <form method="POST" action="{{ route('agent.listings.store') }}" enctype="multipart/form-data">
            @csrf
            <fieldset {{ $canCreateListings ? '' : 'disabled' }}>
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Property Title</span>
                        <input type="text" name="title" value="{{ old('title') }}" required>
                    </label>
                    <label class="workspace-field">
                        <span>Location</span>
                        <input type="text" name="location" value="{{ old('location') }}" required>
                    </label>
                    <label class="workspace-field">
                        <span>ZIP Code</span>
                        <input type="text" name="zip_code" value="{{ old('zip_code') }}" required>
                    </label>
                    <label class="workspace-field">
                        <span>Property Type</span>
                        <select name="property_type" required>
                            @foreach(['house' => 'House', 'apartment' => 'Apartment', 'condo' => 'Condo', 'commercial' => 'Commercial'] as $value => $label)
                                <option value="{{ $value }}" {{ old('property_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="workspace-field">
                        <span>Price</span>
                        <input type="number" name="price" value="{{ old('price') }}" min="0" required>
                    </label>
                    <label class="workspace-field">
                        <span>Beds</span>
                        <input type="number" name="beds" value="{{ old('beds') }}" min="0">
                    </label>
                    <label class="workspace-field">
                        <span>Baths</span>
                        <input type="number" name="baths" value="{{ old('baths') }}" min="0" step="0.5">
                    </label>
                    <label class="workspace-field">
                        <span>Square Feet</span>
                        <input type="number" name="sqft" value="{{ old('sqft') }}" min="0">
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>Description</span>
                        <textarea name="description">{{ old('description') }}</textarea>
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>Listing Image</span>
                        <input type="file" name="image" accept="image/*">
                    </label>
                </div>
            </fieldset>

            <div class="workspace-actions" style="margin-top: 0.8rem;">
                <button type="submit" class="button" {{ $canCreateListings ? '' : 'disabled' }}>Submit For Review</button>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <div class="workspace-actions" style="justify-content: space-between; margin-bottom: 0.7rem;">
            <div>
                <span class="eyebrow">Listing Inventory</span>
                <h2>Current Agent Properties</h2>
            </div>
        </div>

        @if($properties->isEmpty())
            <div class="workspace-empty">No listings yet. Add your first property above.</div>
        @else
            <div class="workspace-property-grid">
                @foreach($properties as $property)
                    <article class="workspace-property">
                        <img src="{{ $property->image_url }}" alt="{{ $property->title }}" loading="lazy">
                        <div class="workspace-property__body">
                            <h3>{{ $property->title }}</h3>
                            <p class="workspace-property__meta">{{ $property->location }}</p>
                            <div class="workspace-pill-row">
                                <span class="workspace-pill">{{ $property->status }}</span>
                                <span class="workspace-pill">{{ $property->approvalStatusLabel() }}</span>
                                <span class="workspace-pill workspace-pill--accent">{{ number_format($property->favorites_count ?? 0) }} saves</span>
                            </div>
                            <div class="workspace-actions">
                                <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">View</a>
                                <a href="{{ route('properties.edit', $property) }}" class="button">Edit</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="workspace-pagination">
            {{ $properties->links() }}
        </div>
    </section>
</div>
@endsection
