@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'GoHighLevel Settings')
@section('dashboard_description', 'Configure API credentials, form URLs, and hidden field mappings. Only super admins may save changes.')

@section('dashboard_actions')
    <a href="{{ route('admin.ghl.index') }}" class="button button--ghost-blue">Overview</a>
    <a href="{{ route('admin.ghl.testing') }}" class="button button--ghost-blue">Test Tools</a>
@endsection

@section('content')
<div class="workspace-stack">

    @if(session('success'))
        <div class="form-alert form-alert--success" role="alert">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="form-alert form-alert--error" role="alert">{{ session('error') }}</div>
    @endif

    @php $canEdit = auth()->user()?->isSuperAdmin(); @endphp

    @unless($canEdit)
    <div class="workspace-card" style="border-left:4px solid var(--color-warning,#f59e0b);">
        <strong>View-only mode.</strong> You can read these settings but only a super admin can save changes.
    </div>
    @endunless

    <form action="{{ route('admin.ghl.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        {{-- 1. Connection --}}
        <section class="workspace-card">
            <span class="eyebrow">1 — Connection Settings</span>
            <h2>API credentials &amp; environment</h2>
            <p style="font-size:.875rem; color:var(--color-text-muted,#6b7280); margin-bottom:1.25rem;">
                API key and webhook secret are stored encrypted. Leave blank to keep the current value.
            </p>

            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>API Key / Access Token <em style="color:var(--color-text-muted,#9ca3af);">(encrypted)</em></span>
                    <input type="password" name="api_key" autocomplete="new-password"
                        placeholder="{{ $settings->api_key ? '●●●●●●●●●●●●●●●● (set — leave blank to keep)' : 'Paste API key…' }}"
                        {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>Location ID</span>
                    <input type="text" name="location_id" value="{{ old('location_id', $settings->location_id) }}"
                        placeholder="GHL location ID" {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>Agency ID <em style="color:var(--color-text-muted,#9ca3af);">(optional)</em></span>
                    <input type="text" name="agency_id" value="{{ old('agency_id', $settings->agency_id) }}"
                        placeholder="Agency ID if using agency account" {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>Webhook Secret <em style="color:var(--color-text-muted,#9ca3af);">(encrypted)</em></span>
                    <input type="password" name="webhook_secret" autocomplete="new-password"
                        placeholder="{{ $settings->webhook_secret ? '●●●●●●●●●●●●●●●● (set — leave blank to keep)' : 'Paste webhook secret…' }}"
                        {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>Environment</span>
                    <select name="environment" {{ $canEdit ? '' : 'disabled' }}>
                        <option value="production" {{ $settings->environment === 'production' ? 'selected' : '' }}>Production</option>
                        <option value="sandbox" {{ $settings->environment === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                    </select>
                </label>
            </div>

            <div class="workspace-field" style="margin-top:.75rem;">
                <span>Connection Status</span>
                <div style="display:flex; align-items:center; gap:.75rem; margin-top:.35rem;">
                    <span class="workspace-pill {{ $settings->statusBadgeClass() }}">{{ $settings->statusLabel() }}</span>
                    @if($settings->last_tested_at)
                        <span style="font-size:.8rem; color:var(--color-text-muted,#6b7280);">Tested {{ $settings->last_tested_at->diffForHumans() }}</span>
                    @endif
                    <a href="{{ route('admin.ghl.testing') }}" class="button button--ghost-blue" style="font-size:.8rem; padding:.3rem .75rem;">Test Connection</a>
                </div>
            </div>
        </section>

        {{-- 2. Form URLs --}}
        <section class="workspace-card" style="margin-top:1.5rem;">
            <span class="eyebrow">2 — Form Configuration</span>
            <h2>GoHighLevel form URLs</h2>

            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Pre-payment Survey Form URL</span>
                    <input type="url" name="pre_payment_survey_url"
                        value="{{ old('pre_payment_survey_url', $settings->pre_payment_survey_url) }}"
                        placeholder="https://api.leadconnectorhq.com/widget/survey/…"
                        {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>Post-payment Onboarding Form URL</span>
                    <input type="url" name="post_payment_onboarding_url"
                        value="{{ old('post_payment_onboarding_url', $settings->post_payment_onboarding_url) }}"
                        placeholder="https://api.leadconnectorhq.com/widget/survey/…"
                        {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>Buyer Onboarding Form URL</span>
                    <input type="url" name="buyer_onboarding_form_url"
                        value="{{ old('buyer_onboarding_form_url', $settings->buyer_onboarding_form_url) }}"
                        placeholder="https://…" {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>Agent Onboarding Form URL</span>
                    <input type="url" name="agent_onboarding_form_url"
                        value="{{ old('agent_onboarding_form_url', $settings->agent_onboarding_form_url) }}"
                        placeholder="https://…" {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>Realtor Onboarding Form URL</span>
                    <input type="url" name="realtor_onboarding_form_url"
                        value="{{ old('realtor_onboarding_form_url', $settings->realtor_onboarding_form_url) }}"
                        placeholder="https://…" {{ $canEdit ? '' : 'disabled' }}>
                </label>
                <label class="workspace-field">
                    <span>Redirect URL After Submission</span>
                    <input type="url" name="redirect_url_after_submission"
                        value="{{ old('redirect_url_after_submission', $settings->redirect_url_after_submission) }}"
                        placeholder="https://…" {{ $canEdit ? '' : 'disabled' }}>
                </label>
            </div>
        </section>

        {{-- 3. Hidden field mapping --}}
        <section class="workspace-card" style="margin-top:1.5rem;">
            <span class="eyebrow">3 — Hidden Fields</span>
            <h2>Identity fields passed to GHL forms</h2>
            <p style="font-size:.875rem; color:var(--color-text-muted,#6b7280); margin-bottom:1rem;">
                Select which fields are injected as query parameters into the onboarding form URL after payment.
            </p>
            @php
                $allHiddenFields = ['user_id', 'email', 'phone', 'name', 'role', 'plan_id', 'payment_id'];
                $selectedFields  = $settings->hidden_fields ?? ['user_id', 'email', 'phone', 'role'];
            @endphp
            <div style="display:flex; flex-wrap:wrap; gap:.75rem;">
                @foreach($allHiddenFields as $field)
                <label style="display:flex; align-items:center; gap:.4rem; cursor:pointer;">
                    <input type="checkbox" name="hidden_fields[]" value="{{ $field }}"
                        {{ in_array($field, $selectedFields) ? 'checked' : '' }}
                        {{ $canEdit ? '' : 'disabled' }}>
                    <code>{{ $field }}</code>
                </label>
                @endforeach
            </div>
        </section>

        {{-- Notes --}}
        <section class="workspace-card" style="margin-top:1.5rem;">
            <span class="eyebrow">Internal Notes</span>
            <label class="workspace-field">
                <span>Admin notes about this configuration</span>
                <textarea name="notes" rows="3" placeholder="Optional internal notes…" {{ $canEdit ? '' : 'disabled' }}>{{ old('notes', $settings->notes) }}</textarea>
            </label>
        </section>

        @if($canEdit)
        <div class="workspace-actions" style="margin-top:1.25rem;">
            <button type="submit" class="button button--orange">Save Settings</button>
            <a href="{{ route('admin.ghl.index') }}" class="button button--ghost-blue">Cancel</a>
        </div>
        @endif

    </form>
</div>
@endsection
