@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Agent Workspace')
@section('dashboard_title', 'Lead Queue & Management')
@section('dashboard_description', 'Search, filter, and respond to assigned leads while intent is fresh.')

@section('dashboard_actions')
    <a href="{{ route('dashboard.agent') }}" class="button button--ghost-blue">Overview</a>
@endsection

@push('styles')
<style>
.agent-pipeline-summary {
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
    margin-bottom: 0.5rem;
}
.agent-pipeline-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
    border: 1px solid var(--dash-shell-border);
    background: var(--dash-shell-panel-soft);
    color: var(--dash-shell-muted);
    white-space: nowrap;
}
.agent-pipeline-chip strong { color: var(--dash-shell-text); font-size: 0.95rem; }
.agent-pipeline-chip--active { border-color: #0b3668; background: rgba(11,54,104,0.07); color: #0b3668; }

.lead-status-form select {
    appearance: none;
    border: 1px solid var(--dash-shell-border);
    background: #fff;
    border-radius: 8px;
    padding: 0.4rem 0.6rem;
    font-size: 0.8rem;
    cursor: pointer;
    color: var(--dash-shell-text);
}

.agent-kpi-icon {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 11px;
    display: grid;
    place-items: center;
    margin-bottom: 0.5rem;
}
.agent-kpi-icon svg { width: 1.1rem; height: 1.1rem; }
.agent-kpi-icon--blue   { background: rgba(11,54,104,0.10); color: #0b3668; }
.agent-kpi-icon--orange { background: rgba(255,107,0,0.13); color: #c2410c; }
.agent-kpi-icon--green  { background: rgba(22,163,74,0.12); color: #15803d; }
.agent-kpi-icon--violet { background: rgba(109,93,252,0.12); color: #5145cd; }

.lead-intent-badge {
    display: inline-flex; align-items: center; gap: 0.25rem;
    font-size: 0.7rem; font-weight: 700;
    padding: 0.16rem 0.45rem;
    border-radius: 999px;
}
.lead-intent-badge--buyer  { background: rgba(14,165,233,0.12); color: #0369a1; }
.lead-intent-badge--seller { background: rgba(255,107,0,0.12); color: #c2410c; }
.lead-intent-badge--investor { background: rgba(168,85,247,0.12); color: #7e22ce; }
.lead-intent-badge--other { background: rgba(100,116,139,0.12); color: #475569; }

.lead-priority-dot {
    display: inline-block;
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #ff6b00;
    margin-right: 0.3rem;
    vertical-align: middle;
}

/* Quota Card Bar */
.quota-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    color: #fff;
    margin-bottom: 1.25rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.quota-banner__info h4 {
    margin: 0 0 0.25rem 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #f8fafc;
}
.quota-banner__info p {
    margin: 0;
    font-size: 0.83rem;
    color: #94a3b8;
}
.quota-banner__progress-wrap {
    flex: 1;
    min-width: 240px;
    max-width: 400px;
}
.quota-progress-track {
    height: 10px;
    background: rgba(255,255,255,0.15);
    border-radius: 999px;
    overflow: hidden;
    margin-top: 0.4rem;
}
.quota-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
    border-radius: 999px;
    transition: width 0.4s ease;
}
.quota-banner__stats {
    display: flex;
    gap: 1.25rem;
}
.quota-stat-pill {
    text-align: right;
}
.quota-stat-pill span {
    display: block;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
}
.quota-stat-pill strong {
    font-size: 1.15rem;
    font-weight: 800;
    color: #38bdf8;
}

/* Quick Action Buttons */
.lead-quick-actions {
    display: flex;
    gap: 0.35rem;
    align-items: center;
}
.quick-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: var(--dash-shell-panel-soft);
    border: 1px solid var(--dash-shell-border);
    color: var(--dash-shell-text);
    text-decoration: none;
    transition: all 0.15s ease;
}
.quick-action-btn:hover {
    background: #0b3668;
    color: #fff;
    border-color: #0b3668;
}
.quick-action-btn svg {
    width: 14px;
    height: 14px;
}
</style>
@endpush

@section('content')
<div class="workspace-stack">

    {{-- Monthly Quota Progress Banner --}}
    @if(isset($quotaStats))
        <div class="quota-banner">
            <div class="quota-banner__info">
                <h4>{{ $quotaStats['packageName'] }} &bull; {{ $quotaStats['monthName'] }}</h4>
                <p>Monthly Lead Allowance: <strong>{{ number_format($quotaStats['monthlyQuota']) }} leads</strong></p>
            </div>
            <div class="quota-banner__progress-wrap">
                <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: #cbd5e1;">
                    <span>Assigned: <strong>{{ $quotaStats['assignedCount'] }} / {{ $quotaStats['monthlyQuota'] }}</strong></span>
                    <span><strong>{{ $quotaStats['percentage'] }}%</strong> Used</span>
                </div>
                <div class="quota-progress-track">
                    <div class="quota-progress-fill" style="width: {{ $quotaStats['percentage'] }}%;"></div>
                </div>
            </div>
            <div class="quota-banner__stats">
                <div class="quota-stat-pill">
                    <span>Remaining</span>
                    <strong style="color: {{ $quotaStats['remainingCount'] > 0 ? '#34d399' : '#f87171' }};">
                        {{ number_format($quotaStats['remainingCount']) }}
                    </strong>
                </div>
            </div>
        </div>
    @endif

    {{-- KPI Row --}}
    <section class="workspace-grid workspace-grid--4">

        <article class="workspace-card workspace-kpi" data-trend="Total queue">
            <div class="agent-kpi-icon agent-kpi-icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
            </div>
            <span>Assigned</span>
            <strong>{{ number_format($agentStats['leads_received']) }}</strong>
            <span>Total leads in queue</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--warm" data-trend="{{ number_format(data_get(collect($pipeline)->firstWhere('label', 'Contacted'), 'count', 0)) }} contacted">
            <div class="agent-kpi-icon agent-kpi-icon--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <span>In Progress</span>
            <strong>{{ number_format(data_get(collect($pipeline)->firstWhere('label', 'Contacted'), 'count', 0) + data_get(collect($pipeline)->firstWhere('label', 'Qualified'), 'count', 0)) }}</strong>
            <span>Contacted + qualified</span>
        </article>

        <article class="workspace-card workspace-kpi" data-trend="Converted">
            <div class="agent-kpi-icon agent-kpi-icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <span>Closed</span>
            <strong>{{ number_format($agentStats['closed_leads']) }}</strong>
            <span>Deals marked closed</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--violet" data-trend="Contact pace">
            <div class="agent-kpi-icon agent-kpi-icon--violet">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <span>Response Rate</span>
            <strong>{{ $agentStats['response_rate'] }}</strong>
            <span>Current contact performance</span>
        </article>

    </section>

    {{-- Pipeline Summary Chips --}}
    <div class="agent-pipeline-summary">
        @foreach($pipeline as $stage)
            <a href="{{ route('agent.leads.index', array_merge(request()->query(), ['status' => strtolower($stage['label']) === 'new' ? 'new' : (strtolower($stage['label']) === 'contacted' ? 'contacted' : (strtolower($stage['label']) === 'qualified' ? 'qualified' : 'closed'))])) }}" class="agent-pipeline-chip {{ ($filters['status'] ?? '') === strtolower($stage['label']) ? 'agent-pipeline-chip--active' : '' }}" style="text-decoration:none;">
                {{ $stage['label'] }} <strong>{{ number_format($stage['count']) }}</strong>
            </a>
        @endforeach
        @if($agentStats['leads_received'] > 0)
            <div class="agent-pipeline-chip" style="margin-left:auto;">
                Conversion <strong>{{ $agentStats['response_rate'] }}</strong>
            </div>
        @endif
    </div>

    {{-- Search & Filter Toolbar Card --}}
    <section class="workspace-card" style="padding: 1rem 1.25rem;">
        <form method="GET" action="{{ route('agent.leads.index') }}" id="leadFilterForm">
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">

                {{-- Search --}}
                <div style="flex: 2; min-width: 200px;">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                           placeholder="Search name, phone, email, city..."
                           style="width: 100%; border: 1px solid var(--dash-shell-border); border-radius: 8px; padding: 0.45rem 0.75rem; font-size: 0.82rem; background: #fff; color: var(--dash-shell-text);">
                </div>

                {{-- Status Filter --}}
                <div style="flex: 1; min-width: 130px;">
                    <select name="status" onchange="this.form.submit()" style="width: 100%; border: 1px solid var(--dash-shell-border); border-radius: 8px; padding: 0.45rem 0.6rem; font-size: 0.82rem; background: #fff; color: var(--dash-shell-text);">
                        <option value="">All Statuses</option>
                        @foreach(['new' => 'New', 'contacted' => 'Contacted', 'in_progress' => 'In Progress', 'qualified' => 'Qualified', 'closed' => 'Closed', 'not_interested' => 'Not Interested'] as $val => $lbl)
                            <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Intent Filter --}}
                <div style="flex: 1; min-width: 130px;">
                    <select name="intent" onchange="this.form.submit()" style="width: 100%; border: 1px solid var(--dash-shell-border); border-radius: 8px; padding: 0.45rem 0.6rem; font-size: 0.82rem; background: #fff; color: var(--dash-shell-text);">
                        <option value="">All Intents</option>
                        @foreach(['buyer' => 'Buyer', 'seller' => 'Seller', 'investor' => 'Investor', 'other' => 'Other'] as $val => $lbl)
                            <option value="{{ $val }}" {{ ($filters['intent'] ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Sort Filter --}}
                <div style="flex: 1; min-width: 140px;">
                    <select name="sort" onchange="this.form.submit()" style="width: 100%; border: 1px solid var(--dash-shell-border); border-radius: 8px; padding: 0.45rem 0.6rem; font-size: 0.82rem; background: #fff; color: var(--dash-shell-text);">
                        <option value="latest" {{ ($filters['sort'] ?? '') === 'latest' ? 'selected' : '' }}>Newest Assigned</option>
                        <option value="oldest" {{ ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' }}>Oldest Assigned</option>
                        <option value="priority" {{ ($filters['sort'] ?? '') === 'priority' ? 'selected' : '' }}>Priority First</option>
                        <option value="status" {{ ($filters['sort'] ?? '') === 'status' ? 'selected' : '' }}>By Status</option>
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div style="display: flex; gap: 0.4rem;">
                    <button type="submit" class="button button--ghost-blue" style="padding: 0.45rem 0.8rem; font-size: 0.8rem;">Search</button>
                    @if(!empty($filters['search']) || !empty($filters['status']) || !empty($filters['intent']) || ($filters['sort'] ?? 'latest') !== 'latest')
                        <a href="{{ route('agent.leads.index') }}" class="button button--ghost-blue" style="padding: 0.45rem 0.8rem; font-size: 0.8rem; color: #ef4444; border-color: #fca5a5;">Clear</a>
                    @endif
                </div>

            </div>
        </form>
    </section>

    {{-- Leads Table --}}
    <section class="workspace-card">
        @if($leads->isEmpty())
            <div class="workspace-empty" style="padding:2rem;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:#cbd5e1; margin:0 auto 0.75rem; display:block;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <strong style="display:block; margin-bottom:0.35rem; color:var(--dash-shell-text);">No leads found matching your criteria</strong>
                <p style="font-size:0.85rem; color:var(--dash-shell-muted); max-width:340px; margin:0 auto;">
                    Try clearing filters or adjusting your search term.
                </p>
                <a href="{{ route('agent.leads.index') }}" class="button button--ghost-blue" style="display:inline-block; margin-top:1rem;">Clear All Filters</a>
            </div>
        @else
            <div class="workspace-table-wrap">
                <table class="workspace-table">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Location &amp; Type</th>
                            <th>Intent &amp; Budget</th>
                            <th>Assigned</th>
                            <th>Status</th>
                            <th>Quick Connect</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                            <tr>
                                <td data-label="Lead">
                                    <a href="{{ route('agent.leads.show', $lead) }}" style="color:var(--dash-shell-text); font-weight:700; text-decoration:none;" onmouseover="this.style.color='#0b3668'" onmouseout="this.style.color='var(--dash-shell-text)'">
                                        @if($lead->is_priority)
                                            <span class="lead-priority-dot" title="Priority lead"></span>
                                        @endif
                                        {{ $lead->name }}
                                    </a>
                                    <div class="workspace-property__meta">
                                        {{ $lead->phone ?: 'No phone' }}
                                        @if($lead->email)
                                            · {{ Str::limit($lead->email, 22) }}
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Location & Type">
                                    <strong>{{ $lead->city ?: ($lead->zip_code ?: '—') }}</strong>
                                    <div class="workspace-property__meta">
                                        {{ $lead->property_type ?: 'Type pending' }}
                                        @if($lead->beds_baths)
                                            · {{ $lead->beds_baths }}
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Intent & Budget">
                                    <span class="lead-intent-badge lead-intent-badge--{{ $lead->intent ?? 'buyer' }}">
                                        {{ ucfirst($lead->intent ?? 'Buyer') }}
                                    </span>
                                    <div class="workspace-property__meta" style="margin-top:0.25rem;">
                                        {{ strtoupper($lead->package_type ?: 'N/A') }}
                                        @if($lead->budget)
                                            · {{ $lead->budget }}
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Assigned">
                                    <strong style="font-size:0.83rem;">{{ $lead->assigned_at?->format('M j') ?? '—' }}</strong>
                                    <div class="workspace-property__meta">{{ $lead->assigned_at?->diffForHumans() ?? 'Date unknown' }}</div>
                                </td>
                                <td data-label="Status">
                                    <form action="{{ route('agent.leads.status', $lead) }}" method="POST" class="lead-status-form">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" aria-label="Update lead status for {{ $lead->name }}">
                                            @foreach(['new' => 'New', 'contacted' => 'Contacted', 'in_progress' => 'In Progress', 'qualified' => 'Qualified', 'closed' => 'Closed', 'not_interested' => 'Not Interested'] as $value => $label)
                                                <option value="{{ $value }}" {{ $lead->status === $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td data-label="Quick Connect">
                                    <div class="lead-quick-actions">
                                        @if($lead->phone)
                                            <a href="tel:{{ $lead->phone }}" class="quick-action-btn" title="Call {{ $lead->phone }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                            </a>
                                            <a href="sms:{{ $lead->phone }}" class="quick-action-btn" title="SMS {{ $lead->phone }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                            </a>
                                        @endif
                                        @if($lead->email)
                                            <a href="mailto:{{ $lead->email }}" class="quick-action-btn" title="Email {{ $lead->email }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Action">
                                    <a href="{{ route('agent.leads.show', $lead) }}" class="button button--ghost-blue" style="font-size:0.75rem; padding:0.3rem 0.6rem;">
                                        View Details &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="workspace-pagination" style="margin-top: 1rem;">
                {{ $leads->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
