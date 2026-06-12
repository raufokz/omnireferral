@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Staff Workspace')
@section('dashboard_title', 'Agent Profiles')
@section('dashboard_description', 'Create and publish agent directory profiles sourced from Zillow, Realtor.com, social media, and brokerage sites.')

@section('dashboard_actions')
    <a href="{{ route('admin.agent-profiles.create') }}" class="button button--orange">Add Agent Profile</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <div class="admin-user-show__pills">
            @foreach(['all' => 'All', 'draft' => 'Draft', 'published' => 'Published', 'featured' => 'Featured'] as $key => $label)
                <a href="{{ route('admin.agent-profiles.index', ['status' => $key]) }}" class="admin-user-show__pill {{ $status === $key ? 'is-active' : '' }}">
                    {{ $label }} ({{ $counts[$key] ?? 0 }})
                </a>
            @endforeach
        </div>
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
                            <td><span class="status-pill">{{ ucfirst($profile->profile_status) }}</span></td>
                            <td>{{ $profile->createdByUser?->name ?? '—' }}</td>
                            <td><a href="{{ route('admin.agent-profiles.show', $profile) }}" class="button button--ghost-blue">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="workspace-empty">No profiles yet. <a href="{{ route('admin.agent-profiles.create') }}">Add the first agent</a>.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $profiles->links() }}</div>
    </section>
</div>
@endsection
