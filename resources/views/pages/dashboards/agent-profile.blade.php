@extends('layouts.app')

@section('content')
@php
    $agentHeadshot = $agentProfile?->headshot;
    $agentImage = $agentHeadshot
        ? (\Illuminate\Support\Str::startsWith($agentHeadshot, ['http://', 'https://', '/storage/', 'storage/']) ? $agentHeadshot : asset($agentHeadshot))
        : ($agentUser?->avatar ? asset('storage/' . ltrim($agentUser->avatar, '/')) : asset('images/realtors/3.png'));

    $serviceArea = collect([
        old('city', $agentProfile->city ?: $agentUser->city),
        old('state', $agentProfile->state ?: $agentUser->state),
        old('zip_code', $agentProfile->zip_code ?: $agentUser->zip_code),
    ])->filter()->implode(', ');
@endphp

<section class="page-hero dashboard-page-hero dashboard-page-hero--agent">
    <div class="container page-hero__content">
        <span class="eyebrow">Agent Profile</span>
        <h1>Keep your public agent profile complete and trustworthy</h1>
        <p>Your name, brokerage, contact details, headshot, and service area now feed directly into your public profile and message routing.</p>
    </div>
</section>

<section class="section dashboard-page agent-portal-shell">
    <div class="container agent-portal-grid">
        @include('pages.dashboards.partials.agent-portal-sidebar')

        <div class="agent-portal-main">
            <div class="agent-portal-content-grid">
                <section class="cockpit-table-card agent-portal-section">
                    <div class="agent-portal-section__header">
                        <div>
                            <span class="eyebrow">Profile Details</span>
                            <h2>Update public-facing information</h2>
                        </div>
                    </div>

                    <form action="{{ route('agent.profile.update') }}" method="POST" enctype="multipart/form-data" class="agent-portal-form">
                        @csrf
                        @method('PUT')

                        <div class="form-grid-2">
                            <label>
                                <span>Full Name</span>
                                <input type="text" name="name" value="{{ old('name', $agentUser->name) }}" required>
                            </label>
                            <label>
                                <span>Email</span>
                                <input type="email" name="email" value="{{ old('email', $agentUser->email) }}" required>
                            </label>
                            <label>
                                <span>Phone Number</span>
                                <input type="text" name="phone" value="{{ old('phone', $agentUser->phone) }}" required>
                            </label>
                            <label>
                                <span>Brokerage</span>
                                <input type="text" name="brokerage_name" value="{{ old('brokerage_name', $agentProfile->brokerage_name) }}" required>
                            </label>
                            <label>
                                <span>License Number</span>
                                <input type="text" name="license_number" value="{{ old('license_number', $agentProfile->license_number) }}" required>
                            </label>
                            <label>
                                <span>Specialties</span>
                                <input type="text" name="specialties" value="{{ old('specialties', $agentProfile->specialties) }}" placeholder="Buyer representation, listing strategy, relocation">
                            </label>
                            <label class="form-full-row">
                                <span>Address Line 1</span>
                                <input type="text" name="address_line_1" value="{{ old('address_line_1', $agentUser->address_line_1 ?: $agentProfile->address_line_1) }}" required>
                            </label>
                            <label class="form-full-row">
                                <span>Address Line 2</span>
                                <input type="text" name="address_line_2" value="{{ old('address_line_2', $agentUser->address_line_2 ?: $agentProfile->address_line_2) }}">
                            </label>
                            <label>
                                <span>City</span>
                                <input type="text" name="city" value="{{ old('city', $agentProfile->city ?: $agentUser->city) }}" required>
                            </label>
                            <label>
                                <span>State</span>
                                <input type="text" name="state" value="{{ old('state', $agentProfile->state ?: $agentUser->state) }}" maxlength="2" required>
                            </label>
                            <label>
                                <span>ZIP Code</span>
                                <input type="text" name="zip_code" value="{{ old('zip_code', $agentProfile->zip_code ?: $agentUser->zip_code) }}" required>
                            </label>
                            <label>
                                <span>Profile Image</span>
                                <input type="file" name="profile_image" accept="image/*">
                            </label>
                            <label class="form-full-row">
                                <span>Bio</span>
                                <textarea name="bio" rows="6" placeholder="Tell buyers and sellers what makes your process different.">{{ old('bio', $agentProfile->bio) }}</textarea>
                            </label>
                        </div>

                        <div class="agent-portal-form__actions">
                            <button type="submit" class="button">Save Profile</button>
                            <a href="{{ route('agents.show', $agentProfile) }}" class="button button--ghost-blue">Preview Public Page</a>
                        </div>
                    </form>
                </section>

                <section class="cockpit-table-card agent-portal-section">
                    <div class="agent-portal-section__header">
                        <div>
                            <span class="eyebrow">Public Preview</span>
                            <h2>How your profile appears to users</h2>
                        </div>
                    </div>

                    <article class="agent-profile-preview-card">
                        <img src="{{ $agentImage }}" alt="{{ $agentUser->name }} profile photo" loading="lazy">
                        <div>
                            <h3>{{ old('name', $agentUser->name) }}</h3>
                            <p>{{ old('brokerage_name', $agentProfile->brokerage_name ?: 'Brokerage information pending') }}</p>
                            <span>{{ $serviceArea ?: 'Service area pending' }}</span>
                        </div>
                    </article>

                    <div class="agent-profile-preview-card__meta">
                        <div>
                            <dt>Specialties</dt>
                            <dd>{{ old('specialties', $agentProfile->specialties ?: 'Buyer representation, seller strategy, lead conversion') }}</dd>
                        </div>
                        <div>
                            <dt>Bio</dt>
                            <dd>{{ old('bio', $agentProfile->bio ?: 'Add a short introduction so buyers and sellers know what to expect from your process.') }}</dd>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
@endsection
