@extends('layouts.dashboard')

@section('dashboard_eyebrow', $isStaffView ? 'Staff Workspace' : 'Admin Workspace')
@section('dashboard_title', 'Enquiries & conversations')
@section('dashboard_description', 'Property listing enquiries with sender, listed-by routing, status, and threaded replies.')

@section('dashboard_actions')
    <a href="{{ route('admin.dashboard') }}" class="button button--ghost-blue">Overview</a>
    @if($canExport)
        <a href="{{ route('admin.enquiries.export.csv') }}" class="button">Export CSV</a>
        <a href="{{ route('admin.enquiries.export.xlsx') }}" class="button button--ghost-blue">Export Excel</a>
    @endif
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <span class="eyebrow">Filters</span>
        <h2>Refine enquiries</h2>
        <form method="GET" action="{{ route('admin.enquiries.index') }}">
            <div class="workspace-form-grid">
                <label class="workspace-field workspace-field--full">
                    <span>Search</span>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Sender name, email, subject, message, property title">
                </label>
                <label class="workspace-field">
                    <span>Property ID</span>
                    <input type="text" name="property_id" value="{{ $filters['property_id'] }}" placeholder="Numeric id">
                </label>
                <label class="workspace-field">
                    <span>User ID (sender or receiver)</span>
                    <input type="text" name="user_id" value="{{ $filters['user_id'] }}" placeholder="User id">
                </label>
                <label class="workspace-field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">Any status</option>
                        <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="replied" {{ $filters['status'] === 'replied' ? 'selected' : '' }}>Replied</option>
                        <option value="closed" {{ $filters['status'] === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </label>
                <label class="workspace-field">
                    <span>From date</span>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}">
                </label>
                <label class="workspace-field">
                    <span>To date</span>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}">
                </label>
            </div>
            <div class="workspace-actions" style="margin-top:0.75rem;">
                <button type="submit" class="button">Apply filters</button>
                <a href="{{ route('admin.enquiries.index') }}" class="button button--ghost-blue">Reset</a>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>Listed by</th>
                        <th>Property</th>
                        <th>Status</th>
                        <th>Replies</th>
                        <th>When</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enquiries as $row)
                        <tr>
                            <td data-label="From">
                                <strong>{{ $row->sender_name }}</strong>
                                <div class="workspace-property__meta">{{ $row->sender_email }}</div>
                                @if($row->sender)
                                    <div class="workspace-property__meta">Account: {{ $row->sender->name }}</div>
                                @endif
                            </td>
                            <td data-label="Listed by">
                                <strong>{{ $row->receiver?->name ?? '—' }}</strong>
                                <div class="workspace-property__meta">{{ $row->receiver?->email }}</div>
                            </td>
                            <td data-label="Property">
                                @if($row->property)
                                    {{ \Illuminate\Support\Str::limit($row->property->title, 42) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td data-label="Status">
                                <span class="workspace-pill">{{ ucfirst($row->status) }}</span>
                            </td>
                            <td data-label="Replies">{{ $row->replies_count }}</td>
                            <td data-label="When">{{ $row->created_at?->format('M j, Y g:i A') }}</td>
                            <td data-label="">
                                <a href="{{ route('admin.enquiries.show', $row) }}" class="button button--ghost-blue">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"><div class="workspace-empty">No enquiries found.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="workspace-pagination">{{ $enquiries->links() }}</div>
    </section>
</div>
@endsection
