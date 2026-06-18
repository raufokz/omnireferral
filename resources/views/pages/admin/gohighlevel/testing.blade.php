@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'GHL Test Tools')
@section('dashboard_description', 'Test API connectivity, send sample webhooks, validate form URLs, and retry failed syncs.')

@section('dashboard_actions')
    <a href="{{ route('admin.ghl.index') }}" class="button button--ghost-blue">Overview</a>
    <a href="{{ route('admin.ghl.logs') }}" class="button button--ghost-blue">Logs</a>
@endsection

@push('styles')
<style>
.ghl-test-panel { border:1px solid var(--color-border,#e5e7eb); border-radius:10px; padding:1.25rem; background:var(--color-surface-subtle,#f8fafc); }
.ghl-result-box { margin-top:1rem; padding:.85rem 1rem; border-radius:8px; font-family:monospace; font-size:.82rem; white-space:pre-wrap; word-break:break-all; display:none; }
.ghl-result-box--ok    { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
.ghl-result-box--error { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }
.ghl-test-spinner { display:none; width:16px; height:16px; border:2px solid currentColor; border-top-color:transparent; border-radius:50%; animation:spin .7s linear infinite; vertical-align:middle; margin-left:.4rem; }
@keyframes spin { to { transform:rotate(360deg); } }
</style>
@endpush

@section('content')
<div class="workspace-stack">

    {{-- 1. Test API connection --}}
    <section class="workspace-card">
        <span class="eyebrow">1 — Connection Test</span>
        <h2>Test API connection</h2>
        <p style="font-size:.875rem; color:var(--color-text-muted,#6b7280); margin-bottom:1rem;">
            Sends a single request to GoHighLevel using the stored API key and Location ID.
            Connection status is updated automatically.
        </p>
        <div class="ghl-test-panel">
            <div style="display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
                <code style="font-size:.8rem;">{{ route('admin.ghl.test.connection') }}</code>
                <button type="button" class="button button--orange" data-test-btn="connection">
                    Test API Connection
                    <span class="ghl-test-spinner" data-spinner="connection"></span>
                </button>
            </div>
            <div class="ghl-result-box" data-result="connection"></div>
        </div>
    </section>

    {{-- 2. Test form URLs --}}
    <section class="workspace-card">
        <span class="eyebrow">2 — Form URL Check</span>
        <h2>Verify form URLs</h2>
        @foreach([
            'Pre-payment Survey'     => $settings->pre_payment_survey_url,
            'Post-payment Onboarding'=> $settings->post_payment_onboarding_url,
            'Buyer Onboarding'       => $settings->buyer_onboarding_form_url,
            'Agent Onboarding'       => $settings->agent_onboarding_form_url,
            'Realtor Onboarding'     => $settings->realtor_onboarding_form_url,
        ] as $label => $url)
        <div style="display:flex; align-items:center; gap:.75rem; padding:.6rem 0; border-bottom:1px solid var(--color-border,#e5e7eb); flex-wrap:wrap;">
            <strong style="min-width:200px; font-size:.875rem;">{{ $label }}</strong>
            @if($url)
                <code style="font-size:.78rem; color:var(--color-text-muted,#6b7280); word-break:break-all; flex:1;">{{ $url }}</code>
                <a href="{{ $url }}" target="_blank" rel="noopener" class="button button--ghost-blue" style="font-size:.8rem; padding:.3rem .75rem;">Open Form</a>
            @else
                <span style="color:var(--color-text-muted,#9ca3af); font-size:.875rem;">Not configured</span>
                <a href="{{ route('admin.ghl.settings') }}" class="button button--ghost-blue" style="font-size:.8rem; padding:.3rem .75rem;">Configure</a>
            @endif
        </div>
        @endforeach
    </section>

    {{-- 3. Test contact sync --}}
    <section class="workspace-card">
        <span class="eyebrow">3 — Contact Sync Test</span>
        <h2>Sync a user to GoHighLevel</h2>
        <p style="font-size:.875rem; color:var(--color-text-muted,#6b7280); margin-bottom:1rem;">
            Enter a User ID to push their profile as a contact to GHL using the current API settings.
        </p>
        <div class="ghl-test-panel">
            <div style="display:flex; gap:.75rem; flex-wrap:wrap; align-items:flex-end;">
                <label class="workspace-field" style="flex:1; min-width:200px; margin:0;">
                    <span>User ID</span>
                    <input type="number" id="syncUserId" placeholder="e.g. 42" min="1">
                </label>
                <button type="button" class="button button--orange" data-test-btn="sync">
                    Test Contact Sync
                    <span class="ghl-test-spinner" data-spinner="sync"></span>
                </button>
            </div>
            <div class="ghl-result-box" data-result="sync"></div>
        </div>
    </section>

    {{-- 4. Send test webhook --}}
    <section class="workspace-card">
        <span class="eyebrow">4 — Webhook Test</span>
        <h2>Send a test webhook payload</h2>
        <p style="font-size:.875rem; color:var(--color-text-muted,#6b7280); margin-bottom:1rem;">
            Fires a sample payload to your own webhook endpoint. Uses the configured webhook secret for signing.
        </p>
        <div class="ghl-test-panel">
            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Event Type</span>
                    <select id="webhookEventType">
                        <option value="onboarding_completed">onboarding_completed</option>
                        <option value="package_purchased">package_purchased</option>
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Test Email</span>
                    <input type="email" id="webhookEmail" value="{{ auth()->user()->email }}" placeholder="test@example.com">
                </label>
                <label class="workspace-field">
                    <span>Role</span>
                    <select id="webhookRole">
                        <option value="agent">agent</option>
                        <option value="buyer">buyer</option>
                        <option value="seller">seller</option>
                    </select>
                </label>
            </div>
            <div style="margin-top:.75rem; display:flex; gap:.75rem; flex-wrap:wrap; align-items:center;">
                <code style="font-size:.78rem; color:var(--color-text-muted,#6b7280);" id="webhookTargetUrl">{{ $webhookUrl }}</code>
                <button type="button" class="button button--orange" data-test-btn="webhook">
                    Send Test Webhook
                    <span class="ghl-test-spinner" data-spinner="webhook"></span>
                </button>
            </div>
            <div class="ghl-result-box" data-result="webhook"></div>
        </div>
    </section>

    {{-- 5. Webhook endpoint info --}}
    <section class="workspace-card">
        <span class="eyebrow">5 — Endpoint Reference</span>
        <h2>Webhook URLs to configure in GoHighLevel</h2>
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead><tr><th>Endpoint</th><th>URL</th><th>Secret Header</th></tr></thead>
                <tbody>
                    <tr>
                        <td><strong>Onboarding Completed</strong></td>
                        <td><code style="font-size:.78rem;">{{ route('webhooks.gohighlevel.onboarding') }}</code></td>
                        <td><code style="font-size:.78rem;">X-OmniReferral-Webhook</code></td>
                    </tr>
                    <tr>
                        <td><strong>Package Purchased</strong></td>
                        <td><code style="font-size:.78rem;">{{ route('webhooks.gohighlevel.purchase') }}</code></td>
                        <td><code style="font-size:.78rem;">X-OmniReferral-Webhook</code></td>
                    </tr>
                    <tr>
                        <td><strong>Lead Status Updated</strong></td>
                        <td><code style="font-size:.78rem;">{{ route('webhooks.gohighlevel.lead-status') }}</code></td>
                        <td><code style="font-size:.78rem;">X-OmniReferral-Webhook</code></td>
                    </tr>
                    <tr>
                        <td><strong>General Events</strong></td>
                        <td><code style="font-size:.78rem;">{{ route('webhooks.gohighlevel.events') }}</code></td>
                        <td><code style="font-size:.78rem;">X-OmniReferral-Webhook</code></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p style="font-size:.8rem; color:var(--color-text-muted,#6b7280); margin-top:.75rem;">
            Webhook secret is stored encrypted. Configure the same value as <code>X-OmniReferral-Webhook</code> in GoHighLevel's automation webhook settings.
        </p>
    </section>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    async function post(url, body) {
        const r = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });
        return r.json();
    }

    function showResult(key, data) {
        const box = document.querySelector(`[data-result="${key}"]`);
        if (! box) return;
        box.style.display = 'block';
        box.className = `ghl-result-box ghl-result-box--${data.ok ? 'ok' : 'error'}`;
        box.textContent = JSON.stringify(data, null, 2);
    }

    function toggleSpinner(key, show) {
        const el = document.querySelector(`[data-spinner="${key}"]`);
        if (el) el.style.display = show ? 'inline-block' : 'none';
    }

    // Test connection
    document.querySelector('[data-test-btn="connection"]')?.addEventListener('click', async () => {
        toggleSpinner('connection', true);
        const data = await post('{{ route('admin.ghl.test.connection') }}', {});
        toggleSpinner('connection', false);
        showResult('connection', data);
    });

    // Test sync
    document.querySelector('[data-test-btn="sync"]')?.addEventListener('click', async () => {
        const userId = document.getElementById('syncUserId')?.value;
        if (! userId) { alert('Please enter a User ID.'); return; }
        toggleSpinner('sync', true);
        const data = await post('{{ route('admin.ghl.test.sync') }}', { user_id: userId });
        toggleSpinner('sync', false);
        showResult('sync', data);
    });

    // Test webhook
    document.querySelector('[data-test-btn="webhook"]')?.addEventListener('click', async () => {
        const eventType = document.getElementById('webhookEventType')?.value;
        const email     = document.getElementById('webhookEmail')?.value;
        const role      = document.getElementById('webhookRole')?.value;
        toggleSpinner('webhook', true);
        const data = await post('{{ route('admin.ghl.test.webhook') }}', { event_type: eventType, email, role });
        toggleSpinner('webhook', false);
        showResult('webhook', data);
    });

    // Update displayed target URL on event type change
    document.getElementById('webhookEventType')?.addEventListener('change', function () {
        const urlEl = document.getElementById('webhookTargetUrl');
        if (! urlEl) return;
        urlEl.textContent = this.value === 'package_purchased'
            ? '{{ $purchaseWebhookUrl }}'
            : '{{ $webhookUrl }}';
    });
})();
</script>
@endpush
