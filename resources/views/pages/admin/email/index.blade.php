@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Email & Auth Diagnostics')
@section('dashboard_description', 'Verify outgoing mail, run a test send, and review email delivery + authentication activity.')

@section('content')
<div class="workspace-stack">

    {{-- Mail configuration --}}
    <section class="workspace-card">
        <span class="eyebrow">Mail Configuration</span>
        <h2>Current outgoing mail settings</h2>
        <p style="color:var(--color-text-muted,#6b7280); font-size:.9rem;">
            SMTP credentials are managed in <code>.env</code> (not editable here, by design). After changing
            <code>.env</code>, run <code>php artisan config:clear</code>.
        </p>
        <div class="workspace-table-wrap" style="margin-top:.75rem;">
            <table class="workspace-table">
                <tbody>
                    <tr><td data-label="Driver"><strong>Driver</strong></td><td><code>{{ $config['driver'] }}</code>
                        @if($config['driver'] === 'log')<span class="workspace-pill workspace-pill--orange" style="font-size:.7rem;">log only — not delivering real email</span>@endif
                    </td></tr>
                    <tr><td><strong>Host</strong></td><td>{{ $config['host'] ?: '—' }}</td></tr>
                    <tr><td><strong>Port</strong></td><td>{{ $config['port'] ?: '—' }}</td></tr>
                    <tr><td><strong>Encryption</strong></td><td>{{ $config['encryption'] ?: '—' }}</td></tr>
                    <tr><td><strong>Username</strong></td><td>{{ $config['username'] ?: '—' }}</td></tr>
                    <tr><td><strong>Password</strong></td><td>{{ $config['password_set'] ? '•••••••• (set)' : 'not set' }}</td></tr>
                    <tr><td><strong>From</strong></td><td>{{ $config['from_name'] }} &lt;{{ $config['from_address'] }}&gt;</td></tr>
                    <tr><td><strong>Queue</strong></td><td><code>{{ $config['queue'] }}</code>
                        @if($config['queue'] !== 'sync')<span class="workspace-pill workspace-pill--orange" style="font-size:.7rem;">queued mail needs a running worker (php artisan queue:work)</span>@endif
                    </td></tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- Test tools --}}
    <section class="workspace-card">
        <span class="eyebrow">Test Tools</span>
        <h2>Verify delivery</h2>
        <div class="workspace-form-grid" style="margin-top:.75rem;">
            <label class="workspace-field" style="grid-column: span 2;">
                <span>Send a test email to</span>
                <input type="email" id="test-email" placeholder="you@example.com" value="{{ auth()->user()->email }}">
            </label>
        </div>
        <div class="workspace-actions" style="margin-top:.75rem; gap:.5rem; display:flex; flex-wrap:wrap;">
            <button type="button" class="button" onclick="sendTestEmail()">Send Test Email</button>
            <button type="button" class="button button--ghost-blue" onclick="testSmtp()">Test Connection</button>
        </div>
        <div id="email-result" style="margin-top:.9rem;"></div>
    </section>

    {{-- Status summary --}}
    <section class="workspace-card">
        <span class="eyebrow">Status</span>
        <h2>Delivery summary</h2>
        <div class="workspace-form-grid" style="margin-top:.75rem;">
            <div class="workspace-field"><span>Sent (7 days)</span><strong style="font-size:1.4rem;">{{ $stats['sent_7d'] }}</strong></div>
            <div class="workspace-field"><span>Failed (7 days)</span><strong style="font-size:1.4rem; color:{{ $stats['failed_7d'] ? '#b91c1c' : 'inherit' }};">{{ $stats['failed_7d'] }}</strong></div>
            <div class="workspace-field"><span>Failed logins (24h)</span><strong style="font-size:1.4rem;">{{ $stats['login_fail_24h'] }}</strong></div>
        </div>
        <div style="margin-top:.75rem; font-size:.85rem; color:var(--color-text-muted,#6b7280);">
            <p><strong>Last sent:</strong>
                @if($stats['last_sent']) {{ $stats['last_sent']->subject ?: $stats['last_sent']->event_type }} → {{ $stats['last_sent']->email }} ({{ $stats['last_sent']->created_at->diffForHumans() }}) @else none yet @endif
            </p>
            <p><strong>Last error:</strong>
                @if($stats['last_failed']) <span style="color:#b91c1c;">{{ $stats['last_failed']->email }} — {{ \Illuminate\Support\Str::limit($stats['last_failed']->error_message, 120) }} ({{ $stats['last_failed']->created_at->diffForHumans() }})</span> @else none @endif
            </p>
        </div>
    </section>

    {{-- Email logs --}}
    <section class="workspace-card">
        <span class="eyebrow">Email Logs</span>
        <h2>Recent email activity</h2>
        <div class="workspace-table-wrap" style="margin-top:.75rem;">
            <table class="workspace-table">
                <thead><tr><th>When</th><th>To</th><th>Subject / Type</th><th>Status</th><th>Error</th></tr></thead>
                <tbody>
                    @forelse($emailLogs as $log)
                    <tr>
                        <td data-label="When">{{ $log->created_at?->format('M j, g:i A') }}</td>
                        <td data-label="To">{{ $log->email ?: '—' }}</td>
                        <td data-label="Subject">{{ $log->subject ?: $log->event_type }}</td>
                        <td data-label="Status"><span class="workspace-pill {{ $log->status === 'sent' ? 'workspace-pill--green' : 'workspace-pill--red' }}">{{ ucfirst($log->status) }}</span></td>
                        <td data-label="Error">@if($log->error_message)<span style="color:#b91c1c; font-size:.75rem;" title="{{ $log->error_message }}">{{ \Illuminate\Support\Str::limit($log->error_message, 50) }}</span>@else—@endif</td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><div class="workspace-empty">No email logs yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Auth logs --}}
    <section class="workspace-card">
        <span class="eyebrow">Authentication Logs</span>
        <h2>Recent login & password activity</h2>
        <div class="workspace-table-wrap" style="margin-top:.75rem;">
            <table class="workspace-table">
                <thead><tr><th>When</th><th>Event</th><th>Email</th><th>Status</th><th>IP</th></tr></thead>
                <tbody>
                    @forelse($authLogs as $log)
                    <tr>
                        <td data-label="When">{{ $log->created_at?->format('M j, g:i A') }}</td>
                        <td data-label="Event"><code>{{ $log->event }}</code></td>
                        <td data-label="Email">{{ $log->email ?: '—' }}</td>
                        <td data-label="Status">
                            <span class="workspace-pill {{ $log->status === 'success' ? 'workspace-pill--green' : ($log->status === 'failure' ? 'workspace-pill--red' : '') }}">{{ ucfirst($log->status) }}</span>
                        </td>
                        <td data-label="IP">{{ $log->ip_address ?: '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><div class="workspace-empty">No auth logs yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>

<script>
function postJson(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify(body || {}),
    }).then(r => r.json());
}
function showResult(ok, msg) {
    const el = document.getElementById('email-result');
    el.innerHTML = '<div class="workspace-pill ' + (ok ? 'workspace-pill--green' : 'workspace-pill--red') + '" style="display:inline-block; padding:.5rem .9rem;">' + msg + '</div>';
}
function sendTestEmail() {
    const email = document.getElementById('test-email').value;
    if (!email) { showResult(false, 'Enter an email address first.'); return; }
    showResult(true, 'Sending…');
    postJson('{{ route('admin.email.test') }}', { email })
        .then(d => { showResult(d.ok, d.message); if (d.ok) setTimeout(() => location.reload(), 1200); })
        .catch(e => showResult(false, 'Request failed: ' + e.message));
}
function testSmtp() {
    showResult(true, 'Testing connection…');
    postJson('{{ route('admin.email.smtp-test') }}', {})
        .then(d => showResult(d.ok, d.message))
        .catch(e => showResult(false, 'Request failed: ' + e.message));
}
</script>
@endsection
