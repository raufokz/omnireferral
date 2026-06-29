@extends('layouts.app')

@push('styles')
<style>
    .onboarding-form-shell {
        max-width: 720px;
        margin: 0 auto;
    }
    .onboarding-form-shell .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    @media (max-width: 600px) {
        .onboarding-form-shell .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<section class="page-hero">
    <div class="container-sm page-hero__content">
        <span class="eyebrow">Onboarding</span>
        <h1>Complete Your Onboarding</h1>
        <p>Fill in your details to activate your OmniReferral portal access.</p>
    </div>
</section>

<section class="section">
    <div class="container-sm onboarding-form-shell">
        <form class="contact-form profile-card form-card" method="POST" action="{{ route('onboarding.submit') }}" enctype="multipart/form-data">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-intro">
                <h2>Personal Information</h2>
            </div>

            <label>
                <span>Full name <strong style="color:var(--color-danger)">*</strong></span>
                <input type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="e.g. Jane Smith">
            </label>

            <div class="form-row">
                <label>
                    <span>Phone <strong style="color:var(--color-danger)">*</strong></span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="(555) 123-4567">
                </label>
                <label>
                    <span>Email <strong style="color:var(--color-danger)">*</strong></span>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com">
                </label>
            </div>

            <div class="form-intro" style="margin-top: 2rem;">
                <h2>License &amp; Brokerage</h2>
            </div>

            <div class="form-row">
                <label>
                    <span>License number</span>
                    <input type="text" name="license_number" value="{{ old('license_number') }}" placeholder="TX-1234567">
                </label>
                <label>
                    <span>Brokerage name</span>
                    <input type="text" name="brokerage_name" value="{{ old('brokerage_name') }}" placeholder="Premier Realty Group">
                </label>
            </div>

            <div class="form-row">
                <label>
                    <span>Broker name</span>
                    <input type="text" name="broker_name" value="{{ old('broker_name') }}" placeholder="John Broker">
                </label>
                <label>
                    <span>Office email</span>
                    <input type="email" name="office_email" value="{{ old('office_email') }}" placeholder="office@brokerage.com">
                </label>
            </div>

            <div class="form-row">
                <label>
                    <span>Office phone</span>
                    <input type="tel" name="office_phone" value="{{ old('office_phone') }}" placeholder="(555) 987-6543">
                </label>
                <label>
                    <span>Office address</span>
                    <input type="text" name="office_address" value="{{ old('office_address') }}" placeholder="123 Main Street">
                </label>
            </div>

            <div class="form-intro" style="margin-top: 2rem;">
                <h2>Service Area</h2>
            </div>

            <div class="form-row">
                <label>
                    <span>City <strong style="color:var(--color-danger)">*</strong></span>
                    <input type="text" name="city" value="{{ old('city') }}" required placeholder="Dallas">
                </label>
                <label>
                    <span>State <strong style="color:var(--color-danger)">*</strong></span>
                    <input type="text" name="state" value="{{ old('state') }}" required placeholder="TX">
                </label>
            </div>

            <div class="form-row">
                <label>
                    <span>Country</span>
                    <input type="text" name="country" value="{{ old('country') }}" placeholder="United States">
                </label>
                <label>
                    <span>Postal / ZIP code <strong style="color:var(--color-danger)">*</strong></span>
                    <input type="text" name="postal_code" value="{{ old('postal_code') }}" required placeholder="75201">
                </label>
            </div>

            <div class="form-intro" style="margin-top: 2rem;">
                <h2>Professional Details</h2>
            </div>

            <label>
                <span>Primary area of service</span>
                <input type="text" name="primary_area_of_service" value="{{ old('primary_area_of_service') }}" placeholder="e.g. Dallas-Fort Worth Metroplex">
            </label>

            <div class="form-row">
                <label>
                    <span>Service radius (miles)</span>
                    <input type="number" name="radius_miles" value="{{ old('radius_miles') }}" min="0" max="999" placeholder="50">
                </label>
                <label>
                    <span>Secondary area</span>
                    <input type="text" name="secondary_area" value="{{ old('secondary_area') }}" placeholder="e.g. Fort Worth">
                </label>
            </div>

            <label>
                <span>Lead types / specialties</span>
                <input type="text" name="lead_types" value="{{ old('lead_types') }}" placeholder="e.g. Buyer Representation, Seller Strategy">
            </label>

            <label>
                <span>Languages spoken</span>
                <input type="text" name="languages" value="{{ old('languages') }}" placeholder="e.g. English, Spanish">
            </label>

            <div class="form-intro" style="margin-top: 2rem;">
                <h2>Additional Information</h2>
            </div>

            <label>
                <span>How did you hear about us?</span>
                <input type="text" name="how_did_you_hear_about_us" value="{{ old('how_did_you_hear_about_us') }}" placeholder="e.g. Google, Referral, Social Media">
            </label>

            <label>
                <span>Representative name (if applicable)</span>
                <input type="text" name="representative_name" value="{{ old('representative_name') }}" placeholder="Your representative's name">
            </label>

            <div class="form-intro" style="margin-top: 2rem;">
                <h2>Upload Headshot</h2>
                <p>Upload a profile photo (JPEG, PNG, GIF, or WebP, max 5 MB).</p>
            </div>

            <label>
                <span>Profile picture / headshot</span>
                <input type="file" name="upload_picture" accept="image/jpeg,image/png,image/gif,image/webp">
            </label>

            <div class="form-intro" style="margin-top: 2rem;">
                <h2>Agreement</h2>
            </div>

            <label class="checkbox-label" style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 1.5rem;">
                <input type="checkbox" name="terms" value="1" style="margin-top: 0.25rem; width: auto;" {{ old('terms') ? 'checked' : '' }}>
                <span>I agree to the <a href="{{ route('terms') }}" target="_blank">Terms of Service</a> and <a href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>.</span>
            </label>

            <div class="form-actions" style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button class="button" type="submit">Complete Onboarding</button>
                <a class="button button--ghost" href="{{ route('login') }}">Back to login</a>
            </div>
        </form>
    </div>
</section>
@endsection
