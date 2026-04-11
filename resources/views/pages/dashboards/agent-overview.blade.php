@extends('layouts.app')

@section('content')
@php
    $leadTableMax = max(1, collect($pipeline)->max('count'));
    $agentHeadshot = $agentProfile?->headshot;
    $agentImage = $agentHeadshot
        ? (\Illuminate\Support\Str::startsWith($agentHeadshot, ['http://', 'https://', '/storage/', 'storage/']) ? $agentHeadshot : asset($agentHeadshot))
        : ($agentUser?->avatar ? asset('storage/' . ltrim($agentUser->avatar, '/')) : asset('images/realtors/3.png'));

    $agentLocation = collect([
        $agentProfile?->city ?: $agentUser?->city,
        $agentProfile?->state ?: $agentUser?->state,
        $agentProfile?->zip_code ?: $agentUser?->zip_code,
    ])->filter()->implode(', ');

    $agentHighlights = [
        ['label' => 'Rating', 'value' => $agentStats['score']],
        ['label' => 'Response', 'value' => $agentStats['response_rate']],
        ['label' => 'Unread', 'value' => $unreadMessagesCount],
        ['label' => 'Listings', 'value' => $activeListingCount . ' live / ' . $pendingReviewCount . ' pending'],
    ];

    $agentFocus = [
        'Prioritize new lead touches within the first ten minutes whenever possible.',
        'Keep listing details complete so pending submissions clear admin review faster.',
        'Move website inquiries into replied or archived states so the inbox stays clean.',
    ];
@endphp

<section class="or-dashboard or-dashboard--agent">
    <div class="or-dashboard__shell">
        <aside class="or-dashboard__sidebar">
            <div class="or-dashboard__brand">
                <img src="{{ asset('images/omnireferral-logo.png') }}" alt="OmniReferral logo">
                <div class="or-dashboard__brand-copy">
                    <strong>Agent Workspace</strong>
                    <span>OmniReferral command desk</span>
                </div>
            </div>

            <nav class="or-dashboard__nav" aria-label="Agent workspace navigation">
                <a class="is-active" href="{{ route('dashboard.agent') }}">
                    <span>Overview</span>
                    <small>Performance snapshot and recent activity</small>
                </a>
                <a href="{{ route('agent.profile') }}">
                    <span>Profile</span>
                    <small>Update public details and service area</small>
                </a>
                <a href="{{ route('agent.leads.index') }}">
                    <span>Leads</span>
                    <small>Work assigned opportunities from one queue</small>
                </a>
                <a href="{{ route('agent.listings.index') }}">
                    <span>Listings</span>
                    <small>Publish homes within your package limit</small>
                </a>
                <a href="{{ route('agent.messages.index') }}">
                    <span>Messages</span>
                    <small>Review every listing and profile inquiry</small>
                </a>
            </nav>

            <article class="or-dashboard__profile-card">
                <div class="or-dashboard__profile-head">
                    <div class="or-dashboard__avatar">
                        <img src="{{ $agentImage }}" alt="{{ $agentUser->name }} profile image" loading="lazy">
                    </div>
                    <div class="or-dashboard__profile-copy">
                        <span class="eyebrow">Agent Profile</span>
                        <h2>{{ $agentUser->name }}</h2>
                        <p>{{ $agentProfile?->brokerage_name ?: 'Brokerage profile pending' }}</p>
                    </div>
                </div>

                <div class="or-dashboard__chip-row">
                    <span>Agent</span>
                    <span>{{ $activePlan?->name ?: 'No active package' }}</span>
                    @if($agentLocation)
                        <span>{{ $agentLocation }}</span>
                    @endif
                </div>

                <div class="or-dashboard__profile-grid">
                    @foreach($agentHighlights as $highlight)
                        <div>
                            <span>{{ $highlight['label'] }}</span>
                            <strong>{{ $highlight['value'] }}</strong>
                        </div>
                    @endforeach
                </div>

                <div class="or-dashboard__action-row">
                    <a href="{{ route('agent.profile') }}" class="button button--ghost-blue">Edit Profile</a>
                    <a href="{{ route('agents.show', $agentProfile) }}" class="button button--blue">Public Page</a>
                </div>
            </article>

            <article class="or-dashboard__mini-card">
                <span class="eyebrow">Package Access</span>
                <strong>{{ $activePlan?->name ?: 'No active lead package' }}</strong>
                <p>{{ $activePlan?->description ?: 'Choose a package to unlock listing access and stronger lead routing support.' }}</p>
                <div class="or-dashboard__mini-grid">
                    <div>
                        <span>Slots Left</span>
                        <strong>{{ $remainingListingSlots }}</strong>
                    </div>
                    <div>
                        <span>Active Plan</span>
                        <strong>{{ $listingLimitLabel }}</strong>
                    </div>
                </div>
                <a href="{{ route('pricing') }}" class="button button--orange">Compare Packages</a>
            </article>
        </aside>

        <main class="or-dashboard__main">
            <header class="or-dashboard__header">
                <div class="or-dashboard__header-copy">
                    <span class="eyebrow">Agent Dashboard</span>
                    <h1>{{ $agentUser->name }}, keep leads, listings, and replies moving.</h1>
                    <p>This refreshed workspace follows the same executive dashboard layout you referenced, but now it is tuned to Omnireferral's blue-and-orange brand system and your real agent workflows.</p>
                    <div class="or-dashboard__header-chips">
                        <span>{{ $agentStats['response_rate'] }} response rate</span>
                        <span>{{ $agentStats['closed_leads'] }} closed leads</span>
                        <span>{{ $totalMessagesCount }} inbox conversations</span>
                        <span>{{ $totalFavoritesReceived }} property saves</span>
                    </div>
                </div>

                <div class="or-dashboard__header-actions">
                    <a href="{{ route('agent.listings.index') }}" class="button">Manage Listings</a>
                    <a href="{{ route('agent.messages.index') }}" class="button button--ghost-blue">Open Messages</a>
                </div>
            </header>

            <div class="or-dashboard__stat-row">
                <article class="or-dashboard__stat-card">
                    <span>Assigned Leads</span>
                    <strong>{{ $agentStats['leads_received'] }}</strong>
                    <p>Total opportunities routed to your account</p>
                </article>
                <article class="or-dashboard__stat-card">
                    <span>Closed Won</span>
                    <strong>{{ $agentStats['closed_leads'] }}</strong>
                    <p>Deals marked closed in your workspace</p>
                </article>
                <article class="or-dashboard__stat-card">
                    <span>Inbox</span>
                    <strong>{{ $totalMessagesCount }}</strong>
                    <p>Direct website inquiries waiting in your queue</p>
                </article>
                <article class="or-dashboard__stat-card or-dashboard__stat-card--warm">
                    <span>Listing Slots</span>
                    <strong>{{ $remainingListingSlots }}</strong>
                    <p>Remaining capacity before you hit your package cap</p>
                </article>
            </div>

            <div class="or-dashboard__content-grid">
                <section class="or-dashboard__surface">
                    <div class="or-dashboard__surface-header">
                        <div>
                            <span class="eyebrow">Pipeline</span>
                            <h2>Lead movement at a glance</h2>
                            <p>Keep new, contacted, qualified, and closed opportunities visible without leaving the overview page.</p>
                        </div>
                        <a href="{{ route('agent.leads.index') }}" class="button button--ghost-blue">View All Leads</a>
                    </div>

                    <div class="or-dashboard__progress-list">
                        @foreach($pipeline as $stage)
                            <article class="or-dashboard__progress-item">
                                <div class="or-dashboard__progress-item-top">
                                    <strong>{{ $stage['label'] }}</strong>
                                    <span>{{ $stage['count'] }} leads</span>
                                </div>
                                <div class="or-dashboard__progress-track">
                                    <span style="width: {{ ($stage['count'] / $leadTableMax) * 100 }}%"></span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="or-dashboard__surface">
                    <div class="or-dashboard__surface-header">
                        <div>
                            <span class="eyebrow">Capacity Pulse</span>
                            <h2>Package and listing readiness</h2>
                            <p>Quick signals for how much listing room and inbox capacity you still have right now.</p>
                        </div>
                    </div>

                    <div class="or-dashboard__mini-grid">
                        <div>
                            <span>Plan</span>
                            <strong>{{ $activePlan?->name ?: 'Not selected' }}</strong>
                        </div>
                        <div>
                            <span>Listing Access</span>
                            <strong>{{ $listingLimitLabel }}</strong>
                        </div>
                        <div>
                            <span>Live Listings</span>
                            <strong>{{ $activeListingCount }}</strong>
                        </div>
                        <div>
                            <span>Pending Review</span>
                            <strong>{{ $pendingReviewCount }}</strong>
                        </div>
                    </div>

                    <div class="or-dashboard__tag-cloud">
                        <span>Lead routing ready</span>
                        <span>Profile live</span>
                        <span>Website inbox connected</span>
                    </div>
                </section>
            </div>

            <section class="or-dashboard__surface or-dashboard__surface--wide">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Recent Leads</span>
                        <h2>Latest assigned opportunities</h2>
                        <p>The queue below keeps the most recent assigned leads visible without opening the full lead workspace.</p>
                    </div>
                    <a href="{{ route('agent.leads.index') }}" class="button button--ghost-blue">Manage Queue</a>
                </div>

                <div class="or-dashboard__table-wrap">
                    <table class="or-dashboard__table">
                        <thead>
                            <tr>
                                <th>Lead</th>
                                <th>Market</th>
                                <th>Package</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLeads as $lead)
                                <tr>
                                    <td>
                                        <div class="or-dashboard__detail-stack">
                                            <strong>{{ $lead->name }}</strong>
                                            <span>{{ ucfirst($lead->intent) }} lead</span>
                                            <small>{{ $lead->phone ?: 'No phone provided' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="or-dashboard__detail-stack">
                                            <strong>{{ $lead->zip_code ?: 'No ZIP' }}</strong>
                                            <span>{{ $lead->property_type ?: 'Property type pending' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="or-dashboard__detail-stack">
                                            <strong>{{ strtoupper($lead->package_type ?: 'N/A') }}</strong>
                                            <small>Lead ID: {{ $lead->id }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-pill status-pill--{{ $lead->statusTone() }}">{{ $lead->statusLabel() }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="or-dashboard__empty">
                                            <h3>No leads assigned yet</h3>
                                            <p>Once admin routes new opportunities to you, they will appear here automatically.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="or-dashboard__surface or-dashboard__surface--wide">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Recent Listings</span>
                        <h2>Your latest submitted properties</h2>
                        <p>See approval state, pricing, and market status at a glance before each property goes live.</p>
                    </div>
                    <a href="{{ route('agent.listings.index') }}" class="button button--ghost-blue">Manage Listings</a>
                </div>

                <div class="or-dashboard__listing-grid">
                    @forelse($recentProperties as $property)
                        <article class="or-dashboard__listing-card">
                            <div class="or-dashboard__listing-media">
                                <img src="{{ $property->image_url }}" alt="{{ $property->title }}" loading="lazy">
                                <span class="or-dashboard__listing-badge">{{ $property->status }}</span>
                            </div>
                            <div class="or-dashboard__listing-body">
                                <div class="or-dashboard__listing-top">
                                    <strong>${{ number_format($property->price) }}</strong>
                                    <span>{{ $property->property_type }}</span>
                                </div>
                                <h3>{{ $property->title }}</h3>
                                <p>{{ $property->location }}</p>
                                <div class="or-dashboard__listing-meta">
                                    <span>{{ $property->beds ?: 0 }} bd</span>
                                    <span>{{ $property->baths ?: 0 }} ba</span>
                                    <span>{{ number_format($property->sqft ?: 0) }} sqft</span>
                                </div>
                                <div class="or-dashboard__tag-cloud" style="margin-top: 1rem;">
                                    <span>{{ $property->approvalStatusLabel() }}</span>
                                    <span>{{ number_format($property->favorites_count ?? 0) }} saves</span>
                                    @if($property->approval_notes)
                                        <span>{{ \Illuminate\Support\Str::limit($property->approval_notes, 38) }}</span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="or-dashboard__empty">
                            <h3>No listings submitted yet</h3>
                            <p>Use your available package capacity to submit the next market-ready property for approval.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </main>

        <aside class="or-dashboard__rail">
            <section class="or-dashboard__summary-card">
                <span class="eyebrow">Today&apos;s Snapshot</span>
                <h3>Open workload</h3>
                <strong class="or-dashboard__summary-total">{{ $agentStats['leads_received'] }}</strong>
                <p>Assigned opportunities connected to your account right now.</p>

                <div class="or-dashboard__summary-meta">
                    <div>
                        <span>Response</span>
                        <strong>{{ $agentStats['response_rate'] }}</strong>
                    </div>
                    <div>
                        <span>Closed</span>
                        <strong>{{ $agentStats['closed_leads'] }}</strong>
                    </div>
                    <div>
                        <span>Unread</span>
                        <strong>{{ $unreadMessagesCount }}</strong>
                    </div>
                    <div>
                        <span>Slots Left</span>
                        <strong>{{ $remainingListingSlots }}</strong>
                    </div>
                </div>

                <div class="or-dashboard__summary-actions">
                    <a href="{{ route('agent.messages.index') }}" class="button button--ghost-blue">Inbox</a>
                    <a href="{{ route('pricing') }}" class="button button--orange">Upgrade</a>
                </div>
            </section>

            <section class="or-dashboard__panel">
                <span class="eyebrow">Activity</span>
                <h3>Newest conversations</h3>
                <div class="or-dashboard__timeline">
                    @forelse($recentMessages as $message)
                        <article>
                            <strong>{{ $message->subject ?: 'New inquiry' }}</strong>
                            <small>{{ $message->name }} &middot; {{ $message->created_at->format('M j, g:i A') }}</small>
                            <p>{{ \Illuminate\Support\Str::limit($message->message, 88) }}</p>
                        </article>
                    @empty
                        <article>
                            <strong>No messages yet</strong>
                            <p>New listing and profile inquiries will appear here automatically.</p>
                        </article>
                    @endforelse
                </div>
            </section>

            <section class="or-dashboard__panel">
                <span class="eyebrow">Focus Areas</span>
                <h3>What to keep moving</h3>
                <div class="or-dashboard__spotlight">
                    @foreach($agentFocus as $index => $focus)
                        <article>
                            <span class="or-dashboard__spotlight-index">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <strong>{{ $focus }}</strong>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>
</section>
@endsection
