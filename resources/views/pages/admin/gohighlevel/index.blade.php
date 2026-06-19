@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'GoHighLevel Integration')
@section('dashboard_description', 'Monitor connection health, sync activity, and webhook delivery for the GoHighLevel CRM integration.')

@section('dashboard_actions')
    <a href="{{ route('admin.ghl.settings') }}" class="button button--ghost-blue">Settings</a>
    <a href="{{ route('admin.ghl.mappings') }}" class="button button--ghost-blue">Field Mappings</a>
    <a href="{{ route('admin.ghl.logs') }}" class="button button--ghost-blue">Logs</a>
    <a href="{{ route('admin.ghl.debug') }}" class="button button--ghost-blue">Debug</a>
    <a href="{{ route('admin.ghl.testing') }}" class="button button--ghost-blue">Test Tools</a>
    <button type="button" class="button" id="ghlTestConnectionBtn" data-url="{{ $testConnectionUrl }}">Test Connection</button>
@endsection

@push('styles')
<style>
.ghl-status-bar { display:flex; align-items:center; gap:1rem; padding:1rem 1.25rem; border-radius:10px; background:var(--color-surface-subtle,#f8fafc); border:1px solid var(--color-border,#e5e7eb); }
.ghl-status-indicator { width:12px; height:12px; border-radius:50%; flex-shrink:0; }
.ghl-status-indicator--connected { background:#22c55e; box-shadow:0 0 0 3px rgba(34,197,94,.2); }
.ghl-status-indicator--invalid   { background:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.2); }
.ghl-status-indicator--error     { background:#f97316; box-shadow:0 0 0 3px rgba(249,115,22,.2); }
.ghl-status-indicator--unknown   { background:#9ca3af; }
.ghl-kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1rem; }
.ghl-kpi { background:var(--color-surface-subtle,#f8fafc); border:1px solid var(--color-border,#e5e7eb); border-radius:10px; padding:1.1rem 1.25rem; }
.ghl-kpi span { display:block; font-size:.8rem; color:var(--color-text-muted,#6b7280); margin-bottom:.3rem; }
.ghl-kpi strong { font-size:1.6rem; font-weight:700; color:var(--color-text,#111827); }
.ghl-two-column { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:1.5rem; }
@media (max-width: 900px) {
    .ghl-two-column { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="workspace-stack">

    {{-- Connection status bar --}}
    <section class="workspace-card">
        <div class="ghl-status-bar">
            <span class="ghl-status-indicator ghl-status-indicator--{{ $settings->connection_status }}"></span>
            <div>
                <strong>{{ $settings->statusLabel() }}</strong>
                @if($settings->last_tested_at)
                    <span style="font-size:.8rem; color:var(--color-text-muted,#6b7280); margin-left:.5rem;">Last tested {{ $settings->last_tested_at->diffForHumans() }}</span>
                @endif
            </div>
            <div style="margin-left:auto; display:flex; gap:.75rem; flex-wrap:wrap;">
                @if($settings->hasCredentials())
                    <span class="workspace-pill workspace-pill--green">API Key set</span>
                    <span class="workspace-pill workspace-pill--green">Location ID set</span>
                @else
                    <span class="workspace-pill workspace-pill--red">Missing credentials</span>
                @endif
                <span class="workspace-pill">{{ ucfirst($settings->environment) }}</span>
            </div>
        </div>
        <div id="ghlTestResult" style="display:none; margin-top:1rem; padding:.75rem 1rem; border-radius:8px; font-size:.88rem;"></div>
        <div class="ghl-kpi-grid" style="margin-top:1rem;">
            <div class="ghl-kpi">
                <span>Last Webhook Received</span>
                <strong style="font-size:1rem;">{{ $stats['last_webhook_at']?->diffForHumans() ?? 'Never' }}</strong>
            </div>
            <div class="ghl-kpi">
                <span>Last Tested</span>
                <strong style="font-size:1rem;">{{ $settings->last_tested_at?->diffForHumans() ?? 'Never' }}</strong>
            </div>
            <div class="ghl-kpi">
                <span>Failed / Pending Webhooks</span>
                <strong>{{ number_format($stats['webhooks_pending']) }}</strong>
            </div>
            <div class="ghl-kpi">
                <span>Leads Synced to GHL</span>
                <strong>{{ number_format($stats['leads_ghl_synced']) }}</strong>
            </div>
        </div>
    </section>

    {{-- KPI stats --}}
    <section class="workspace-card">
        <span class="eyebrow">Integration Stats</span>
        <h2>Platform overview</h2>
        <div class="ghl-kpi-grid" style="margin-top:1rem;">
            <div class="ghl-kpi">
                <span>Total Webhooks</span>
                <strong>{{ number_format($stats['webhooks_total']) }}</strong>
            </div>
            <div class="ghl-kpi">
                <span>Processed</span>
                <strong>{{ number_format($stats['webhooks_processed']) }}</strong>
            </div>
            <div class="ghl-kpi">
                <span>Pending</span>
                <strong>{{ number_format($stats['webhooks_pending']) }}</strong>
            </div>
            <div class="ghl-kpi">
                <span>Onboarding Events</span>
                <strong>{{ number_format($stats['onboarding_total']) }}</strong>
            </div>
            <div class="ghl-kpi">
                <span>GHL-Synced Users</span>
                <strong>{{ number_format($stats['users_ghl_synced']) }}</strong>
            </div>
        </div>
    </section>

    {{-- Form URLs quick view --}}
    <section class="workspace-card">
        <span class="eyebrow">Active Form Configuration</span>
        <h2>Configured form URLs</h2>
        <div class="workspace-table-wrap" style="margin-top:1rem;">
            <table class="workspace-table">
                <thead><tr><th>Form</th><th>URL</th><th></th></tr></thead>
                <tbody>
                    @foreach([
                        'Pre-payment Survey'     => $settings->pre_payment_survey_url,
                        'Post-payment Onboarding'=> $settings->post_payment_onboarding_url,
                        'Buyer Onboarding'       => $settings->buyer_onboarding_form_url,
                        'Agent Onboarding'       => $settings->agent_onboarding_form_url,
                        'Realtor Onboarding'     => $settings->realtor_onboarding_form_url,
                    ] as $label => $url)
                    <tr>
                        <td data-label="Form"><strong>{{ $label }}</strong></td>
                        <td data-label="URL">
                            @if($url)
                                <code style="font-size:.78rem; word-break:break-all;">{{ $url }}</code>
                            @else
                                <span style="color:var(--color-text-muted,#9ca3af);">Not configured</span>
                            @endif
                        </td>
                        <td data-label="">
                            @if($url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="button button--ghost-blue" style="font-size:.8rem; padding:.3rem .75rem;">Open</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            <a href="{{ route('admin.ghl.settings') }}" class="button button--ghost-blue">Edit settings</a>
        </div>
    </section>

    {{-- Recent webhook activity --}}
    <div class="ghl-two-column">
        <section class="workspace-card">
            <span class="eyebrow">Recent Webhooks</span>
            <h2>Last 5 GHL events</h2>
            <div class="workspace-table-wrap" style="margin-top:.75rem;">
                <table class="workspace-table">
                    <thead><tr><th>Event</th><th>Status</th><th>When</th></tr></thead>
                    <tbody>
                        @forelse($recentWebhooks as $wh)
                        <tr>
                            <td data-label="Event">{{ $wh->event }}</td>
                            <td data-label="Status"><span class="workspace-pill {{ $wh->statusBadgeClass() }}">{{ $wh->statusLabel() }}</span></td>
                            <td data-label="When">{{ $wh->created_at?->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" data-label=""><div class="workspace-empty">No webhooks yet.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:.75rem;"><a href="{{ route('admin.ghl.logs') }}" class="button button--ghost-blue" style="font-size:.85rem;">View all logs</a></div>
        </section>

        <section class="workspace-card">
            <span class="eyebrow">Recent Onboarding</span>
            <h2>Last 5 submissions</h2>
            <div class="workspace-table-wrap" style="margin-top:.75rem;">
                <table class="workspace-table">
                    <thead><tr><th>User</th><th>Type</th><th>When</th></tr></thead>
                    <tbody>
                        @forelse($recentOnboarding as $log)
                        <tr>
                            <td data-label="User">{{ $log->triggered_by ?? $log->user?->email ?? '—' }}</td>
                            <td data-label="Type">{{ $log->event_type }}</td>
                            <td data-label="When">{{ $log->created_at?->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" data-label=""><div class="workspace-empty">No onboarding events yet.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:.75rem;"><a href="{{ route('admin.ghl.logs') }}#onboarding" class="button button--ghost-blue" style="font-size:.85rem;">View onboarding logs</a></div>
        </section>
    </div>

</div>

@push('scripts')
<script>
(function () {
    const btn = document.getElementById('ghlTestConnectionBtn');
    const out = document.getElementById('ghlTestResult');
    if (!btn || !out) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    btn.addEventListener('click', async function () {
        const original = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Testing…';
        out.style.display = 'block';
        out.style.background = '#f1f5f9';
        out.style.color = '#334155';
        out.textContent = 'Contacting GoHighLevel…';

        try {
            const res = await fetch(btn.dataset.url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            });
            const data = await res.json();
            const ok = data.ok === true;
            out.style.background = ok ? 'rgba(34,197,94,.12)' : 'rgba(239,68,68,.12)';
            out.style.color = ok ? '#15803d' : '#b91c1c';
            out.textContent = (ok ? '✓ ' : '✕ ') + (data.message || (ok ? 'Connection successful.' : 'Connection failed.'));
        } catch (e) {
            out.style.background = 'rgba(239,68,68,.12)';
            out.style.color = '#b91c1c';
            out.textContent = '✕ Request failed: ' + e.message;
        } finally {
            btn.disabled = false;
            btn.textContent = original;
        }
    });
})();
</script>
@endpush
@endsection
