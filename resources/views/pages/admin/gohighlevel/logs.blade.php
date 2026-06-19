@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'GHL Logs & Monitoring')
@section('dashboard_description', 'Inspect GoHighLevel webhook deliveries, onboarding submissions, and sync activity.')

@section('dashboard_actions')
    <a href="{{ route('admin.ghl.index') }}" class="button button--ghost-blue">Overview</a>
    <a href="{{ route('admin.ghl.debug') }}" class="button button--ghost-blue">Debug</a>
    <a href="{{ route('admin.ghl.testing') }}" class="button">Test Tools</a>
@endsection

@section('content')
<div class="workspace-stack">

    {{-- Filters --}}
    <section class="workspace-card">
        <span class="eyebrow">Filters</span>
        <h2>Search webhook events</h2>
        <form method="GET" action="{{ route('admin.ghl.logs') }}">
            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Event type</span>
                    <select name="event_type">
                        <option value="">All events</option>
                        @foreach($eventTypes as $type)
                            <option value="{{ $type }}" {{ ($filters['eventType'] ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">Any</option>
                        <option value="processed" {{ ($filters['status'] ?? '') === 'processed' ? 'selected' : '' }}>Processed</option>
                        <option value="pending"   {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Search (email / remote ID)</span>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search…">
                </label>
            </div>
            <div class="workspace-actions" style="margin-top:.75rem;">
                <button type="submit" class="button">Apply</button>
                <a href="{{ route('admin.ghl.logs') }}" class="button button--ghost-blue">Reset</a>
            </div>
        </form>
    </section>

    {{-- Webhook events --}}
    <section class="workspace-card">
        <span class="eyebrow">Webhook Events</span>
        <h2>GoHighLevel deliveries <span style="font-size:.9rem; font-weight:400; color:var(--color-text-muted,#6b7280);">({{ $webhooks->total() }} total)</span></h2>
        <div class="workspace-table-wrap" style="margin-top:.75rem;">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event</th>
                        <th>Remote ID</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($webhooks as $wh)
                    <tr>
                        <td data-label="ID"><strong>#{{ $wh->id }}</strong></td>
                        <td data-label="Event"><code>{{ $wh->event }}</code></td>
                        <td data-label="Remote ID">{{ $wh->remote_id ?: '—' }}</td>
                        <td data-label="Status"><span class="workspace-pill {{ $wh->statusBadgeClass() }}">{{ $wh->statusLabel() }}</span></td>
                        <td data-label="Received">{{ $wh->created_at?->format('M j, Y g:i A') }}</td>
                        <td data-label="" style="display:flex; gap:.5rem; flex-wrap:wrap;">
                            <a href="{{ route('admin.webhook-events.show', $wh->id) }}" class="button button--ghost-blue" style="font-size:.8rem; padding:.25rem .6rem;">Detail</a>
                            @if(! $wh->processed_at)
                            <form action="{{ route('admin.ghl.retry', $wh->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="button" style="font-size:.8rem; padding:.25rem .6rem;">Retry</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6"><div class="workspace-empty">No GHL webhook events found.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="workspace-pagination">{{ $webhooks->links() }}</div>
    </section>

    {{-- Onboarding logs --}}
    <section class="workspace-card" id="onboarding">
        <span class="eyebrow">Onboarding Sync Logs</span>
        <h2>GHL onboarding submissions <span style="font-size:.9rem; font-weight:400; color:var(--color-text-muted,#6b7280);">({{ $onboardingLogs->total() }} total)</span></h2>
        <div class="workspace-table-wrap" style="margin-top:.75rem;">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Triggered By</th>
                        <th>User</th>
                        <th>Event</th>
                        <th>Processed</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($onboardingLogs as $log)
                    <tr>
                        <td data-label="ID"><strong>#{{ $log->id }}</strong></td>
                        <td data-label="Triggered By">{{ $log->triggered_by ?? '—' }}</td>
                        <td data-label="User">
                            @if($log->user)
                                <a href="{{ route('admin.users.show', $log->user) }}" style="color:inherit;">{{ $log->user->name }}</a>
                                <span class="workspace-pill" style="font-size:.7rem;">{{ $log->user->role }}</span>
                            @else
                                <span style="color:var(--color-text-muted,#9ca3af);">No user linked</span>
                            @endif
                        </td>
                        <td data-label="Event"><code>{{ $log->event_type }}</code></td>
                        <td data-label="Processed">
                            <span class="workspace-pill {{ $log->processed_at ? 'workspace-pill--green' : 'workspace-pill--orange' }}">
                                {{ $log->processed_at ? 'Done' : 'Pending' }}
                            </span>
                        </td>
                        <td data-label="When">{{ $log->created_at?->format('M j, Y g:i A') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6"><div class="workspace-empty">No onboarding logs found.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="workspace-pagination">{{ $onboardingLogs->links() }}</div>
    </section>

</div>
@endsection
