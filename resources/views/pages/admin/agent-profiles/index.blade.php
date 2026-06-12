@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Staff Workspace')
@section('dashboard_title', 'Agent Profiles')
@section('dashboard_description', 'Create, approve, suspend, and maintain scalable agent directory profiles.')

@section('dashboard_actions')
    <a href="{{ route('admin.agents.import') }}" class="button button--orange">Add Agent Profile</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <div class="admin-user-show__pills">
            @foreach(['all' => 'All', 'draft' => 'Pending', 'published' => 'Approved', 'featured' => 'Featured', 'suspended' => 'Suspended'] as $key => $label)
                <a href="{{ route('admin.agents.manage', ['status' => $key, 'q' => $search ?? null]) }}" class="admin-user-show__pill {{ $status === $key ? 'is-active' : '' }}">
                    {{ $label }} ({{ $counts[$key] ?? 0 }})
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.agents.manage') }}" class="workspace-form-grid" style="margin-top:1rem;">
            <input type="hidden" name="status" value="{{ $status }}">
            <label class="workspace-field workspace-field--full">
                <span>Search agents</span>
                <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Name, email, brokerage, license, city, state, or ZIP">
            </label>
            <div>
                <button type="submit" class="button button--orange">Search</button>
                <a href="{{ route('admin.agents.manage') }}" class="button button--ghost-blue">Reset</a>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Brokerage</th>
                        <th>Market</th>
                        <th>Status</th>
                        <th>Created by</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profiles as $profile)
                        <tr>
                            <td>
                                <strong>{{ $profile->user?->publicDisplayName() }}</strong>
                                <br><span class="text-muted">{{ $profile->slug }}</span>
                            </td>
                            <td>{{ $profile->brokerage_name }}</td>
                            <td>{{ $profile->serviceAreaLabel() }}</td>
                            <td><span class="status-pill status-pill--{{ $profile->profile_status }}">{{ $profile->statusLabel() }}</span></td>
                            <td>{{ $profile->createdByUser?->name ?? '-' }}</td>
                            <td><a href="{{ route('admin.agent-profiles.show', $profile) }}" class="button button--ghost-blue">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="workspace-empty">No profiles found. <a href="{{ route('admin.agents.import') }}">Add an agent</a>.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $profiles->links() }}</div>
    </section>
</div>
@endsection
