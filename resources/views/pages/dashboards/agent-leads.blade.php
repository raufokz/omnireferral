@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Agent Workspace')
@section('dashboard_title', 'Lead Queue')
@section('dashboard_description', 'Every assigned lead lives in this dedicated page so updates stay focused and fast.')

@section('dashboard_actions')
    <a href="{{ route('dashboard.agent') }}" class="button button--ghost-blue">Overview</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi">
            <span>Assigned</span>
            <strong>{{ number_format($agentStats['leads_received']) }}</strong>
            <span>Total leads in your queue</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Qualified</span>
            <strong>{{ number_format(data_get(collect($pipeline)->firstWhere('label', 'Qualified'), 'count', 0)) }}</strong>
            <span>Leads ready for close</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Closed</span>
            <strong>{{ number_format($agentStats['closed_leads']) }}</strong>
            <span>Deals marked closed</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Response Rate</span>
            <strong>{{ $agentStats['response_rate'] }}</strong>
            <span>Current contact performance</span>
        </article>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Location</th>
                        <th>Package</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        <tr>
                            <td>
                                <strong>{{ $lead->name }}</strong>
                                <div class="workspace-property__meta">{{ ucfirst($lead->intent) }} lead · {{ $lead->phone ?: 'No phone' }}</div>
                            </td>
                            <td>
                                <strong>{{ $lead->zip_code ?: 'No ZIP' }}</strong>
                                <div class="workspace-property__meta">{{ $lead->property_type ?: 'Property type pending' }}</div>
                            </td>
                            <td>
                                <strong>{{ strtoupper($lead->package_type ?: 'N/A') }}</strong>
                                <div class="workspace-property__meta">Lead ID {{ $lead->id }}</div>
                            </td>
                            <td>
                                <span class="status-pill status-pill--{{ $lead->statusTone() }}">{{ $lead->statusLabel() }}</span>
                            </td>
                            <td>
                                <form action="{{ route('agent.leads.status', $lead) }}" method="POST">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" aria-label="Update lead status">
                                        @foreach(['new', 'contacted', 'in_progress', 'qualified', 'closed', 'not_interested'] as $status)
                                            <option value="{{ $status }}" {{ $lead->status === $status ? 'selected' : '' }}>
                                                {{ $status === 'not_interested' ? 'Rejected' : ucfirst(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="workspace-empty">No leads assigned yet.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="workspace-pagination">
            {{ $leads->links() }}
        </div>
    </section>
</div>
@endsection
