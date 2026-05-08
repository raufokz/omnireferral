@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Data exports')
@section('dashboard_description', 'Queued exports generated in the background. Use the export links with ?async=1 to queue large downloads.')

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Format</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Finished</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exports as $export)
                        <tr>
                            <td>{{ $export->id }}</td>
                            <td>{{ ucfirst($export->type) }}</td>
                            <td>{{ strtoupper($export->format) }}</td>
                            <td>
                                <span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug($export->status, '_') }}">
                                    {{ ucfirst($export->status) }}
                                </span>
                                @if($export->status === 'failed' && $export->error)
                                    <div class="workspace-property__meta">{{ \Illuminate\Support\Str::limit($export->error, 120) }}</div>
                                @endif
                            </td>
                            <td>{{ $export->created_at?->format('M j, Y H:i') }}</td>
                            <td>{{ $export->finished_at?->format('M j, Y H:i') ?? '—' }}</td>
                            <td>
                                @if($export->status === 'complete')
                                    <a class="button button--ghost-blue" href="{{ route('admin.exports.download', $export) }}">Download</a>
                                @else
                                    <span class="workspace-property__meta">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="workspace-empty">No exports have been queued yet.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="workspace-pagination">{{ $exports->links() }}</div>
    </section>
</div>
@endsection

