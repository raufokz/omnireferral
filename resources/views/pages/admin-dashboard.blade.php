@extends('layouts.app')

@section('content')
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <span class="eyebrow">Super Admin</span>
            <h2>Control Center</h2>
            <p>OmniReferral operations and lead routing overview.</p>
        </div>
        <nav class="admin-nav" aria-label="Admin navigation">
            <a class="is-active" href="{{ route('admin.dashboard') }}">Overview</a>
            <a href="#lead-registry">Lead Pipeline</a>
            <a href="#agent-review">Agent Review</a>
            <a href="{{ route('admin.blog.index') }}">Blog CMS</a>
            <a href="{{ route('pricing') }}">Packages</a>
        </nav>
        <div class="admin-sidecard">
            <div class="sidecard-header">
                <span class="eyebrow">Internal Queues</span>
                <h4>Team Velocity</h4>
            </div>
            <div class="sidecard-list">
                @foreach($teamQueues as $queue)
                    <div class="sidecard-row">
                        <div>
                            <strong>{{ $queue['team'] }}</strong>
                            <small>{{ $queue['copy'] }}</small>
                        </div>
                        <span class="badge badge--pill">{{ $queue['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="admin-system">
            <span class="eyebrow">System Health</span>
            <h4>Platform Sync</h4>
            <p>All systems operational. Lead routing and API integrations stable.</p>
            <div class="system-meta">
                <span class="system-dot"></span>
                <span class="system-text">Uptime: 99.9%</span>
            </div>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-search">
                <input type="text" placeholder="Search workspace" aria-label="Search dashboard">
            </div>
            <div class="topbar-actions">
                <a class="button button--ghost-blue" href="{{ route('pricing') }}">View Packages</a>
                <a class="button button--orange" href="{{ route('admin.blog.index') }}">Manage Content</a>
            </div>
        </header>

        <section class="admin-kpi-grid" aria-label="Key metrics">
            <article class="admin-kpi-card">
                <span class="kpi-label">Leads</span>
                <div class="kpi-value">{{ number_format($stats['leads']) }}</div>
                <div class="kpi-sub">Global ecosystem</div>
            </article>
            <article class="admin-kpi-card">
                <span class="kpi-label">Partners</span>
                <div class="kpi-value">{{ number_format($stats['realtors']) }}</div>
                <div class="kpi-sub">Active agents</div>
            </article>
            <article class="admin-kpi-card">
                <span class="kpi-label">Pending</span>
                <div class="kpi-value">{{ number_format($stats['pending']) }}</div>
                <div class="kpi-sub">Compliance queue</div>
            </article>
            <article class="admin-kpi-card">
                <span class="kpi-label">Revenue</span>
                <div class="kpi-value">${{ number_format($stats['estimatedRevenue']) }}</div>
                <div class="kpi-sub">Projected monthly</div>
            </article>
        </section>

        <section id="lead-registry" class="admin-panel" aria-labelledby="lead-registry-heading">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Lead Registry</span>
                    <h2 id="lead-registry-heading">Qualification through routing</h2>
                </div>
                <div class="panel-filters">
                    <input type="text" placeholder="Filter leads" aria-label="Filter leads">
                </div>
            </div>
            <div class="admin-table" role="table" aria-label="Lead table">
                <div class="admin-table-row admin-table-head" role="row">
                    <span role="columnheader">Contact</span>
                    <span role="columnheader">Market</span>
                    <span role="columnheader">Package</span>
                    <span role="columnheader">Status</span>
                    <span role="columnheader">Routing</span>
                </div>
                @forelse($recentLeads as $lead)
                    <div class="admin-table-row" role="row">
                        <span role="cell">
                            <strong>{{ $lead->name }}</strong>
                            <small>{{ ucfirst($lead->intent) }}</small>
                        </span>
                        <span role="cell">
                            <strong>{{ $lead->zip_code }}</strong>
                            <small>Target market</small>
                        </span>
                        <span role="cell">
                            <strong>{{ strtoupper($lead->package_type ?? 'FREE') }}</strong>
                            <small>ID: {{ $lead->id }}</small>
                        </span>
                        <span role="cell">
                            <span class="badge badge--status">{{ ucfirst($lead->status ?? 'new') }}</span>
                            <small>Updated today</small>
                        </span>
                        <span role="cell">
                            @if(!$lead->assigned_agent_id)
                                <form action="{{ route('admin.leads.route', $lead) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="button button--ghost-blue button--compact">Assign</button>
                                </form>
                            @else
                                <span class="badge badge--success">Routed</span>
                            @endif
                        </span>
                    </div>
                @empty
                    <div class="admin-table-row empty" role="row">No recent leads found.</div>
                @endforelse
            </div>
        </section>

        <section id="agent-review" class="admin-panel admin-panel--dual" aria-labelledby="agent-review-heading">
            <div>
                <div class="panel-header">
                    <div>
                        <span class="eyebrow">Partner Compliance</span>
                        <h2 id="agent-review-heading">Agents awaiting approval</h2>
                    </div>
                </div>
                <div class="stack-list">
                    @forelse($pendingRealtors as $profile)
                        <article class="stack-card">
                            <div class="avatar-pill">{{ substr($profile->user->name ?? 'A', 0, 1) }}</div>
                            <div class="stack-copy">
                                <strong>{{ $profile->user->name ?? 'Unknown' }}</strong>
                                <small>{{ $profile->brokerage_name ?? 'Omni Partner' }}</small>
                            </div>
                            <a class="button button--ghost-blue button--compact" href="{{ route('agents.show', $profile) }}">Review</a>
                        </article>
                    @empty
                        <div class="stack-card stack-card--empty">Queue is clear</div>
                    @endforelse
                </div>
            </div>
            <div>
                <div class="panel-header">
                    <div>
                        <span class="eyebrow">Operational Goals</span>
                        <h2>Focus areas</h2>
                    </div>
                </div>
                <div class="stack-list">
                    <article class="stack-card stack-card--note">
                        <strong>Lead Lag Index</strong>
                        <p>Target first touch inside 10 minutes for all ISA queues.</p>
                    </article>
                    <article class="stack-card stack-card--note">
                        <strong>Route Saturation</strong>
                        <p>Ensure at least 3 active agents cover each top-10 ZIP.</p>
                    </article>
                    <article class="stack-card stack-card--note">
                        <strong>Content Velocity</strong>
                        <p>Publish 2 market updates per week to maintain SEO authority.</p>
                    </article>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
