@extends('layouts.app')

@section('content')
<section class="page-hero dashboard-page-hero dashboard-page-hero--agent">
    <div class="container page-hero__content">
        <span class="eyebrow">Assigned Leads</span>
        <h1>Work every assigned lead from one focused queue</h1>
        <p>Only leads assigned to you appear here, and every status change is saved immediately so the pipeline stays current.</p>
    </div>
</section>

<section class="section dashboard-page agent-portal-shell">
    <div class="container agent-portal-grid">
        @include('pages.dashboards.partials.agent-portal-sidebar')

        <div class="agent-portal-main">
            <div class="cockpit-kpi-row">
                <article class="cockpit-kpi-card">
                    <span class="eyebrow">Assigned</span>
                    <strong>{{ $agentStats['leads_received'] }}</strong>
                    <p>Total leads in your queue</p>
                </article>
                <article class="cockpit-kpi-card">
                    <span class="eyebrow">Qualified</span>
                    <strong>{{ data_get(collect($pipeline)->firstWhere('label', 'Qualified'), 'count', 0) }}</strong>
                    <p>Leads marked qualified</p>
                </article>
                <article class="cockpit-kpi-card">
                    <span class="eyebrow">Closed</span>
                    <strong>{{ $agentStats['closed_leads'] }}</strong>
                    <p>Deals closed from your queue</p>
                </article>
                <article class="cockpit-kpi-card">
                    <span class="eyebrow">Response Rate</span>
                    <strong>{{ $agentStats['response_rate'] }}</strong>
                    <p>Based on contacted and progressed leads</p>
                </article>
            </div>

            <section class="cockpit-table-card agent-portal-section">
                <div class="agent-portal-section__header">
                    <div>
                        <span class="eyebrow">Lead Queue</span>
                        <h2>All assigned opportunities</h2>
                    </div>
                </div>

                <table class="cockpit-table">
                    <thead>
                        <tr>
                            <th>Lead Detail</th>
                            <th>Location</th>
                            <th>Package</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr>
                                <td>
                                    <div class="agent-lead-detail">
                                        <strong>{{ $lead->name }}</strong>
                                        <span>{{ ucfirst($lead->intent) }} lead &middot; {{ $lead->phone ?: 'No phone' }}</span>
                                        <small>{{ $lead->email ?: 'No email provided' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="agent-lead-detail">
                                        <strong>{{ $lead->zip_code ?: 'No ZIP' }}</strong>
                                        <span>{{ $lead->property_type ?: 'Property type pending' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="agent-lead-detail">
                                        <strong>{{ strtoupper($lead->package_type ?: 'N/A') }}</strong>
                                        <span>Lead ID: {{ $lead->id }}</span>
                                    </div>
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
                                    <div class="cockpit-empty-state">
                                        <h3>Your queue is clear</h3>
                                        <p class="text-gray-500">Admin-assigned leads will appear here automatically.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="agent-portal-pagination">
                    {{ $leads->links() }}
                </div>
            </section>
        </div>
    </div>
</section>
@endsection
