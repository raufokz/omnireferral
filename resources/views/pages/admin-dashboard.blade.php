@extends('layouts.app')

@section('content')
@php
    $workspaceAvatar = $workspaceUser?->avatar
        ? asset('storage/' . ltrim($workspaceUser->avatar, '/'))
        : asset('images/realtors/3.png');
    $workspaceTitle = $isStaffView
        ? $workspaceUser->roleLabel() . ' Operations Desk'
        : 'Admin Command Center';
    $workspaceCopy = $isStaffView
        ? 'Coordinate lead follow-up, listing review, and internal operations from the same Omnireferral dashboard shell.'
        : 'Oversee leads, agent supply, listing moderation, and growth operations from one aligned Omnireferral workspace.';
    $workspaceHighlights = [
        ['label' => 'Leads', 'value' => number_format($stats['leads'] ?? 0)],
        ['label' => 'Partners', 'value' => number_format($stats['realtors'] ?? 0)],
        ['label' => 'Listings', 'value' => number_format($stats['properties'] ?? 0)],
        ['label' => 'Reviews', 'value' => number_format($stats['pendingListings'] ?? 0)],
    ];
@endphp

<section class="or-dashboard or-dashboard--admin">
    <div class="or-dashboard__shell">
        <aside class="or-dashboard__sidebar">
            <div class="or-dashboard__brand">
                <img src="{{ asset('images/omnireferral-logo.png') }}" alt="OmniReferral logo">
                <div class="or-dashboard__brand-copy">
                    <strong>{{ $isStaffView ? 'Staff Workspace' : 'Admin Workspace' }}</strong>
                    <span>OmniReferral operations desk</span>
                </div>
            </div>

            <nav class="or-dashboard__nav" aria-label="Operations workspace navigation">
                <a class="is-active" href="{{ route('admin.dashboard') }}">
                    <span>Overview</span>
                    <small>Lead flow, listing reviews, and team health</small>
                </a>
                <a href="{{ route('admin.leads.index') }}">
                    <span>Lead Registry</span>
                    <small>Review qualification, assignment, and package stages</small>
                </a>
                <a href="{{ route('admin.testimonials.index') }}">
                    <span>Testimonials</span>
                    <small>Manage published proof and customer stories</small>
                </a>
                <a href="{{ route('admin.blog.index') }}">
                    <span>Content</span>
                    <small>Update blog posts and growth-facing content</small>
                </a>
                <a href="{{ route('listings') }}">
                    <span>Marketplace</span>
                    <small>Check the public property experience</small>
                </a>
            </nav>

            <article class="or-dashboard__profile-card">
                <div class="or-dashboard__profile-head">
                    <div class="or-dashboard__avatar">
                        <img src="{{ $workspaceAvatar }}" alt="{{ $workspaceUser?->name ?: 'Workspace user' }} profile image" loading="lazy">
                    </div>
                    <div class="or-dashboard__profile-copy">
                        <span class="eyebrow">{{ $isStaffView ? 'Staff Access' : 'Admin Access' }}</span>
                        <h2>{{ $workspaceUser?->name ?: 'OmniReferral Team' }}</h2>
                        <p>{{ $workspaceUser?->email ?: 'Operations access active' }}</p>
                    </div>
                </div>

                <div class="or-dashboard__chip-row">
                    <span>{{ $workspaceUser?->roleLabel() ?? 'Operations' }}</span>
                    <span>{{ number_format($stats['pending'] ?? 0) }} pending agents</span>
                    <span>{{ number_format($stats['pendingListings'] ?? 0) }} listing reviews</span>
                </div>

                <div class="or-dashboard__profile-grid">
                    @foreach($workspaceHighlights as $highlight)
                        <div>
                            <span>{{ $highlight['label'] }}</span>
                            <strong>{{ $highlight['value'] }}</strong>
                        </div>
                    @endforeach
                </div>

                <div class="or-dashboard__action-row">
                    <a href="{{ route('admin.leads.index') }}" class="button button--blue">Manage Leads</a>
                    <a href="{{ route('admin.blog.index') }}" class="button button--ghost-blue">Open Content</a>
                </div>
            </article>

            <article class="or-dashboard__mini-card">
                <span class="eyebrow">Revenue Pulse</span>
                <strong>${{ number_format($stats['estimatedRevenue'] ?? 0) }}</strong>
                <p>Projected monthly revenue based on current lead-package signals and recent system activity.</p>
                <div class="or-dashboard__mini-grid">
                    <div>
                        <span>Packages</span>
                        <strong>{{ number_format($stats['packages'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Contacts</span>
                        <strong>{{ number_format($stats['contacts'] ?? 0) }}</strong>
                    </div>
                </div>
                <a href="{{ route('admin.testimonials.index') }}" class="button button--orange">Review Proof</a>
            </article>
        </aside>

        <main class="or-dashboard__main">
            <header class="or-dashboard__header">
                <div class="or-dashboard__header-copy">
                    <span class="eyebrow">{{ $isStaffView ? 'Staff Dashboard' : 'Admin Dashboard' }}</span>
                    <h1>{{ $workspaceTitle }}</h1>
                    <p>{{ $workspaceCopy }}</p>
                    <div class="or-dashboard__header-chips">
                        <span>{{ number_format($stats['leads'] ?? 0) }} total leads</span>
                        <span>{{ number_format($stats['pendingListings'] ?? 0) }} listings to review</span>
                        <span>{{ number_format($stats['pending'] ?? 0) }} pending agent approvals</span>
                        <span>{{ number_format($stats['propertyFavorites'] ?? 0) }} property saves</span>
                    </div>
                </div>

                <div class="or-dashboard__header-actions">
                    <a href="{{ route('admin.leads.index') }}" class="button">Lead Registry</a>
                    <a href="{{ route('admin.blog.index') }}" class="button button--ghost-blue">Manage Content</a>
                </div>
            </header>

            <div class="or-dashboard__stat-row">
                <article class="or-dashboard__stat-card">
                    <span>Total Leads</span>
                    <strong>{{ number_format($stats['leads'] ?? 0) }}</strong>
                    <p>All platform opportunities currently in the ecosystem</p>
                </article>
                <article class="or-dashboard__stat-card">
                    <span>Active Partners</span>
                    <strong>{{ number_format($stats['realtors'] ?? 0) }}</strong>
                    <p>Agents and partner profiles active across the network</p>
                </article>
                <article class="or-dashboard__stat-card">
                    <span>Pending Approvals</span>
                    <strong>{{ number_format($stats['pending'] ?? 0) }}</strong>
                    <p>Agent profiles waiting for operations review</p>
                </article>
                <article class="or-dashboard__stat-card or-dashboard__stat-card--warm">
                    <span>Listing Reviews</span>
                    <strong>{{ number_format($stats['pendingListings'] ?? 0) }}</strong>
                    <p>Property submissions queued for approval or rejection</p>
                </article>
            </div>

            <div class="or-dashboard__content-grid">
                <section class="or-dashboard__surface">
                    <div class="or-dashboard__surface-header">
                        <div>
                            <span class="eyebrow">Lead Pipeline</span>
                            <h2>Stage distribution across the funnel</h2>
                            <p>Monitor how many leads are new, qualified, assigned, or closed without leaving the dashboard.</p>
                        </div>
                    </div>

                    <div class="or-dashboard__progress-list">
                        @foreach($pipelineHealth as $stage)
                            <article class="or-dashboard__progress-item">
                                <div class="or-dashboard__progress-item-top">
                                    <strong>{{ $stage['label'] }}</strong>
                                    <span>{{ number_format($stage['count']) }} leads</span>
                                </div>
                                <div class="or-dashboard__progress-track">
                                    <span style="width: {{ $stage['percent'] }}%"></span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="or-dashboard__surface">
                    <div class="or-dashboard__surface-header">
                        <div>
                            <span class="eyebrow">Team Queues</span>
                            <h2>Operations focus by desk</h2>
                            <p>See the pressure points across qualification, packaging, agent delivery, and growth work.</p>
                        </div>
                    </div>

                    <div class="or-dashboard__metric-board">
                        @foreach($teamQueues as $queue)
                            <article>
                                <div class="or-dashboard__detail-stack">
                                    <strong>{{ $queue['team'] }}</strong>
                                    <span>{{ $queue['copy'] }}</span>
                                </div>
                                <strong>{{ number_format($queue['count']) }}</strong>
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>

            <section class="or-dashboard__surface or-dashboard__surface--wide">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Recent Leads</span>
                        <h2>Latest records in the registry</h2>
                        <p>Keep the most recent leads visible before you jump into the full management screen.</p>
                    </div>
                    <a href="{{ route('admin.leads.index') }}" class="button button--ghost-blue">Manage All Leads</a>
                </div>

                <div class="or-dashboard__table-wrap">
                    <table class="or-dashboard__table">
                        <thead>
                            <tr>
                                <th>Lead</th>
                                <th>Intent</th>
                                <th>Package</th>
                                <th>Status</th>
                                <th>Assigned</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLeads as $lead)
                                <tr>
                                    <td>
                                        <div class="or-dashboard__detail-stack">
                                            <strong>{{ $lead->name }}</strong>
                                            <span>{{ $lead->email ?: 'No email provided' }}</span>
                                            <small>{{ $lead->phone ?: 'No phone provided' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="or-dashboard__detail-stack">
                                            <strong>{{ ucfirst($lead->intent ?: 'Unknown') }}</strong>
                                            <span>{{ $lead->zip_code ?: 'No ZIP' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ strtoupper($lead->package_type ?: 'N/A') }}</td>
                                    <td>
                                        <span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug((string) $lead->status, '_') }}">
                                            {{ $lead->statusLabel() }}
                                        </span>
                                    </td>
                                    <td>{{ $lead->assignedAgent?->name ?? 'Unassigned' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="or-dashboard__empty">
                                            <h3>No recent leads yet</h3>
                                            <p>Lead intake activity will appear here as soon as records start flowing in.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="or-dashboard__dual-grid">
                <section class="or-dashboard__surface">
                    <div class="or-dashboard__surface-header">
                        <div>
                            <span class="eyebrow">Listing Review Queue</span>
                            <h2>Approve or reject pending listings</h2>
                            <p>Agent and seller submissions remain pending until operations takes action.</p>
                        </div>
                    </div>

                    <div class="or-dashboard__queue-list">
                        @forelse($pendingProperties as $property)
                            <article>
                                <strong>{{ $property->title }}</strong>
                                <small>{{ $property->created_at?->format('M j, Y g:i A') ?: 'Pending review' }}</small>
                                <p>{{ $property->location }} and {{ optional(optional($property->realtorProfile)->user)->name ?? 'unassigned owner' }}.</p>
                                <small>{{ number_format($property->favorites_count ?? 0) }} saves</small>
                                <div class="or-dashboard__action-row">
                                    <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">Preview</a>
                                    <form method="POST" action="{{ route('admin.properties.review', $property) }}" class="admin-inline-form">
                                        @csrf
                                        <input type="hidden" name="decision" value="approve">
                                        <button type="submit" class="button">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.properties.review', $property) }}" class="admin-inline-form">
                                        @csrf
                                        <input type="hidden" name="decision" value="reject">
                                        <button type="submit" class="button button--ghost-blue">Reject</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <article>
                                <strong>No listings waiting for review</strong>
                                <p>New submissions will appear here as soon as agents or sellers send them in.</p>
                            </article>
                        @endforelse
                    </div>
                </section>

                <section class="or-dashboard__surface">
                    <div class="or-dashboard__surface-header">
                        <div>
                            <span class="eyebrow">Recent Listing Inquiries</span>
                            <h2>Messages routed through listing contact flows</h2>
                            <p>Buyer, seller, and agent listing inquiries now land in a single view for operations visibility.</p>
                        </div>
                    </div>

                    <div class="or-dashboard__queue-list">
                        @forelse($recentListingMessages as $message)
                            <article>
                                <strong>{{ $message->subject ?: 'Listing inquiry' }}</strong>
                                <small>{{ $message->created_at?->format('M j, Y g:i A') ?: 'Just now' }}</small>
                                <p>
                                    {{ ucfirst($message->role ?: 'guest') }}
                                    about
                                    {{ $message->property?->title ?: ($message->realtorProfile?->user?->name ? 'agent profile for ' . $message->realtorProfile->user->name : 'a direct inquiry') }}.
                                </p>
                                <small>Routed to {{ $message->recipient?->name ?: 'OmniReferral team' }}</small>
                            </article>
                        @empty
                            <article>
                                <strong>No listing inquiries yet</strong>
                                <p>Website listing and agent-profile messages will show here once they are submitted.</p>
                            </article>
                        @endforelse
                    </div>
                </section>
            </div>
        </main>

        <aside class="or-dashboard__rail">
            <article class="or-dashboard__summary-card">
                <span class="eyebrow">Operations Summary</span>
                <h3>Platform-wide snapshot</h3>
                <p>The summary rail mirrors the same premium Omnireferral card language used across every role dashboard.</p>
                <strong class="or-dashboard__summary-total">${{ number_format($stats['estimatedRevenue'] ?? 0) }}</strong>
                <div class="or-dashboard__summary-meta">
                    <div>
                        <span>Packages</span>
                        <strong>{{ number_format($stats['packages'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Testimonials</span>
                        <strong>{{ number_format($stats['testimonials'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Listings</span>
                        <strong>{{ number_format($stats['properties'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Favorites</span>
                        <strong>{{ number_format($stats['propertyFavorites'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Contacts</span>
                        <strong>{{ number_format($stats['contacts'] ?? 0) }}</strong>
                    </div>
                </div>
                <div class="or-dashboard__summary-actions">
                    <a href="{{ route('admin.leads.index') }}" class="button button--orange">Open Registry</a>
                    <a href="{{ route('admin.testimonials.index') }}" class="button button--ghost-blue">Manage Testimonials</a>
                </div>
            </article>

            <article class="or-dashboard__panel">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Pending Agents</span>
                        <h2>Profiles needing approval</h2>
                    </div>
                </div>

                <div class="or-dashboard__queue-list">
                    @forelse($pendingRealtors as $realtor)
                        <article>
                            <strong>{{ optional($realtor->user)->name ?? 'Pending profile' }}</strong>
                            <small>{{ optional($realtor->created_at)->format('M j, Y') ?: 'Recently created' }}</small>
                            <p>{{ $realtor->brokerage_name ?: 'Brokerage pending' }} in {{ $realtor->city ?: 'market pending' }}.</p>
                        </article>
                    @empty
                        <article>
                            <strong>No pending agent profiles</strong>
                            <p>New partner approvals will appear here when onboarding creates them.</p>
                        </article>
                    @endforelse
                </div>
            </article>

            <article class="or-dashboard__panel">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Recent Activity</span>
                        <h2>Latest timeline</h2>
                    </div>
                </div>

                <ul class="or-dashboard__activity-feed">
                    @forelse(collect($recentActivityFeed ?? [])->take(4) as $activity)
                        <li>
                            <strong>{{ ucfirst($activity->type ?? 'Activity') }}</strong>
                            <p>{{ $activity->content }}</p>
                        </li>
                    @empty
                        <li>
                            <strong>No activity recorded yet</strong>
                            <p>Lead notes, assignment updates, and workflow events will show here once they are available.</p>
                        </li>
                    @endforelse
                </ul>
            </article>
        </aside>
    </div>
</section>
@endsection
