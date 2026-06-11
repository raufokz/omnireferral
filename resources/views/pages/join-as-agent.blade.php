@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container" style="max-width: 920px;">
        <div class="section-heading">
            <span class="eyebrow">Agent Onboarding</span>
            <h1>Join the OmniReferral Agent Network</h1>
            <p>Submit your profile for admin review. Once approved, your public agent page and directory listing go live.</p>
        </div>

        @if ($errors->any())
            <div class="auth-error-summary" style="margin-bottom: 1.5rem;">
                <strong>Please review the form below.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('join-as-agent.store') }}" enctype="multipart/form-data" class="card-panel">
            @csrf

            {{-- Honeypot spam trap --}}
            <div aria-hidden="true" style="position:absolute;left:-9999px;height:0;overflow:hidden;">
                <label>Company website<input type="text" name="company_website" tabindex="-1" autocomplete="off"></label>
            </div>

            <h2>Account Details</h2>
            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Full Name *</span>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                </label>
                <label class="workspace-field">
                    <span>Display Name</span>
                    <input type="text" name="display_name" value="{{ old('display_name') }}" placeholder="How you appear publicly">
                </label>
                <label class="workspace-field">
                    <span>Email *</span>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                </label>
                <label class="workspace-field">
                    <span>Phone</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}">
                </label>
                <label class="workspace-field">
                    <span>Password *</span>
                    <input type="password" name="password" required>
                </label>
                <label class="workspace-field">
                    <span>Confirm Password *</span>
                    <input type="password" name="password_confirmation" required>
                </label>
                <label class="workspace-field">
                    <span>City *</span>
                    <input type="text" name="city" value="{{ old('city') }}" required>
                </label>
                <label class="workspace-field">
                    <span>State *</span>
                    <input type="text" name="state" value="{{ old('state') }}" maxlength="2" placeholder="TX" required>
                </label>
                <label class="workspace-field">
                    <span>ZIP Code</span>
                    <input type="text" name="zip_code" value="{{ old('zip_code') }}">
                </label>
            </div>

            <h2 style="margin-top: 2rem;">Agent Profile</h2>
            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Service City *</span>
                    <input type="text" name="service_city" value="{{ old('service_city') }}" required>
                </label>
                <label class="workspace-field">
                    <span>Service State *</span>
                    <input type="text" name="service_state" value="{{ old('service_state') }}" maxlength="2" required>
                </label>
                <label class="workspace-field">
                    <span>Service ZIP</span>
                    <input type="text" name="service_zip_code" value="{{ old('service_zip_code') }}">
                </label>
                <label class="workspace-field">
                    <span>Brokerage Name *</span>
                    <input type="text" name="brokerage_name" value="{{ old('brokerage_name') }}" required>
                </label>
                <label class="workspace-field">
                    <span>License Number</span>
                    <input type="text" name="license_number" value="{{ old('license_number') }}">
                </label>
                <label class="workspace-field">
                    <span>Years of Experience</span>
                    <input type="number" name="years_of_experience" min="0" max="60" value="{{ old('years_of_experience') }}">
                </label>
                <label class="workspace-field">
                    <span>Languages</span>
                    <input type="text" name="languages" value="{{ old('languages') }}" placeholder="English, Spanish">
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Market Areas</span>
                    <input type="text" name="market_areas" value="{{ old('market_areas') }}" placeholder="Neighborhoods or counties you serve">
                </label>
            </div>

            <fieldset style="margin-top: 1rem; border: 0; padding: 0;">
                <legend><strong>Specialties *</strong></legend>
                <div class="workspace-form-grid">
                    @foreach($specialtyOptions as $option)
                        <label class="workspace-field">
                            <span style="display:flex;gap:0.5rem;align-items:center;font-weight:400;">
                                <input type="checkbox" name="specialties[]" value="{{ $option }}" @checked(in_array($option, old('specialties', []), true))>
                                {{ $option }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <label class="workspace-field workspace-field--full" style="margin-top: 1rem;">
                <span>Professional Bio * (80–1000 characters)</span>
                <textarea name="bio" rows="6" required minlength="80" maxlength="1000">{{ old('bio') }}</textarea>
            </label>

            <label class="workspace-field workspace-field--full">
                <span>Headshot (optional, max 2MB)</span>
                <input type="file" name="headshot" accept="image/jpeg,image/png,image/webp">
            </label>

            <h3 style="margin-top: 1.5rem;">Social Links</h3>
            <div class="workspace-form-grid">
                <label class="workspace-field"><span>Website</span><input type="url" name="social_website" value="{{ old('social_website') }}"></label>
                <label class="workspace-field"><span>Facebook</span><input type="url" name="social_facebook" value="{{ old('social_facebook') }}"></label>
                <label class="workspace-field"><span>LinkedIn</span><input type="url" name="social_linkedin" value="{{ old('social_linkedin') }}"></label>
                <label class="workspace-field"><span>Instagram</span><input type="url" name="social_instagram" value="{{ old('social_instagram') }}"></label>
            </div>

            <label style="display:flex;gap:0.5rem;align-items:flex-start;margin-top:1.5rem;">
                <input type="checkbox" name="terms_accepted" value="1" @checked(old('terms_accepted')) required>
                <span>I agree to the <a href="{{ route('terms') }}">Terms of Service</a> and <a href="{{ route('privacy') }}">Privacy Policy</a>.</span>
            </label>

            <button type="submit" class="button button--orange" style="margin-top: 1.5rem;">Submit Application</button>
        </form>
    </div>
</section>
@endsection
