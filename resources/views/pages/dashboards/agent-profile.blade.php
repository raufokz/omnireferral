@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Agent Workspace')
@section('dashboard_title', 'Agent Profile Settings')
@section('dashboard_description', 'Manage the profile details used across your public page, message routing, and listing identity.')

@section('dashboard_actions')
    @if($agentProfile)
        <a href="{{ route('agents.show', $agentProfile) }}" class="button button--ghost-blue">Public Profile</a>
    @endif
@endsection

@section('content')
@php
    $agentHeadshot = $agentProfile?->headshot;
    $agentImage = $agentHeadshot
        ? (\Illuminate\Support\Str::startsWith($agentHeadshot, ['http://', 'https://', '/storage/', 'storage/']) ? $agentHeadshot : asset($agentHeadshot))
        : ($agentUser?->avatar ? asset('storage/' . ltrim($agentUser->avatar, '/')) : asset('images/realtors/3.png'));
@endphp

<div class="workspace-grid workspace-grid--2">
    <section class="workspace-card">
        <span class="eyebrow">Profile Form</span>
        <h2>Update Agent Details</h2>
        <form action="{{ route('agent.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Full Name</span>
                    <input type="text" name="name" value="{{ old('name', $agentUser->name) }}" required>
                </label>
                <label class="workspace-field">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email', $agentUser->email) }}" required>
                </label>
                <label class="workspace-field">
                    <span>Phone</span>
                    <input type="text" name="phone" value="{{ old('phone', $agentUser->phone) }}" required>
                </label>
                <label class="workspace-field">
                    <span>Brokerage Name</span>
                    <input type="text" name="brokerage_name" value="{{ old('brokerage_name', $agentProfile->brokerage_name) }}" required>
                </label>
                <label class="workspace-field">
                    <span>License Number</span>
                    <input type="text" name="license_number" value="{{ old('license_number', $agentProfile->license_number) }}" required>
                </label>
                <label class="workspace-field">
                    <span>Specialties</span>
                    <input type="text" name="specialties" value="{{ old('specialties', $agentProfile->specialties) }}">
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Address Line 1</span>
                    <input type="text" name="address_line_1" value="{{ old('address_line_1', $agentUser->address_line_1 ?: $agentProfile->address_line_1) }}" required>
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Address Line 2</span>
                    <input type="text" name="address_line_2" value="{{ old('address_line_2', $agentUser->address_line_2 ?: $agentProfile->address_line_2) }}">
                </label>
                <label class="workspace-field">
                    <span>City</span>
                    <input type="text" name="city" value="{{ old('city', $agentProfile->city ?: $agentUser->city) }}" required>
                </label>
                <label class="workspace-field">
                    <span>State</span>
                    <input type="text" name="state" value="{{ old('state', $agentProfile->state ?: $agentUser->state) }}" maxlength="2" required>
                </label>
                <label class="workspace-field">
                    <span>ZIP Code</span>
                    <input type="text" name="zip_code" value="{{ old('zip_code', $agentProfile->zip_code ?: $agentUser->zip_code) }}" required>
                </label>
                <label class="workspace-field">
                    <span>Profile Image</span>
                    <input type="file" name="profile_image" accept="image/*">
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Bio</span>
                    <textarea name="bio">{{ old('bio', $agentProfile->bio) }}</textarea>
                </label>
            </div>

            <div class="workspace-actions" style="margin-top: 0.9rem;">
                <button type="submit" class="button">Save Profile</button>
                <a href="{{ route('dashboard.agent') }}" class="button button--ghost-blue">Cancel</a>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <span class="eyebrow">Public Preview</span>
        <h2>Profile Snapshot</h2>
        <article class="workspace-property" style="overflow: visible;">
            <img src="{{ $agentImage }}" alt="{{ $agentUser->name }} profile photo" loading="lazy" style="height: 220px;">
            <div class="workspace-property__body">
                <h3>{{ old('name', $agentUser->name) }}</h3>
                <p class="workspace-property__meta">{{ old('brokerage_name', $agentProfile->brokerage_name ?: 'Brokerage info pending') }}</p>
                <div class="workspace-pill-row">
                    <span class="workspace-pill">{{ old('city', $agentProfile->city ?: $agentUser->city) ?: 'City' }}</span>
                    <span class="workspace-pill">{{ strtoupper(old('state', $agentProfile->state ?: $agentUser->state) ?: 'NA') }}</span>
                    <span class="workspace-pill workspace-pill--accent">{{ old('zip_code', $agentProfile->zip_code ?: $agentUser->zip_code) ?: 'ZIP' }}</span>
                </div>
            </div>
        </article>
        <ul class="workspace-list" style="margin-top: 0.9rem;">
            <li>
                <strong>Specialties</strong>
                <small>{{ old('specialties', $agentProfile->specialties ?: 'Buyer representation, seller strategy, negotiation') }}</small>
            </li>
            <li>
                <strong>Bio</strong>
                <small>{{ old('bio', $agentProfile->bio ?: 'Add a profile bio to improve trust and conversion on your public profile page.') }}</small>
            </li>
        </ul>
    </section>
</div>
@endsection
