@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Account')
@section('dashboard_title', 'Profile & account')
@section('dashboard_description', 'Keep your personal details, photo, and security settings current. Changes apply only to your signed-in account.')

@section('dashboard_actions')
    <a href="{{ auth()->user()->dashboardRoute() }}" class="button button--ghost-blue">Back to overview</a>
@endsection

@section('content')
@php
    $u = $user;
    $avatarPreview = $u->avatar
        ? asset('storage/' . ltrim($u->avatar, '/'))
        : asset('images/realtors/3.png');
@endphp

<div class="workspace-stack profile-mgmt">
    <form
        id="profileForm"
        class="workspace-stack"
        method="POST"
        action="{{ route('account.profile.update') }}"
        enctype="multipart/form-data"
        novalidate
    >
        @csrf
        @method('PUT')

        <section class="workspace-card profile-mgmt__hero">
            <div class="profile-mgmt__hero-grid">
                <div class="profile-mgmt__avatar-block">
                    <div class="profile-mgmt__avatar-wrap">
                        <img src="{{ $avatarPreview }}" alt="Profile photo preview" width="120" height="120" id="avatarPreview">
                    </div>
                    <div class="profile-mgmt__avatar-actions">
                        <label class="button profile-mgmt__file-btn">
                            <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/webp,image/gif" class="profile-mgmt__file-input">
                            Change photo
                        </label>
                        <p class="profile-mgmt__hint">JPG, PNG, WebP or GIF · up to 3&nbsp;MB. Image is stored securely on our servers.</p>
                    </div>
                </div>
                <div class="profile-mgmt__hero-copy">
                    <span class="eyebrow">Signed in as</span>
                    <h2>{{ $u->publicDisplayName() }}</h2>
                    <p class="profile-mgmt__muted">{{ $u->email }} · {{ $u->roleLabel() }}</p>
                    <p class="profile-mgmt__muted">Member since {{ $u->created_at?->format('M j, Y') }} · Last profile update {{ $u->updated_at?->diffForHumans() }}</p>
                </div>
            </div>
        </section>

        <div class="workspace-grid workspace-grid--2">
            <section class="workspace-card profile-mgmt__section">
                <span class="eyebrow">Identity</span>
                <h3>Display &amp; legal name</h3>
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Full name <abbr title="required">*</abbr></span>
                        <input type="text" name="name" value="{{ old('name', $u->name) }}" required maxlength="255" autocomplete="name" data-profile-field>
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>Display name</span>
                        <input type="text" name="display_name" value="{{ old('display_name', $u->display_name) }}" maxlength="120" placeholder="How you prefer to be addressed" autocomplete="nickname" data-profile-field>
                    </label>
                </div>
            </section>

            <section class="workspace-card profile-mgmt__section">
                <span class="eyebrow">Contact</span>
                <h3>Email &amp; phone</h3>
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Email <abbr title="required">*</abbr></span>
                        <input type="email" name="email" value="{{ old('email', $u->email) }}" required maxlength="255" autocomplete="email" data-profile-email>
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>Phone</span>
                        <input type="tel" name="phone" value="{{ old('phone', $u->phone) }}" maxlength="40" autocomplete="tel" data-profile-field>
                    </label>
                </div>
                @if($u->email_verified_at)
                    <p class="profile-mgmt__hint profile-mgmt__hint--ok">Email verified on {{ $u->email_verified_at->format('M j, Y') }}.</p>
                @else
                    <p class="profile-mgmt__hint profile-mgmt__hint--warn">This email is not verified yet. Some notifications may be delayed until you confirm it.</p>
                @endif
            </section>
        </div>

        <section class="workspace-card profile-mgmt__section">
            <span class="eyebrow">Location</span>
            <h3>Mailing address <span class="profile-mgmt__optional">(optional)</span></h3>
            <div class="workspace-form-grid">
                <label class="workspace-field workspace-field--full">
                    <span>Address line 1</span>
                    <input type="text" name="address_line_1" value="{{ old('address_line_1', $u->address_line_1) }}" maxlength="255" autocomplete="address-line1">
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Address line 2</span>
                    <input type="text" name="address_line_2" value="{{ old('address_line_2', $u->address_line_2) }}" maxlength="255" autocomplete="address-line2">
                </label>
                <label class="workspace-field">
                    <span>City</span>
                    <input type="text" name="city" value="{{ old('city', $u->city) }}" maxlength="100" autocomplete="address-level2">
                </label>
                <label class="workspace-field">
                    <span>State / region</span>
                    <input type="text" name="state" value="{{ old('state', $u->state) }}" maxlength="120" autocomplete="address-level1">
                </label>
                <label class="workspace-field">
                    <span>ZIP / postal</span>
                    <input type="text" name="zip_code" value="{{ old('zip_code', $u->zip_code) }}" maxlength="10" autocomplete="postal-code">
                </label>
            </div>
        </section>

        <div class="workspace-grid workspace-grid--2">
            <section class="workspace-card profile-mgmt__section">
                <span class="eyebrow">Security</span>
                <h3>Change password</h3>
                <p class="profile-mgmt__muted">Leave blank to keep your current password. When updating, your current password is required.</p>
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Current password</span>
                        <input type="password" name="current_password" autocomplete="current-password" data-profile-current-password>
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>New password</span>
                        <input type="password" name="password" autocomplete="new-password" minlength="8" data-profile-new-password>
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>Confirm new password</span>
                        <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8">
                    </label>
                </div>
                <div class="profile-mgmt__pw-meter" aria-hidden="true">
                    <div class="profile-mgmt__pw-meter-bar" id="pwStrengthBar"></div>
                </div>
                <p class="profile-mgmt__hint" id="pwStrengthLabel">Use at least 8 characters with a mix of letters and numbers.</p>
            </section>

            <section class="workspace-card profile-mgmt__section">
                <span class="eyebrow">Social</span>
                <h3>Professional links</h3>
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Facebook URL</span>
                        <input type="url" name="social_facebook_url" value="{{ old('social_facebook_url', $u->social_facebook_url) }}" maxlength="255" placeholder="https://facebook.com/...">
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>LinkedIn URL</span>
                        <input type="url" name="social_linkedin_url" value="{{ old('social_linkedin_url', $u->social_linkedin_url) }}" maxlength="255" placeholder="https://linkedin.com/in/...">
                    </label>
                </div>
            </section>
        </div>

        <section class="workspace-card profile-mgmt__section">
            <span class="eyebrow">Preferences</span>
            <h3>Notifications &amp; security options</h3>
            <div class="profile-mgmt__toggle-list">
                <label class="profile-mgmt__toggle">
                    <input type="hidden" name="notify_email" value="0">
                    <input type="checkbox" name="notify_email" value="1" {{ old('notify_email', $u->notify_email ? '1' : '0') === '1' ? 'checked' : '' }}>
                    <span><strong>Product &amp; account email</strong><small>Important updates about your workspace and listings.</small></span>
                </label>
                <label class="profile-mgmt__toggle">
                    <input type="hidden" name="notify_marketing" value="0">
                    <input type="checkbox" name="notify_marketing" value="1" {{ old('notify_marketing', $u->notify_marketing ? '1' : '0') === '1' ? 'checked' : '' }}>
                    <span><strong>Marketing &amp; tips</strong><small>Occasional education, offers, and marketplace highlights.</small></span>
                </label>
                <label class="profile-mgmt__toggle">
                    <input type="hidden" name="two_factor_enabled" value="0">
                    <input type="checkbox" name="two_factor_enabled" value="1" {{ old('two_factor_enabled', $u->two_factor_enabled ? '1' : '0') === '1' ? 'checked' : '' }}>
                    <span><strong>Two-factor authentication (2FA)</strong><small>Preference is saved for your account. Full enrollment may require an additional setup step when available.</small></span>
                </label>
            </div>
        </section>

        <div class="workspace-actions profile-mgmt__submit-row">
            <button type="submit" class="button" id="profileSaveBtn">
                <span class="profile-mgmt__btn-label">Save changes</span>
                <span class="profile-mgmt__btn-loading" hidden>Saving…</span>
            </button>
            <a href="{{ route('account.security') }}" class="button button--ghost-blue">Password-only page</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('profileForm');
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const btn = document.getElementById('profileSaveBtn');
    const label = btn?.querySelector('.profile-mgmt__btn-label');
    const loading = btn?.querySelector('.profile-mgmt__btn-loading');
    const newPw = document.querySelector('[data-profile-new-password]');
    const curPw = document.querySelector('[data-profile-current-password]');
    const bar = document.getElementById('pwStrengthBar');
    const pwLabel = document.getElementById('pwStrengthLabel');
    const emailInput = document.querySelector('[data-profile-email]');

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function () {
            const file = avatarInput.files && avatarInput.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                avatarPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    function scorePassword(value) {
        let score = 0;
        if (value.length >= 8) score += 25;
        if (value.length >= 12) score += 15;
        if (/[a-z]/.test(value)) score += 15;
        if (/[A-Z]/.test(value)) score += 15;
        if (/[0-9]/.test(value)) score += 15;
        if (/[^A-Za-z0-9]/.test(value)) score += 15;
        return Math.min(100, score);
    }

    function refreshPwMeter() {
        if (!newPw || !bar || !pwLabel) return;
        const v = newPw.value || '';
        if (!v.length) {
            bar.style.width = '0%';
            pwLabel.textContent = 'Use at least 8 characters with a mix of letters and numbers.';
            return;
        }
        const s = scorePassword(v);
        bar.style.width = s + '%';
        if (s < 40) pwLabel.textContent = 'Strength: weak — add length and symbols.';
        else if (s < 70) pwLabel.textContent = 'Strength: fair — consider more variety.';
        else pwLabel.textContent = 'Strength: strong — great choice.';
    }

    newPw?.addEventListener('input', refreshPwMeter);

    if (emailInput) {
        emailInput.addEventListener('blur', function () {
            const ok = emailInput.checkValidity();
            emailInput.classList.toggle('profile-mgmt__input-invalid', !ok && emailInput.value.length > 0);
        });
    }

    form?.addEventListener('submit', function (e) {
        const np = newPw?.value || '';
        if (np.length) {
            if (!(curPw?.value || '').length) {
                e.preventDefault();
                curPw?.focus();
                alert('Please enter your current password to set a new one.');
                return;
            }
            if (np.length < 8) {
                e.preventDefault();
                newPw?.focus();
                alert('New password must be at least 8 characters.');
                return;
            }
        }
        if (btn) {
            btn.disabled = true;
            if (label) label.hidden = true;
            if (loading) loading.hidden = false;
        }
    });
});
</script>
@endpush
