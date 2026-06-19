@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'GoHighLevel Debugging Center')
@section('dashboard_description', 'Automated health checks across connection, webhook, and database layers — with the exact cause and suggested fix for anything that is not working.')

@section('dashboard_actions')
    <a href="{{ route('admin.ghl.index') }}" class="button button--ghost-blue">Overview</a>
    <a href="{{ route('admin.ghl.logs') }}" class="button button--ghost-blue">Logs</a>
    <a href="{{ route('admin.ghl.testing') }}" class="button">Test Tools</a>
@endsection

@push('styles')
<style>
.ghl-debug-summary { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:1rem; }
.ghl-debug-summary .ghl-kpi strong { font-size:1.8rem; }
.ghl-sev { font-size:.68rem; text-transform:uppercase; letter-spacing:.04em; font-weight:700; padding:.15rem .5rem; border-radius:6px; }
.ghl-sev--high { background:rgba(239,68,68,.12); color:#b91c1c; }
.ghl-sev--medium { background:rgba(249,115,22,.12); color:#c2410c; }
.ghl-sev--low { background:rgba(34,197,94,.12); color:#15803d; }
.ghl-check { border:1px solid var(--color-border,#e5e7eb); border-radius:10px; padding:1rem 1.1rem; display:grid; gap:.4rem; }
.ghl-check--broken { border-left:4px solid #ef4444; }
.ghl-check--warning { border-left:4px solid #f97316; }
.ghl-check--working { border-left:4px solid #22c55e; }
.ghl-check__head { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; justify-content:space-between; }
.ghl-check__title { font-weight:600; color:var(--color-text,#111827); }
.ghl-check__cause { font-size:.85rem; color:var(--color-text,#374151); }
.ghl-check__fix { font-size:.83rem; color:var(--color-text-muted,#6b7280); }
.ghl-check__file { font-size:.74rem; color:var(--color-text-muted,#9ca3af); }
.ghl-doc-list { font-size:.85rem; line-height:1.6; color:var(--color-text,#374151); padding-left:1.1rem; }
.ghl-doc-list code { word-break:break-all; }
</style>
@endpush

@section('content')
<div class="workspace-stack">

    {{-- Summary --}}
    <section class="workspace-card">
        <span class="eyebrow">Diagnostics Summary</span>
        <h2>System health snapshot</h2>
        <div class="ghl-debug-summary" style="margin-top:1rem;">
            <div class="ghl-kpi" style="border-left:4px solid #ef4444;">
                <span>Broken</span>
                <strong>{{ $summary['broken'] }}</strong>
            </div>
            <div class="ghl-kpi" style="border-left:4px solid #f97316;">
                <span>Warnings</span>
                <strong>{{ $summary['warning'] }}</strong>
            </div>
            <div class="ghl-kpi" style="border-left:4px solid #22c55e;">
                <span>Working</span>
                <strong>{{ $summary['working'] }}</strong>
            </div>
        </div>
        @if($summary['broken'] === 0 && $summary['warning'] === 0)
            <p style="margin-top:1rem; color:#15803d; font-weight:600;">All checks passing — the GoHighLevel integration looks healthy.</p>
        @elseif($summary['broken'] > 0)
            <p style="margin-top:1rem; color:#b91c1c; font-weight:600;">{{ $summary['broken'] }} blocking issue(s) found. Resolve High-severity items first.</p>
        @else
            <p style="margin-top:1rem; color:#c2410c; font-weight:600;">No blocking issues, but {{ $summary['warning'] }} item(s) need attention.</p>
        @endif
    </section>

    {{-- Checks grouped by area --}}
    @foreach(collect($checks)->groupBy('area') as $area => $areaChecks)
    <section class="workspace-card">
        <span class="eyebrow">Diagnostics</span>
        <h2>{{ $area }}</h2>
        <div style="display:grid; gap:.85rem; margin-top:1rem;">
            @foreach($areaChecks as $check)
            <div class="ghl-check ghl-check--{{ $check['status'] }}">
                <div class="ghl-check__head">
                    <span class="ghl-check__title">{{ $check['label'] }}</span>
                    <span style="display:flex; gap:.5rem; align-items:center;">
                        @if($check['meta'])<span class="ghl-check__file">{{ $check['meta'] }}</span>@endif
                        <span class="ghl-sev ghl-sev--{{ $check['severity'] }}">{{ $check['severity'] }}</span>
                        <span class="workspace-pill {{ $check['status'] === 'working' ? 'workspace-pill--green' : ($check['status'] === 'warning' ? 'workspace-pill--orange' : 'workspace-pill--red') }}">
                            {{ ucfirst($check['status']) }}
                        </span>
                    </span>
                </div>
                <div class="ghl-check__cause">{{ $check['cause'] }}</div>
                @if($check['fix'])
                    <div class="ghl-check__fix"><strong>Suggested fix:</strong> {{ $check['fix'] }}</div>
                @endif
                @if($check['file'])
                    <div class="ghl-check__file"><strong>Affected:</strong> <code>{{ $check['file'] }}</code></div>
                @endif
            </div>
            @endforeach
        </div>
    </section>
    @endforeach

    {{-- Webhook endpoint documentation --}}
    <section class="workspace-card">
        <span class="eyebrow">Webhook Endpoint Documentation</span>
        <h2>Connect these URLs in GoHighLevel</h2>
        <div class="workspace-table-wrap" style="margin-top:1rem;">
            <table class="workspace-table">
                <thead><tr><th>Purpose</th><th>Endpoint URL</th><th>Method</th></tr></thead>
                <tbody>
                    <tr><td data-label="Purpose"><strong>Onboarding completed</strong></td><td data-label="Endpoint URL"><code>{{ $endpointDocs['onboarding'] }}</code></td><td data-label="Method">POST</td></tr>
                    <tr><td data-label="Purpose"><strong>Package purchased</strong></td><td data-label="Endpoint URL"><code>{{ $endpointDocs['purchase'] }}</code></td><td data-label="Method">POST</td></tr>
                    <tr><td data-label="Purpose"><strong>Lead status updated</strong></td><td data-label="Endpoint URL"><code>{{ $endpointDocs['leadStatus'] }}</code></td><td data-label="Method">POST</td></tr>
                    <tr><td data-label="Purpose"><strong>Generic events</strong></td><td data-label="Endpoint URL"><code>{{ $endpointDocs['events'] }}</code></td><td data-label="Method">POST</td></tr>
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem; display:grid; gap:.4rem;">
            <p style="font-size:.85rem; color:var(--color-text,#374151);">
                <strong>Required header:</strong>
                @if($secretEnabled)
                    <code>X-OmniReferral-Webhook: &lt;your webhook secret&gt;</code> (signature validation is <strong>enabled</strong>).
                @else
                    none currently enforced — signature validation is <strong>not enabled</strong> (set a webhook secret in Settings to enforce it).
                @endif
            </p>
            <p style="font-size:.85rem; color:var(--color-text,#374151);"><strong>Content type:</strong> <code>application/json</code>. A minimal payload must include <code>email</code> (and ideally <code>name</code>, <code>phone</code>, <code>role</code>).</p>
        </div>
    </section>

    {{-- Common setup mistakes --}}
    <section class="workspace-card">
        <span class="eyebrow">Troubleshooting</span>
        <h2>Common setup mistakes</h2>
        <ul class="ghl-doc-list" style="margin-top:1rem;">
            <li>Wrong webhook URL pasted into GoHighLevel (must exactly match the URLs above, including <code>https</code>).</li>
            <li>Private integration key or Location ID missing/incorrect under Settings.</li>
            <li>Webhook secret set on the site but not sent by GoHighLevel (or vice-versa) → 401 Unauthorized.</li>
            <li>Form/survey in GoHighLevel not wired to a workflow that POSTs to the webhook.</li>
            <li>Payload missing <code>email</code> → request rejected with 422.</li>
            <li>Duplicate contact (same email/phone) → lead creation is skipped by design to avoid duplicates.</li>
            <li>Field names from GoHighLevel don't match the configured Field Mappings.</li>
        </ul>
        <p style="margin-top:1rem; font-size:.8rem; color:var(--color-text-muted,#6b7280);">
            <strong>Note:</strong> the <code>webhook_events</code> store records the raw payload and processed state but does not currently persist a per-event <code>error_message</code> or <code>retry_count</code>. Detailed failure reasons for a specific event are written to <code>storage/logs/laravel.log</code> (search by email or contact ID).
        </p>
    </section>

</div>
@endsection
