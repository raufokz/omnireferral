@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Buyer Workspace')
@section('dashboard_title', 'Saved Homes')
@section('dashboard_description', 'All properties you have saved, organized in a dedicated page for faster review.')

@section('dashboard_actions')
    <a href="{{ route('dashboard.buyer') }}" class="button button--ghost-blue">Back To Overview</a>
    <a href="{{ route('listings') }}" class="button">Browse Listings</a>
@endsection

@section('content')
<section class="workspace-card">
    @if($savedHomes->isEmpty())
        <div class="workspace-empty">You do not have saved homes yet. Visit marketplace listings and favorite homes to build your shortlist.</div>
    @else
        <div class="workspace-property-grid">
            @foreach($savedHomes as $property)
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
                            <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">Open Listing</a>
                            <a href="{{ route('properties.show', $property) }}#property-contact" class="button">Contact Agent</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="workspace-pagination">
            {{ $savedHomes->links() }}
        </div>
    @endif
</section>
@endsection
