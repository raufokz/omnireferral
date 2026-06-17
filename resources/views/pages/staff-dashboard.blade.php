@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Staff Workspace')
@section('dashboard_title', 'Operations Overview')
@section('dashboard_description', 'Your queue-focused view: track ISA qualification, sales packaging, agent delivery, and growth ops in one coordinated workspace.')

@section('dashboard_actions')
    <a href="{{ route('admin.search') }}" class="button button--ghost-blue">Platform Search</a>
    <a href="{{ route('admin.leads.index') }}" class="button button--ghost-blue">Lead Registry</a>
    <a href="{{ route('admin.users.index') }}" class="button">Team Users</a>
@endsection

@push('styles')
<style>
.staff-kpi-icon {
    width: 2.5rem; height: 2.5rem;
    border-radius: 12px; display: grid; place-items: center;
    margin-bottom: 0.55rem;
}
.staff-kpi-icon svg { width: 1.15rem; height: 1.15rem; }
.staff-kpi-icon--blue   { background: rgba(11,54,104,0.10); color: #0b3668; }
.staff-kpi-icon--orange { background: rgba(255,107,0,0.13); color: #c2410c; }
.staff-kpi-icon--green  { background: rgba(22,163,74,0.12); color: #15803d; }
.staff-kpi-icon--violet { background: rgba(109,93,252,0.12); color: #5145cd; }
.staff-kpi-icon--teal   { background: rgba(14,165,233,0.12); color: #0369a1; }

.staff-queue-card {
    background: var(--dash-shell-panel);
    border: 1px solid var(--dash-shell-border);
    border-radius: 18px;
    padding: 1.25rem;
    display: grid;
    gap: 0.6rem;
    transition: box-shadow 0.2s, border-color 0.2s;
    position: relative;
    overflow: hidden;
}
.staff-queue-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 18px 18px 0 0;
}
.staff-queue-card--isa::before   { background: #3b82f6; }
.staff-queue-card--sales::before { background: #ff6b00; }
.staff-queue-card--agent::before { background: #0b3668; }
.staff-queue-card--growth::before{ background: #8b5cf6; }
.staff-queue-card:hover { border-color: rgba(11,54,104,0.25); box-shadow: 0 8px 22px rgba(11,54,104,0.09); }
.staff-queue-count { font-family: 'Sora', 'Inter', sans-serif; font-size: 2.25rem; font-weight: 800; line-height: 1; }
.staff-queue-count--isa   { color: #3b82f6; }
.staff-queue-count--sales { color: #ff6b00; }
.staff-queue-count--agent { color: #0b3668; }
.staff-queue-count--growth{ color: #8b5cf6; }
.staff-queue-label { font-size: 0.86rem; font-weight: 700; color: var(--dash-shell-text); }
.staff-queue-copy  { font-size: 0.8rem; color: var(--dash-shell-muted); line-height: 1.5; margin: 0; }

.staff-pipeline-bar { height: 9px; border-radius: 999px; background: #e8edf4; overflow: hidden; margin-top: 0.35rem; }
.staff-pipeline-bar__fill { height: 100%; border-radius: 999px; transition: width 0.6s cubic-bezier(.16,1,.3,1); }

.staff-pending-user {
    display: flex; align-items: center; gap: 0.65rem;
    padding: 0.7rem 0.6rem;
    border-bottom: 1px solid var(--dash-shell-border);
}
.staff-pending-user:last-child { border-bottom: none; }
.staff-pending-user-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, #0b3668, #1d5fa0);
    display: grid; place-items: center;
    color: #fff; font-size: 0.8rem; font-weight: 700; flex-shrink: 0;
}
.staff-pending-user-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }

.staff-pipeline-overview {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    border: 1px solid var(--dash-shell-border);
    border-radius: 14px;
    overflow: hidden;
}
.staff-pipeline-stage {
    padding: 1rem;
    text-align: center;
    border-right: 1px solid var(--dash-shell-border);
}
.staff-pipeline-stage:last-child { border-right: none; }
.staff-pipeline-stage strong { display: block; font-family: 'Sora', sans-serif; font-size: 1.6rem; font-weight: 800; line-height: 1; }
.staff-pipeline-stage span { display: block; font-size: 0.73rem; font-weight: 700; color: var(--dash-shell-muted); text-transform: uppercase; letter-spacing: 0.06em; margin-top: 0.25rem; }

@media (max-width: 760px) {
    .staff-pipeline-overview { grid-template-columns: repeat(2, 1fr); }
    .staff-pipeline-stage:nth-child(2) { border-right: none; }
    .staff-pipeline-stage:nth-child(1),
    .staff-pipeline-stage:nth-child(2) { border-bottom: 1px solid var(--dash-shell-border); }
}
</style>
@endpush

@section('content')
@php
    $pipelineTotal = max(1, collect($pipelineHealth)->sum('count'));
@endphp

<div class="workspace-stack">

    {{-- Platform KPIs -----------------------------------------------}}
    <section class="workspace-grid workspace-grid--4">

        <article class="workspace-card workspace-kpi" data-trend="All leads">
            <div class="staff-kpi-icon staff-kpi-icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <span>Total Leads</span>
            <strong>{{ number_format($stats['leads']) }}</strong>
            <span>Across all pipeline stages</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--warm" data-trend="{{ number_format($stats['pendingAccounts']) }} pending">
            <div class="staff-kpi-icon staff-kpi-icon--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <span>Platform Users</span>
            <strong>{{ number_format($stats['usersTotal']) }}</strong>
            <span>{{ number_format($stats['usersActive']) }} active · {{ number_format($stats['pendingAccounts']) }} pending</span>
        </article>

        <article class="workspace-card workspace-kpi" data-trend="{{ number_format($stats['enquiries']) }} enquiries">
            <div class="staff-kpi-icon staff-kpi-icon--teal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <span>Enquiries</span>
            <strong>{{ number_format($stats['enquiries']) }}</strong>
            <span>{{ number_format($stats['contacts']) }} direct contacts too</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--violet" data-trend="{{ number_format($stats['properties']) }} total">
            <div class="staff-kpi-icon staff-kpi-icon--violet">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <span>Properties</span>
            <strong>{{ number_format($stats['properties']) }}</strong>
            <span>{{ number_format($stats['pendingListings']) }} pending review</span>
        </article>

    </section>

    {{-- Lead Pipeline Overview ────────────────────────────────── --}}
    <article class="workspace-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.9rem;">
            <div>
                <span class="eyebrow">Pipeline Health</span>
                <h2>Lead Stage Distribution</h2>
            </div>
            <a href="{{ route('admin.leads.index') }}" class="button button--ghost-blue">Open Registry</a>
        </div>
        <div class="staff-pipeline-overview">
            @foreach($pipelineHealth as $stage)
                <div class="staff-pipeline-stage">
                    <strong style="color: {{ ['#3b82f6','#ff6b00','#0b3668','#16a34a'][$loop->index] ?? '#0b3668' }};">
                        {{ number_format($stage['count']) }}
                    </strong>
                    <span>{{ $stage['label'] }}</span>
                </div>
            @endforeach
        </div>
        <div class="workspace-stack" style="margin-top:1rem; gap:0.7rem;">
            @foreach($pipelineHealth as $index => $stage)
                @php
                    $barColors = ['#3b82f6','#ff6b00','#0b3668','#16a34a'];
                    $pct = $pipelineTotal > 0 ? round(($stage['count'] / $pipelineTotal) * 100) : 0;
                @endphp
                <div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.3rem;">
                        <span style="font-size:0.83rem; font-weight:600; color:var(--dash-shell-text);">{{ $stage['label'] }}</span>
                        <span style="font-size:0.78rem; color:var(--dash-shell-muted); font-weight:700;">{{ $pct }}% of pipeline</span>
                    </div>
                    <div class="staff-pipeline-bar">
                        <div class="staff-pipeline-bar__fill" style="width:{{ $pct }}%; background:{{ $barColors[$index] ?? '#0b3668' }};"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </article>

    {{-- Operations Queues ───────────────────────────────────────── --}}
    <section>
        <div style="margin-bottom:0.75rem;">
            <span class="eyebrow">Operations</span>
            <h2 style="margin:0.25rem 0 0; font-family:'Sora',sans-serif; font-size:clamp(1rem,1.6vw,1.25rem);">Team Queue Snapshot</h2>
        </div>
        <div class="workspace-grid workspace-grid--4">
            @php
                $queueStyles = ['isa','sales','agent','growth'];
            @endphp
            @foreach($teamQueues as $index => $queue)
                <article class="staff-queue-card staff-queue-card--{{ $queueStyles[$index] ?? 'isa' }}">
                    <div class="staff-queue-count staff-queue-count--{{ $queueStyles[$index] ?? 'isa' }}">{{ number_format($queue['count']) }}</div>
                    <div class="staff-queue-label">{{ $queue['team'] }}</div>
                    <p class="staff-queue-copy">{{ $queue['copy'] }}</p>
                    @if($index === 0)
                        <a href="{{ route('admin.leads.index', ['status' => 'new']) }}" style="font-size:0.78rem; font-weight:700; color:#3b82f6; text-decoration:none; margin-top:0.25rem;">Open ISA Queue →</a>
                    @elseif($index === 1)
                        <a href="{{ route('admin.leads.index', ['status' => 'qualified']) }}" style="font-size:0.78rem; font-weight:700; color:#ff6b00; text-decoration:none; margin-top:0.25rem;">Open Sales Queue →</a>
                    @elseif($index === 2)
                        <a href="{{ route('admin.agent-profiles.index') }}" style="font-size:0.78rem; font-weight:700; color:#0b3668; text-decoration:none; margin-top:0.25rem;">Agent Profiles →</a>
                    @else
                        <a href="{{ route('admin.enquiries.index') }}" style="font-size:0.78rem; font-weight:700; color:#8b5cf6; text-decoration:none; margin-top:0.25rem;">View Enquiries →</a>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    {{-- Pending Accounts + Recent Leads ─────────────────────────── --}}
    <section class="workspace-grid workspace-grid--2">

        <article class="workspace-card">
            <div class="workspace-actions" style="justify-content:space-between; margin-bottom:0.7rem;">
                <div>
                    <span class="eyebrow">User Approvals</span>
                    <h2>Pending Accounts</h2>
                </div>
                <a href="{{ route('admin.users.index') }}" class="button button--ghost-blue">View All</a>
            </div>
            @if($pendingAccounts->isEmpty())
                <div class="workspace-empty" style="padding:1.4rem;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin: 0 auto 0.5rem; display:block;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    No accounts pending review
                </div>
            @else
                @foreach($pendingAccounts as $user)
                    <div class="staff-pending-user">
                        <div class="staff-pending-user-avatar">
                            @if($user->avatar)
                                <img src="{{ $user->profilePhotoPublicUrl() }}" alt="{{ $user->name }}" loading="lazy">
                            @else
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            @endif
                        </div>
                        <div style="flex:1; min-width:0;">
                            <strong style="font-size:0.88rem; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $user->name }}</strong>
                            <span style="font-size:0.76rem; color:var(--dash-shell-muted);">{{ ucfirst($user->role) }} · {{ $user->created_at?->diffForHumans() }}</span>
                        </div>
                        <a href="{{ route('admin.users.show', $user) }}" class="button button--ghost-blue" style="font-size:0.78rem; padding:0.3rem 0.7rem; white-space:nowrap;">Review</a>
                    </div>
                @endforeach
            @endif
        </article>

        <article class="workspace-card">
            <div class="workspace-actions" style="justify-content:space-between; margin-bottom:0.7rem;">
                <div>
                    <span class="eyebrow">Recent Activity</span>
                    <h2>Latest Leads</h2>
                </div>
                <a href="{{ route('admin.leads.index') }}" class="button button--ghost-blue">Registry</a>
            </div>
            <div class="workspace-table-wrap">
                <table class="workspace-table">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Status</th>
                            <th>Package</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLeads as $lead)
                            <tr>
                                <td data-label="Lead">
                                    <strong>{{ $lead->name }}</strong>
                                    <div class="workspace-property__meta">{{ $lead->zip_code ?: 'No ZIP' }} · {{ ucfirst($lead->intent ?? 'buyer') }}</div>
                                </td>
                                <td data-label="Status">
                                    <span class="status-pill status-pill--{{ $lead->statusTone() }}">{{ $lead->statusLabel() }}</span>
                                </td>
                                <td data-label="Package">
                                    <strong style="font-size:0.83rem;">{{ strtoupper($lead->package_type ?: 'N/A') }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><div class="workspace-empty">No leads yet.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

    </section>

    {{-- Recent Enquiries ─────────────────────────────────────────── --}}
    <article class="workspace-card">
        <div class="workspace-actions" style="justify-content:space-between; margin-bottom:0.7rem;">
            <div>
                <span class="eyebrow">Inbox</span>
                <h2>Recent Enquiries</h2>
            </div>
            <a href="{{ route('admin.enquiries.index') }}" class="button button--ghost-blue">View All</a>
        </div>
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Enquiry</th>
                        <th>Property</th>
                        <th>Receiver</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentEnquiries as $enquiry)
                        <tr>
                            <td data-label="Enquiry">
                                <strong>{{ $enquiry->sender?->name ?? 'Unknown' }}</strong>
                                <div class="workspace-property__meta">{{ Str::limit($enquiry->subject, 40) }}</div>
                            </td>
                            <td data-label="Property">
                                @if($enquiry->property)
                                    <a href="{{ route('properties.show', $enquiry->property) }}" style="font-size:0.83rem; color:#0b3668; font-weight:600;">{{ Str::limit($enquiry->property->title, 28) }}</a>
                                @else
                                    <span style="color:var(--dash-shell-muted); font-size:0.83rem;">—</span>
                                @endif
                            </td>
                            <td data-label="Receiver">
                                <span style="font-size:0.83rem;">{{ $enquiry->receiver?->name ?? '—' }}</span>
                            </td>
                            <td data-label="Date">
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

    {{-- Quick Links ─────────────────────────────────────────────── --}}
    <article class="workspace-card">
        <span class="eyebrow">Operations Links</span>
        <h2>Quick Navigation</h2>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:0.65rem; margin-top:0.75rem;">
            @foreach([
                ['label' => 'Lead Registry',       'route' => route('admin.leads.index'),          'desc' => 'Full lead list with status and assignment'],
                ['label' => 'User Directory',       'route' => route('admin.users.index'),          'desc' => 'Browse all platform users'],
                ['label' => 'Agent Profiles',       'route' => route('admin.agent-profiles.index'), 'desc' => 'Review and publish agent listings'],
                ['label' => 'Property Review',      'route' => route('admin.properties.index'),     'desc' => 'Approve or reject user submissions'],
                ['label' => 'Enquiry Threads',      'route' => route('admin.enquiries.index'),      'desc' => 'Inbound property conversations'],
                ['label' => 'Testimonials',         'route' => route('admin.testimonials.index'),   'desc' => 'Moderate pending reviews'],
                ['label' => 'Blog Content',         'route' => route('admin.blog.index'),           'desc' => 'Manage platform articles'],
                ['label' => 'Platform Search',      'route' => route('admin.search'),               'desc' => 'Find records platform-wide'],
            ] as $link)
                <a href="{{ $link['route'] }}" style="
                    background:var(--dash-shell-panel-soft);
                    border:1px solid var(--dash-shell-border);
                    border-radius:13px; padding:0.8rem;
                    text-decoration:none; color:inherit;
                    display:grid; gap:0.2rem;
                    transition: border-color 0.18s, box-shadow 0.18s;
                " onmouseover="this.style.borderColor='#0b3668'" onmouseout="this.style.borderColor=''">
                    <strong style="font-size:0.87rem; color:var(--dash-shell-text);">{{ $link['label'] }}</strong>
                    <span style="font-size:0.74rem; color:var(--dash-shell-muted);">{{ $link['desc'] }}</span>
                </a>
            @endforeach
        </div>
    </article>

</div>
@endsection
