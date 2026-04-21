@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Buyer Workspace')
@section('dashboard_title', 'Buyer Overview')
@section('dashboard_description', 'Monitor your shortlist, request progress, and market movement from a single clean dashboard.')

@section('dashboard_actions')
    <a href="{{ route('dashboard.buyer.saved') }}" class="button button--ghost-blue">Saved Homes</a>
    <a href="{{ route('dashboard.buyer.requests') }}" class="button">Requests</a>
@endsection

@section('content')
@php
    $buyerJourneyMax = max(1, collect($buyerJourney)->max('count'));
    $latestSavedHomes = $properties->take(3);
@endphp

<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi">
            <span>Saved Listings</span>
            <strong>{{ number_format($buyerStats['saved_listings']) }}</strong>
            <span>Homes currently in your shortlist</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Favorites</span>
            <strong>{{ number_format($buyerStats['favorites']) }}</strong>
            <span>Homes flagged for fast follow-up</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Recent Requests</span>
            <strong>{{ number_format($buyerRequests->count()) }}</strong>
            <span>Latest buyer requests in queue</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Market Alerts</span>
            <strong>{{ number_format($buyerStats['new_alerts']) }}</strong>
            <span>Signals tied to buyer intent</span>
        </article>
    </section>

    <section class="workspace-grid workspace-grid--2">
        <article class="workspace-card">
            <span class="eyebrow">Journey</span>
            <h2>Request Stage Progress</h2>
            <div class="workspace-stack">
                @foreach($buyerJourney as $stage)
                    <div>
                        <div class="workspace-actions" style="justify-content: space-between;">
                            <strong>{{ $stage['label'] }}</strong>
                            <small>{{ number_format($stage['count']) }}</small>
                        </div>
                        <div style="height: 8px; border-radius: 999px; background: #e8edf4; margin-top: 0.45rem;">
                            <div style="height:100%; border-radius:999px; width: {{ ($stage['count'] / $buyerJourneyMax) * 100 }}%; background: linear-gradient(90deg, #0b3668, #ff6b00);"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="workspace-card">
            <span class="eyebrow">Quick Actions</span>
            <h2>Move Your Search Forward</h2>
            <ul class="workspace-list">
                <li>
                    <strong>Review shortlist details</strong>
                    <small>Open your full saved homes page with listing-level actions.</small>
                </li>
                <li>
                    <strong>Track request updates</strong>
                    <small>See statuses from submitted through matched and closed.</small>
                </li>
                <li>
                    <strong>Browse live listings</strong>
                    <small>Explore current approved inventory across the marketplace.</small>
                </li>
            </ul>
            <div class="workspace-actions" style="margin-top: 0.8rem;">
                <a href="{{ route('dashboard.buyer.saved') }}" class="button button--ghost-blue">Open Saved Homes</a>
                <a href="{{ route('listings') }}" class="button">Browse Marketplace</a>
            </div>
        </article>
    </section>

    <section class="workspace-card">
        <div class="workspace-actions" style="justify-content: space-between; align-items: flex-start; margin-bottom: 0.8rem;">
            <div>
                <span class="eyebrow">Preview</span>
                <h2>Latest Saved Properties</h2>
            </div>
            <a href="{{ route('dashboard.buyer.saved') }}" class="button button--ghost-blue">View All</a>
        </div>

        @if($latestSavedHomes->isEmpty())
            <div class="workspace-empty">No saved homes yet. Start by exploring marketplace listings.</div>
        @else
            <div class="workspace-property-grid">
                @foreach($latestSavedHomes as $property)
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
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
