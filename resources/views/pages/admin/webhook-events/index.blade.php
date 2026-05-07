@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Webhook events')
@section('dashboard_description', 'Inspect inbound events, payloads, and processing state for integrations.')

@section('dashboard_actions')
    <a href="{{ route('admin.dashboard') }}" class="button button--ghost-blue">Overview</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <span class="eyebrow">Filters</span>
        <h2>Search events</h2>
        <form method="GET" action="{{ route('admin.webhook-events.index') }}">
            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Provider</span>
                    <select name="provider">
                        <option value="">All</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider }}" {{ $filters['provider'] === $provider ? 'selected' : '' }}>{{ $provider }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Event</span>
                    <input type="text" name="event" value="{{ $filters['event'] }}" placeholder="event name contains...">
                </label>
                <label class="workspace-field">
                    <span>Processed</span>
                    <select name="processed">
                        <option value="">Any</option>
                        <option value="1" {{ $filters['processed'] === '1' ? 'selected' : '' }}>Processed</option>
                        <option value="0" {{ $filters['processed'] === '0' ? 'selected' : '' }}>Unprocessed</option>
                    </select>
                </label>
            </div>
            <div class="workspace-actions" style="margin-top:0.75rem;">
                <button type="submit" class="button">Apply</button>
                <a href="{{ route('admin.webhook-events.index') }}" class="button button--ghost-blue">Reset</a>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Provider</th>
                        <th>Event</th>
                        <th>Remote</th>
                        <th>Processed</th>
                        <th>When</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $row)
                        <tr>
                            <td data-label="ID"><strong>#{{ $row->id }}</strong></td>
                            <td data-label="Provider">{{ $row->provider }}</td>
                            <td data-label="Event">{{ $row->event }}</td>
                            <td data-label="Remote">{{ $row->remote_id ?: '—' }}</td>
                            <td data-label="Processed">
                                <span class="workspace-pill">{{ $row->processed_at ? 'Yes' : 'No' }}</span>
                            </td>
                            <td data-label="When">{{ $row->created_at?->format('M j, Y g:i A') }}</td>
                            <td data-label="">
                                <a href="{{ route('admin.webhook-events.show', $row) }}" class="button button--ghost-blue">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="workspace-empty">No events found.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="workspace-pagination">{{ $events->links() }}</div>
    </section>
</div>
@endsection

