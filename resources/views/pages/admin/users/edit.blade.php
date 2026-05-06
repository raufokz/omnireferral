@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Edit user')
@section('dashboard_description', 'Update profile data stored on the users record. Changes apply immediately across listings and enquiries.')

@section('dashboard_actions')
    <a href="{{ route('admin.users.show', $record) }}" class="button button--ghost-blue">View record</a>
    <a href="{{ route('admin.users.index') }}" class="button button--ghost-blue">All users</a>
@endsection

@section('content')
@php
    $u = $record;
    $previewUrl = $u->profilePhotoPublicUrl();
@endphp
<div class="workspace-stack admin-user-edit">
    @if($errors->any())
        <div class="workspace-card admin-user-show__flash admin-user-show__flash--err">
            <strong>Please fix the highlighted fields.</strong>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $u) }}" enctype="multipart/form-data" class="workspace-stack" id="adminUserEditForm">
        @csrf
        @method('PUT')

        <section class="workspace-card admin-user-edit__hero">
            <span class="eyebrow">Profile photo</span>
            <h3>Avatar</h3>
            <div class="admin-user-edit__avatar-row">
                <div class="admin-user-edit__avatar-preview">
                    @if($previewUrl)
                        <img src="{{ $previewUrl }}" alt="" width="120" height="120" id="adminAvatarPreview">
                    @else
                        <span class="listed-by-placeholder listed-by-placeholder--profile-hero" id="adminAvatarPlaceholder">{{ $u->profileInitials() }}</span>
                        <img src="" alt="" width="120" height="120" id="adminAvatarPreview" hidden>
                    @endif
                </div>
                <div class="admin-user-edit__avatar-actions">
                    <label class="button button--ghost-blue">
                        <input type="file" name="avatar" id="adminAvatarInput" accept="image/jpeg,image/png,image/webp,image/gif" class="profile-mgmt__file-input">
                        Upload image
                    </label>
                    <label class="admin-user-edit__remove">
                        <input type="checkbox" name="remove_avatar" value="1" id="removeAvatarCheck">
                        Remove current photo
                    </label>
                    <p class="profile-mgmt__hint">Stored under <code>storage/app/public/avatars/</code>. Max 4&nbsp;MB.</p>
                </div>
            </div>
        </section>

        <div class="workspace-grid workspace-grid--2">
            <section class="workspace-card">
                <span class="eyebrow">Identity</span>
                <h3>Names &amp; role</h3>
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Full name <abbr title="required">*</abbr></span>
                        <input type="text" name="name" value="{{ old('name', $u->name) }}" required maxlength="255">
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>Display name</span>
                        <input type="text" name="display_name" value="{{ old('display_name', $u->display_name) }}" maxlength="120">
                    </label>
                    <label class="workspace-field">
                        <span>Role <abbr title="required">*</abbr></span>
                        <select name="role" required data-role-select>
                            @foreach(['buyer','seller','agent','staff','admin'] as $r)
                                <option value="{{ $r }}" @selected(old('role', $u->role) === $r)>{{ ucfirst($r) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="workspace-field">
                        <span>Status <abbr title="required">*</abbr></span>
                        <select name="status" required>
                            @foreach(['pending','active','suspended'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $u->status) === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="workspace-field workspace-field--full" data-staff-team-wrap style="{{ old('role', $u->role) === 'staff' ? '' : 'display:none;' }}">
                        <span>Staff team</span>
                        <select name="staff_team">
                            <option value="">—</option>
                            @foreach($staffTeams as $key => $label)
                                <option value="{{ $key }}" @selected(old('staff_team', $u->staff_team) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="workspace-card">
                <span class="eyebrow">Contact</span>
                <h3>Email &amp; phone</h3>
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Email <abbr title="required">*</abbr></span>
                        <input type="email" name="email" value="{{ old('email', $u->email) }}" required maxlength="255">
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>Phone</span>
                        <input type="text" name="phone" value="{{ old('phone', $u->phone) }}" maxlength="40">
                    </label>
                    <label class="workspace-field workspace-field--full profile-mgmt__toggle">
                        <input type="hidden" name="email_verified" value="0">
                        <input type="checkbox" name="email_verified" value="1" {{ old('email_verified', $u->email_verified_at ? '1' : '0') === '1' ? 'checked' : '' }}>
                        <span><strong>Email marked verified</strong></span>
                    </label>
                </div>
            </section>
        </div>

        <section class="workspace-card">
            <span class="eyebrow">Address</span>
            <h3>Mailing</h3>
            <div class="workspace-form-grid">
                <label class="workspace-field workspace-field--full">
                    <span>Line 1</span>
                    <input type="text" name="address_line_1" value="{{ old('address_line_1', $u->address_line_1) }}" maxlength="255">
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Line 2</span>
                    <input type="text" name="address_line_2" value="{{ old('address_line_2', $u->address_line_2) }}" maxlength="255">
                </label>
                <label class="workspace-field"><span>City</span><input type="text" name="city" value="{{ old('city', $u->city) }}" maxlength="100"></label>
                <label class="workspace-field"><span>State</span><input type="text" name="state" value="{{ old('state', $u->state) }}" maxlength="120"></label>
                <label class="workspace-field"><span>ZIP</span><input type="text" name="zip_code" value="{{ old('zip_code', $u->zip_code) }}" maxlength="10"></label>
            </div>
        </section>

        <div class="workspace-grid workspace-grid--2">
            <section class="workspace-card">
                <span class="eyebrow">Social</span>
                <h3>Links</h3>
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Facebook URL</span>
                        <input type="url" name="social_facebook_url" value="{{ old('social_facebook_url', $u->social_facebook_url) }}" maxlength="255">
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>LinkedIn URL</span>
                        <input type="url" name="social_linkedin_url" value="{{ old('social_linkedin_url', $u->social_linkedin_url) }}" maxlength="255">
                    </label>
                </div>
            </section>

            <section class="workspace-card">
                <span class="eyebrow">Business</span>
                <h3>Referrals &amp; plan</h3>
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Referred by user ID</span>
                        <input type="number" name="referred_by_user_id" value="{{ old('referred_by_user_id', $u->referred_by_user_id) }}" min="1" step="1" placeholder="User ID">
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>Affiliate code</span>
                        <input type="text" name="affiliate_code" value="{{ old('affiliate_code', $u->affiliate_code) }}" maxlength="64">
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>Current plan</span>
                        <select name="current_plan_id">
                            <option value="">— None —</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected((string) old('current_plan_id', $u->current_plan_id) === (string) $plan->id)>
                                    {{ $plan->name }} ({{ $plan->category }})
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>
        </div>

        <section class="workspace-card">
            <span class="eyebrow">Security &amp; preferences</span>
            <h3>Flags</h3>
            <div class="profile-mgmt__toggle-list">
                <label class="profile-mgmt__toggle">
                    <input type="hidden" name="notify_email" value="0">
                    <input type="checkbox" name="notify_email" value="1" {{ old('notify_email', $u->notify_email ? '1' : '0') === '1' ? 'checked' : '' }}>
                    <span><strong>Product email</strong></span>
                </label>
                <label class="profile-mgmt__toggle">
                    <input type="hidden" name="notify_marketing" value="0">
                    <input type="checkbox" name="notify_marketing" value="1" {{ old('notify_marketing', $u->notify_marketing ? '1' : '0') === '1' ? 'checked' : '' }}>
                    <span><strong>Marketing email</strong></span>
                </label>
                <label class="profile-mgmt__toggle">
                    <input type="hidden" name="two_factor_enabled" value="0">
                    <input type="checkbox" name="two_factor_enabled" value="1" {{ old('two_factor_enabled', $u->two_factor_enabled ? '1' : '0') === '1' ? 'checked' : '' }}>
                    <span><strong>2FA preference</strong></span>
                </label>
                <label class="profile-mgmt__toggle">
                    <input type="hidden" name="must_reset_password" value="0">
                    <input type="checkbox" name="must_reset_password" value="1" {{ old('must_reset_password', $u->must_reset_password ? '1' : '0') === '1' ? 'checked' : '' }}>
                    <span><strong>Force password reset on next login</strong></span>
                </label>
            </div>
            <div class="workspace-form-grid" style="margin-top:1rem;">
                <label class="workspace-field workspace-field--full">
                    <span>New password (optional)</span>
                    <input type="password" name="password" autocomplete="new-password" minlength="8">
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Confirm password</span>
                    <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8">
                </label>
            </div>
        </section>

        <div class="workspace-actions">
            <button type="submit" class="button">Save changes</button>
            <a href="{{ route('admin.users.show', $u) }}" class="button button--ghost-blue">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.querySelector('[data-role-select]');
    const staffWrap = document.querySelector('[data-staff-team-wrap]');
    roleSelect?.addEventListener('change', function () {
        if (!staffWrap) return;
        staffWrap.style.display = roleSelect.value === 'staff' ? '' : 'none';
    });

    const input = document.getElementById('adminAvatarInput');
    const preview = document.getElementById('adminAvatarPreview');
    const placeholder = document.getElementById('adminAvatarPlaceholder');
    const removeCheck = document.getElementById('removeAvatarCheck');

    input?.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file || !preview) return;
        removeCheck && (removeCheck.checked = false);
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.hidden = false;
            if (placeholder) placeholder.hidden = true;
        };
        reader.readAsDataURL(file);
    });

    removeCheck?.addEventListener('change', function () {
        if (removeCheck.checked && preview && preview.src && !preview.src.startsWith('data:')) {
            preview.hidden = true;
        }
    });
});
</script>
@endpush
