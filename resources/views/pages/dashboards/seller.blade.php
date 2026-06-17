@extends('layouts.dashboard')

@php
    $hour = (int) now()->format('H');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $seller = auth()->user();
    $firstName = explode(' ', trim($seller->name ?? 'there'))[0];
    $journeyMax = max(1, collect($sellerJourney)->max('count'));
@endphp

@section('dashboard_eyebrow', 'Seller Workspace')
@section('dashboard_title', $greeting . ', ' . $firstName)
@section('dashboard_description', now()->format('l, F j, Y') . ' · Monitor your listings, enquiries, and buyer interest in real time.')

@section('dashboard_actions')
    <a href="{{ route('listings') }}" class="button button--ghost-blue">View Marketplace</a>
    <a href="{{ route('dashboard.seller.listings') }}" class="button button--ghost-blue">My Listings</a>
    <a href="{{ route('dashboard.enquiries.index') }}" class="button">Enquiries</a>
@endsection

@push('styles')
<style>
.seller-kpi-icon {
    width: 2.5rem; height: 2.5rem;
    border-radius: 12px; display: grid; place-items: center;
    margin-bottom: 0.55rem;
}
.seller-kpi-icon svg { width: 1.15rem; height: 1.15rem; }
.seller-kpi-icon--blue   { background: rgba(11,54,104,0.10); color: #0b3668; }
.seller-kpi-icon--orange { background: rgba(255,107,0,0.13); color: #c2410c; }
.seller-kpi-icon--teal   { background: rgba(14,165,233,0.12); color: #0369a1; }
.seller-kpi-icon--violet { background: rgba(109,93,252,0.12); color: #5145cd; }
.seller-kpi-icon--green  { background: rgba(22,163,74,0.12); color: #15803d; }

.seller-journey-bar { height: 9px; border-radius: 999px; background: #e8edf4; overflow: hidden; margin-top: 0.35rem; }
.seller-journey-bar__fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #ff6b00, #0b3668); transition: width 0.6s cubic-bezier(.16,1,.3,1); }

.seller-trend-chart { display: flex; align-items: flex-end; gap: 4px; height: 64px; }
.seller-trend-bar { flex: 1; border-radius: 4px 4px 0 0; min-height: 4px; }

.seller-listing-card {
    background: var(--dash-shell-panel);
    border: 1px solid var(--dash-shell-border);
    border-radius: 14px; overflow: hidden;
    transition: box-shadow 0.2s, border-color 0.2s;
}
.seller-listing-card:hover { border-color: rgba(255,107,0,0.3); box-shadow: 0 6px 18px rgba(255,107,0,0.09); }
.seller-listing-card img { width: 100%; height: 155px; object-fit: cover; display: block; }
.seller-listing-card__body { padding: 0.85rem; }
.seller-listing-card__price { font-family: 'Sora', sans-serif; font-size: 1.15rem; font-weight: 800; color: #0b3668; }
.seller-listing-card__title { font-size: 0.88rem; font-weight: 700; margin: 0.2rem 0; }
.seller-listing-card__meta  { font-size: 0.78rem; color: var(--dash-shell-muted); }

.seller-action-card {
    background: var(--dash-shell-panel-soft, #f8fafd);
    border: 1px solid var(--dash-shell-border);
    border-radius: 14px; padding: 1rem;
    display: grid; gap: 0.3rem;
    text-decoration: none; color: inherit;
    transition: border-color 0.18s, box-shadow 0.18s, transform 0.18s;
}
.seller-action-card:hover {
    border-color: #ff6b00;
    box-shadow: 0 6px 18px rgba(255,107,0,0.10);
    transform: translateY(-2px);
}
.seller-action-card strong { font-size: 0.88rem; color: var(--dash-shell-text); }
.seller-action-card span  { font-size: 0.76rem; color: var(--dash-shell-muted); }
.seller-action-icon { width: 1.6rem; height: 1.6rem; color: #ff6b00; margin-bottom: 0.35rem; }

.seller-status-badge {
    display: inline-flex; align-items: center; gap: 0.25rem;
    padding: 0.25rem 0.65rem; border-radius: 999px;
    font-size: 0.72rem; font-weight: 700;
}
.seller-status-badge--active { background: rgba(22,163,74,0.13); color: #15803d; }
.seller-status-badge--pending { background: rgba(255,107,0,0.12); color: #c2410c; }
.seller-status-badge--sold { background: rgba(11,54,104,0.11); color: #0b3668; }
.seller-status-badge--inactive { background: #f1f5f9; color: #64748b; }
</style>
@endpush

@section('content')
<div class="workspace-stack">

    {{-- KPI Row --}}
    <section class="workspace-grid workspace-grid--4">

        <article class="workspace-card workspace-kpi" data-trend="Active Listings">
            <div class="seller-kpi-icon seller-kpi-icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <span>Active Listings</span>
            <strong>{{ number_format($sellerStats['active_listings'] ?? $properties->where('status', 'Active')->count()) }}</strong>
            <span>Live on marketplace right now</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--warm" data-trend="Open Enquiries">
            <div class="seller-kpi-icon seller-kpi-icon--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <span>Open Enquiries</span>
            <strong>{{ number_format($sellerStats['open_inquiries'] ?? $recentEnquiries->count()) }}</strong>
            <span>Buyer messages needing a response</span>
        </article>

        <article class="workspace-card workspace-kpi" data-trend="Buyer Matches">
            <div class="seller-kpi-icon seller-kpi-icon--teal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <span>Buyer Matches</span>
            <strong>{{ number_format($sellerStats['buyer_matches'] ?? collect($sellerJourney)->firstWhere('label', 'Agent Match')['count'] ?? 0) }}</strong>
            <span>Qualified leads matched to your property</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--violet" data-trend="Total Listings">
            <div class="seller-kpi-icon seller-kpi-icon--violet">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <span>Total Listings</span>
            <strong>{{ number_format($properties->count()) }}</strong>
            <span>All properties under your account</span>
        </article>

    </section>

    {{-- Seller Journey + Enquiry Trend --}}
    <section class="workspace-grid workspace-grid--2">

        <article class="workspace-card">
            <span class="eyebrow">Transaction Pipeline</span>
            <h2>Seller Journey Stages</h2>
            <div class="workspace-stack" style="margin-top:0.75rem; gap:0.75rem;">
                @foreach($sellerJourney as $stage)
                    @php $pct = $journeyMax > 0 ? round(($stage['count'] / $journeyMax) * 100) : 0; @endphp
                    <div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:0.3rem;">
                            <strong style="font-size:0.86rem;">{{ $stage['label'] }}</strong>
                            <span style="font-size:0.78rem; color:var(--dash-shell-muted); font-weight:700;">{{ number_format($stage['count']) }}</span>
                        </div>
                        <div class="seller-journey-bar">
                            <div class="seller-journey-bar__fill" style="width:{{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach
                @if(collect($sellerJourney)->sum('count') === 0)
                    <div class="workspace-empty" style="padding:1rem;">No activity in pipeline yet. Submit a listing to get started.</div>
                @endif
            </div>
            <div style="margin-top:0.9rem;">
                <a href="{{ route('dashboard.seller.listings') }}" class="button button--ghost-blue" style="font-size:0.8rem;">View All Listings</a>
            </div>
        </article>

        <article class="workspace-card">
            <span class="eyebrow">Market Activity</span>
            <h2>Enquiry Volume Trend</h2>
            <div class="seller-trend-chart" style="margin-top:0.75rem;">
                @foreach($enquiryTrend as $point)
                    <div class="seller-trend-bar" style="height:{{ max(4, $point['percent'] ?? 10) }}%; background:#ff6b00; opacity:{{ 0.5 + ($point['percent'] ?? 0) / 200 }};" title="{{ $point['label'] ?? '' }}: {{ $point['count'] ?? 0 }}"></div>
                @endforeach
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:0.4rem;">
                @foreach($enquiryTrend as $point)
                    <span style="font-size:0.64rem; color:var(--dash-shell-muted); flex:1; text-align:center;">{{ $point['label'] ?? '' }}</span>
                @endforeach
            </div>
            <div style="margin-top:0.75rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
                <div style="background:rgba(11,54,104,0.08); border-radius:999px; padding:0.3rem 0.8rem; font-size:0.78rem; font-weight:700; color:#0b3668;">
                    {{ number_format($properties->count()) }} total listings
                </div>
                <div style="background:rgba(255,107,0,0.1); border-radius:999px; padding:0.3rem 0.8rem; font-size:0.78rem; font-weight:700; color:#c2410c;">
                    {{ number_format($sellerStats['open_inquiries'] ?? $recentEnquiries->count()) }} open enquiries
                </div>
            </div>
        </article>

    </section>

    {{-- Pipeline Health --}}
    @if($pipelineHealth->isNotEmpty())
    <article class="workspace-card">
        <span class="eyebrow">Lead Pipeline</span>
        <h2>Overall Platform Lead Health</h2>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:0.65rem; margin-top:0.75rem;">
            @foreach($pipelineHealth as $stage)
                <div style="background:var(--dash-shell-panel-soft, #f8fafd); border:1px solid var(--dash-shell-border); border-radius:12px; padding:0.9rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                        <strong style="font-size:0.83rem;">{{ $stage['label'] }}</strong>
                        <span style="font-size:0.78rem; font-weight:800; color:#0b3668;">{{ number_format($stage['count']) }}</span>
                    </div>
                    <div class="seller-journey-bar">
                        <div class="seller-journey-bar__fill" style="width:{{ $stage['percent'] ?? 0 }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </article>
    @endif

    {{-- My Listings Table --}}
    <article class="workspace-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
            <div>
                <span class="eyebrow">Portfolio</span>
                <h2>My Listings</h2>
            </div>
            <a href="{{ route('dashboard.seller.listings') }}" class="button button--ghost-blue">All Listings</a>
        </div>
        @if($properties->isEmpty())
            <div class="workspace-empty" style="padding:2rem; text-align:center;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 0.6rem; display:block;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                No listings yet.<br>
                <small style="font-size:0.8rem; color:var(--dash-shell-muted);">Submit a property to begin attracting buyers.</small>
                <div style="margin-top:1rem;"><a href="{{ route('dashboard.seller.listings') }}" class="button">Submit a Listing</a></div>
            </div>
        @else
            <div class="workspace-table-wrap">
                <table class="workspace-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Favourites</th>
                            <th>Listed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($properties->take(8) as $prop)
                            <tr>
                                <td data-label="Property">
                                    <a href="{{ route('properties.show', $prop) }}" style="font-weight:700; color:#0b3668; text-decoration:none; font-size:0.88rem;">{{ Str::limit($prop->title, 48) }}</a>
                                    <div class="workspace-property__meta">{{ $prop->location }}</div>
                                </td>
                                <td data-label="Type"><span style="font-size:0.81rem;">{{ ucfirst($prop->property_type ?: 'N/A') }}</span></td>
                                <td data-label="Status">
                                    @php
                                        $s = strtolower($prop->status ?? 'inactive');
                                        $tone = match($s) { 'active' => 'active', 'pending' => 'pending', 'sold' => 'sold', default => 'inactive' };
                                    @endphp
                                    <span class="seller-status-badge seller-status-badge--{{ $tone }}">{{ ucfirst($prop->status ?? 'N/A') }}</span>
                                </td>
                                <td data-label="Price"><strong style="font-size:0.88rem;">${{ number_format($prop->price) }}</strong></td>
                                <td data-label="Favourites">
                                    <span style="display:flex; align-items:center; gap:4px; font-size:0.82rem;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="#ff6b00" stroke="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                        {{ number_format($prop->favorites_count ?? 0) }}
                                    </span>
                                </td>
                                <td data-label="Listed"><span style="font-size:0.78rem; color:var(--dash-shell-muted);">{{ $prop->created_at?->diffForHumans() }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </article>

    {{-- Property Type Distribution + Recent Enquiries --}}
    <section class="workspace-grid workspace-grid--2">

        @if($propertyTypeDistribution->isNotEmpty())
        <article class="workspace-card">
            <span class="eyebrow">Analytics</span>
            <h2>Property Type Breakdown</h2>
            <div class="workspace-stack" style="margin-top:0.75rem; gap:0.65rem;">
                @foreach($propertyTypeDistribution as $row)
                    <div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:0.3rem;">
                            <span style="font-size:0.84rem; font-weight:600;">{{ $row['label'] }}</span>
                            <span style="font-size:0.78rem; font-weight:700; color:var(--dash-shell-muted);">{{ number_format($row['count']) }} ({{ $row['percent'] ?? 0 }}%)</span>
                        </div>
                        <div class="seller-journey-bar">
                            <div class="seller-journey-bar__fill" style="width:{{ $row['percent'] ?? 0 }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>
        @endif

        <article class="workspace-card">
            <span class="eyebrow">Conversations</span>
            <h2>Recent Enquiries</h2>
            @if($recentEnquiries->isEmpty())
                <div class="workspace-empty" style="padding:1.25rem;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="display:block; margin:0 auto 0.5rem;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    No enquiries received yet.
                </div>
            @else
                <ul class="workspace-list" style="margin-top:0.65rem;">
                    @foreach($recentEnquiries->take(5) as $enq)
                        <li>
                            <strong>{{ $enq->property?->title ? Str::limit($enq->property->title, 45) : ($enq->subject ?: 'Property enquiry') }}</strong>
                            <small>From {{ $enq->sender?->name ?? 'Anonymous' }} · {{ $enq->created_at?->diffForHumans() }}</small>
                            <div style="margin-top:0.35rem;">
                                <a href="{{ route('dashboard.enquiries.index') }}" class="button button--ghost-blue" style="font-size:0.76rem; padding:0.3rem 0.7rem;">Open</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div style="margin-top:0.75rem;">
                    <a href="{{ route('dashboard.enquiries.index') }}" class="button button--ghost-blue" style="font-size:0.8rem;">View All Threads</a>
                </div>
            @endif
        </article>

    </section>

    {{-- Recent Leads --}}
    @if($recentLeads->isNotEmpty())
    <article class="workspace-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
            <div>
                <span class="eyebrow">Buyer Interest</span>
                <h2>Recent Lead Activity</h2>
            </div>
            <a href="{{ route('dashboard.seller.requests') }}" class="button button--ghost-blue">All Requests</a>
        </div>
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Package</th>
                        <th>Status</th>
                        <th>Value</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLeads->take(6) as $lead)
                        <tr>
                            <td data-label="Lead">
                                <strong>{{ $lead->zip_code ?: 'Any ZIP' }}</strong>
                                <div class="workspace-property__meta">{{ ucfirst($lead->intent ?? 'Buyer') }} · {{ $lead->property_type ?: 'Any type' }}</div>
                            </td>
                            <td data-label="Package"><strong style="font-size:0.83rem;">{{ strtoupper($lead->package_type ?: 'N/A') }}</strong></td>
                            <td data-label="Status">
                                <span class="status-pill status-pill--{{ $lead->statusTone() }}">{{ $lead->statusLabel() }}</span>
                            </td>
                            <td data-label="Value">
                                @php
                                    $lv = match(strtolower($lead->package_type ?? '')) {
                                        'starter', 'quick' => 199,
                                        'growth', 'power' => 349,
                                        'elite', 'prime' => 549,
                                        default => 0
                                    };
                                @endphp
                                <span style="font-weight:700; font-size:0.83rem;">${{ number_format($lv) }}</span>
                            </td>
                            <td data-label="Date"><span style="font-size:0.78rem; color:var(--dash-shell-muted);">{{ $lead->created_at?->diffForHumans() }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
    @endif

    {{-- Quick Actions --}}
    <article class="workspace-card">
        <span class="eyebrow">Quick Actions</span>
        <h2>Seller Shortcuts</h2>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(175px, 1fr)); gap:0.65rem; margin-top:0.75rem;">
            @foreach([
                ['href' => route('dashboard.seller.listings'),  'icon' => 'home',    'label' => 'My Listings',       'desc' => 'Edit, update, or remove your properties'],
                ['href' => route('dashboard.enquiries.index'),  'icon' => 'chat',    'label' => 'Enquiry Threads',   'desc' => 'Respond to incoming buyer messages'],
                ['href' => route('dashboard.seller.requests'),  'icon' => 'leads',   'label' => 'Lead Requests',     'desc' => 'View matched buyer requests'],
                ['href' => route('account.profile'),            'icon' => 'profile', 'label' => 'Edit Profile',      'desc' => 'Update your seller account details'],
                ['href' => route('agents.index'),               'icon' => 'agents',  'label' => 'Find an Agent',     'desc' => 'Connect with buyer agents in your area'],
                ['href' => route('contact'),                    'icon' => 'support', 'label' => 'Contact Support',   'desc' => 'Get help from the OmniReferral team'],
            ] as $action)
                <a href="{{ $action['href'] }}" class="seller-action-card">
                    @if($action['icon'] === 'home')
                        <svg class="seller-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    @elseif($action['icon'] === 'chat')
                        <svg class="seller-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    @elseif($action['icon'] === 'leads')
                        <svg class="seller-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    @elseif($action['icon'] === 'profile')
                        <svg class="seller-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    @elseif($action['icon'] === 'agents')
                        <svg class="seller-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    @else
                        <svg class="seller-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.61 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    @endif
                    <strong>{{ $action['label'] }}</strong>
                    <span>{{ $action['desc'] }}</span>
                </a>
            @endforeach
        </div>
    </article>

</div>
@endsection
