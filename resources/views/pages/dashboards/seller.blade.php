@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Seller Workspace')
@section('dashboard_title', 'Seller Overview')
@section('dashboard_description', 'Track listing readiness, request velocity, and market exposure from one page.')

@section('dashboard_actions')
    <a href="{{ route('dashboard.seller.listings') }}" class="button">Manage Listings</a>
    <a href="{{ route('dashboard.seller.requests') }}" class="button button--ghost-blue">View Requests</a>
@endsection

@section('content')
@php
    $sellerJourneyMax = max(1, collect($sellerJourney)->max('count'));
@endphp

<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi" data-icon="🏠" data-trend="Visible">
            <span>Active Listings</span>
            <strong>{{ number_format($sellerStats['active_listings']) }}</strong>
            <span>Approved properties visible in market</span>
        </article>
        <article class="workspace-card workspace-kpi workspace-kpi--warm" data-icon="✉" data-trend="Open">
            <span>Open Inquiries</span>
            <strong>{{ number_format($sellerStats['open_inquiries']) }}</strong>
            <span>Seller-side lead activity</span>
        </article>
        <article class="workspace-card workspace-kpi" data-icon="↔" data-trend="Demand">
            <span>Buyer Matches</span>
            <strong>{{ number_format($sellerStats['buyer_matches']) }}</strong>
            <span>Buyer demand signals in queue</span>
        </article>
        <article class="workspace-card workspace-kpi workspace-kpi--violet" data-icon="$" data-trend="Pricing">
            <span>Price Updates</span>
            <strong>{{ number_format($sellerStats['price_updates']) }}</strong>
            <span>Recent listing adjustment prompts</span>
        </article>
    </section>

    <section class="workspace-grid workspace-grid--2">
        <article class="workspace-card">
            <span class="eyebrow">Pipeline</span>
            <h2>Seller Request Journey</h2>
            <div class="workspace-stack">
                @foreach($sellerJourney as $stage)
                    <div>
                        <div class="workspace-actions" style="justify-content: space-between;">
                            <strong>{{ $stage['label'] }}</strong>
                            <small>{{ number_format($stage['count']) }}</small>
                        </div>
                        <div style="height: 8px; border-radius: 999px; background: #e8edf4; margin-top: 0.45rem;">
                            <div style="height:100%; border-radius:999px; width: {{ ($stage['count'] / $sellerJourneyMax) * 100 }}%; background: linear-gradient(90deg, #0b3668, #ff6b00);"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="workspace-card">
            <span class="eyebrow">Action Center</span>
            <h2>Keep Listings Moving</h2>
            <ul class="workspace-list">
                <li>
                    <strong>Submit your next listing</strong>
                    <small>Use the Listings page to send new inventory for admin review.</small>
                </li>
                <li>
                    <strong>Review seller request outcomes</strong>
                    <small>Check qualified, in-market, and closed movement in one table.</small>
                </li>
                <li>
                    <strong>Compare public visibility</strong>
                    <small>Open marketplace pages to validate listing presentation.</small>
                </li>
            </ul>
            <div class="workspace-actions" style="margin-top: 0.8rem;">
                <a href="{{ route('dashboard.seller.listings') }}" class="button">Listings Page</a>
                <a href="{{ route('listings') }}" class="button button--ghost-blue">Open Marketplace</a>
            </div>
        </article>
    </section>

    <section class="workspace-card">
        <div class="workspace-actions" style="justify-content: space-between; align-items: flex-start; margin-bottom: 0.8rem;">
            <div>
                <span class="eyebrow">Live Listings Preview</span>
                <h2>Current Marketplace Properties</h2>
            </div>
            <a href="{{ route('dashboard.seller.listings') }}" class="button button--ghost-blue">Open Listings Page</a>
        </div>

        @if($properties->isEmpty())
            <div class="workspace-empty">No active marketplace properties are available yet.</div>
        @else
            <div class="workspace-property-grid">
                @foreach($properties->take(3) as $property)
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
                                <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">View Listing</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
