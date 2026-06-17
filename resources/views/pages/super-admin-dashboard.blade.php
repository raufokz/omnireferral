@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Super Admin')
@section('dashboard_title', 'Platform Control Center')
@section('dashboard_description', 'Full-platform visibility: system health, audit logs, pipeline value, user growth, and direct access to every operational layer.')

@section('dashboard_actions')
    <a href="{{ route('admin.activity.index') }}" class="button button--ghost-blue">Audit Log</a>
    <a href="{{ route('admin.users.index') }}" class="button button--ghost-blue">User Management</a>
    <a href="{{ route('admin.leads.index') }}" class="button">Lead Registry</a>
@endsection

@push('styles')
<style>
.sa-kpi-icon {
    width: 2.6rem; height: 2.6rem;
    border-radius: 13px; display: grid; place-items: center;
    margin-bottom: 0.55rem;
}
.sa-kpi-icon svg { width: 1.2rem; height: 1.2rem; }
.sa-kpi-icon--blue   { background: rgba(11,54,104,0.10); color: #0b3668; }
.sa-kpi-icon--orange { background: rgba(255,107,0,0.13); color: #c2410c; }
.sa-kpi-icon--green  { background: rgba(22,163,74,0.12); color: #15803d; }
.sa-kpi-icon--violet { background: rgba(109,93,252,0.12); color: #5145cd; }
.sa-kpi-icon--teal   { background: rgba(14,165,233,0.12); color: #0369a1; }
.sa-kpi-icon--amber  { background: rgba(217,119,6,0.12); color: #b45309; }

/* Revenue & Pipeline Highlights */
.sa-revenue-band {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    border: 1px solid var(--dash-shell-border);
    border-radius: 16px;
    overflow: hidden;
    background: var(--dash-shell-panel);
}
.sa-revenue-segment {
    padding: 1.1rem 1.25rem;
    border-right: 1px solid var(--dash-shell-border);
    position: relative;
}
.sa-revenue-segment:last-child { border-right: none; }
.sa-revenue-segment__eyebrow { font-size: 0.73rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: var(--dash-shell-muted); margin-bottom: 0.25rem; }
.sa-revenue-segment__value { font-family: 'Sora', 'Inter', sans-serif; font-size: 1.85rem; font-weight: 800; line-height: 1; color: var(--dash-shell-text); }
.sa-revenue-segment__sub { font-size: 0.78rem; color: var(--dash-shell-muted); margin-top: 0.3rem; }
@media (max-width: 620px) {
    .sa-revenue-band { grid-template-columns: 1fr; }
    .sa-revenue-segment { border-right: none; border-bottom: 1px solid var(--dash-shell-border); }
    .sa-revenue-segment:last-child { border-bottom: none; }
}

/* Trend chart bars */
.sa-trend-chart { display: flex; align-items: flex-end; gap: 4px; height: 72px; }
.sa-trend-bar { flex: 1; border-radius: 4px 4px 0 0; min-height: 4px; transition: height 0.5s cubic-bezier(.16,1,.3,1); }

/* Pipeline bar */
.sa-pipeline-bar { height: 10px; border-radius: 999px; background: #e8edf4; overflow: hidden; }
.sa-pipeline-bar__fill { height: 100%; border-radius: 999px; transition: width 0.6s cubic-bezier(.16,1,.3,1); }

/* Audit log */
.sa-audit-row {
    display: flex; align-items: flex-start; gap: 0.65rem;
    padding: 0.65rem 0;
    border-bottom: 1px solid var(--dash-shell-border);
}
.sa-audit-row:last-child { border-bottom: none; }
.sa-audit-icon {
    width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
    display: grid; place-items: center; background: rgba(11,54,104,0.09); color: #0b3668;
}
.sa-audit-icon svg { width: 0.9rem; height: 0.9rem; }
.sa-audit-actor { font-size: 0.82rem; font-weight: 700; color: var(--dash-shell-text); }
.sa-audit-action { font-size: 0.79rem; color: var(--dash-shell-muted); line-height: 1.45; }
.sa-audit-time { font-size: 0.72rem; color: #94a3b8; white-space: nowrap; }

/* Quick links */
.sa-quick-links { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 0.65rem; }
.sa-quick-link {
    background: var(--dash-shell-panel-soft);
    border: 1px solid var(--dash-shell-border);
    border-radius: 13px; padding: 0.85rem 1rem;
    text-decoration: none; color: inherit;
    display: grid; gap: 0.2rem;
    transition: border-color 0.18s, box-shadow 0.18s;
}
.sa-quick-link:hover { border-color: #0b3668; box-shadow: 0 4px 14px rgba(11,54,104,0.10); }
.sa-quick-link strong { font-size: 0.87rem; color: var(--dash-shell-text); }
.sa-quick-link span { font-size: 0.74rem; color: var(--dash-shell-muted); }

/* User stat chips */
.sa-user-chips { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.75rem; }
.sa-user-chip {
    background: var(--dash-shell-panel-soft);
    border: 1px solid var(--dash-shell-border);
    border-radius: 999px; padding: 0.3rem 0.85rem;
    font-size: 0.78rem; font-weight: 700; color: var(--dash-shell-text);
    display: flex; align-items: center; gap: 0.4rem;
}
.sa-user-chip__dot { width: 7px; height: 7px; border-radius: 50%; }

/* Property type bar */
.sa-proptype-bar { height: 8px; border-radius: 999px; background: #e8edf4; overflow: hidden; margin-top: 0.3rem; }
.sa-proptype-fill { height: 100%; background: #0b3668; border-radius: 999px; }
</style>
@endpush

@section('content')
@php
    $pipelineTotal = max(1, collect($pipelineHealth)->sum('count'));
    $trendsMonthly = $analyticsTrends['monthly'] ?? [];
    $revenueBars  = $trendsMonthly['revenue']    ?? [];
    $userBars     = $trendsMonthly['users']       ?? [];
    $enquiryBars  = $trendsMonthly['enquiries']   ?? [];
@endphp

<div class="workspace-stack">

    {{-- Platform KPIs ────────────────────────────────────────── --}}
    <section class="workspace-grid workspace-grid--4">

        <article class="workspace-card workspace-kpi" data-trend="{{ number_format($stats['leads']) }} total">
            <div class="sa-kpi-icon sa-kpi-icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <span>Total Leads</span>
            <strong>{{ number_format($stats['leads']) }}</strong>
            <span>Pipeline across all stages</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--warm" data-trend="{{ number_format($stats['realtors']) }} agents">
            <div class="sa-kpi-icon sa-kpi-icon--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <span>Estimated Revenue</span>
            <strong>${{ number_format($stats['estimatedRevenue']) }}</strong>
            <span>From last 6 leads</span>
        </article>

        <article class="workspace-card workspace-kpi" data-trend="{{ number_format($stats['mrrEstimate'] / 100, 2) }} MRR">
            <div class="sa-kpi-icon sa-kpi-icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <span>Pipeline Value</span>
            <strong>${{ number_format($stats['leadPipelineValue']) }}</strong>
            <span>${{ number_format($stats['mrrEstimate']) }} plan MRR</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--violet" data-trend="{{ number_format($stats['usersTotal']) }} total">
            <div class="sa-kpi-icon sa-kpi-icon--violet">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <span>Platform Users</span>
            <strong>{{ number_format($stats['usersTotal']) }}</strong>
            <span>{{ number_format($stats['usersActive']) }} active · {{ number_format($stats['pendingAccounts']) }} pending</span>
        </article>

    </section>

    {{-- Revenue, Pipeline, MRR band ─────────────────────────── --}}
    <section class="sa-revenue-band">
        <div class="sa-revenue-segment">
            <div class="sa-revenue-segment__eyebrow">Lead Pipeline</div>
            <div class="sa-revenue-segment__value" style="color:#0b3668;">${{ number_format($stats['leadPipelineValue']) }}</div>
            <div class="sa-revenue-segment__sub">{{ number_format($stats['leads']) }} leads × avg package value</div>
        </div>
        <div class="sa-revenue-segment">
            <div class="sa-revenue-segment__eyebrow">Plan MRR</div>
            <div class="sa-revenue-segment__value" style="color:#15803d;">${{ number_format($stats['mrrEstimate']) }}</div>
            <div class="sa-revenue-segment__sub">Sum of active package subscriptions</div>
        </div>
        <div class="sa-revenue-segment">
            <div class="sa-revenue-segment__eyebrow">Pending Accounts</div>
            <div class="sa-revenue-segment__value" style="color:{{ $stats['pendingAccounts'] > 0 ? '#ff6b00' : '#0b3668' }};">{{ number_format($stats['pendingAccounts']) }}</div>
            <div class="sa-revenue-segment__sub">Awaiting account activation review</div>
        </div>
    </section>

    {{-- Analytics Trends ─────────────────────────────────────── --}}
    <section class="workspace-grid workspace-grid--3">

        <article class="workspace-card">
            <span class="eyebrow">Monthly Revenue</span>
            <h2>Estimated Revenue Trend</h2>
            <div class="sa-trend-chart" style="margin-top:0.75rem;" title="Monthly revenue estimate">
                @foreach($revenueBars as $bar)
                    <div class="sa-trend-bar" style="height:{{ $bar['percent'] ?? 10 }}%; background:#0b3668;" title="{{ $bar['label'] ?? '' }}: ${{ number_format($bar['amount'] ?? 0) }}"></div>
                @endforeach
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:0.3rem;">
                @foreach($revenueBars as $bar)
                    <span style="font-size:0.64rem; color:var(--dash-shell-muted); flex:1; text-align:center;">{{ $bar['label'] ?? '' }}</span>
                @endforeach
            </div>
        </article>

        <article class="workspace-card">
            <span class="eyebrow">Monthly Users</span>
            <h2>User Growth Trend</h2>
            <div class="sa-trend-chart" style="margin-top:0.75rem;">
                @foreach($userBars as $bar)
                    <div class="sa-trend-bar" style="height:{{ $bar['percent'] ?? 10 }}%; background:#8b5cf6;" title="{{ $bar['label'] ?? '' }}: {{ number_format($bar['count'] ?? 0) }} users"></div>
                @endforeach
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:0.3rem;">
                @foreach($userBars as $bar)
                    <span style="font-size:0.64rem; color:var(--dash-shell-muted); flex:1; text-align:center;">{{ $bar['label'] ?? '' }}</span>
                @endforeach
            </div>
        </article>

        <article class="workspace-card">
            <span class="eyebrow">Monthly Enquiries</span>
            <h2>Inbound Enquiry Trend</h2>
            <div class="sa-trend-chart" style="margin-top:0.75rem;">
                @foreach($enquiryBars as $bar)
                    <div class="sa-trend-bar" style="height:{{ $bar['percent'] ?? 10 }}%; background:#0ea5e9;" title="{{ $bar['label'] ?? '' }}: {{ number_format($bar['count'] ?? 0) }} enquiries"></div>
                @endforeach
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:0.3rem;">
                @foreach($enquiryBars as $bar)
                    <span style="font-size:0.64rem; color:var(--dash-shell-muted); flex:1; text-align:center;">{{ $bar['label'] ?? '' }}</span>
                @endforeach
            </div>
        </article>

    </section>

    {{-- Pipeline Health + User Distribution ─────────────────── --}}
    <section class="workspace-grid workspace-grid--2">

        <article class="workspace-card">
            <span class="eyebrow">Lead Funnel</span>
            <h2>Pipeline Health</h2>
            <div class="workspace-stack" style="margin-top:0.75rem; gap:0.7rem;">
                @foreach($pipelineHealth as $index => $stage)
                    @php
                        $barColors = ['#3b82f6','#ff6b00','#0b3668','#16a34a'];
                        $pct = $pipelineTotal > 0 ? round(($stage['count'] / $pipelineTotal) * 100) : 0;
                    @endphp
                    <div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:0.3rem;">
                            <span style="font-size:0.83rem; font-weight:700; color:var(--dash-shell-text);">{{ $stage['label'] }}</span>
                            <span style="font-size:0.78rem; color:var(--dash-shell-muted); font-weight:700;">{{ number_format($stage['count']) }} &middot; {{ $pct }}%</span>
                        </div>
                        <div class="sa-pipeline-bar">
                            <div class="sa-pipeline-bar__fill" style="width:{{ $pct }}%; background:{{ $barColors[$index] ?? '#0b3668' }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div style="margin-top:0.9rem; display:flex; justify-content:flex-end;">
                <a href="{{ route('admin.leads.index') }}" class="button button--ghost-blue" style="font-size:0.8rem; padding:0.4rem 0.9rem;">Open Registry</a>
            </div>
        </article>

        <article class="workspace-card">
            <span class="eyebrow">Platform Users</span>
            <h2>User Role Distribution</h2>
            @php
                $usersActive    = $stats['usersActive']    ?? 0;
                $usersSuspended = $stats['usersSuspended'] ?? 0;
                $usersTotal     = max(1, $stats['usersTotal'] ?? 1);
                $userChips = [
                    ['label' => 'Active',    'count' => $usersActive,    'color' => '#16a34a'],
                    ['label' => 'Pending',   'count' => $stats['pendingAccounts'] ?? 0, 'color' => '#ff6b00'],
                    ['label' => 'Suspended', 'count' => $usersSuspended, 'color' => '#ef4444'],
                ];
            @endphp
            <div class="sa-user-chips">
                @foreach($userChips as $chip)
                    <div class="sa-user-chip">
                        <div class="sa-user-chip__dot" style="background:{{ $chip['color'] }};"></div>
                        <span>{{ number_format($chip['count']) }} {{ $chip['label'] }}</span>
                    </div>
                @endforeach
            </div>
            <div style="margin-top:1rem;">
                <div style="font-size:0.78rem; font-weight:700; color:var(--dash-shell-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.55rem;">Agent Profiles</div>
                @foreach([['label'=>'Published','count'=>$stats['publishedAgentProfiles'],'color'=>'#16a34a'],['label'=>'Draft','count'=>$stats['draftAgentProfiles'],'color'=>'#ff6b00'],['label'=>'Featured','count'=>$stats['featuredAgentProfiles'],'color'=>'#0b3668']] as $row)
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.45rem;">
                        <span style="font-size:0.82rem; color:var(--dash-shell-text);">{{ $row['label'] }}</span>
                        <strong style="font-size:0.82rem; color:{{ $row['color'] }};">{{ number_format($row['count']) }}</strong>
                    </div>
                @endforeach
            </div>
            <div style="margin-top:0.9rem; display:flex; justify-content:flex-end;">
                <a href="{{ route('admin.users.index') }}" class="button button--ghost-blue" style="font-size:0.8rem; padding:0.4rem 0.9rem;">Manage Users</a>
            </div>
        </article>

    </section>

    {{-- Property Type Distribution ──────────────────────────── --}}
    <article class="workspace-card">
        <div class="workspace-actions" style="justify-content:space-between; margin-bottom:0.8rem;">
            <div>
                <span class="eyebrow">Inventory</span>
                <h2>Property Type Distribution</h2>
            </div>
            <div style="display:flex; gap:0.6rem; align-items:center;">
                <span style="font-size:0.82rem; color:var(--dash-shell-muted);">{{ number_format($stats['properties']) }} total · {{ number_format($stats['pendingListings']) }} pending</span>
                <a href="{{ route('admin.properties.index') }}" class="button button--ghost-blue" style="font-size:0.8rem; padding:0.4rem 0.9rem;">Manage</a>
            </div>
        </div>
        @foreach($propertyTypeDistribution as $pt)
            <div style="margin-bottom:0.7rem;">
                <div style="display:flex; justify-content:space-between; margin-bottom:0.3rem;">
                    <span style="font-size:0.84rem; font-weight:600; color:var(--dash-shell-text);">{{ $pt['label'] }}</span>
                    <span style="font-size:0.78rem; color:var(--dash-shell-muted); font-weight:700;">{{ number_format($pt['count']) }} &middot; {{ $pt['percent'] }}%</span>
                </div>
                <div class="sa-proptype-bar">
                    <div class="sa-proptype-fill" style="width:{{ $pt['percent'] }}%;"></div>
                </div>
            </div>
        @endforeach
    </article>

    {{-- Audit Log + Recent Leads ─────────────────────────────── --}}
    <section class="workspace-grid workspace-grid--2">

        <article class="workspace-card">
            <div class="workspace-actions" style="justify-content:space-between; margin-bottom:0.7rem;">
                <div>
                    <span class="eyebrow">Compliance</span>
                    <h2>Audit Log</h2>
                </div>
                @if($canViewFullAudit)
                    <a href="{{ route('admin.activity.index') }}" class="button button--ghost-blue">Full Log</a>
                @endif
            </div>
            @if($recentAudit->isEmpty())
                <div class="workspace-empty" style="padding:1.25rem;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 0.5rem; display:block;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    No audit entries recorded yet.
                </div>
            @else
                @foreach($recentAudit as $entry)
                    <div class="sa-audit-row">
                        <div class="sa-audit-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div class="sa-audit-actor">{{ $entry->actor?->name ?? 'System' }}</div>
                            <div class="sa-audit-action">{{ Str::limit($entry->description ?? $entry->action ?? 'No description', 70) }}</div>
                        </div>
                        <div class="sa-audit-time">{{ $entry->created_at?->diffForHumans() }}</div>
                    </div>
                @endforeach
            @endif
        </article>

        <article class="workspace-card">
            <div class="workspace-actions" style="justify-content:space-between; margin-bottom:0.7rem;">
                <div>
                    <span class="eyebrow">Latest Activity</span>
                    <h2>Recent Leads</h2>
                </div>
                <a href="{{ route('admin.leads.index') }}" class="button button--ghost-blue">Registry</a>
            </div>
            <div class="workspace-table-wrap">
                <table class="workspace-table">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Intent</th>
                            <th>Status</th>
                            <th>Pkg</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLeads as $lead)
                            <tr>
                                <td data-label="Lead">
                                    <strong>{{ $lead->name }}</strong>
                                    <div class="workspace-property__meta">{{ $lead->zip_code ?: '—' }}</div>
                                </td>
                                <td data-label="Intent">
                                    <span style="font-size:0.78rem; font-weight:700; color:{{ $lead->intent === 'seller' ? '#ff6b00' : '#0b3668' }};">{{ ucfirst($lead->intent ?? 'buyer') }}</span>
                                </td>
                                <td data-label="Status">
                                    <span class="status-pill status-pill--{{ $lead->statusTone() }}">{{ $lead->statusLabel() }}</span>
                                </td>
                                <td data-label="Pkg">
                                    <strong style="font-size:0.8rem;">{{ strtoupper($lead->package_type ?: 'N/A') }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="workspace-empty">No leads yet.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

    </section>

    {{-- Platform Quick Links ─────────────────────────────────── --}}
    <article class="workspace-card">
        <span class="eyebrow">Super Admin Navigation</span>
        <h2>Platform Quick Links</h2>
        <div class="sa-quick-links" style="margin-top:0.75rem;">
            @foreach([
                ['label' => 'Activity Log',        'route' => route('admin.activity.index'),         'desc' => 'Full admin action history'],
                ['label' => 'Lead Registry',       'route' => route('admin.leads.index'),            'desc' => 'All leads, all stages'],
                ['label' => 'User Management',     'route' => route('admin.users.index'),            'desc' => 'Roles, status, suspensions'],
                ['label' => 'Agent Profiles',      'route' => route('admin.agent-profiles.index'),   'desc' => 'Publish, feature, deactivate'],
                ['label' => 'Property Review',     'route' => route('admin.properties.index'),       'desc' => 'Approve user-submitted listings'],
                ['label' => 'Packages & Pricing',  'route' => route('admin.packages.index'),         'desc' => 'Manage subscription tiers'],
                ['label' => 'Testimonials',        'route' => route('admin.testimonials.index'),     'desc' => 'Approve and feature reviews'],
                ['label' => 'Enquiry Threads',     'route' => route('admin.enquiries.index'),        'desc' => 'Inbound property conversations'],
                ['label' => 'Blog Content',        'route' => route('admin.blog.index'),             'desc' => 'Articles and editorial'],
                ['label' => 'Platform Search',     'route' => route('admin.search'),                 'desc' => 'Find any record fast'],
                ['label' => 'Webhook Events',      'route' => route('admin.webhook-events.index'),   'desc' => 'Inbound webhook log'],
                ['label' => 'Seeder / Setup',      'route' => route('admin.dashboard'),              'desc' => 'Platform configuration'],
            ] as $link)
                <a href="{{ $link['route'] }}" class="sa-quick-link">
                    <strong>{{ $link['label'] }}</strong>
                    <span>{{ $link['desc'] }}</span>
                </a>
            @endforeach
        </div>
    </article>

    {{-- Recent Enquiries ─────────────────────────────────────── --}}
    <article class="workspace-card">
        <div class="workspace-actions" style="justify-content:space-between; margin-bottom:0.7rem;">
            <div>
                <span class="eyebrow">Inbound</span>
                <h2>Recent Enquiries</h2>
            </div>
            <a href="{{ route('admin.enquiries.index') }}" class="button button--ghost-blue">View All</a>
        </div>
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Sender</th>
                        <th>Property</th>
                        <th>Receiver</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentEnquiries as $enquiry)
                        <tr>
                            <td data-label="Sender">
                                <strong>{{ $enquiry->sender?->name ?? 'Unknown' }}</strong>
                                <div class="workspace-property__meta">{{ Str::limit($enquiry->subject, 36) }}</div>
                            </td>
                            <td data-label="Property">
                                @if($enquiry->property)
                                    <a href="{{ route('properties.show', $enquiry->property) }}" style="font-size:0.83rem; color:#0b3668; font-weight:600;">{{ Str::limit($enquiry->property->title, 24) }}</a>
                                @else
                                    <span style="color:var(--dash-shell-muted); font-size:0.83rem;">—</span>
                                @endif
                            </td>
                            <td data-label="Receiver">
                                <span style="font-size:0.83rem;">{{ $enquiry->receiver?->name ?? '—' }}</span>
                            </td>
                            <td data-label="Time">
                                <span style="font-size:0.78rem; color:var(--dash-shell-muted);">{{ $enquiry->created_at?->diffForHumans() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="workspace-empty">No enquiries yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>

</div>
@endsection
