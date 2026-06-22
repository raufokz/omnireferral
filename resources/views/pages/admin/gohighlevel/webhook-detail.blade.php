@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Webhook Event #'.$event->id)
@section('dashboard_description', 'Full payload and metadata for this GoHighLevel webhook event.')

@section('dashboard_actions')
    <a href="{{ route('admin.ghl.logs') }}" class="button button--ghost-blue">&larr; Back to Logs</a>
    @if(! $event->processed_at)
    <form action="{{ route('admin.ghl.retry', $event->id) }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="button" onclick="return confirm('Retry processing this webhook?')">Retry</button>
    </form>
    @endif
@endsection

@section('content')
<div class="workspace-stack">

    {{-- Summary --}}
    <section class="workspace-card">
        <span class="eyebrow">Event Summary</span>
        <h2>Webhook #{{ $event->id }}</h2>
        <div class="workspace-table-wrap" style="margin-top:.75rem;">
            <table class="workspace-table">
                <tbody>
                    <tr><td><strong>Event Type</strong></td><td><code>{{ $event->event }}</code></td></tr>
                    <tr><td><strong>Remote ID</strong></td><td><code>{{ $event->remote_id ?: '—' }}</code></td></tr>
                    <tr><td><strong>Status</strong></td>
                        <td>
                            <span class="workspace-pill {{ $event->statusBadgeClass() }}">{{ $event->statusLabel() }}</span>
                            @if($event->processed_at)
                                <span style="font-size:.8rem; color:var(--color-text-muted,#6b7280); margin-left:.5rem;">{{ $event->processed_at->format('M j, Y g:i A') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr><td><strong>Received</strong></td><td>{{ $event->created_at?->format('M j, Y g:i:s A') }}</td></tr>
                    <tr><td><strong>IP Address</strong></td><td><code>{{ $event->ip_address ?: '—' }}</code></td></tr>
                    <tr><td><strong>User Agent</strong></td><td><code style="font-size:.75rem; word-break:break-all;">{{ $event->user_agent ?: '—' }}</code></td></tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- Headers --}}
    <section class="workspace-card">
        <span class="eyebrow">Headers</span>
        <h2>Incoming request headers</h2>
        <div class="workspace-table-wrap" style="margin-top:.75rem;">
            <table class="workspace-table">
                <thead><tr><th>Header</th><th>Value</th></tr></thead>
                <tbody>
                    @forelse(($event->headers ?? []) as $header => $value)
                    <tr>
                        <td><code>{{ $header }}</code></td>
                        <td><code style="font-size:.8rem; word-break:break-all;">{{ is_string($value) ? $value : json_encode($value) }}</code></td>
                    </tr>
                    @empty
                    <tr><td colspan="2"><div class="workspace-empty">No headers recorded.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Payload --}}
    <section class="workspace-card">
        <span class="eyebrow">Payload</span>
        <h2>Raw webhook payload</h2>
        <div style="margin-top:.75rem; background:#1e293b; color:#e2e8f0; border-radius:8px; padding:1rem; overflow-x:auto; font-family:'SF Mono','Fira Code','Courier New',monospace; font-size:.8rem; line-height:1.6; max-height:600px; overflow-y:auto;">
            <pre style="margin:0; white-space:pre-wrap; word-break:break-word;">{{ json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </section>

    {{-- Validation --}}
    <section class="workspace-card">
        <span class="eyebrow">Validation</span>
        <h2>Payload validation results</h2>
        <div class="workspace-table-wrap" style="margin-top:.75rem;">
            <table class="workspace-table">
                <tbody>
                    @php
                        $payload = $event->payload ?? [];
                        $hasEmail = filled($payload['email'] ?? data_get($payload, 'contact.email'));
                        $hasName = filled($payload['name'] ?? data_get($payload, 'contact.name'));
                        $hasEvent = filled($payload['event_type'] ?? $payload['event'] ?? $payload['type'] ?? null);
                        $payloadStructure = is_array($payload) ? array_keys($payload) : [];
                    @endphp
                    <tr>
                        <td>Email field</td>
                        <td>@if($hasEmail)<span class="workspace-pill workspace-pill--green">Present</span>@else<span class="workspace-pill workspace-pill--red">Missing</span>@endif</td>
                        <td style="color:var(--color-text-muted,#6b7280); font-size:.85rem;">{{ $payload['email'] ?? data_get($payload, 'contact.email', '—') }}</td>
                    </tr>
                    <tr>
                        <td>Name field</td>
                        <td>@if($hasName)<span class="workspace-pill workspace-pill--green">Present</span>@else<span class="workspace-pill workspace-pill--orange">Missing</span>@endif</td>
                        <td style="font-size:.85rem;">{{ $payload['name'] ?? data_get($payload, 'contact.name', '—') }}</td>
                    </tr>
                    <tr>
                        <td>Event type</td>
                        <td>@if($hasEvent)<span class="workspace-pill workspace-pill--green">Present</span>@else<span class="workspace-pill workspace-pill--red">Missing</span>@endif</td>
                        <td><code>{{ $payload['event_type'] ?? $payload['event'] ?? $payload['type'] ?? '—' }}</code></td>
                    </tr>
                    <tr>
                        <td>Top-level keys</td>
                        <td colspan="2"><code style="font-size:.8rem;">{{ implode(', ', $payloadStructure) ?: '—' }}</code></td>
                    </tr>
                    <tr>
                        <td>Processed</td>
                        <td>@if($event->processed_at)<span class="workspace-pill workspace-pill--green">Yes</span>@else<span class="workspace-pill workspace-pill--orange">No</span>@endif</td>
                        <td style="font-size:.85rem;">{{ $event->processed_at ? $event->processed_at->format('M j, Y g:i A') : 'Not yet processed' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection
