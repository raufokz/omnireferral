@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', $user?->publicDisplayName() ?: 'Agent Profile Review')
@section('dashboard_description', 'Review profile data, approve or reject public listing eligibility, and manage the linked account.')

@section('dashboard_actions')
    <a href="{{ route('admin.agent-profiles.index') }}" class="button button--ghost-blue">All profiles</a>
    @if($profile->isApprovedForPublicShow())
        <a href="{{ route('agents.show', $profile) }}" class="button" target="_blank" rel="noopener">Preview public profile</a>
    @endif
@endsection

@section('content')
<div class="workspace-stack">
    @if(session('success'))
        <div class="workspace-card">{{ session('success') }}</div>
    @endif

    <section class="workspace-card admin-user-show__hero">
        <div class="admin-user-show__hero-grid">
            <div class="admin-user-show__avatar">
                @include('partials.agent-avatar', ['user' => $user, 'profile' => $profile, 'size' => 120])
            </div>
            <div class="admin-user-show__hero-main">
                <span class="eyebrow">Profile #{{ $profile->id }}</span>
                <h2>{{ $user?->publicDisplayName() }}</h2>
                <p class="admin-user-show__muted">{{ $user?->email }}</p>
                <div class="admin-user-show__pills">
                    <span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug($user?->status ?? 'pending', '_') }}">{{ ucfirst($user?->status ?? 'unknown') }}</span>
                    @if($profile->approved_at)
                        <span class="admin-user-show__pill">Approved {{ $profile->approved_at->format('M j, Y') }}</span>
                    @elseif($profile->rejected_at)
                        <span class="admin-user-show__pill">Rejected {{ $profile->rejected_at->format('M j, Y') }}</span>
                    @else
                        <span class="admin-user-show__pill">Pending review</span>
                    @endif
                </div>
            </div>
            <div class="admin-user-show__hero-actions">
                @if($canApprove && ! $profile->approved_at)
                    <form method="POST" action="{{ route('admin.agent-profiles.approve', $profile) }}">
                        @csrf
                        <input type="hidden" name="approval_notes" value="Approved by admin">
                        <button type="submit" class="button">Approve profile</button>
                    </form>
                @endif
                @if($user && $user->status !== 'active')
                    <form method="POST" action="{{ route('admin.agent-profiles.activate-user', $profile) }}">
                        @csrf
                        <button type="submit" class="button button--ghost-blue">Activate account</button>
                    </form>
                @endif
                @if($user && $user->status === 'active')
                    <form method="POST" action="{{ route('admin.agent-profiles.suspend-user', $profile) }}" onsubmit="return confirm('Suspend this agent account?');">
                        @csrf
                        <button type="submit" class="button button--ghost-blue">Suspend account</button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    @if($canEdit)
        <section class="workspace-card">
            <span class="eyebrow">Edit profile</span>
            <h3>Update before approval</h3>
            <form method="POST" action="{{ route('admin.agent-profiles.update', $profile) }}">
                @csrf
                @method('PUT')
                <div class="workspace-form-grid">
                    <label class="workspace-field"><span>Name</span><input type="text" name="name" value="{{ old('name', $user?->name) }}" required></label>
                    <label class="workspace-field"><span>Display name</span><input type="text" name="display_name" value="{{ old('display_name', $user?->display_name) }}"></label>
                    <label class="workspace-field"><span>Email</span><input type="email" name="email" value="{{ old('email', $user?->email) }}" required></label>
                    <label class="workspace-field"><span>Phone</span><input type="text" name="phone" value="{{ old('phone', $user?->phone) }}"></label>
                    <label class="workspace-field"><span>Brokerage</span><input type="text" name="brokerage_name" value="{{ old('brokerage_name', $profile->brokerage_name) }}" required></label>
                    <label class="workspace-field"><span>License</span><input type="text" name="license_number" value="{{ old('license_number', $profile->license_number) }}"></label>
                    <label class="workspace-field"><span>Service city</span><input type="text" name="service_city" value="{{ old('service_city', $profile->service_city) }}" required></label>
                    <label class="workspace-field"><span>Service state</span><input type="text" name="service_state" value="{{ old('service_state', $profile->service_state) }}" maxlength="2" required></label>
                    <label class="workspace-field"><span>Service ZIP</span><input type="text" name="service_zip_code" value="{{ old('service_zip_code', $profile->service_zip_code) }}"></label>
                    <label class="workspace-field"><span>Years experience</span><input type="number" name="years_of_experience" value="{{ old('years_of_experience', $profile->years_of_experience) }}" min="0" max="60"></label>
                    <label class="workspace-field"><span>Languages</span><input type="text" name="languages" value="{{ old('languages', $profile->languages) }}"></label>
                    <label class="workspace-field"><span>Rating</span><input type="number" step="0.1" min="3" max="5" name="rating" value="{{ old('rating', $profile->rating) }}"></label>
                    <label class="workspace-field workspace-field--full"><span>Specialties</span><input type="text" name="specialties" value="{{ old('specialties', $profile->specialties) }}" required></label>
                    <label class="workspace-field workspace-field--full"><span>Market areas</span><input type="text" name="market_areas" value="{{ old('market_areas', $profile->market_areas) }}"></label>
                    <label class="workspace-field workspace-field--full"><span>Bio</span><textarea name="bio" rows="5" required minlength="80" maxlength="1000">{{ old('bio', $profile->bio) }}</textarea></label>
                    <label class="workspace-field workspace-field--full"><span>Approval notes</span><textarea name="approval_notes" rows="3">{{ old('approval_notes', $profile->approval_notes) }}</textarea></label>
                </div>
                <button type="submit" class="button button--orange" style="margin-top:1rem;">Save changes</button>
            </form>
        </section>
    @endif

    @if($canReject && ! $profile->rejected_at)
        <section class="workspace-card">
            <span class="eyebrow">Reject profile</span>
            <h3>Decline public listing</h3>
            <form method="POST" action="{{ route('admin.agent-profiles.reject', $profile) }}">
                @csrf
                <label class="workspace-field workspace-field--full">
                    <span>Reason *</span>
                    <textarea name="approval_notes" rows="4" required placeholder="Explain what needs to change or why the profile was rejected."></textarea>
                </label>
                <label style="display:flex;gap:0.5rem;align-items:center;margin-top:0.75rem;">
                    <input type="checkbox" name="suspend_user" value="1">
                    <span>Also suspend the linked user account</span>
                </label>
                <button type="submit" class="button button--ghost-blue" style="margin-top:1rem;">Reject profile</button>
            </form>
        </section>
    @endif
</div>
@endsection
