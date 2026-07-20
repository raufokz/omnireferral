@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'GHL Onboarding Test Panel')
@section('dashboard_description', 'End-to-end production diagnostic tool. Uses real GoHighLevel API and the exact same onboarding pipeline as live submissions.')

@section('dashboard_actions')
    <a href="{{ route('admin.ghl.index') }}" class="button button--ghost-blue">Overview</a>
    <a href="{{ route('admin.ghl.logs') }}" class="button button--ghost-blue">Logs</a>
    <a href="{{ route('admin.ghl.test.history') }}" class="button">Test History</a>
    <a href="{{ route('admin.ghl.webhook-debugger') }}" class="button button--ghost-blue">Webhook Debugger</a>
@endsection

@push('styles')
<style>
.ghl-panel-layout { display:grid; grid-template-columns:1.15fr 0.85fr; gap:1.5rem; align-items:start; }
.ghl-form-col { min-width:0; }
.ghl-live-col { position:sticky; top:84px; }
.ghl-summary-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); gap:.6rem; margin-top:.75rem; }
.ghl-summary-grid .ghl-kpi { background:var(--color-surface-subtle,#f8fafc); border:1px solid var(--color-border,#e5e7eb); border-radius:8px; padding:.7rem .85rem; }
.ghl-summary-grid .ghl-kpi span { display:block; font-size:.7rem; color:var(--color-text-muted,#6b7280); text-transform:uppercase; letter-spacing:.03em; }
.ghl-summary-grid .ghl-kpi strong { font-size:1.15rem; font-weight:700; color:var(--color-text,#111827); }
.ghl-workflow { display:flex; flex-direction:column; gap:.35rem; margin-top:.75rem; }
.ghl-wf-step { display:flex; align-items:center; gap:.6rem; padding:.55rem .75rem; border-radius:7px; border:1px solid var(--color-border,#e5e7eb); background:var(--color-surface,#fff); transition:all .2s; font-size:.85rem; }
.ghl-wf-step--running { border-left:4px solid #f97316; background:#fff7ed; }
.ghl-wf-step--completed { border-left:4px solid #22c55e; background:#f0fdf4; }
.ghl-wf-step--failed { border-left:4px solid #ef4444; background:#fef2f2; }
.ghl-wf-step--skipped { border-left:4px solid #9ca3af; opacity:.6; }
.ghl-wf-step--pending { border-left:4px solid #d1d5db; opacity:.5; }
.ghl-wf-step__icon { font-size:1rem; width:24px; text-align:center; flex-shrink:0; }
.ghl-wf-step__info { flex:1; min-width:0; }
.ghl-wf-step__label { font-weight:600; font-size:.83rem; color:var(--color-text,#111827); }
.ghl-wf-step__meta { font-size:.7rem; color:var(--color-text-muted,#6b7280); display:flex; gap:.5rem; flex-wrap:wrap; }
.ghl-wf-step__duration { font-size:.65rem; color:var(--color-text-muted,#9ca3af); background:#f1f5f9; padding:.1rem .4rem; border-radius:3px; }
.ghl-wf-step__error { font-size:.75rem; color:#b91c1c; margin-top:1px; }
.ghl-wf-step__retry { font-size:.68rem; padding:.15rem .5rem; border-radius:4px; cursor:pointer; flex-shrink:0; }
.ghl-wf-step__pill { font-size:.62rem; text-transform:uppercase; font-weight:700; padding:.15rem .45rem; border-radius:4px; flex-shrink:0; }
.ghl-wf-step__pill--running { background:#fed7aa; color:#9a3412; }
.ghl-wf-step__pill--completed { background:#bbf7d0; color:#166534; }
.ghl-wf-step__pill--failed { background:#fecaca; color:#991b1b; }
.ghl-wf-step__pill--skipped { background:#e5e7eb; color:#6b7280; }
.ghl-wf-step__pill--pending { background:#f3f4f6; color:#9ca3af; }
.ghl-json-box { background:#1e293b; color:#e2e8f0; border-radius:7px; padding:.7rem .9rem; font-family:'SF Mono',Consolas,Monaco,monospace; font-size:.73rem; line-height:1.5; max-height:350px; overflow:auto; white-space:pre-wrap; word-break:break-all; margin-top:.35rem; position:relative; }
.ghl-json-box--compact { max-height:180px; }
.ghl-json-box-error { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:7px; padding:.7rem .9rem; font-family:monospace; font-size:.75rem; white-space:pre-wrap; margin-top:.35rem; }
.ghl-copy-btn { position:absolute; top:6px; right:6px; font-size:.65rem; padding:.15rem .5rem; border-radius:4px; background:rgba(255,255,255,.1); color:#94a3b8; border:1px solid rgba(255,255,255,.15); cursor:pointer; }
.ghl-copy-btn:hover { background:rgba(255,255,255,.2); color:#e2e8f0; }
.ghl-empty-state { padding:2rem; text-align:center; color:var(--color-text-muted,#9ca3af); }
.ghl-empty-state strong { display:block; font-size:1.05rem; margin-bottom:.25rem; color:var(--color-text,#374151); }
.ghl-recent-list { display:grid; gap:.4rem; margin-top:.6rem; }
.ghl-recent-item { display:flex; align-items:center; gap:.5rem; padding:.4rem .65rem; border-radius:6px; border:1px solid var(--color-border,#e5e7eb); font-size:.8rem; cursor:pointer; transition:background .15s; }
.ghl-recent-item:hover { background:var(--color-surface-subtle,#f8fafc); }
.ghl-recent-item__pill { font-size:.6rem; font-weight:700; text-transform:uppercase; padding:.12rem .35rem; border-radius:3px; flex-shrink:0; }
.ghl-recent-item__pill--completed { background:#bbf7d0; color:#166534; }
.ghl-recent-item__pill--failed { background:#fecaca; color:#991b1b; }
.ghl-recent-item__pill--processing { background:#fed7aa; color:#9a3412; }
.ghl-spinner { display:inline-block; width:13px; height:13px; border:2px solid currentColor; border-top-color:transparent; border-radius:50%; animation:ghl-spin .7s linear infinite; vertical-align:middle; margin-right:.35rem; }
@keyframes ghl-spin { to { transform:rotate(360deg); } }
.ghl-detail-tabs { display:flex; gap:.25rem; flex-wrap:wrap; border-bottom:1px solid var(--color-border,#e5e7eb); padding-bottom:.4rem; margin-bottom:.75rem; }
.ghl-detail-tab { font-size:.78rem; padding:.3rem .7rem; border-radius:5px; border:1px solid transparent; cursor:pointer; color:var(--color-text-muted,#6b7280); transition:all .15s; }
.ghl-detail-tab:hover { border-color:var(--color-border,#e5e7eb); background:var(--color-surface-subtle,#f8fafc); }
.ghl-detail-tab.is-active { border-color:var(--color-orange,#f97316); background:rgba(249,115,22,.08); color:#c2410c; font-weight:600; }
.ghl-section-card { border:1px solid var(--color-border,#e5e7eb); border-radius:8px; padding:.85rem 1rem; margin-bottom:.75rem; background:var(--color-surface,#fff); }
.ghl-section-card h4 { font-size:.82rem; font-weight:600; margin-bottom:.4rem; color:var(--color-text,#374151); }
.ghl-section-card .ghl-meta-grid { display:grid; grid-template-columns:auto 1fr; gap:.2rem 1rem; font-size:.78rem; }
.ghl-section-card .ghl-meta-grid dt { font-weight:600; color:var(--color-text-muted,#6b7280); }
.ghl-section-card .ghl-meta-grid dd { margin:0; color:var(--color-text,#111827); word-break:break-all; }
.ghl-masked { color:#94a3b8; font-family:monospace; font-size:.75rem; }
.ghl-badge { display:inline-block; font-size:.65rem; font-weight:700; text-transform:uppercase; padding:.15rem .5rem; border-radius:4px; }
.ghl-badge--green { background:#bbf7d0; color:#166534; }
.ghl-badge--red { background:#fecaca; color:#991b1b; }
.ghl-badge--orange { background:#fed7aa; color:#9a3412; }
.ghl-badge--grey { background:#e5e7eb; color:#6b7280; }
.ghl-badge--blue { background:#dbeafe; color:#1e40af; }
@media (max-width:1100px){ .ghl-panel-layout { grid-template-columns:1fr; } .ghl-live-col { position:relative; top:auto; } }
</style>
@endpush

@section('content')
<div class="workspace-stack">

    {{-- GHL Connection Status Bar --}}
    @if($ghlConfigured)
    <div style="display:flex; align-items:center; gap:.75rem; padding:.65rem 1rem; border-radius:8px; background:#f0fdf4; border:1px solid #bbf7d0; font-size:.85rem;">
        <span style="width:10px;height:10px;border-radius:50%;background:#22c55e;flex-shrink:0;"></span>
        <span><strong>GoHighLevel configured</strong> — Location: <code>{{ $locationId }}</code>, Key: <code class="ghl-masked">{{ $maskedApiKey }}</code></span>
        <span style="margin-left:auto; font-size:.75rem; color:#6b7280;">Env: <strong>{{ $environment }}</strong></span>
    </div>
    @else
    <div style="display:flex; align-items:center; gap:.75rem; padding:.65rem 1rem; border-radius:8px; background:#fef2f2; border:1px solid #fecaca; font-size:.85rem;">
        <span style="width:10px;height:10px;border-radius:50%;background:#ef4444;flex-shrink:0;"></span>
        <span><strong>GoHighLevel not configured</strong> — tests will use mock data only</span>
        <a href="{{ route('admin.ghl.settings') }}" class="button button--ghost-blue" style="margin-left:auto; font-size:.78rem;">Configure</a>
    </div>
    @endif

    {{-- Recent tests --}}
    <section class="workspace-card">
        <span class="eyebrow">Quick Access</span>
        <h2>Recent test submissions</h2>
        @if($recentTests->isEmpty())
            <div class="ghl-empty-state"><strong>No tests yet</strong><span>Submit your first onboarding test below.</span></div>
        @else
            <div class="ghl-recent-list">
                @foreach($recentTests as $rt)
                <div class="ghl-recent-item" onclick="loadTest({{ $rt->id }})">
                    <span class="ghl-recent-item__pill ghl-recent-item__pill--{{ $rt->status }}">{{ $rt->status }}</span>
                    <strong style="flex:1;">{{ $rt->email }}</strong>
                    <span style="color:var(--color-text-muted,#6b7280);font-size:.75rem;">{{ $rt->role }}</span>
                    <span style="color:var(--color-text-muted,#9ca3af);font-size:.7rem;">#{{ $rt->id }} · {{ $rt->created_at?->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
            <div style="margin-top:.5rem;"><a href="{{ route('admin.ghl.test.history') }}" class="button button--ghost-blue" style="font-size:.8rem;">View full history →</a></div>
        @endif
    </section>

    {{-- Main panel --}}
    <div class="ghl-panel-layout">
        {{-- FORM COLUMN --}}
        <div class="ghl-form-col">
            <section class="workspace-card">
                <span class="eyebrow">Step 1 — Submit Test Onboarding</span>
                <h2>Onboarding form</h2>
                <p style="font-size:.85rem;color:var(--color-text-muted,#6b7280);margin-bottom:1rem;">
                    @if($ghlConfigured && $ghlFormUrl)
                        GoHighLevel is configured. The real onboarding form will be used.
                    @else
                        No GHL form URL configured. A mock form is shown below.
                    @endif
                    Submission triggers the full production pipeline.
                </p>

                <form id="ghl-test-form" novalidate>
                    @csrf
                    <input type="hidden" name="intent" id="test-intent" value="agent">
                    <input type="hidden" name="form_method" id="test-form-method" value="{{ $ghlConfigured && $ghlFormUrl ? 'real_form' : 'mock_form' }}">

                    <div class="workspace-form-grid">
                        <label class="workspace-field">
                            <span>Lead type</span>
                            <select id="test-role-select">
                                <option value="agent">Agent / Realtor</option>
                                <option value="buyer">Buyer</option>
                                <option value="seller">Seller</option>
                            </select>
                        </label>
                        <label class="workspace-field">
                            <span>Full name *</span>
                            <input type="text" id="test-name" placeholder="Taylor Morgan" required>
                        </label>
                        <label class="workspace-field">
                            <span>Email address *</span>
                            <input type="email" id="test-email" placeholder="test@example.com" required value="{{ auth()->user()->email }}">
                        </label>
                        <label class="workspace-field">
                            <span>Phone number</span>
                            <input type="tel" id="test-phone" placeholder="(555) 123-4567">
                        </label>
                        <label class="workspace-field">
                            <span>City</span>
                            <input type="text" id="test-city" placeholder="Dallas">
                        </label>
                        <label class="workspace-field">
                            <span>State</span>
                            <input type="text" id="test-state" placeholder="TX" maxlength="2">
                        </label>
                        <label class="workspace-field">
                            <span>ZIP code</span>
                            <input type="text" id="test-zip" placeholder="75201" maxlength="10">
                        </label>
                        <label class="workspace-field" id="test-brokerage-field">
                            <span>Brokerage name</span>
                            <input type="text" id="test-brokerage" placeholder="OmniReferral Test Partner">
                        </label>
                    </div>

                    @if($ghlConfigured && $ghlFormUrl)
                    <div style="margin-top:1rem; padding:.75rem; border-radius:7px; border:1px solid #bbf7d0; background:#f0fdf4; font-size:.8rem;">
                        <strong>Real GHL form available</strong> — <code style="font-size:.72rem;">{{ $ghlFormUrl }}</code>
                    </div>
                    @endif

                    <div class="workspace-actions" style="margin-top:1.25rem;">
                        <button type="submit" class="button button--orange" id="ghl-test-submit-btn">
                            <span id="ghl-submit-text">Submit Test Onboarding</span>
                        </button>
                        <span style="font-size:.78rem;color:var(--color-text-muted,#6b7280);margin-left:.75rem;">
                            Creates a real user, profile, and onboarding log.
                        </span>
                    </div>
                </form>
            </section>
        </div>

        {{-- LIVE LOG COLUMN --}}
        <div class="ghl-live-col">
            <section class="workspace-card">
                <span class="eyebrow">Live Event Log</span>
                <h2>Execution timeline <span id="ghl-test-id-label" style="font-weight:400;font-size:.8rem;color:var(--color-text-muted,#6b7280);"></span></h2>

                <div id="ghl-workflow-container">
                    <div class="ghl-empty-state"><strong>Awaiting submission</strong><span>Submit the form to see the live execution timeline with per-stage timing.</span></div>
                </div>

                <div class="ghl-summary-grid" id="ghl-test-summary" style="display:none;">
                    <div class="ghl-kpi"><span>Status</span><strong id="kpi-status">—</strong></div>
                    <div class="ghl-kpi"><span>User ID</span><strong id="kpi-user-id">—</strong></div>
                    <div class="ghl-kpi"><span>GHL Contact</span><strong id="kpi-ghl-id" style="font-size:.78rem;">—</strong></div>
                    <div class="ghl-kpi"><span>Duration</span><strong id="kpi-duration" style="font-size:.85rem;">—</strong></div>
                </div>

                <div class="workspace-actions" style="margin-top:.75rem;display:none;" id="ghl-test-actions">
                    <button type="button" class="button button--ghost-blue" id="ghl-clear-btn" onclick="clearTest()">Clear</button>
                </div>
            </section>
        </div>
    </div>

    {{-- DETAILED RESULTS --}}
    <section class="workspace-card" id="ghl-test-details" style="display:none;">
        <span class="eyebrow">Detailed Results</span>
        <h2>Full diagnostic output</h2>

        <div class="ghl-detail-tabs" id="ghl-detail-tabs">
            <span class="ghl-detail-tab is-active" data-tab="dashboard">Dashboard</span>
            <span class="ghl-detail-tab" data-tab="user">User</span>
            <span class="ghl-detail-tab" data-tab="profile">Profile</span>
            <span class="ghl-detail-tab" data-tab="subscription">Subscription</span>
            <span class="ghl-detail-tab" data-tab="password">Password</span>
            <span class="ghl-detail-tab" data-tab="email">Email</span>
            <span class="ghl-detail-tab" data-tab="ghl-api">GHL API</span>
            <span class="ghl-detail-tab" data-tab="payload">Payload</span>
            <span class="ghl-detail-tab" data-tab="queue">Queue</span>
        </div>

        {{-- TAB: Dashboard --}}
        <div data-pane="dashboard">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem;">
                <div class="ghl-section-card">
                    <h4>User Summary</h4>
                    <dl class="ghl-meta-grid" id="dash-user">
                        <dt>ID</dt><dd id="dd-user-id">—</dd>
                        <dt>Name</dt><dd id="dd-user-name">—</dd>
                        <dt>Email</dt><dd id="dd-user-email">—</dd>
                        <dt>Role</dt><dd id="dd-user-role">—</dd>
                        <dt>Status</dt><dd id="dd-user-status">—</dd>
                        <dt>GHL Contact ID</dt><dd id="dd-user-ghl">—</dd>
                    </dl>
                </div>
                <div class="ghl-section-card">
                    <h4>Profile Summary</h4>
                    <dl class="ghl-meta-grid" id="dash-profile">
                        <dt>Profile ID</dt><dd id="dd-prof-id">—</dd>
                        <dt>Slug</dt><dd id="dd-prof-slug">—</dd>
                        <dt>Brokerage</dt><dd id="dd-prof-brokerage">—</dd>
                        <dt>Service Area</dt><dd id="dd-prof-area">—</dd>
                        <dt>Approved</dt><dd id="dd-prof-approved">—</dd>
                        <dt>Published</dt><dd id="dd-prof-published">—</dd>
                    </dl>
                </div>
                <div class="ghl-section-card">
                    <h4>Subscription / Plan</h4>
                    <dl class="ghl-meta-grid" id="dash-sub">
                        <dt>Plan</dt><dd id="dd-plan-name">—</dd>
                        <dt>Billing</dt><dd id="dd-plan-billing">—</dd>
                        <dt>Lead Quota</dt><dd id="dd-plan-quota">—</dd>
                        <dt>Sub ID</dt><dd id="dd-sub-id">—</dd>
                        <dt>Payment</dt><dd id="dd-sub-payment">—</dd>
                    </dl>
                </div>
                <div class="ghl-section-card">
                    <h4>Login Details</h4>
                    <dl class="ghl-meta-grid" id="dash-login">
                        <dt>Email</dt><dd id="dd-login-email">—</dd>
                        <dt>Temp Password</dt><dd id="dd-login-password">—</dd>
                        <dt>Must Reset</dt><dd id="dd-login-reset">—</dd>
                        <dt>Portal URL</dt><dd id="dd-login-url">—</dd>
                    </dl>
                </div>
            </div>
            <div class="ghl-section-card" style="margin-top:.75rem;">
                <h4>Email Delivery</h4>
                <dl class="ghl-meta-grid" id="dash-email">
                    <dt>Status</dt><dd id="dd-email-status">—</dd>
                    <dt>Recipient</dt><dd id="dd-email-recipient">—</dd>
                    <dt>Subject</dt><dd id="dd-email-subject">—</dd>
                    <dt>Mail Log</dt><dd id="dd-email-log">—</dd>
                </dl>
                <div style="margin-top:.5rem;"><button type="button" class="button button--ghost-blue" id="btn-resend-email" style="font-size:.78rem;display:none;" onclick="resendEmail()">Resend Welcome Email</button></div>
            </div>
        </div>

        {{-- TAB: User --}}
        <div data-pane="user" style="display:none;">
            <div class="ghl-section-card"><h4>Users Record</h4><div class="ghl-json-box" id="detail-user-json">{}</div></div>
        </div>

        {{-- TAB: Profile --}}
        <div data-pane="profile" style="display:none;">
            <div class="ghl-section-card"><h4>Realtor Profile Record</h4><div class="ghl-json-box" id="detail-profile-json">{}</div></div>
        </div>

        {{-- TAB: Subscription --}}
        <div data-pane="subscription" style="display:none;">
            <div class="ghl-section-card"><h4>Agent Subscription</h4><div class="ghl-json-box" id="detail-sub-json">{}</div></div>
            <div class="ghl-section-card"><h4>Plan / Package</h4><div class="ghl-json-box" id="detail-plan-json">{}</div></div>
        </div>

        {{-- TAB: Password --}}
        <div data-pane="password" style="display:none;">
            <div class="ghl-section-card">
                <h4>Generated Credentials</h4>
                <dl class="ghl-meta-grid" id="pwd-details">
                    <dt>Email</dt><dd id="pwd-email">—</dd>
                    <dt>Temporary Password Token</dt><dd id="pwd-token" class="ghl-masked">—</dd>
                    <dt>Password Hash Generated</dt><dd id="pwd-hash">—</dd>
                    <dt>Must Reset Password</dt><dd id="pwd-must-reset">—</dd>
                    <dt>Setup URL</dt><dd id="pwd-setup-url">—</dd>
                    <dt>Portal Login URL</dt><dd id="pwd-portal-url">—</dd>
                </dl>
                <div style="margin-top:.5rem;font-size:.75rem;color:#6b7280;">Passwords are never stored in plaintext. Only the hashed token is persisted.</div>
            </div>
        </div>

        {{-- TAB: Email --}}
        <div data-pane="email" style="display:none;">
            <div class="ghl-section-card">
                <h4>SMTP / Email Log</h4>
                <dl class="ghl-meta-grid" id="email-details-grid">
                    <dt>SMTP Result</dt><dd id="email-smtp-result">—</dd>
                    <dt>Status</dt><dd><span id="email-status-badge" class="ghl-badge ghl-badge--grey">—</span></dd>
                    <dt>Queued</dt><dd id="email-queued">—</dd>
                    <dt>Sent</dt><dd id="email-sent">—</dd>
                    <dt>Failed</dt><dd id="email-failed">—</dd>
                    <dt>Recipient</dt><dd id="email-recipient">—</dd>
                    <dt>Subject</dt><dd id="email-subject">—</dd>
                    <dt>Delivery Time</dt><dd id="email-delivery-time">—</dd>
                    <dt>Mail Log ID</dt><dd id="email-log-id">—</dd>
                </dl>
                <div style="margin-top:.5rem;"><button type="button" class="button" id="btn-resend-email2" style="font-size:.78rem;display:none;" onclick="resendEmail()">Resend Welcome Email</button></div>
            </div>
            <div class="ghl-section-card"><h4>Full Email Log</h4><div class="ghl-json-box" id="detail-email-json">{}</div></div>
        </div>

        {{-- TAB: GHL API --}}
        <div data-pane="ghl-api" style="display:none;">
            <div class="ghl-section-card">
                <h4>GoHighLevel API Details</h4>
                <dl class="ghl-meta-grid" id="ghl-api-details-grid">
                    <dt>Location ID</dt><dd id="ghl-location-id">—</dd>
                    <dt>Form ID / URL</dt><dd id="ghl-form-id">—</dd>
                    <dt>Survey ID</dt><dd id="ghl-survey-id">—</dd>
                    <dt>Pipeline ID</dt><dd id="ghl-pipeline-id">—</dd>
                    <dt>Stage ID</dt><dd id="ghl-stage-id">—</dd>
                    <dt>Private Integration Token</dt><dd id="ghl-api-key" class="ghl-masked">—</dd>
                    <dt>HTTP Status</dt><dd id="ghl-http-status">—</dd>
                </dl>
            </div>
            <div class="ghl-section-card"><h4>API Request Payload</h4><div class="ghl-json-box ghl-json-box--compact" id="detail-api-request">{}</div></div>
            <div class="ghl-section-card"><h4>API Response Payload</h4><div class="ghl-json-box ghl-json-box--compact" id="detail-api-response">{}</div></div>
            <div class="ghl-section-card"><h4>Full GHL API Details</h4><div class="ghl-json-box ghl-json-box--compact" id="detail-api-full">{}</div></div>
        </div>

        {{-- TAB: Payload --}}
        <div data-pane="payload" style="display:none;">
            <div class="ghl-section-card"><h4>Webhook Payload</h4><div class="ghl-json-box" id="detail-webhook-payload">{}</div></div>
            <div class="ghl-section-card"><h4>Webhook Response</h4><div class="ghl-json-box ghl-json-box--compact" id="detail-webhook-response">{}</div></div>
            <div class="ghl-section-card"><h4>Webhook Headers</h4><div class="ghl-json-box ghl-json-box--compact" id="detail-webhook-headers">{}</div></div>
        </div>

        {{-- TAB: Queue --}}
        <div data-pane="queue" style="display:none;">
            <div class="ghl-section-card">
                <h4>Queue Status</h4>
                <dl class="ghl-meta-grid" id="queue-details-grid">
                    <dt>Portal Access Email</dt><dd id="queue-email">—</dd>
                    <dt>Sync User to GHL</dt><dd id="queue-sync">—</dd>
                    <dt>Sync Job ID</dt><dd id="queue-sync-id">—</dd>
                </dl>
            </div>
            <div class="ghl-section-card"><h4>Full Queue Details</h4><div class="ghl-json-box" id="detail-queue-json">{}</div></div>
        </div>
    </section>

    {{-- ERROR PANEL --}}
    <section class="workspace-card" id="ghl-test-error" style="display:none;">
        <span class="eyebrow" style="color:#b91c1c;">Error</span>
        <h2 style="color:#b91c1c;">Test failed at: <span id="ghl-error-stage" style="font-weight:400;"></span></h2>
        <div class="ghl-json-box-error" id="ghl-error-message"></div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const csrf = '{{ csrf_token() }}';
    let currentTestId = null;
    let pollInterval = null;
    let lastTestData = null;

    document.getElementById('test-role-select')?.addEventListener('change', function () {
        document.getElementById('test-intent').value = this.value;
        const f = document.getElementById('test-brokerage-field');
        if (f) f.style.display = this.value === 'agent' ? '' : 'none';
    });

    // Tab switching
    document.querySelectorAll('.ghl-detail-tab').forEach(t => {
        t.addEventListener('click', function () {
            document.querySelectorAll('.ghl-detail-tab').forEach(x => x.classList.remove('is-active'));
            this.classList.add('is-active');
            document.querySelectorAll('[data-pane]').forEach(p => p.style.display = 'none');
            const pane = document.querySelector(`[data-pane="${this.dataset.tab}"]`);
            if (pane) pane.style.display = '';
        });
    });

    document.getElementById('ghl-test-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        await submitTest();
    });

    async function submitTest() {
        const btn = document.getElementById('ghl-test-submit-btn');
        const txt = document.getElementById('ghl-submit-text');
        btn.disabled = true;
        txt.innerHTML = '<span class="ghl-spinner"></span> Processing…';

        const role = document.getElementById('test-role-select').value;
        const body = {
            intent: role,
            role: role,
            name: document.getElementById('test-name').value,
            email: document.getElementById('test-email').value,
            phone: document.getElementById('test-phone').value,
            city: document.getElementById('test-city').value,
            state: document.getElementById('test-state').value,
            zip_code: document.getElementById('test-zip').value,
            brokerage_name: document.getElementById('test-brokerage').value || undefined,
            form_method: document.getElementById('test-form-method').value,
        };

        try {
            const res = await fetch('{{ route('admin.ghl.test.submit') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(body),
            });
            const data = await res.json();
            if (data.ok && data.test_id) {
                currentTestId = data.test_id;
                startPolling(data.test_id);
            } else {
                showError(data.message || 'Submission failed.');
                btn.disabled = false;
                txt.textContent = 'Submit Test Onboarding';
            }
        } catch (err) {
            showError('Request failed: ' + err.message);
            btn.disabled = false;
            txt.textContent = 'Submit Test Onboarding';
        }
    }

    function startPolling(testId) {
        if (pollInterval) clearInterval(pollInterval);
        currentTestId = testId;
        document.getElementById('ghl-test-id-label').textContent = '#{{ route('admin.ghl.test-panel') }}/' + testId;
        document.getElementById('ghl-test-actions').style.display = 'flex';
        pollInterval = setInterval(() => fetchLog(testId), 1200);
        fetchLog(testId);
    }

    async function fetchLog(testId) {
        try {
            const res = await fetch('{{ route('admin.ghl.test.event-log', 999999) }}'.replace('999999', testId), {
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.ok && data.test) {
                lastTestData = data.test;
                renderTest(data.test);
                if (data.test.is_complete || data.test.has_failed) {
                    if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
                    const btn = document.getElementById('ghl-test-submit-btn');
                    document.getElementById('ghl-submit-text').textContent = 'Submit Test Onboarding';
                    btn.disabled = false;
                }
            }
        } catch (err) {}
    }

    function renderTest(test) {
        const container = document.getElementById('ghl-workflow-container');
        const wf = test.workflow || [];

        if (wf.length === 0) {
            container.innerHTML = '<div class="ghl-empty-state"><strong>No workflow data</strong></div>';
            return;
        }

        let html = '<div class="ghl-workflow">';
        wf.forEach(step => {
            const s = step.status || 'pending';
            const ts = step.timestamp ? new Date(step.timestamp).toLocaleTimeString() : '';
            const dur = step.duration ? `<span class="ghl-wf-step__duration">${step.duration}ms</span>` : '';
            const err = step.error ? `<div class="ghl-wf-step__error">✕ ${escHtml(step.error)}</div>` : '';
            const retryBtn = s === 'failed' ? `<button class="ghl-wf-step__retry button" onclick="event.stopPropagation();retryStage('${step.key}')">Retry</button>` : '';
            html += `
                <div class="ghl-wf-step ghl-wf-step--${s}">
                    <span class="ghl-wf-step__icon">${step.icon}</span>
                    <div class="ghl-wf-step__info">
                        <div class="ghl-wf-step__label">${step.label}</div>
                        <div class="ghl-wf-step__meta">${ts} ${dur}</div>
                        ${err}
                    </div>
                    ${retryBtn}
                    <span class="ghl-wf-step__pill ghl-wf-step__pill--${s}">${s}</span>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;

        // KPI summary
        document.getElementById('ghl-test-summary').style.display = 'grid';
        document.getElementById('kpi-status').textContent = test.status;
        document.getElementById('kpi-status').style.color = test.has_failed ? '#b91c1c' : (test.is_complete ? '#15803d' : '#c2410c');
        document.getElementById('kpi-user-id').textContent = test.user?.id || '—';
        document.getElementById('kpi-ghl-id').textContent = test.ghl_contact_id || '—';
        document.getElementById('kpi-duration').textContent = test.total_duration_seconds ? test.total_duration_seconds + 's' : '—';

        document.getElementById('ghl-test-details').style.display = '';

        // Dashboard tab
        setText('dd-user-id', test.user?.id ?? '—');
        setText('dd-user-name', test.user?.name ?? '—');
        setText('dd-user-email', test.user?.email ?? '—');
        setText('dd-user-role', test.user?.role ?? '—');
        setText('dd-user-status', test.user?.status ?? '—');
        setText('dd-user-ghl', test.user?.ghl_contact_id ?? test.ghl_contact_id ?? '—');
        setText('dd-prof-id', test.profile?.id ?? '—');
        setText('dd-prof-slug', test.profile?.slug ?? '—');
        setText('dd-prof-brokerage', test.profile?.brokerage_name ?? '—');
        const city = test.profile?.service_city ?? '';
        const state = test.profile?.service_state ?? '';
        setText('dd-prof-area', (city || state) ? city + ', ' + state : '—');
        setText('dd-prof-approved', test.profile_approved ? '<span class="ghl-badge ghl-badge--green">Yes</span>' : '<span class="ghl-badge ghl-badge--orange">No</span>');
        setText('dd-prof-published', test.profile_published ? '<span class="ghl-badge ghl-badge--green">Yes</span>' : '<span class="ghl-badge ghl-badge--orange">No</span>');
        setText('dd-plan-name', test.package?.name ?? test.subscription_details?.plan_name ?? '—');
        setText('dd-plan-billing', test.package?.billing_type ?? test.subscription_details?.billing ?? '—');
        setText('dd-plan-quota', test.package?.monthly_lead_quota ?? test.subscription_details?.quota ?? '—');
        setText('dd-sub-id', test.subscription?.id ?? test.subscription_details?.subscription_id ?? '—');
        setText('dd-sub-payment', test.subscription?.payment_status ?? test.subscription_details?.payment_status ?? '—');
        setText('dd-login-email', test.email ?? '—');
        setText('dd-login-password', test.password_token ? `<code class="ghl-masked">${test.password_token}</code>` : '<span style="color:#9ca3af;">Not generated</span>');
        setText('dd-login-reset', test.user?.must_reset_password ? '<span class="ghl-badge ghl-badge--green">Yes</span>' : '<span class="ghl-badge ghl-badge--orange">No</span>');
        setText('dd-login-url', test.portal_login_url ? `<a href="${test.portal_login_url}" target="_blank" rel="noopener">${test.portal_login_url}</a>` : '—');
        const es = test.email_status || 'pending';
        const ec = es === 'sent' ? 'green' : (es === 'failed' ? 'red' : (es === 'queued' ? 'blue' : 'grey'));
        setText('dd-email-status', `<span class="ghl-badge ghl-badge--${ec}">${es}</span>`);
        setText('dd-email-recipient', test.email_recipient ?? '—');
        setText('dd-email-subject', test.email_details?.subject ?? 'Your OmniReferral Portal Access Is Ready');
        setText('dd-email-log', test.email_log_id ? `<a href="#" onclick="switchTab('email');return false;">#${test.email_log_id}</a>` : '—');

        // Resend button
        const rb1 = document.getElementById('btn-resend-email');
        const rb2 = document.getElementById('btn-resend-email2');
        if (test.user?.id && test.email) {
            rb1.style.display = 'inline-block';
            rb2.style.display = 'inline-block';
        } else {
            rb1.style.display = 'none';
            rb2.style.display = 'none';
        }

        // Detail tabs
        setJson('detail-user-json', test.user_data || test.user);
        setJson('detail-profile-json', test.profile || test.profile_data);
        setJson('detail-sub-json', test.subscription);
        setJson('detail-plan-json', test.package || test.subscription_details);
        setJson('detail-webhook-payload', test.webhook_payload);
        setJson('detail-webhook-response', test.webhook_response);
        setJson('detail-webhook-headers', test.webhook_headers);
        setJson('detail-api-request', test.api_request_payload);
        setJson('detail-api-response', test.api_response_payload);
        setJson('detail-api-full', test.ghl_api_details);
        setJson('detail-email-json', test.email_log || test.email_details);
        setJson('detail-queue-json', test.queue_details);

        // Password tab
        setText('pwd-email', test.email ?? '—');
        setText('pwd-token', test.password_token || '—');
        setText('pwd-hash', test.password_generated ? '<span class="ghl-badge ghl-badge--green">Generated</span>' : '<span class="ghl-badge ghl-badge--grey">No</span>');
        setText('pwd-must-reset', test.user?.must_reset_password ? '<span class="ghl-badge ghl-badge--green">Yes</span>' : '<span class="ghl-badge ghl-badge--orange">No</span>');
        const setupUrl = extractFromWorkflow(wf, 'password_generated', 'data.setup_url');
        setText('pwd-setup-url', setupUrl ? `<a href="${setupUrl}" target="_blank" rel="noopener">${setupUrl}</a>` : '—');
        setText('pwd-portal-url', test.portal_login_url ? `<a href="${test.portal_login_url}" target="_blank" rel="noopener">${test.portal_login_url}</a>` : '—');

        // Email tab
        setText('email-smtp-result', es);
        setText('email-status-badge', es);
        document.getElementById('email-status-badge').className = 'ghl-badge ghl-badge--' + ec;
        setText('email-queued', es === 'queued' ? nowStr() : (test.email_details?.queued_at ? new Date(test.email_details.queued_at).toLocaleString() : '—'));
        setText('email-sent', es === 'sent' ? nowStr() : '—');
        setText('email-failed', es === 'failed' ? (test.email_details?.error || nowStr()) : '—');
        setText('email-recipient', test.email_recipient ?? '—');
        setText('email-subject', test.email_details?.subject ?? 'Your OmniReferral Portal Access Is Ready');
        setText('email-delivery-time', test.email_details?.queued_at ? new Date(test.email_details.queued_at).toLocaleString() : '—');
        setText('email-log-id', test.email_log_id ?? '—');

        // GHL API tab
        setText('ghl-location-id', test.location_id || '—');
        setText('ghl-form-id', test.ghl_form_id || test.ghl_form_url || '—');
        setText('ghl-survey-id', '—');
        setText('ghl-pipeline-id', test.pipeline_id || test.ghl_api_details?.pipeline_id || '—');
        setText('ghl-stage-id', test.pipeline_stage || test.ghl_api_details?.stage_id || '—');
        setText('ghl-api-key', test.masked_api_key || '—');
        setText('ghl-http-status', test.http_status ? `<span class="ghl-badge ${test.http_status < 300 ? 'ghl-badge--green' : 'ghl-badge--red'}">${test.http_status}</span>` : '—');

        // Queue tab
        setText('queue-email', test.queue_details?.send_portal_access_email === 'dispatched' ? '<span class="ghl-badge ghl-badge--green">Dispatched</span>' : '<span class="ghl-badge ghl-badge--grey">—</span>');
        setText('queue-sync', test.queue_details?.sync_user_to_ghl === 'dispatched' ? '<span class="ghl-badge ghl-badge--green">Dispatched</span>' : '<span class="ghl-badge ghl-badge--grey">—</span>');
        setText('queue-sync-id', test.sync_job_id || '—');

        // Error display
        if (test.has_failed) {
            document.getElementById('ghl-test-error').style.display = '';
            document.getElementById('ghl-error-stage').textContent = test.error_stage || 'unknown';
            document.getElementById('ghl-error-message').textContent = test.error_message || 'Unknown error';
        } else {
            document.getElementById('ghl-test-error').style.display = 'none';
        }
    }

    // Per-stage retry
    window.retryStage = async function (stage) {
        if (!currentTestId) return;
        if (!confirm(`Retry "${stage}" stage? Only this stage will be rerun.`)) return;

        try {
            const res = await fetch('{{ route('admin.ghl.test.retry-stage', 999999) }}'.replace('999999', currentTestId), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ stage }),
            });
            const data = await res.json();
            if (data.ok) {
                startPolling(currentTestId);
            } else {
                alert('Retry failed: ' + (data.message || 'Unknown error'));
            }
        } catch (err) {
            alert('Request failed: ' + err.message);
        }
    };

    // Resend email
    window.resendEmail = async function () {
        if (!currentTestId) return;
        if (!confirm('Resend welcome email for this test?')) return;

        try {
            const res = await fetch('{{ route('admin.ghl.test.resend-email', 999999) }}'.replace('999999', currentTestId), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.ok) {
                alert(data.message);
                fetchLog(currentTestId);
            } else {
                alert('Failed: ' + (data.message || 'Unknown error'));
            }
        } catch (err) {
            alert('Request failed: ' + err.message);
        }
    };

    // Load test
    window.loadTest = function (testId) {
        if (pollInterval) clearInterval(pollInterval);
        currentTestId = testId;
        document.getElementById('ghl-test-id-label').textContent = '#{{ route('admin.ghl.test-panel') }}/' + testId;
        document.getElementById('ghl-test-actions').style.display = 'flex';
        fetchLog(testId);
    };

    // Clear
    window.clearTest = function () {
        if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
        currentTestId = null; lastTestData = null;
        document.getElementById('ghl-test-id-label').textContent = '';
        document.getElementById('ghl-workflow-container').innerHTML = '<div class="ghl-empty-state"><strong>Awaiting submission</strong><span>Submit the form to see the live execution timeline.</span></div>';
        document.getElementById('ghl-test-summary').style.display = 'none';
        document.getElementById('ghl-test-details').style.display = 'none';
        document.getElementById('ghl-test-error').style.display = 'none';
        document.getElementById('ghl-test-actions').style.display = 'none';
    };

    // Switch tab programmatically
    window.switchTab = function (tab) {
        const el = document.querySelector(`.ghl-detail-tab[data-tab="${tab}"]`);
        if (el) el.click();
    };

    function setText(id, html) {
        const el = document.getElementById(id);
        if (el) el.innerHTML = html;
    }

    function setJson(id, obj) {
        const el = document.getElementById(id);
        if (!el) return;
        if (!obj) { el.textContent = '—'; return; }
        try {
            el.textContent = JSON.stringify(obj, null, 2);
        } catch { el.textContent = String(obj); }
    }

    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function nowStr() {
        return new Date().toLocaleTimeString();
    }

    function extractFromWorkflow(wf, key, path) {
        const step = wf.find(s => s.key === key);
        if (!step) return null;
        const parts = path.split('.');
        let val = step;
        for (const p of parts) { if (val) val = val[p]; }
        return val;
    }

    // Copy buttons for JSON blocks
    document.querySelectorAll('.ghl-json-box').forEach(box => {
        const btn = document.createElement('button');
        btn.className = 'ghl-copy-btn';
        btn.textContent = 'Copy';
        btn.onclick = function () {
            navigator.clipboard.writeText(box.textContent).then(() => {
                btn.textContent = 'Copied!';
                setTimeout(() => { btn.textContent = 'Copy'; }, 2000);
            });
        };
        if (box.textContent !== '—' && box.textContent !== '{}') {
            box.style.position = 'relative';
            box.appendChild(btn);
        }
    });
})();
</script>
@endpush
