@extends('layouts.app')

@section('content')
@php
    $buyerUser = auth()->user();
    $buyerAvatar = $buyerUser?->avatar
        ? asset('storage/' . ltrim($buyerUser->avatar, '/'))
        : asset('images/realtors/3.png');
    $buyerJourneyMax = max(1, collect($buyerJourney)->max('count'));
    $propertyMix = $properties
        ->groupBy(fn ($property) => ucfirst($property->property_type ?: 'Home'))
        ->map(fn ($group) => $group->count())
        ->sortDesc()
        ->take(3);
    $agentMatchCount = data_get(collect($buyerJourney)->firstWhere('label', 'Agent Match'), 'count', 0);
    $closedBuyerCount = data_get(collect($buyerJourney)->firstWhere('label', 'Closed'), 'count', 0);
    $buyerHighlights = [
        ['label' => 'Shortlist', 'value' => $buyerStats['saved_listings']],
        ['label' => 'Favorites', 'value' => $buyerStats['favorites']],
        ['label' => 'Requests', 'value' => $buyerRequests->count()],
        ['label' => 'Alerts', 'value' => $buyerStats['new_alerts']],
    ];
@endphp

<section class="or-dashboard or-dashboard--buyer">
    <div class="or-dashboard__shell">
        <aside class="or-dashboard__sidebar">
            <div class="or-dashboard__brand">
                <img src="{{ asset('images/omnireferral-logo.png') }}" alt="OmniReferral logo">
                <div class="or-dashboard__brand-copy">
                    <strong>Buyer Workspace</strong>
                    <span>OmniReferral search desk</span>
                </div>
            </div>

            <nav class="or-dashboard__nav" aria-label="Buyer workspace navigation">
                <a class="is-active" href="{{ route('dashboard.buyer') }}">
                    <span>Overview</span>
                    <small>Search progress, saved homes, and request activity</small>
                </a>
                <a href="#buyer-shortlist">
                    <span>Saved Homes</span>
                    <small>Review the homes you are tracking right now</small>
                </a>
                <a href="#buyer-requests">
                    <span>Requests</span>
                    <small>Watch buyer intake and agent match movement</small>
                </a>
                <a href="{{ route('listings') }}">
                    <span>Marketplace</span>
                    <small>Explore approved homes across OmniReferral</small>
                </a>
                <a href="{{ route('contact') }}">
                    <span>Support</span>
                    <small>Talk with the team about your search strategy</small>
                </a>
            </nav>

            <article class="or-dashboard__profile-card">
                <div class="or-dashboard__profile-head">
                    <div class="or-dashboard__avatar">
                        <img src="{{ $buyerAvatar }}" alt="{{ $buyerUser?->name ?: 'Buyer' }} profile image" loading="lazy">
                    </div>
                    <div class="or-dashboard__profile-copy">
                        <span class="eyebrow">Buyer Profile</span>
                        <h2>{{ $buyerUser?->name ?: 'OmniReferral Buyer' }}</h2>
                        <p>{{ $buyerUser?->email ?: 'Search preferences ready to refine' }}</p>
                    </div>
                </div>

                <div class="or-dashboard__chip-row">
                    <span>Buyer</span>
                    <span>{{ $buyerStats['saved_searches'] }} saved searches</span>
                    <span>{{ $buyerStats['new_alerts'] }} live alerts</span>
                </div>

                <div class="or-dashboard__profile-grid">
                    @foreach($buyerHighlights as $highlight)
                        <div>
                            <span>{{ $highlight['label'] }}</span>
                            <strong>{{ $highlight['value'] }}</strong>
                        </div>
                    @endforeach
                </div>

                <div class="or-dashboard__action-row">
                    <a href="{{ route('listings') }}" class="button button--blue">Browse Listings</a>
                    <a href="{{ route('contact') }}" class="button button--ghost-blue">Talk To Support</a>
                </div>
            </article>

            <article class="or-dashboard__mini-card">
                <span class="eyebrow">Search Rhythm</span>
                <strong>Stay ready for the next best match</strong>
                <p>OmniReferral keeps shortlist activity, request movement, and new inventory signals visible in one consistent workspace.</p>
                <div class="or-dashboard__mini-grid">
                    <div>
                        <span>Agent Match</span>
                        <strong>{{ $agentMatchCount }}</strong>
                    </div>
                    <div>
                        <span>Closed</span>
                        <strong>{{ $closedBuyerCount }}</strong>
                    </div>
                </div>
                <a href="{{ route('contact') }}" class="button button--orange">Request Buyer Help</a>
            </article>
        </aside>

        <main class="or-dashboard__main">
            <header class="or-dashboard__header">
                <div class="or-dashboard__header-copy">
                    <span class="eyebrow">Buyer Dashboard</span>
                    <h1>Keep your shortlist, search alerts, and request follow-up aligned.</h1>
                    <p>This buyer workspace now uses the same Omnireferral shell as every other role, so the brand colors, spacing, font, and interaction patterns stay consistent everywhere.</p>
                    <div class="or-dashboard__header-chips">
                        <span>{{ $buyerStats['saved_searches'] }} saved searches</span>
                        <span>{{ $buyerRequests->count() }} recent requests</span>
                        <span>{{ $properties->count() }} live homes in view</span>
                    </div>
                </div>

                <div class="or-dashboard__header-actions">
                    <a href="{{ route('listings') }}" class="button">Explore Listings</a>
                    <a href="{{ route('contact') }}" class="button button--ghost-blue">Contact OmniReferral</a>
                </div>
            </header>

            <div class="or-dashboard__stat-row">
                <article class="or-dashboard__stat-card">
                    <span>Saved Shortlist</span>
                    <strong>{{ $buyerStats['saved_listings'] }}</strong>
                    <p>Homes currently visible inside your shortlist flow</p>
                </article>
                <article class="or-dashboard__stat-card">
                    <span>Top Picks</span>
                    <strong>{{ $buyerStats['favorites'] }}</strong>
                    <p>Favorite properties worth another look this week</p>
                </article>
                <article class="or-dashboard__stat-card">
                    <span>Request Queue</span>
                    <strong>{{ $buyerRequests->count() }}</strong>
                    <p>Buyer submissions waiting on qualification or routing</p>
                </article>
                <article class="or-dashboard__stat-card or-dashboard__stat-card--warm">
                    <span>Market Alerts</span>
                    <strong>{{ $buyerStats['new_alerts'] }}</strong>
                    <p>Signals tied to new activity in the buyer funnel</p>
                </article>
            </div>

            <div class="or-dashboard__content-grid">
                <section class="or-dashboard__surface">
                    <div class="or-dashboard__surface-header">
                        <div>
                            <span class="eyebrow">Journey Pulse</span>
                            <h2>Buyer request movement at a glance</h2>
                            <p>Track each stage from first submission through qualification, matching, and close.</p>
                        </div>
                    </div>

                    <div class="or-dashboard__progress-list">
                        @foreach($buyerJourney as $stage)
                            <article class="or-dashboard__progress-item">
                                <div class="or-dashboard__progress-item-top">
                                    <strong>{{ $stage['label'] }}</strong>
                                    <span>{{ $stage['count'] }} records</span>
                                </div>
                                <div class="or-dashboard__progress-track">
                                    <span style="width: {{ ($stage['count'] / $buyerJourneyMax) * 100 }}%"></span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="or-dashboard__surface">
                    <div class="or-dashboard__surface-header">
                        <div>
                            <span class="eyebrow">Search Profile</span>
                            <h2>What your current activity says</h2>
                            <p>A compact snapshot of saved-search depth, inventory mix, and search momentum.</p>
                        </div>
                    </div>

                    <div class="or-dashboard__mini-grid">
                        <div>
                            <span>Saved Searches</span>
                            <strong>{{ $buyerStats['saved_searches'] }}</strong>
                        </div>
                        <div>
                            <span>Favorites</span>
                            <strong>{{ $buyerStats['favorites'] }}</strong>
                        </div>
                        <div>
                            <span>Active Inventory</span>
                            <strong>{{ $properties->count() }}</strong>
                        </div>
                        <div>
                            <span>Alerts</span>
                            <strong>{{ $buyerStats['new_alerts'] }}</strong>
                        </div>
                    </div>

                    <div class="or-dashboard__tag-cloud">
                        @forelse($propertyMix as $label => $count)
                            <span>{{ $label }} {{ $count }}</span>
                        @empty
                            <span>Inventory mix updating</span>
                        @endforelse
                    </div>
                </section>
            </div>

            <section class="or-dashboard__surface or-dashboard__surface--wide" id="buyer-shortlist">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Saved Homes</span>
                        <h2>Properties worth keeping close</h2>
                        <p>The marketplace cards below keep your current shortlist-style view aligned with the rest of the workspace design.</p>
                    </div>
                    <a href="{{ route('listings') }}" class="button button--ghost-blue">Browse More</a>
                </div>

                <div class="or-dashboard__listing-grid">
                    @forelse($properties->take(3) as $property)
                        <article class="or-dashboard__listing-card">
                            <div class="or-dashboard__listing-media">
                                <img src="{{ $property->image_url }}" alt="{{ $property->title }}" loading="lazy">
                                <span class="or-dashboard__listing-badge">{{ ucfirst($property->property_type ?: 'Home') }}</span>
                            </div>
                            <div class="or-dashboard__listing-body">
                                <div class="or-dashboard__listing-top">
                                    <strong>${{ number_format($property->price) }}</strong>
                                    <span>{{ $property->status }}</span>
                                </div>
                                <h3>{{ $property->title }}</h3>
                                <p>{{ $property->location }}</p>
                                <div class="or-dashboard__listing-meta">
                                    <span>{{ $property->beds }} bd</span>
                                    <span>{{ $property->baths }} ba</span>
                                    <span>{{ number_format($property->sqft) }} sqft</span>
                                </div>
                                <div class="or-dashboard__listing-actions">
                                    <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">View Listing</a>
                                    <a href="{{ route('properties.show', $property) }}#property-contact" class="button">Contact Agent</a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="or-dashboard__empty">
                            <h3>No approved homes are visible yet</h3>
                            <p>New marketplace listings will appear here as soon as inventory is available.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="or-dashboard__surface or-dashboard__surface--wide" id="buyer-requests">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Recent Requests</span>
                        <h2>Your latest buyer conversations</h2>
                        <p>See the most recent request activity without leaving the overview screen.</p>
                    </div>
                </div>

                <div class="or-dashboard__table-wrap">
                    <table class="or-dashboard__table">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Market</th>
                                <th>Status</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($buyerRequests->take(4) as $request)
                                <tr>
                                    <td>
                                        <div class="or-dashboard__detail-stack">
                                            <strong>{{ $request->name }}</strong>
                                            <span>{{ $request->email ?: 'No email provided' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="or-dashboard__detail-stack">
                                            <strong>{{ $request->zip_code ?: 'No ZIP yet' }}</strong>
                                            <span>{{ $request->property_type ?: 'Search preferences pending' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug((string) $request->status, '_') }}">
                                            {{ $request->statusLabel() }}
                                        </span>
                                    </td>
                                    <td>{{ $request->created_at?->format('M j, Y') ?: 'Pending' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="or-dashboard__empty">
                                            <h3>No buyer requests yet</h3>
                                            <p>Once you submit a request or inquiry, the activity timeline will show up here.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <aside class="or-dashboard__rail">
            <article class="or-dashboard__summary-card">
                <span class="eyebrow">Search Summary</span>
                <h3>Buyer activity snapshot</h3>
                <p>One consistent Omnireferral card system keeps the most important numbers visible on every role dashboard.</p>
                <strong class="or-dashboard__summary-total">{{ $buyerStats['saved_listings'] }}</strong>
                <div class="or-dashboard__summary-meta">
                    <div>
                        <span>Saved Searches</span>
                        <strong>{{ $buyerStats['saved_searches'] }}</strong>
                    </div>
                    <div>
                        <span>Favorites</span>
                        <strong>{{ $buyerStats['favorites'] }}</strong>
                    </div>
                    <div>
                        <span>Requests</span>
                        <strong>{{ $buyerRequests->count() }}</strong>
                    </div>
                    <div>
                        <span>Alerts</span>
                        <strong>{{ $buyerStats['new_alerts'] }}</strong>
                    </div>
                </div>
                <div class="or-dashboard__summary-actions">
                    <a href="{{ route('listings') }}" class="button button--orange">See Listings</a>
                    <a href="{{ route('contact') }}" class="button button--ghost-blue">Get Help</a>
                </div>
            </article>

            <article class="or-dashboard__panel">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Activity</span>
                        <h2>Newest buyer requests</h2>
                    </div>
                </div>

                <div class="or-dashboard__queue-list">
                    @forelse($buyerRequests->take(3) as $request)
                        <article>
                            <strong>{{ $request->name }}</strong>
                            <small>{{ $request->created_at?->format('M j, g:i A') ?: 'Pending' }}</small>
                            <p>{{ $request->zip_code ?: 'No ZIP submitted yet' }} and {{ $request->statusLabel() }}.</p>
                        </article>
                    @empty
                        <article>
                            <strong>No buyer activity yet</strong>
                            <p>New conversations and requests will appear here as they arrive.</p>
                        </article>
                    @endforelse
                </div>
            </article>

            <article class="or-dashboard__panel">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Top Categories</span>
                        <h2>Inventory mix</h2>
                    </div>
                </div>

                <div class="or-dashboard__rail-list">
                    @forelse($propertyMix as $label => $count)
                        <article>
                            <strong>{{ $label }}</strong>
                            <p>{{ $count }} homes in the current visible set.</p>
                        </article>
                    @empty
                        <article>
                            <strong>Mix updating</strong>
                            <p>Inventory categories will populate as listings are approved.</p>
                        </article>
                    @endforelse
                </div>
            </article>

            <article class="or-dashboard__panel">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Focus Areas</span>
                        <h2>How to keep momentum</h2>
                    </div>
                </div>

                <div class="or-dashboard__spotlight">
                    <article>
                        <span class="or-dashboard__spotlight-index">01</span>
                        <div>
                            <strong>Keep favorites current</strong>
                            <p>Refreshing your shortlist makes it easier to surface the right homes quickly.</p>
                        </div>
                    </article>
                    <article>
                        <span class="or-dashboard__spotlight-index">02</span>
                        <div>
                            <strong>Respond to agent outreach</strong>
                            <p>Fast replies help OmniReferral route you toward the right expert without delay.</p>
                        </div>
                    </article>
                    <article>
                        <span class="or-dashboard__spotlight-index">03</span>
                        <div>
                            <strong>Use listing contact forms</strong>
                            <p>Direct listing inquiries keep the conversation tied to the exact home you care about.</p>
                        </div>
                    </article>
                </div>
            </article>
        </aside>
    </div>
</section>
@endsection
