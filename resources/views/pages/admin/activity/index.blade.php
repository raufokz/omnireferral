@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Activity & audit log')
@section('dashboard_description', 'Immutable-style record of sensitive administrative actions (exports, user overrides, property changes, reviews).')

@section('dashboard_actions')
    <a href="{{ route('admin.dashboard') }}" class="button button--ghost-blue">Overview</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <span class="eyebrow">Filters</span>
        <h2>Find log entries</h2>
        <form method="GET" action="{{ route('admin.activity.index') }}">
            <div class="workspace-form-grid">
                <label class="workspace-field workspace-field--full">
                    <span>Action contains</span>
                    <input type="text" name="action" value="{{ $filters['action'] }}" placeholder="e.g. user.updated">
                </label>
                <label class="workspace-field">
                    <span>Actor user ID</span>
                    <input type="text" name="actor" value="{{ $filters['actor'] }}">
                </label>
                <label class="workspace-field">
                    <span>From</span>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}">
                </label>
                <label class="workspace-field">
                    <span>To</span>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}">
                </label>
            </div>
            <div class="workspace-actions" style="margin-top:0.75rem;">
                <button type="submit" class="button">Apply</button>
                <a href="{{ route('admin.activity.index') }}" class="button button--ghost-blue">Reset</a>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Actor</th>
                        <th>Action</th>
                        <th>Subject</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td data-label="When">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                            <td data-label="Actor">{{ $log->actor?->name ?? '—' }}</td>
                            <td data-label="Action"><code style="font-size:0.85rem;">{{ $log->action }}</code></td>
                            <td data-label="Subject">
                                @if($log->subject_type)
                                    {{ $log->subject_type }} #{{ $log->subject_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td data-label="IP">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><div class="workspace-empty">No log entries yet. Actions will appear as administrators use the platform.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="workspace-pagination">{{ $logs->links() }}</div>
    </section>
</div>
@endsection
