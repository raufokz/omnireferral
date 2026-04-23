@extends('layouts.dashboard')

@section('dashboard_eyebrow', $isStaffView ? 'Staff Workspace' : 'Admin Workspace')
@section('dashboard_title', 'Lead Registry')
@section('dashboard_description', 'Filter, import, assign, and track all lead records from one fully responsive operations page.')

@section('dashboard_actions')
    <a href="{{ route('admin.dashboard') }}" class="button button--ghost-blue">Overview</a>
    <a href="{{ route('admin.leads.export.csv', request()->query()) }}" class="button">Export CSV</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi">
            <span>Matching Leads</span>
            <strong>{{ number_format($summary['total']) }}</strong>
            <span>Current filtered records</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Qualified</span>
            <strong>{{ number_format($summary['qualified']) }}</strong>
            <span>Conversion-ready leads</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Rejected</span>
            <strong>{{ number_format($summary['rejected']) }}</strong>
            <span>Not interested or invalid</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Website Source</span>
            <strong>{{ number_format($summary['website']) }}</strong>
            <span>Captured directly on-site</span>
        </article>
    </section>

    <section class="workspace-grid workspace-grid--2">
        <article class="workspace-card">
            <span class="eyebrow">Filters</span>
            <h2>Refine Lead Results</h2>
            <form method="GET" action="{{ route('admin.leads.index') }}">
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Search</span>
                        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Name, email, phone, address...">
                    </label>
                    <label class="workspace-field">
                        <span>Intent</span>
                        <select name="intent">
                            <option value="">All intents</option>
                            @foreach($intents as $intent)
                                <option value="{{ $intent }}" {{ $filters['intent'] === $intent ? 'selected' : '' }}>{{ ucfirst($intent) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="workspace-field">
                        <span>Status</span>
                        <select name="status">
                            <option value="">All statuses</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>
                                    {{ $status === 'not_interested' ? 'Rejected' : ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="workspace-field">
                        <span>Source</span>
                        <select name="source">
                            <option value="">All sources</option>
                            @foreach($sources as $source)
                                <option value="{{ $source }}" {{ $filters['source'] === $source ? 'selected' : '' }}>{{ \Illuminate\Support\Str::headline($source) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="workspace-field">
                        <span>Rep Name</span>
                        <select name="rep_name">
                            <option value="">All reps</option>
                            @foreach($repNames as $repName)
                                <option value="{{ $repName }}" {{ $filters['rep_name'] === $repName ? 'selected' : '' }}>{{ $repName }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="workspace-field">
                        <span>Date From</span>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] }}">
                    </label>
                    <label class="workspace-field">
                        <span>Date To</span>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] }}">
                    </label>
                </div>
                <div class="workspace-actions" style="margin-top: 0.8rem;">
                    <button type="submit" class="button">Apply Filters</button>
                </div>
            </form>
        </article>

        <article class="workspace-card">
            <span class="eyebrow">Import And Sync</span>
            <h2>Bulk Lead Operations</h2>
            <form method="POST" action="{{ route('admin.leads.import.csv') }}" enctype="multipart/form-data" class="js-loading-form">
                @csrf
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Upload File</span>
                        <input type="file" name="lead_file" required>
                    </label>
                </div>
                <div class="workspace-actions" style="margin-top: 0.8rem;">
                    <button type="submit" name="mode" value="import" class="button js-loading-btn">Import Leads</button>
                    <button type="submit" name="mode" value="preview" class="button button--ghost-blue js-loading-btn">Preview</button>
                </div>
            </form>
            <div class="workspace-actions" style="margin-top: 0.85rem;">
                <form method="POST" action="{{ route('admin.leads.sync.google-sheets') }}" class="js-loading-form">
                    @csrf
                    <button type="submit" class="button button--ghost-blue js-loading-btn">Sync Google Sheets</button>
                </form>
            </div>
        </article>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Stage</th>
                        <th>Market</th>
                        <th>Ops</th>
                        <th>Assignment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        @php
                            $statusTone = $lead->statusTone();
                            $statusLabel = $lead->statusLabel();
                            $priceSummary = $lead->budget
                                ? 'Budget $' . number_format($lead->budget)
                                : ($lead->asking_price ? 'Ask $' . number_format($lead->asking_price) : 'N/A');
                        @endphp
                        <tr>
                            <td data-label="Lead">
                                <strong>{{ $lead->name }}</strong>
                                <div class="workspace-property__meta">{{ $lead->email ?: 'No email' }}</div>
                                <div class="workspace-property__meta">{{ $lead->phone }} · {{ $lead->lead_number }}</div>
                            </td>
                            <td data-label="Stage">
                                <span class="status-pill status-pill--{{ $statusTone }}">{{ $statusLabel }}</span>
                                <div class="workspace-property__meta">{{ ucfirst($lead->intent) }} · {{ \Illuminate\Support\Str::headline($lead->source ?: 'Web') }}</div>
                                <form action="{{ route('admin.leads.status', $lead) }}" method="POST" class="js-loading-form" style="margin-top: 0.45rem;">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()">
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" {{ $lead->status === $status ? 'selected' : '' }}>
                                                {{ $status === 'not_interested' ? 'Rejected' : ucfirst(str_replace('_',' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td data-label="Market">
                                <strong>{{ $lead->property_address ?: 'No address' }}</strong>
                                <div class="workspace-property__meta">{{ $lead->zip_code ?: 'N/A' }} · {{ $lead->state ?: 'N/A' }}</div>
                                <div class="workspace-property__meta">{{ $priceSummary }}</div>
                            </td>
                            <td data-label="Ops">
                                <strong>{{ $lead->rep_name ?: 'No rep' }}</strong>
                                <div class="workspace-property__meta">Sent: {{ $lead->sent_to ?: 'N/A' }}</div>
                                <div class="workspace-property__meta">{{ \Illuminate\Support\Str::limit($lead->reason_in_house ?: 'No reason', 42) }}</div>
                            </td>
                            <td data-label="Assignment">
                                <span class="status-pill status-pill--{{ $lead->assigned_agent_id ? 'assigned' : 'new' }}">{{ $lead->assignedAgent?->name ?? 'Unassigned' }}</span>
                                <div class="workspace-property__meta">{{ $lead->assigned_at ? $lead->assigned_at->diffForHumans() : 'Awaiting routing' }}</div>
                                <form action="{{ route('admin.leads.assign', $lead) }}" method="POST" class="js-loading-form" style="margin-top: 0.45rem;">
                                    @csrf
                                    <div class="workspace-actions">
                                        <select name="agent_id" required>
                                            <option value="">Agent...</option>
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->id }}" {{ (int) $lead->assigned_agent_id === (int) $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="button button--ghost-blue js-loading-btn">Set</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="workspace-empty">No registry records found for the current filter.</div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-loading-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const button = event.submitter || form.querySelector('.js-loading-btn');
            if (button) {
                button.disabled = true;
            }
        });
    });
});
</script>
@endpush
@endsection
