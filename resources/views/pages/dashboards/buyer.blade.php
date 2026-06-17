@extends('layouts.dashboard')

@php
    $hour = (int) now()->format('H');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $buyer = auth()->user();
    $firstName = explode(' ', trim($buyer->name ?? 'there'))[0];
    $journeyMax = max(1, collect($buyerJourney)->max('count'));
@endphp

@section('dashboard_eyebrow', 'Buyer Workspace')
@section('dashboard_title', $greeting . ', ' . $firstName)
@section('dashboard_description', now()->format('l, F j, Y') . ' · Track your shortlist, requests, and market activity.')

@section('dashboard_actions')
    <a href="{{ route('listings') }}" class="button button--ghost-blue">Browse Marketplace</a>
    <a href="{{ route('dashboard.buyer.saved') }}" class="button button--ghost-blue">Saved Homes</a>
    <a href="{{ route('dashboard.buyer.requests') }}" class="button">My Requests</a>
@endsection

@push('styles')
<style>
.buyer-kpi-icon {
    width: 2.5rem; height: 2.5rem;
    border-radius: 12px; display: grid; place-items: center;
    margin-bottom: 0.55rem;
}
.buyer-kpi-icon svg { width: 1.15rem; height: 1.15rem; }
.buyer-kpi-icon--blue   { background: rgba(11,54,104,0.10); color: #0b3668; }
.buyer-kpi-icon--orange { background: rgba(255,107,0,0.13); color: #c2410c; }
.buyer-kpi-icon--teal   { background: rgba(14,165,233,0.12); color: #0369a1; }
.buyer-kpi-icon--violet { background: rgba(109,93,252,0.12); color: #5145cd; }
.buyer-kpi-icon--green  { background: rgba(22,163,74,0.12); color: #15803d; }

.buyer-journey-bar { height: 9px; border-radius: 999px; background: #e8edf4; overflow: hidden; margin-top: 0.35rem; }
.buyer-journey-bar__fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #0b3668, #ff6b00); transition: width 0.6s cubic-bezier(.16,1,.3,1); }

.buyer-trend-chart { display: flex; align-items: flex-end; gap: 4px; height: 64px; }
.buyer-trend-bar { flex: 1; border-radius: 4px 4px 0 0; min-height: 4px; }

.buyer-action-card {
    background: var(--dash-shell-panel-soft, #f8fafd);
    border: 1px solid var(--dash-shell-border);
    border-radius: 14px; padding: 1rem;
    display: grid; gap: 0.3rem;
    text-decoration: none; color: inherit;
    transition: border-color 0.18s, box-shadow 0.18s, transform 0.18s;
}
.buyer-action-card:hover {
    border-color: #0b3668;
    box-shadow: 0 6px 18px rgba(11,54,104,0.10);
    transform: translateY(-2px);
}
.buyer-action-card strong { font-size: 0.88rem; color: var(--dash-shell-text); }
.buyer-action-card span  { font-size: 0.76rem; color: var(--dash-shell-muted); }
.buyer-action-icon { width: 1.6rem; height: 1.6rem; color: #0b3668; margin-bottom: 0.35rem; }

.buyer-prop-card {
    background: var(--dash-shell-panel);
    border: 1px solid var(--dash-shell-border);
    border-radius: 14px; overflow: hidden;
    transition: box-shadow 0.2s, border-color 0.2s;
}
.buyer-prop-card:hover { border-color: rgba(11,54,104,0.3); box-shadow: 0 6px 18px rgba(11,54,104,0.09); }
.buyer-prop-card img { width: 100%; height: 160px; object-fit: cover; display: block; }
.buyer-prop-card__body { padding: 0.85rem; }
.buyer-prop-card__price { font-family: 'Sora', sans-serif; font-size: 1.15rem; font-weight: 800; color: #0b3668; }
.buyer-prop-card__title { font-size: 0.88rem; font-weight: 700; margin: 0.2rem 0; }
.buyer-prop-card__meta  { font-size: 0.78rem; color: var(--dash-shell-muted); }
</style>
@endpush

@section('content')
<div class="workspace-stack">

    {{-- KPI Row --}}
    <section class="workspace-grid workspace-grid--4">

        <article class="workspace-card workspace-kpi" data-trend="Shortlist">
            <div class="buyer-kpi-icon buyer-kpi-icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </div>
            <span>Saved Homes</span>
            <strong>{{ number_format($buyerStats['saved_listings']) }}</strong>
            <span>Properties in your shortlist</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--warm" data-trend="Active">
            <div class="buyer-kpi-icon buyer-kpi-icon--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <span>Open Requests</span>
            <strong>{{ number_format($buyerStats['new_alerts']) }}</strong>
            <span>In submitted or contacted stage</span>
        </article>

        <article class="workspace-card workspace-kpi" data-trend="Completed">
            <div class="buyer-kpi-icon buyer-kpi-icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <span>Closed Deals</span>
            <strong>{{ number_format(collect($buyerJourney)->firstWhere('label', 'Closed')['count'] ?? 0) }}</strong>
            <span>Successfully matched & closed</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--violet" data-trend="Total">
            <div class="buyer-kpi-icon buyer-kpi-icon--violet">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <span>Total Requests</span>
            <strong>{{ number_format($stats['leads'] ?? $buyerRequests->count()) }}</strong>
            <span>Across all journey stages</span>
        </article>

    </section>

    {{-- Journey + Trends --}}
    <section class="workspace-grid workspace-grid--2">

        <article class="workspace-card">
            <span class="eyebrow">Buyer Journey</span>
            <h2>Request Stage Progress</h2>
            <div class="workspace-stack" style="margin-top:0.75rem; gap:0.75rem;">
                @foreach($buyerJourney as $stage)
                    @php $pct = $journeyMax > 0 ? round(($stage['count'] / $journeyMax) * 100) : 0; @endphp
                    <div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:0.3rem;">
                            <strong style="font-size:0.86rem;">{{ $stage['label'] }}</strong>
                            <span style="font-size:0.78rem; color:var(--dash-shell-muted); font-weight:700;">{{ number_format($stage['count']) }}</span>
                        </div>
                        <div class="buyer-journey-bar">
                            <div class="buyer-journey-bar__fill" style="width:{{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach
                @if(collect($buyerJourney)->sum('count') === 0)
                    <div class="workspace-empty" style="padding:1rem;">No requests submitted yet. Get started by browsing the marketplace.</div>
                @endif
            </div>
            <div style="margin-top:0.9rem;">
                <a href="{{ route('dashboard.buyer.requests') }}" class="button button--ghost-blue" style="font-size:0.8rem;">View All Requests</a>
            </div>
        </article>

        <article class="workspace-card">
            <span class="eyebrow">Activity Trend</span>
            <h2>Monthly Request Activity</h2>
            <div class="buyer-trend-chart" style="margin-top:0.75rem;">
                @foreach($revenueTrend as $point)
                    <div class="buyer-trend-bar" style="height:{{ max(4, $point['percent'] ?? 10) }}%; background:#0b3668; opacity:{{ 0.5 + ($point['percent'] ?? 0) / 200 }};" title="{{ $point['label'] ?? '' }}"></div>
                @endforeach
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:0.4rem;">
                @foreach($revenueTrend as $point)
                    <span style="font-size:0.64rem; color:var(--dash-shell-muted); flex:1; text-align:center;">{{ $point['label'] ?? '' }}</span>
                @endforeach
            </div>
            <div style="margin-top:0.75rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
                <div style="background:rgba(11,54,104,0.08); border-radius:999px; padding:0.3rem 0.8rem; font-size:0.78rem; font-weight:700; color:#0b3668;">
                    {{ number_format($buyerStats['favorites']) }} saved
                </div>
                <div style="background:rgba(255,107,0,0.1); border-radius:999px; padding:0.3rem 0.8rem; font-size:0.78rem; font-weight:700; color:#c2410c;">
                    {{ number_format($buyerStats['new_alerts']) }} open
                </div>
            </div>
        </article>

    </section>

    {{-- Recent Requests Table --}}
    <article class="workspace-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
            <div>
                <span class="eyebrow">Recent Activity</span>
                <h2>Latest Buyer Requests</h2>
            </div>
            <a href="{{ route('dashboard.buyer.requests') }}" class="button button--ghost-blue">View All</a>
        </div>
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Package</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($buyerRequests as $lead)
                        <tr>
                            <td data-label="Request">
                                <strong>{{ $lead->zip_code ?: 'Any ZIP' }}</strong>
                                <div class="workspace-property__meta">{{ ucfirst($lead->intent ?? 'Buyer') }} · {{ $lead->property_type ?: 'Any type' }}</div>
                            </td>
                            <td data-label="Package">
                                <strong style="font-size:0.83rem;">{{ strtoupper($lead->package_type ?: 'N/A') }}</strong>
                            </td>
                            <td data-label="Status">
                                <span class="status-pill status-pill--{{ $lead->statusTone() }}">{{ $lead->statusLabel() }}</span>
                            </td>
                            <td data-label="Submitted">
                                <span style="font-size:0.78rem; color:var(--dash-shell-muted);">{{ $lead->created_at?->diffForHumans() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="workspace-empty" style="padding:1.5rem;">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 0.5rem; display:block;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    No buyer requests yet.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>

    {{-- Saved Homes Preview --}}
    <article class="workspace-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
            <div>
                <span class="eyebrow">Shortlist</span>
                <h2>Recently Saved Homes</h2>
            </div>
            <a href="{{ route('dashboard.buyer.saved') }}" class="button button--ghost-blue">View All Saved</a>
        </div>
        @if($properties->isEmpty())
            <div class="workspace-empty" style="padding:2rem; text-align:center;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 0.6rem; display:block;"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                No saved homes yet.<br>
                <small style="font-size:0.8rem; color:var(--dash-shell-muted);">Browse the marketplace and heart listings to save them here.</small>
                <div style="margin-top:1rem;"><a href="{{ route('listings') }}" class="button">Browse Marketplace</a></div>
            </div>
        @else
            <div class="workspace-grid workspace-grid--3">
                @foreach($properties->take(3) as $property)
                    <div class="buyer-prop-card">
                        <img src="{{ $property->image_url }}" alt="{{ $property->title }}" loading="lazy"
                             onerror="this.onerror=null;this.src='{{ asset('images/omnireferral-logo.png') }}'">
                        <div class="buyer-prop-card__body">
                            <div class="buyer-prop-card__price">${{ number_format($property->price) }}</div>
                            <div class="buyer-prop-card__title">{{ Str::limit($property->title, 40) }}</div>
                            <div class="buyer-prop-card__meta">{{ $property->location }}</div>
                            <div class="workspace-pill-row" style="margin-top:0.5rem;">
                                <span class="workspace-pill">{{ ucfirst($property->property_type ?: 'Home') }}</span>
                                <span class="workspace-pill workspace-pill--accent">{{ number_format($property->favorites_count ?? 0) }} saves</span>
                            </div>
                            <div style="margin-top:0.7rem;">
                                <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue" style="font-size:0.8rem; width:100%; text-align:center;">View Listing</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </article>

    {{-- Recent Enquiries --}}
    @if($recentEnquiries->isNotEmpty())
    <article class="workspace-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
            <div>
                <span class="eyebrow">Conversations</span>
                <h2>Recent Enquiries</h2>
            </div>
            <a href="{{ route('dashboard.enquiries.index') }}" class="button button--ghost-blue">All Threads</a>
        </div>
        <ul class="workspace-list">
            @foreach($recentEnquiries->take(5) as $enq)
                <li>
                    <strong>{{ $enq->property?->title ? Str::limit($enq->property->title, 50) : ($enq->subject ?: 'Enquiry') }}</strong>
                    <small>{{ $enq->receiver?->name ?? '—' }} · {{ ucfirst($enq->status ?? 'pending') }} · {{ $enq->created_at?->diffForHumans() }}</small>
                    <div class="workspace-actions" style="margin-top:0.4rem;">
                        <a href="{{ route('dashboard.enquiries.index') }}" class="button button--ghost-blue" style="font-size:0.76rem; padding:0.3rem 0.7rem;">Open</a>
                    </div>
                </li>
            @endforeach
        </ul>
    </article>
    @endif

    {{-- Quick Actions --}}
    <article class="workspace-card">
        <span class="eyebrow">Quick Actions</span>
        <h2>Move Your Search Forward</h2>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:0.65rem; margin-top:0.75rem;">
            @foreach([
                ['href' => route('listings'),                  'icon' => 'search',   'label' => 'Browse Listings',    'desc' => 'Explore all approved marketplace inventory'],
                ['href' => route('dashboard.buyer.saved'),     'icon' => 'heart',    'label' => 'Saved Homes',        'desc' => 'Review and manage your shortlist'],
                ['href' => route('dashboard.buyer.requests'),  'icon' => 'list',     'label' => 'My Requests',        'desc' => 'Track request stages and outcomes'],
                ['href' => route('dashboard.enquiries.index'), 'icon' => 'chat',     'label' => 'Enquiry Threads',    'desc' => 'Messages with agents and sellers'],
                ['href' => route('agents.index'),              'icon' => 'agent',    'label' => 'Find an Agent',      'desc' => 'Connect with verified buyer agents'],
                ['href' => route('contact'),                   'icon' => 'support',  'label' => 'Get Support',        'desc' => 'Talk to the OmniReferral team'],
            ] as $action)
                <a href="{{ $action['href'] }}" class="buyer-action-card">
                    @if($action['icon'] === 'search')
                        <svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    @elseif($action['icon'] === 'heart')
                        <svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    @elseif($action['icon'] === 'list')
                        <svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    @elseif($action['icon'] === 'chat')
                        <svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    @elseif($action['icon'] === 'agent')
                        <svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    @else
                        <svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.61 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    @endif
                    <strong>{{ $action['label'] }}</strong>
                    <span>{{ $action['desc'] }}</span>
                </a>
            @endforeach
        </div>
    </article>

</div>
@endsection
