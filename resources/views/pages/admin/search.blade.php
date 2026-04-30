@extends('layouts.dashboard')

@section('dashboard_eyebrow', $isStaffView ? 'Staff Workspace' : 'Admin Workspace')
@section('dashboard_title', 'Platform search')
@section('dashboard_description', 'Run one query across users, property listings, and enquiry records.')

@section('dashboard_actions')
    <a href="{{ route('admin.dashboard') }}" class="button button--ghost-blue">Overview</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <form method="GET" action="{{ route('admin.search') }}" class="workspace-form-grid">
            <label class="workspace-field workspace-field--full">
                <span>Search query</span>
                <input type="text" name="q" value="{{ $query }}" placeholder="Name, email, listing title, ZIP…" autofocus>
            </label>
            <div class="workspace-actions workspace-field--full">
                <button type="submit" class="button">Search</button>
            </div>
        </form>
    </section>

    @if($query === '')
        <section class="workspace-card">
            <p style="margin:0; color:#64748b;">Enter a keyword to search across the platform.</p>
        </section>
    @else
        <section class="workspace-grid workspace-grid--3">
            <article class="workspace-card">
                <span class="eyebrow">Users</span>
                <h2>{{ $users->count() }} results</h2>
                <ul class="workspace-list">
                    @forelse($users as $u)
                        <li>
                            <strong>{{ $u->name }}</strong>
                            <small>{{ $u->email }} · {{ ucfirst($u->role) }} · {{ ucfirst($u->status) }}</small>
                            <div class="workspace-actions" style="margin-top:0.45rem;">
                                <a href="{{ route('admin.users.index', ['search' => $u->email]) }}" class="button button--ghost-blue">Open in users</a>
                            </div>
                        </li>
                    @empty
                        <li><strong>No user matches</strong></li>
                    @endforelse
                </ul>
            </article>
            <article class="workspace-card">
                <span class="eyebrow">Properties</span>
                <h2>{{ $properties->count() }} results</h2>
                <ul class="workspace-list">
                    @forelse($properties as $p)
                        <li>
                            <strong>{{ \Illuminate\Support\Str::limit($p->title, 48) }}</strong>
                            <small>{{ $p->zip_code }} · {{ $p->status }}</small>
                            <div class="workspace-actions" style="margin-top:0.45rem;">
                                <a href="{{ route('admin.properties.edit', $p) }}" class="button button--ghost-blue">Edit</a>
                                <a href="{{ route('properties.show', $p) }}" class="button button--ghost-blue" target="_blank">View</a>
                            </div>
                        </li>
                    @empty
                        <li><strong>No listing matches</strong></li>
                    @endforelse
                </ul>
            </article>
            <article class="workspace-card">
                <span class="eyebrow">Enquiries</span>
                <h2>{{ $enquiries->count() }} results</h2>
                <ul class="workspace-list">
                    @forelse($enquiries as $e)
                        <li>
                            <strong>{{ $e->sender_name }}</strong>
                            <small>{{ $e->subject ?: 'No subject' }} · {{ ucfirst($e->status) }} · {{ $e->created_at?->diffForHumans() }}</small>
                            <div class="workspace-actions" style="margin-top:0.45rem;">
                                <a href="{{ route('admin.enquiries.show', $e) }}" class="button button--ghost-blue">Open</a>
                            </div>
                        </li>
                    @empty
                        <li><strong>No enquiry matches</strong></li>
                    @endforelse
                </ul>
            </article>
        </section>
    @endif
</div>
@endsection
