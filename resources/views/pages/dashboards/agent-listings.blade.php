@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Agent Workspace')
@section('dashboard_title', 'Agent Listings')
@section('dashboard_description', 'Publish properties within your package capacity and track every listing state in one workspace.')

@section('dashboard_actions')
    <a href="{{ route('pricing') }}" class="button button--ghost-blue">Upgrade Capacity</a>
    <a href="{{ route('dashboard.agent') }}" class="button button--ghost-blue">Overview</a>
@endsection

@push('styles')
<style>
.agent-kpi-icon {
    width: 2.4rem; height: 2.4rem;
    border-radius: 11px;
    display: grid; place-items: center;
    margin-bottom: 0.5rem;
}
.agent-kpi-icon svg { width: 1.1rem; height: 1.1rem; }
.agent-kpi-icon--blue   { background: rgba(11,54,104,0.10); color: #0b3668; }
.agent-kpi-icon--orange { background: rgba(255,107,0,0.13); color: #c2410c; }
.agent-kpi-icon--teal   { background: rgba(14,165,233,0.12); color: #0369a1; }
.agent-kpi-icon--violet { background: rgba(109,93,252,0.12); color: #5145cd; }
.agent-kpi-icon--green  { background: rgba(22,163,74,0.12); color: #15803d; }

.agent-capacity-bar { height: 10px; border-radius: 999px; background: #e8edf4; overflow: hidden; margin-top: 0.4rem; }
.agent-capacity-bar__fill { height: 100%; border-radius: 999px; transition: width 0.6s cubic-bezier(.16,1,.3,1); }

.listing-form-section {
    border-top: 1px solid var(--dash-shell-border);
    padding-top: 1.1rem;
    margin-top: 1.1rem;
}
.listing-form-section h3 {
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--dash-shell-muted);
    margin-bottom: 0.75rem;
}

.listing-property-card {
    background: var(--dash-shell-panel);
    border: 1px solid var(--dash-shell-border);
    border-radius: 16px;
    overflow: hidden;
    display: grid;
    grid-template-rows: 160px auto;
    transition: box-shadow 0.2s, transform 0.2s;
}
.listing-property-card:hover {
    box-shadow: 0 10px 28px rgba(11,54,104,0.12);
    transform: translateY(-3px);
}
.listing-property-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    background: #e8edf4;
}
.listing-property-card__body { padding: 0.9rem; display: grid; gap: 0.5rem; }
.listing-property-card__price { font-family: 'Sora', sans-serif; font-size: 1.05rem; font-weight: 700; color: #0b3668; }
.listing-property-card__title { font-size: 0.92rem; font-weight: 600; color: var(--dash-shell-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.listing-property-card__meta { font-size: 0.78rem; color: var(--dash-shell-muted); }
.listing-property-card__actions { display: flex; gap: 0.4rem; flex-wrap: wrap; }
.listing-property-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1rem;
}

.capacity-notice {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    padding: 0.9rem;
    border-radius: 12px;
    font-size: 0.85rem;
    line-height: 1.5;
}
.capacity-notice--warning { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }
.capacity-notice--info    { background: #eff6ff; border: 1px solid #93c5fd; color: #1e40af; }
.capacity-notice--success { background: #f0fdf4; border: 1px solid #86efac; color: #15803d; }
.capacity-notice svg { flex-shrink: 0; width: 1.1rem; height: 1.1rem; margin-top: 0.1rem; }
</style>
@endpush

@section('content')
@php
    $capacityUsed = $listingLimit > 0 ? min(100, round(($slotUsageCount / $listingLimit) * 100)) : 0;
    $capacityColor = $capacityUsed >= 90 ? '#dc2626' : ($capacityUsed >= 60 ? '#d97706' : '#16a34a');
@endphp

<div class="workspace-stack">

    {{-- KPI Row --}}
    <section class="workspace-grid workspace-grid--4">

        <article class="workspace-card workspace-kpi" data-trend="{{ $activePlan?->displayName() ?: 'No plan' }}">
            <div class="agent-kpi-icon agent-kpi-icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <span>Current Plan</span>
            <strong style="font-size:clamp(1.1rem,2vw,1.5rem) !important;">{{ $activePlan?->displayName() ?: 'No Plan' }}</strong>
            <span>{{ $listingLimitLabel }}</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--warm" data-trend="{{ $activeListingCount }} live">
            <div class="agent-kpi-icon agent-kpi-icon--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <span>Live Listings</span>
            <strong>{{ number_format($activeListingCount) }}</strong>
            <span>Approved and visible in marketplace</span>
        </article>

        <article class="workspace-card workspace-kpi" data-trend="{{ $remainingListingSlots }} available">
            <div class="agent-kpi-icon agent-kpi-icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            </div>
            <span>Slots Remaining</span>
            <strong>{{ number_format($remainingListingSlots) }}</strong>
            <span>Available for new uploads</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--violet" data-trend="Under review">
            <div class="agent-kpi-icon agent-kpi-icon--violet">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <span>Pending Review</span>
            <strong>{{ number_format($pendingReviewCount) }}</strong>
            <span>Awaiting admin decision</span>
        </article>

    </section>

    {{-- Capacity Bar --}}
    @if($listingLimit > 0)
        <div class="workspace-card" style="padding: 0.9rem 1rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                <strong style="font-size:0.85rem;">Listing Capacity</strong>
                <span style="font-size:0.82rem; font-weight:700; color: {{ $capacityColor }};">{{ $slotUsageCount }} / {{ $listingLimit }} slots used</span>
            </div>
            <div class="agent-capacity-bar">
                <div class="agent-capacity-bar__fill" style="width: {{ $capacityUsed }}%; background: {{ $capacityColor }};"></div>
            </div>
        </div>
    @endif

    {{-- Add Listing Form --}}
    <section class="workspace-card">
        <span class="eyebrow">Publish Property</span>
        <h2>Add a New Listing</h2>

        @if(! $activePlan || $listingLimit < 1)
            <div class="capacity-notice capacity-notice--info" style="margin-top:0.75rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>Your current package does not include listing access. <a href="{{ route('pricing') }}" style="font-weight:700; color:inherit;">Upgrade your plan</a> to start publishing properties to the marketplace.</span>
            </div>
        @elseif(! $canCreateListings)
            <div class="capacity-notice capacity-notice--warning" style="margin-top:0.75rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span>You've reached your listing limit of {{ $listingLimit }}. Mark a listing as <em>Sold</em> or <em>Off-Market</em> to free a slot. <a href="{{ route('pricing') }}" style="font-weight:700; color:inherit;">Or upgrade your plan.</a></span>
            </div>
        @else
            <div class="capacity-notice capacity-notice--success" style="margin-top:0.75rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <span>You have <strong>{{ $remainingListingSlots }}</strong> listing {{ Str::plural('slot', $remainingListingSlots) }} available. Listings go to admin review before appearing in the marketplace.</span>
            </div>
        @endif

        @if($pendingReviewCount > 0)
            <div class="capacity-notice capacity-notice--info" style="margin-top:0.6rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>{{ $pendingReviewCount }} listing {{ Str::plural('is', $pendingReviewCount) }} waiting for admin review. You'll be notified once a decision is made.</span>
            </div>
        @endif

        <form method="POST" action="{{ route('agent.listings.store') }}" enctype="multipart/form-data" style="margin-top:1rem;">
            @csrf
            <fieldset {{ $canCreateListings ? '' : 'disabled' }} style="border:none; padding:0; margin:0;">

                <div class="listing-form-section">
                    <h3>Property Basics</h3>
                    <div class="workspace-form-grid">
                        <label class="workspace-field workspace-field--full">
                            <span>Property Title <abbr title="required">*</abbr></span>
                            <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Modern 3BR Family Home in Dallas" required>
                        </label>
                        <label class="workspace-field">
                            <span>Property Type <abbr title="required">*</abbr></span>
                            <select name="property_type" required>
                                <option value="">Select type</option>
                                @foreach(['house' => 'Single-Family House', 'apartment' => 'Apartment', 'condo' => 'Condo / Townhouse', 'commercial' => 'Commercial', 'land' => 'Land / Lot', 'multi_family' => 'Multi-Family'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('property_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="workspace-field">
                            <span>Asking Price ($) <abbr title="required">*</abbr></span>
                            <input type="number" name="price" value="{{ old('price') }}" min="0" placeholder="e.g. 450000" required>
                        </label>
                    </div>
                </div>

                <div class="listing-form-section">
                    <h3>Location</h3>
                    <div class="workspace-form-grid">
                        <label class="workspace-field workspace-field--full">
                            <span>Street Address</span>
                            <input type="text" name="street_address" value="{{ old('street_address') }}" placeholder="e.g. 1234 Oak Street">
                        </label>
                        <label class="workspace-field">
                            <span>City / Area <abbr title="required">*</abbr></span>
                            <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Dallas, TX" required>
                        </label>
                        <label class="workspace-field">
                            <span>ZIP Code <abbr title="required">*</abbr></span>
                            <input type="text" name="zip_code" value="{{ old('zip_code') }}" placeholder="e.g. 75201" required>
                        </label>
                    </div>
                </div>

                <div class="listing-form-section">
                    <h3>Property Details</h3>
                    <div class="workspace-form-grid">
                        <label class="workspace-field">
                            <span>Bedrooms</span>
                            <input type="number" name="beds" value="{{ old('beds') }}" min="0" placeholder="0">
                        </label>
                        <label class="workspace-field">
                            <span>Bathrooms</span>
                            <input type="number" name="baths" value="{{ old('baths') }}" min="0" step="0.5" placeholder="0">
                        </label>
                        <label class="workspace-field">
                            <span>Square Footage</span>
                            <input type="number" name="sqft" value="{{ old('sqft') }}" min="0" placeholder="e.g. 2200">
                        </label>
                        <label class="workspace-field">
                            <span>Year Built</span>
                            <input type="number" name="year_built" value="{{ old('year_built') }}" min="1800" max="{{ now()->year }}" placeholder="e.g. 2005">
                        </label>
                        <label class="workspace-field workspace-field--full">
                            <span>Description</span>
                            <textarea name="description" placeholder="Describe the property, neighborhood, key features, and why buyers will love it." style="min-height:120px;">{{ old('description') }}</textarea>
                        </label>
                    </div>
                </div>

                <div class="listing-form-section">
                    <h3>Photos</h3>
                    @include('partials.property-image-manager', [
                        'existingImages' => [],
                        'featuredImage' => '',
                    ])
                </div>

                <div class="workspace-actions" style="margin-top:1.1rem;">
                    <button type="submit" class="button" {{ $canCreateListings ? '' : 'disabled' }}>Submit For Review</button>
                    <span style="font-size:0.78rem; color:var(--dash-shell-muted); align-self:center;">Listings are reviewed by the admin team before going live.</span>
                </div>
            </fieldset>
        </form>
    </section>

    {{-- Current Listings --}}
    <section class="workspace-card">
        <div class="workspace-actions" style="justify-content:space-between; margin-bottom:0.75rem;">
            <div>
                <span class="eyebrow">Listing Inventory</span>
                <h2>Your Properties</h2>
            </div>
            @if($rejectedListingCount > 0)
                <span class="status-pill status-pill--critical">{{ $rejectedListingCount }} rejected</span>
            @endif
        </div>

        @if($properties->isEmpty())
            <div class="workspace-empty" style="padding:2rem;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:#cbd5e1; margin:0 auto 0.75rem; display:block;"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <strong style="display:block; margin-bottom:0.35rem;">No listings yet</strong>
                <p style="font-size:0.83rem; color:var(--dash-shell-muted);">Add your first property using the form above.</p>
            </div>
        @else
            <div class="listing-property-grid">
                @foreach($properties as $property)
                    <article class="listing-property-card">
                        <img
                            src="{{ $property->image_url }}"
                            alt="{{ $property->title }}"
                            loading="lazy"
                            onerror="this.src='{{ asset('images/omnireferral-logo.png') }}'; this.style.objectFit='contain'; this.style.padding='2rem'; this.style.background='#f4f7fb';"
                        >
                        <div class="listing-property-card__body">
                            @if($property->price)
                                <div class="listing-property-card__price">${{ number_format($property->price) }}</div>
                            @endif
                            <div class="listing-property-card__title" title="{{ $property->title }}">{{ $property->title }}</div>
                            <div class="listing-property-card__meta">{{ $property->location }}</div>
                            <div class="workspace-pill-row">
                                @if($property->beds || $property->baths)
                                    <span class="workspace-pill">{{ $property->beds }}bd · {{ $property->baths }}ba</span>
                                @endif
                                <span class="workspace-pill {{ $property->approval_status === 'approved' ? '' : '' }}">
                                    {{ $property->approvalStatusLabel() }}
                                </span>
                                @if(($property->favorites_count ?? 0) > 0)
                                    <span class="workspace-pill workspace-pill--accent">
                                        ♥ {{ number_format($property->favorites_count) }}
                                    </span>
                                @endif
                            </div>
                            <div class="listing-property-card__actions">
                                <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue" style="flex:1; text-align:center;">View</a>
                                <a href="{{ route('properties.edit', $property) }}" class="button" style="flex:1; text-align:center;">Edit</a>
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
