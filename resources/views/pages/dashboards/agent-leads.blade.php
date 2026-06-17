@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Agent Workspace')
@section('dashboard_title', 'Lead Queue')
@section('dashboard_description', 'Every assigned lead lives here. Update contact status while intent is fresh.')

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

.lead-row-expanded { background: var(--dash-shell-panel-soft) !important; }
.lead-detail-row td { padding: 0.6rem 0.55rem 0.9rem; }
.lead-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.75rem;
    font-size: 0.82rem;
}
.lead-detail-item strong { display: block; font-size: 0.73rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dash-shell-muted); margin-bottom: 0.15rem; }
.lead-detail-item span { color: var(--dash-shell-text); font-weight: 500; }

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

.lead-priority-dot {
    display: inline-block;
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #ff6b00;
    margin-right: 0.3rem;
    vertical-align: middle;
}
</style>
@endpush

@section('content')
@php
    $pipelineMax = max(1, collect($pipeline)->max('count'));
@endphp

<div class="workspace-stack">

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
            <div class="agent-pipeline-chip {{ $stage['count'] > 0 ? 'agent-pipeline-chip--active' : '' }}">
                {{ $stage['label'] }} <strong>{{ number_format($stage['count']) }}</strong>
            </div>
        @endforeach
        @if($agentStats['leads_received'] > 0)
            <div class="agent-pipeline-chip" style="margin-left:auto;">
                Conversion <strong>{{ $agentStats['response_rate'] }}</strong>
            </div>
        @endif
    </div>

    {{-- Leads Table --}}
    <section class="workspace-card">
        @if($leads->isEmpty())
            <div class="workspace-empty" style="padding:2rem;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:#cbd5e1; margin:0 auto 0.75rem; display:block;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <strong style="display:block; margin-bottom:0.35rem; color:var(--dash-shell-text);">No leads assigned yet</strong>
                <p style="font-size:0.85rem; color:var(--dash-shell-muted); max-width:340px; margin:0 auto;">
                    Leads matched to your profile and package tier will appear here as soon as they're assigned.
                </p>
                <a href="{{ route('pricing') }}" class="button button--ghost-blue" style="display:inline-block; margin-top:1rem;">Review Packages</a>
            </div>
        @else
            <div class="workspace-table-wrap">
                <table class="workspace-table">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Location</th>
                            <th>Intent &amp; Package</th>
                            <th>Assigned</th>
                            <th>Status</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                            <tr>
                                <td data-label="Lead">
                                    <strong>
                                        @if($lead->is_priority)
                                            <span class="lead-priority-dot" title="Priority lead"></span>
                                        @endif
                                        {{ $lead->name }}
                                    </strong>
                                    <div class="workspace-property__meta">
                                        {{ $lead->phone ?: 'No phone' }}
                                        @if($lead->email)
                                            · {{ Str::limit($lead->email, 24) }}
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Location">
                                    <strong>{{ $lead->zip_code ?: '—' }}</strong>
                                    <div class="workspace-property__meta">
                                        {{ $lead->property_type ?: 'Type pending' }}
                                        @if($lead->beds_baths)
                                            · {{ $lead->beds_baths }}
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Intent & Package">
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
                                    <span class="status-pill status-pill--{{ $lead->statusTone() }}">{{ $lead->statusLabel() }}</span>
                                </td>
                                <td data-label="Update">
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="workspace-pagination">
                {{ $leads->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
