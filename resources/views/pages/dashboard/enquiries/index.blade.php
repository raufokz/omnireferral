@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Workspace')
@section('dashboard_title', 'My enquiries')
@section('dashboard_description', 'Conversations for properties you inquired about or listings you own.')

@section('dashboard_actions')
    <a href="{{ auth()->user()->dashboardRoute() }}" class="button button--ghost-blue">Dashboard</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <span class="eyebrow">Filters</span>
        <h2>Your threads</h2>
        <form method="GET" action="{{ route('dashboard.enquiries.index') }}">
            <div class="workspace-form-grid">
                <label class="workspace-field workspace-field--full">
                    <span>Search</span>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Sender, email, or property title">
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Status</span>
                    <select name="status">
                        <option value="">Any status</option>
                        <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="replied" {{ $filters['status'] === 'replied' ? 'selected' : '' }}>Replied</option>
                        <option value="closed" {{ $filters['status'] === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </label>
            </div>
            <div class="workspace-actions" style="margin-top:0.75rem;">
                <button type="submit" class="button">Apply</button>
                <a href="{{ route('dashboard.enquiries.index') }}" class="button button--ghost-blue">Reset</a>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>With</th>
                        <th>Status</th>
                        <th>When</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enquiries as $row)
                        @php
                            $isOwner = auth()->id() === (int) $row->receiver_user_id;
                            $other = $isOwner ? $row->sender_name : ($row->receiver?->name ?? 'Owner');
                        @endphp
                        <tr>
                            <td data-label="Property">
                                <strong>{{ $row->property ? \Illuminate\Support\Str::limit($row->property->title, 48) : '—' }}</strong>
                            </td>
                            <td data-label="With">{{ $other }}</td>
                            <td data-label="Status"><span class="workspace-pill">{{ ucfirst($row->status) }}</span></td>
                            <td data-label="When">{{ $row->created_at?->format('M j, Y') }}</td>
                            <td data-label="">
                                <a href="{{ route('dashboard.enquiries.show', $row) }}" class="button button--ghost-blue">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><div class="workspace-empty">No enquiries yet.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="workspace-pagination">{{ $enquiries->links() }}</div>
    </section>
</div>
@endsection
