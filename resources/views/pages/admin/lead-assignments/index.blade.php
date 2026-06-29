@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Lead Assignments')
@section('dashboard_description', 'View and manage all lead-to-agent assignments.')

@section('dashboard_actions')
    <a href="{{ route('admin.lead-assignments.create') }}" class="button">Assign Lead</a>
    <form method="POST" action="{{ route('admin.lead-assignments.auto-assign') }}" style="display:inline" onsubmit="return confirm('Auto-assign unassigned leads to eligible agents?')">
        @csrf
        <button type="submit" class="button button--ghost-blue">Auto-Assign</button>
    </form>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <span class="eyebrow">Filters</span>
        <form method="GET" action="{{ route('admin.lead-assignments.index') }}">
            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Month</span>
                    <input type="month" name="month" value="{{ request('month') }}">
                </label>
                <label class="workspace-field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">All statuses</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
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
                <label class="workspace-field workspace-field--actions">
                    <span>&nbsp;</span>
                    <button type="submit" class="button">Filter</button>
                    <a href="{{ route('admin.lead-assignments.index') }}" class="button button--ghost-blue">Reset</a>
                </label>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Lead</th>
                        <th>Agent</th>
                        <th>Package</th>
                        <th>Month</th>
                        <th>Status</th>
                        <th>Sent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr>
                            <td>#{{ $assignment->id }}</td>
                            <td>
                                <a href="{{ route('admin.leads.index', ['search' => $assignment->lead->email ?? $assignment->lead->name]) }}" class="link">
                                    {{ $assignment->lead->name ?? 'Lead #'.$assignment->lead_id }}
                                </a>
                                <br><small class="muted">{{ $assignment->lead->email }}</small>
                            </td>
                            <td>{{ $assignment->assignedTo?->name ?? 'N/A' }}</td>
                            <td>{{ $assignment->package?->name ?? 'N/A' }}</td>
                            <td>{{ $assignment->assignment_month }}</td>
                            <td>
                                <span class="badge badge--{{ $assignment->assignment_status }}">
                                    {{ str_replace('_', ' ', ucfirst($assignment->assignment_status)) }}
                                </span>
                            </td>
                            <td>{{ $assignment->sent_at?->format('M j, Y') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.lead-assignments.show', $assignment) }}" class="link">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center muted">No assignments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper">
            {{ $assignments->links() }}
        </div>
    </section>
</div>
@endsection
