@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Buyer Workspace')
@section('dashboard_title', 'Buyer Requests')
@section('dashboard_description', 'A dedicated request page so you can follow every buyer lead stage without scanning the overview.')

@section('dashboard_actions')
    <a href="{{ route('dashboard.buyer') }}" class="button button--ghost-blue">Back To Overview</a>
    <a href="{{ route('contact') }}" class="button">Request Support</a>
@endsection

@section('content')
<section class="workspace-card">
    <div class="workspace-table-wrap">
        <table class="workspace-table">
            <thead>
                <tr>
                    <th>Request</th>
                    <th>Market</th>
                    <th>Status</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td data-label="Request">
                            <strong>{{ $request->name }}</strong>
                            <div class="workspace-property__meta">{{ $request->email ?: 'No email provided' }}</div>
                        </td>
                        <td data-label="Market">
                            <strong>{{ $request->zip_code ?: 'No ZIP yet' }}</strong>
                            <div class="workspace-property__meta">{{ $request->property_type ?: 'Search preferences pending' }}</div>
                        </td>
                        <td data-label="Status">
                            <span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug((string) $request->status, '_') }}">
                                {{ $request->statusLabel() }}
                            </span>
                        </td>
                        <td data-label="Submitted">{{ $request->created_at?->format('M j, Y g:i A') ?: 'Pending' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="workspace-empty">No buyer requests found yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="workspace-pagination">
        {{ $requests->links() }}
    </div>
</section>
@endsection
