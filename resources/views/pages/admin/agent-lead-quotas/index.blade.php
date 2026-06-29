@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Agent Lead Quotas')
@section('dashboard_description', 'Monitor and manage monthly lead quotas for all agents.')

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <span class="eyebrow">Filters</span>
        <form method="GET" action="{{ route('admin.agent-lead-quotas.index') }}">
            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Month</span>
                    <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}">
                </label>
                <label class="workspace-field">
                    <span>Agent</span>
                    <select name="agent_id">
                        <option value="">All agents</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Has Remaining</span>
                    <select name="under_quota">
                        <option value="">Any</option>
                        <option value="1" {{ request('under_quota') ? 'selected' : '' }}>Yes</option>
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Over Quota</span>
                    <select name="over_quota">
                        <option value="">Any</option>
                        <option value="1" {{ request('over_quota') ? 'selected' : '' }}>Yes</option>
                    </select>
                </label>
                <label class="workspace-field workspace-field--actions">
                    <span>&nbsp;</span>
                    <button type="submit" class="button">Filter</button>
                    <a href="{{ route('admin.agent-lead-quotas.index') }}" class="button button--ghost-blue">Reset</a>
                </label>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Package</th>
                        <th>Month</th>
                        <th>Quota</th>
                        <th>Assigned</th>
                        <th>Remaining</th>
                        <th>Overdue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotas as $quota)
                        <tr>
                            <td>{{ $quota->user?->name ?? 'N/A' }}</td>
                            <td>{{ $quota->package?->name ?? 'N/A' }}</td>
                            <td>{{ $quota->month }}</td>
                            <td>{{ $quota->monthly_quota }}</td>
                            <td>{{ $quota->assigned_count }}</td>
                            <td>
                                <span class="{{ $quota->remaining_count > 0 ? 'text-success' : ($quota->remaining_count < 0 ? 'text-danger' : '') }}">
                                    {{ $quota->remaining_count }}
                                </span>
                            </td>
                            <td>{{ $quota->overdue_count }}</td>
                            <td>
                                <a href="{{ route('admin.agent-lead-quotas.edit', $quota) }}" class="link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center muted">No quotas found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper">
            {{ $quotas->links() }}
        </div>
    </section>
</div>
@endsection
