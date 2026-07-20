@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'GHL Webhook Debugger')
@section('dashboard_description', 'Inspect every incoming GoHighLevel webhook with raw headers, payload, signature validation, and response status.')

@section('dashboard_actions')
    <a href="{{ route('admin.ghl.index') }}" class="button button--ghost-blue">Overview</a>
    <a href="{{ route('admin.ghl.logs') }}" class="button button--ghost-blue">Logs</a>
    <a href="{{ route('admin.ghl.test-panel') }}" class="button">Test Panel</a>
@endsection

@push('styles')
<style>
.wh-debug-event { border:1px solid var(--color-border,#e5e7eb); border-radius:10px; margin-bottom:1rem; overflow:hidden; }
.wh-debug-event__head { display:flex; align-items:center; gap:.75rem; padding:.75rem 1rem; background:var(--color-surface-subtle,#f8fafc); cursor:pointer; border-bottom:1px solid transparent; transition:background .15s; }
.wh-debug-event__head:hover { background:#f1f5f9; }
.wh-debug-event--expanded .wh-debug-event__head { border-bottom-color:var(--color-border,#e5e7eb); }
.wh-debug-event__meta { flex:1; min-width:0; display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
.wh-debug-event__pill { font-size:.65rem; font-weight:700; text-transform:uppercase; padding:.2rem .5rem; border-radius:5px; }
.wh-debug-event__pill--processed { background:#bbf7d0; color:#166534; }
.wh-debug-event__pill--pending { background:#fed7aa; color:#9a3412; }
.wh-debug-event__pill--invalid { background:#fecaca; color:#991b1b; }
.wh-debug-event__pill--valid { background:#bbf7d0; color:#166534; }
.wh-debug-event__pill--unverified { background:#e5e7eb; color:#6b7280; }
.wh-debug-event__chevron { font-size:.8rem; color:var(--color-text-muted,#9ca3af); transition:transform .2s; }
.wh-debug-event--expanded .wh-debug-event__chevron { transform:rotate(90deg); }
.wh-debug-event__body { display:none; padding:1rem; }
.wh-debug-event--expanded .wh-debug-event__body { display:block; }
.wh-debug-section { margin-bottom:1.25rem; }
.wh-debug-section:last-child { margin-bottom:0; }
.wh-debug-section h4 { font-size:.82rem; font-weight:600; margin-bottom:.4rem; color:var(--color-text,#374151); }
.wh-debug-section__meta { font-size:.78rem; color:var(--color-text-muted,#6b7280); display:grid; grid-template-columns:auto 1fr; gap:.25rem 1rem; }
.wh-debug-section__meta dt { font-weight:600; }
.wh-debug-section__meta dd { margin:0; }
.wh-debug-section pre { background:#1e293b; color:#e2e8f0; border-radius:8px; padding:.75rem 1rem; font-family:'SF Mono',Consolas,Monaco,monospace; font-size:.75rem; line-height:1.5; max-height:350px; overflow:auto; white-space:pre-wrap; word-break:break-all; }
.wh-debug-section pre.wh-pre--light { background:#f8fafc; color:#1e293b; border:1px solid var(--color-border,#e5e7eb); }
.wh-debug-empty { padding:3rem 2rem; text-align:center; color:var(--color-text-muted,#9ca3af); }
.wh-debug-empty strong { display:block; font-size:1.1rem; margin-bottom:.3rem; color:var(--color-text,#374151); }
</style>
@endpush

@section('content')
<div class="workspace-stack">

    <section class="workspace-card">
        <span class="eyebrow">Incoming Webhooks</span>
        <h2>GoHighLevel deliveries <span style="font-size:.9rem; font-weight:400; color:var(--color-text-muted,#6b7280);">({{ $webhooks->total() }} total)</span></h2>

        @if($webhooks->isEmpty())
            <div class="wh-debug-empty">
                <strong>No webhooks received yet</strong>
                <span>GoHighLevel has not sent any webhooks to this server. Use the Test Panel to simulate one.</span>
                <div style="margin-top:1rem;">
                    <a href="{{ route('admin.ghl.test-panel') }}" class="button button--orange">Go to Test Panel</a>
                </div>
            </div>
        @else
            <div style="margin-top:.75rem;">
                @foreach($webhooks as $wh)
                @php
                    $headers = $wh->headers ?? [];
                    $sigHeader = $headers['x-omnireferral-webhook'] ?? $headers['X-OmniReferral-Webhook'] ?? null;
                    $settings = App\Models\GhlSetting::instance();
                    $expected = $settings->webhook_secret ?: config('services.gohighlevel.webhook_secret', '');
                    $valid = $expected ? hash_equals($expected, $sigHeader) : null;
                    $ip = $wh->ip_address ?? '—';
                    $ua = $wh->user_agent ?? '—';
                    $eventId = $wh->event;
                    $status = $wh->processed_at ? 'processed' : 'pending';
                    $processTime = $wh->processed_at && $wh->created_at
                        ? $wh->created_at->diffInMilliseconds($wh->processed_at) . 'ms'
                        : '—';
                @endphp
                <div class="wh-debug-event" data-event-id="{{ $wh->id }}">
                    <div class="wh-debug-event__head" onclick="toggleEvent(this.parentElement)">
                        @if($valid === true)
                            <span class="wh-debug-event__pill wh-debug-event__pill--valid">Valid</span>
                        @elseif($valid === false)
                            <span class="wh-debug-event__pill wh-debug-event__pill--invalid">Invalid</span>
                        @else
                            <span class="wh-debug-event__pill wh-debug-event__pill--{{ $status }}">{{ $status }}</span>
                        @endif
                        <div class="wh-debug-event__meta">
                            <strong style="font-size:.88rem;">{{ $wh->event }}</strong>
                            <span style="font-size:.78rem; color:var(--color-text-muted,#6b7280);">#{{ $wh->id }}</span>
                            @if($wh->remote_id)
                                <code style="font-size:.75rem; color:var(--color-text-muted,#6b7280);">remote: {{ $wh->remote_id }}</code>
                            @endif
                            <span style="font-size:.75rem; color:var(--color-text-muted,#9ca3af);">{{ $wh->created_at?->format('M j, Y g:i A') }}</span>
                        </div>
                        <span class="wh-debug-event__chevron">▶</span>
                    </div>
                    <div class="wh-debug-event__body">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                            {{-- Left column: metadata --}}
                            <div class="wh-debug-section">
                                <h4>Request Metadata</h4>
                                <dl class="wh-debug-section__meta">
                                    <dt>Event ID</dt><dd><code>{{ $wh->id }}</code></dd>
                                    <dt>Event Type</dt><dd><code>{{ $wh->event }}</code></dd>
                                    <dt>Remote ID</dt><dd><code>{{ $wh->remote_id ?: '—' }}</code></dd>
                                    <dt>Payload Hash</dt><dd><code style="font-size:.7rem;">{{ $wh->payload_hash ?: '—' }}</code></dd>
                                    <dt>IP Address</dt><dd><code>{{ $ip }}</code></dd>
                                    <dt>User Agent</dt><dd style="word-break:break-all; font-size:.75rem;">{{ $ua }}</dd>
                                    <dt>Received</dt><dd>{{ $wh->created_at?->format('Y-m-d H:i:s T') }}</dd>
                                    <dt>Processed</dt><dd>{{ $wh->processed_at?->format('Y-m-d H:i:s T') ?: 'Not yet' }}</dd>
                                    <dt>Processing Time</dt><dd><code>{{ $processTime }}</code></dd>
                                </dl>
                            </div>

                            {{-- Right column: signature --}}
                            <div class="wh-debug-section">
                                <h4>Signature Validation</h4>
                                <dl class="wh-debug-section__meta">
                                    <dt>Header Sent</dt>
                                    <dd>
                                        @if($sigHeader)
                                            <code style="word-break:break-all;">{{ substr($sigHeader, 0, 8) . '…' . substr($sigHeader, -4) }}</code>
                                        @else
                                            <span style="color:#9ca3af;">Not sent</span>
                                        @endif
                                    </dd>
                                    <dt>Matches Secret</dt>
                                    <dd>
                                        @if($valid === true)
                                            <span class="workspace-pill workspace-pill--green">Valid ✓</span>
                                        @elseif($valid === false)
                                            <span class="workspace-pill workspace-pill--red">Invalid ✕</span>
                                        @else
                                            <span class="workspace-pill workspace-pill--orange">Not verified (no secret configured)</span>
                                        @endif
                                    </dd>
                                    <dt>Expected Secret</dt>
                                    <dd>
                                        @if($expected)
                                            <code style="word-break:break-all; font-size:.7rem;">{{ substr($expected, 0, 4) }}…{{ substr($expected, -4) }}</code>
                                        @else
                                            <span style="color:#9ca3af;">—</span>
                                        @endif
                                    </dd>
                                    <dt>Sent Secret (full)</dt>
                                    <dd>
                                        @if($sigHeader)
                                            <code style="word-break:break-all; font-size:.7rem;">{{ substr($sigHeader, 0, 4) }}…{{ substr($sigHeader, -4) }}</code>
                                        @else
                                            <span style="color:#9ca3af;">—</span>
                                        @endif
                                    </dd>
                                    <dt>Webhook Secret Config</dt><dd><code style="font-size:.72rem;">GOHIGHLEVEL_WEBHOOK_SECRET</code></dd>
                                </dl>
                            </div>
                        </div>

                        {{-- Processing controller info --}}
                        <div class="wh-debug-section" style="margin-top:.25rem;">
                            <h4>Processing Details</h4>
                            <dl class="wh-debug-section__meta" style="grid-template-columns:auto 1fr 1fr;">
                                <dt>Controller</dt><dd><code>WebhookController</code></dd>
                                <dt>Method</dt><dd><code>handleGoHighLevel</code></dd>
                                <dt>Status</dt><dd>{{ $wh->processed_at ? 'Processed' : 'Pending / Queued' }}</dd>
                                <dt>Processed At</dt><dd>{{ $wh->processed_at?->format('Y-m-d H:i:s T') ?: '—' }}</dd>
                                <dt>Duration</dt><dd><code>{{ $processTime }}</code></dd>
                            </dl>
                        </div>

                        {{-- Headers --}}
                        <div class="wh-debug-section" style="margin-top:1rem;">
                            <h4>All Headers</h4>
                            <pre class="wh-pre--light">{{ $headers ? json_encode($headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '—' }}</pre>
                        </div>

                        {{-- Payload --}}
                        <div class="wh-debug-section" style="margin-top:1rem;">
                            <h4>Payload (body)</h4>
                            <pre>{{ $wh->payload ? json_encode($wh->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '—' }}</pre>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="workspace-pagination" style="margin-top:1rem;">{{ $webhooks->links() }}</div>
        @endif
    </section>

</div>

<script>
function toggleEvent(el) {
    el.classList.toggle('wh-debug-event--expanded');
}
</script>
@endsection
