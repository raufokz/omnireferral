@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Seller Workspace')
@section('dashboard_title', 'Listings Management')
@section('dashboard_description', 'Submit new listings for review and monitor currently active marketplace inventory.')

@section('dashboard_actions')
    <a href="{{ route('dashboard.seller') }}" class="button button--ghost-blue">Back To Overview</a>
    <a href="{{ route('dashboard.seller.requests') }}" class="button">Request Queue</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <span class="eyebrow">Listing Intake</span>
        <h2>Submit A Property For Admin Review</h2>
        <p style="margin-bottom: 0.9rem;">New seller listings remain pending until approved by admin or staff.</p>

        <form method="POST" action="{{ route('properties.store') }}" enctype="multipart/form-data">
            @csrf
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

            <div class="workspace-actions" style="margin-top: 0.8rem;">
                <button type="submit" class="button">Submit Listing</button>
                <a href="{{ route('contact') }}" class="button button--ghost-blue">Need Listing Help?</a>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <div class="workspace-actions" style="justify-content: space-between; align-items: flex-start; margin-bottom: 0.8rem;">
            <div>
                <span class="eyebrow">Marketplace</span>
                <h2>Current Active Listings</h2>
            </div>
            <a href="{{ route('listings') }}" class="button button--ghost-blue">Open Public Marketplace</a>
        </div>

        @if($marketplaceProperties->isEmpty())
            <div class="workspace-empty">No active listings are available right now.</div>
        @else
            <div class="workspace-property-grid">
                @foreach($marketplaceProperties as $property)
                    <article class="workspace-property">
                        <img src="{{ $property->image_url }}" alt="{{ $property->title }}" loading="lazy">
                        <div class="workspace-property__body">
                            <h3>{{ $property->title }}</h3>
                            <p class="workspace-property__meta">{{ $property->location }}</p>
                            <div class="workspace-pill-row">
                                <span class="workspace-pill">${{ number_format($property->price) }}</span>
                                <span class="workspace-pill">{{ ucfirst($property->property_type ?: 'home') }}</span>
                                <span class="workspace-pill workspace-pill--accent">{{ number_format($property->favorites_count ?? 0) }} saves</span>
                            </div>
                            <div class="workspace-actions">
                                <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">View</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="workspace-pagination">
                {{ $marketplaceProperties->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
