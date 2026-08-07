@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Import & Sync History')
@section('dashboard_description', 'Audit trail of all lead imports and Google Sheets syncs.')

@section('dashboard_actions')
    <a href="{{ route('admin.leads.index') }}" class="button button--ghost-blue">← Lead Registry</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <div class="table-scroll">
            <table class="table" style="width:100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #cbd5e1; text-align: left;">
                        <th style="padding: 10px;">Run ID</th>
                        <th style="padding: 10px;">Source</th>
                        <th style="padding: 10px;">Triggered By</th>
                        <th style="padding: 10px;">Status</th>
                        <th style="padding: 10px;">Created</th>
                        <th style="padding: 10px;">Skipped</th>
                        <th style="padding: 10px;">Failed</th>
                        <th style="padding: 10px;">File</th>
                        <th style="padding: 10px;">Duration</th>
                        <th style="padding: 10px;">Ran At</th>
                        <th style="padding: 10px;">Warnings</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($runs as $run)
                        @php
                            $durationSeconds = $run->started_at && $run->finished_at
                                ? $run->started_at->diffInSeconds($run->finished_at)
                                : null;
                        @endphp
                        <tr style="border-bottom: 1px solid #e2e8f0; vertical-align: middle;">
                            <td style="padding: 12px 10px;">
                                <span style="font-family: monospace; font-weight: 600; color: #475569;">#{{ $run->id }}</span>
                            </td>
                            <td style="padding: 12px 10px;">
                                @if($run->source === 'google_sheets')
                                    <span class="badge" style="background: #dcfce7; color: #15803d;">Google Sheets</span>
                                @elseif($run->source === 'file_import')
                                    <span class="badge" style="background: #dbeafe; color: #1d4ed8;">File Import</span>
                                @else
                                    <span class="badge" style="background: #f1f5f9; color: #475569;">{{ ucfirst(str_replace('_', ' ', $run->source)) }}</span>
                                @endif
                            </td>
                            <td style="padding: 12px 10px;">
                                {{ $run->triggeredBy?->name ?? 'System / Auto' }}
                            </td>
                            <td style="padding: 12px 10px;">
                                @if($run->status === 'completed')
                                    <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 600;">Completed</span>
                                @elseif($run->status === 'failed')
                                    <span class="badge" style="background: #fee2e2; color: #b91c1c; font-weight: 600;">Failed</span>
                                @else
                                    <span class="badge" style="background: #fef9c3; color: #a16207;">{{ ucfirst($run->status) }}</span>
                                @endif
                            </td>
                            <td style="padding: 12px 10px; text-align: center;">
                                <strong style="color: #15803d;">{{ number_format($run->created_count) }}</strong>
                            </td>
                            <td style="padding: 12px 10px; text-align: center;">
                                <span style="color: #64748b;">{{ number_format($run->skipped_count) }}</span>
                            </td>
                            <td style="padding: 12px 10px; text-align: center;">
                                @if($run->failed_count > 0)
                                    <strong style="color: #b91c1c;">{{ number_format($run->failed_count) }}</strong>
                                @else
                                    <span style="color: #94a3b8;">0</span>
                                @endif
                            </td>
                            <td style="padding: 12px 10px; font-size: 0.8rem; color: #475569; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $run->file_name }}">
                                {{ $run->file_name ?? '—' }}
                            </td>
                            <td style="padding: 12px 10px; font-size: 0.8rem; color: #64748b;">
                                @if($durationSeconds !== null)
                                    {{ $durationSeconds < 60 ? $durationSeconds . 's' : round($durationSeconds / 60, 1) . 'm' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding: 12px 10px; font-size: 0.8rem; color: #475569;">
                                {{ $run->created_at->format('M j, Y g:i A') }}
                            </td>
                            <td style="padding: 12px 10px; font-size: 0.8rem;">
                                @if(!empty($run->warnings))
                                    <details>
                                        <summary style="cursor: pointer; color: #d97706; font-weight: 600;">{{ count($run->warnings) }} warning(s)</summary>
                                        <ul style="margin: 0.5rem 0 0; padding-left: 1.2rem; font-size: 0.75rem; color: #64748b;">
                                            @foreach(array_slice($run->warnings, 0, 10) as $warning)
                                                <li>
                                                    @if(is_array($warning))
                                                        Row {{ $warning['row'] ?? '?' }}: {{ $warning['reason'] ?? $warning['message'] ?? 'Unknown' }}
                                                        @if(!empty($warning['name'])) ({{ $warning['name'] }}) @endif
                                                    @else
                                                        {{ $warning }}
                                                    @endif
                                                </li>
                                            @endforeach
                                            @if(count($run->warnings) > 10)
                                                <li style="color: #94a3b8;">… and {{ count($run->warnings) - 10 }} more</li>
                                            @endif
                                        </ul>
                                    </details>
                                @else
                                    <span style="color: #94a3b8;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center muted" style="padding: 20px; color: #64748b;">
                                No import or sync runs recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper" style="margin-top: 1.5rem;">
            {{ $runs->links() }}
        </div>
    </section>
</div>
@endsection
