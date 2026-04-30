@extends('layouts.dashboard')

@section('dashboard_eyebrow', $isStaffView ? 'Staff Workspace' : 'Admin Workspace')
@section('dashboard_title', 'User management')
@section('dashboard_description', 'View every registered account, filter by role or status, and export the directory. Full role and status overrides are limited to administrators.')

@section('dashboard_actions')
    <a href="{{ route('admin.dashboard') }}" class="button button--ghost-blue">Overview</a>
    @if($canManage)
        <a href="{{ route('admin.users.export.csv') }}" class="button">Export CSV</a>
        <a href="{{ route('admin.users.export.xlsx') }}" class="button button--ghost-blue">Export Excel</a>
    @endif
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi">
            <span>Total users</span>
            <strong>{{ number_format($users->total()) }}</strong>
            <span>Matching current filters</span>
        </article>
    </section>

    <section class="workspace-card">
        <span class="eyebrow">Filters</span>
        <h2>Search users</h2>
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="workspace-form-grid">
                <label class="workspace-field workspace-field--full">
                    <span>Keyword</span>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Name or email">
                </label>
                <label class="workspace-field">
                    <span>Role</span>
                    <select name="role">
                        <option value="">All roles</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" {{ $filters['role'] === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">All statuses</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ $filters['status'] === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Sort</span>
                    <select name="sort">
                        <option value="latest" {{ $filters['sort'] === 'latest' ? 'selected' : '' }}>Latest</option>
                        <option value="name" {{ $filters['sort'] === 'name' ? 'selected' : '' }}>Name A–Z</option>
                        <option value="email" {{ $filters['sort'] === 'email' ? 'selected' : '' }}>Email A–Z</option>
                    </select>
                </label>
            </div>
            <div class="workspace-actions" style="margin-top: 0.75rem;">
                <button type="submit" class="button">Apply</button>
                <a href="{{ route('admin.users.index') }}" class="button button--ghost-blue">Reset</a>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td data-label="User">
                                <strong>{{ $u->name }}</strong>
                                <div class="workspace-property__meta">{{ $u->email }}</div>
                                @if($u->display_name)
                                    <div class="workspace-property__meta">Display: {{ $u->display_name }}</div>
                                @endif
                            </td>
                            <td data-label="Role">{{ ucfirst($u->role) }}</td>
                            <td data-label="Status">
                                <span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug($u->status, '_') }}">{{ ucfirst($u->status) }}</span>
                            </td>
                            <td data-label="Joined">{{ $u->created_at?->format('M j, Y') }}</td>
                            <td data-label="Actions">
                                @if($canManage && $u->id !== auth()->id() && $u->role !== 'admin')
                                    <form method="POST" action="{{ route('admin.users.update', $u) }}" class="workspace-stack" style="gap:0.4rem;">
                                        @csrf
                                        @method('PUT')
                                        <label class="workspace-field" style="margin:0;">
                                            <span style="font-size:0.75rem;">Role</span>
                                            <select name="role">
                                                @foreach(['buyer','seller','agent','staff'] as $r)
                                                    <option value="{{ $r }}" {{ $u->role === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="workspace-field" style="margin:0;">
                                            <span style="font-size:0.75rem;">Status</span>
                                            <select name="status">
                                                @foreach($statuses as $s)
                                                    <option value="{{ $s }}" {{ $u->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <button type="submit" class="button button--ghost-blue">Save</button>
                                    </form>
                                @elseif($canManage && $u->role === 'admin')
                                    <span class="workspace-property__meta">Protected account</span>
                                @elseif(! $canManage)
                                    <span class="workspace-property__meta">View only</span>
                                @else
                                    <span class="workspace-property__meta">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><div class="workspace-empty">No users match these filters.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="workspace-pagination">{{ $users->links() }}</div>
    </section>
</div>
@endsection
