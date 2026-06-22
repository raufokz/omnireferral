@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Mail Settings')
@section('dashboard_description', 'Configure SMTP or mail driver credentials and verify delivery. Only super admins may save changes.')

@section('dashboard_actions')
    <a href="{{ route('admin.email.index') }}" class="button button--ghost-blue">Email & Auth Logs</a>
@endsection

@section('content')
<div class="workspace-stack">

    @if(session('success'))
        <div class="form-alert form-alert--success" role="alert">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="form-alert form-alert--error" role="alert">{{ session('error') }}</div>
    @endif

    @unless($canEdit)
    <div class="workspace-card" style="border-left:4px solid var(--color-warning,#f59e0b);">
        <strong>View-only mode.</strong> You can read these settings but only a super admin can save changes.
    </div>
    @endunless

    <form action="{{ route('admin.mail-settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        {{-- 1. Driver & server --}}
        <section class="workspace-card">
            <span class="eyebrow">1 — Mail Driver</span>
            <h2>SMTP &amp; server credentials</h2>
            <p style="font-size:.875rem; color:var(--color-text-muted,#6b7280); margin-bottom:1.25rem;">
                Leave a field blank to keep its current value. Password is stored encrypted.
            </p>

            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Mail Driver</span>
                    <select name="mailer" {{ $canEdit ? '' : 'disabled' }}>
                        @foreach(['smtp','sendmail','ses','postmark','resend','log','mailgun'] as $driver)
                            <option value="{{ $driver }}" {{ $settings->mailer === $driver ? 'selected' : '' }}>{{ ucfirst($driver) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>SMTP Host</span>
                    <input type="text" name="host" value="{{ old('host', $settings->host) }}"
                        placeholder="e.g. smtp.sendgrid.net" {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>Port</span>
                    <input type="number" name="port" value="{{ old('port', $settings->port) }}"
                        placeholder="587" min="1" max="65535" {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>Encryption</span>
                    <select name="encryption" {{ $canEdit ? '' : 'disabled' }}>
                        <option value="null" {{ $settings->encryption === null ? 'selected' : '' }}>None</option>
                        @foreach(['tls','ssl'] as $enc)
                            <option value="{{ $enc }}" {{ $settings->encryption === $enc ? 'selected' : '' }}>{{ strtoupper($enc) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>SMTP Username</span>
                    <input type="text" name="username" value="{{ old('username', $settings->username) }}"
                        placeholder="SMTP login" {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>SMTP Password <em style="color:var(--color-text-muted,#9ca3af);">(encrypted)</em></span>
                    <input type="password" name="password" autocomplete="new-password"
                        placeholder="{{ $settings->password ? '●●●●●●●●●● (set — leave blank to keep)' : 'Enter SMTP password…' }}"
                        {{ $canEdit ? '' : 'disabled' }}>
                </label>
            </div>

            <div class="workspace-field" style="margin-top:.75rem;">
                <span>Connection Status</span>
                <div style="display:flex; align-items:center; gap:.75rem; margin-top:.35rem;">
                    <span class="workspace-pill {{ $settings->statusBadgeClass() }}">{{ $settings->statusLabel() }}</span>
                    @if($settings->last_tested_at)
                        <span style="font-size:.8rem; color:var(--color-text-muted,#6b7280);">Tested {{ $settings->last_tested_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
        </section>

        {{-- 2. From addresses --}}
        <section class="workspace-card" style="margin-top:1.5rem;">
            <span class="eyebrow">2 — From Addresses</span>
            <h2>Default sender details</h2>

            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>From Address</span>
                    <input type="email" name="from_address" value="{{ old('from_address', $settings->from_address) }}"
                        placeholder="noreply@omnireferral.com" {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>From Name</span>
                    <input type="text" name="from_name" value="{{ old('from_name', $settings->from_name) }}"
                        placeholder="OmniReferral" {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>Credentials From Address <em style="color:var(--color-text-muted,#9ca3af);">(password resets)</em></span>
                    <input type="email" name="credentials_from_address" value="{{ old('credentials_from_address', $settings->credentials_from_address) }}"
                        placeholder="noreply@omnireferral.com" {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>Credentials From Name</span>
                    <input type="text" name="credentials_from_name" value="{{ old('credentials_from_name', $settings->credentials_from_name) }}"
                        placeholder="OmniReferral" {{ $canEdit ? '' : 'disabled' }}>
                </label>
            </div>
        </section>

        @if($canEdit)
        <div class="workspace-actions" style="margin-top:1.25rem;">
            <button type="submit" class="button button--orange">Save Settings</button>
        </div>
        @endif
    </form>

    {{-- Test tools --}}
    <section class="workspace-card" style="margin-top:1.5rem;">
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
            <button type="button" class="button button--ghost-blue" onclick="testConnection()">Test Connection</button>
        </div>
        <div id="email-result" style="margin-top:.9rem;"></div>
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
    postJson('{{ route('admin.mail-settings.test') }}', { email })
        .then(d => { showResult(d.ok, d.message); if (d.ok) setTimeout(() => location.reload(), 1200); })
        .catch(e => showResult(false, 'Request failed: ' + e.message));
}
function testConnection() {
    showResult(true, 'Testing connection…');
    postJson('{{ route('admin.mail-settings.test-connection') }}', {})
        .then(d => showResult(d.ok, d.message))
        .catch(e => showResult(false, 'Request failed: ' + e.message));
}
</script>
@endsection
