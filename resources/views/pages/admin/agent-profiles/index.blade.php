@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Agent Profiles')
@section('dashboard_description', 'Review pending applications, approve public profiles, and manage linked agent accounts.')

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <div class="admin-user-show__pills">
            <a href="{{ route('admin.agent-profiles.index', ['status' => 'pending']) }}" class="admin-user-show__pill {{ $status === 'pending' ? 'is-active' : '' }}">Pending ({{ $counts['pending'] }})</a>
            <a href="{{ route('admin.agent-profiles.index', ['status' => 'approved']) }}" class="admin-user-show__pill {{ $status === 'approved' ? 'is-active' : '' }}">Approved ({{ $counts['approved'] }})</a>
            <a href="{{ route('admin.agent-profiles.index', ['status' => 'rejected']) }}" class="admin-user-show__pill {{ $status === 'rejected' ? 'is-active' : '' }}">Rejected ({{ $counts['rejected'] }})</a>
        </div>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Brokerage</th>
                        <th>Service Area</th>
                        <th>Account</th>
                        <th>Submitted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profiles as $profile)
                        @php $user = $profile->user; @endphp
                        <tr>
                            <td>
                                <strong>{{ $user?->publicDisplayName() ?: 'Unknown' }}</strong><br>
                                <span class="text-muted">{{ $user?->email }}</span>
                            </td>
                            <td>{{ $profile->brokerage_name ?: '—' }}</td>
                            <td>{{ $profile->serviceAreaLabel() ?: '—' }}</td>
                            <td><span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug($user?->status ?? 'pending', '_') }}">{{ ucfirst($user?->status ?? 'unknown') }}</span></td>
                            <td>{{ $profile->created_at?->format('M j, Y') }}</td>
                            <td><a href="{{ route('admin.agent-profiles.show', $profile) }}" class="button button--ghost-blue">Review</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="workspace-empty">No profiles in this queue.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $profiles->links() }}</div>
    </section>
</div>
@endsection
