@extends('layouts.app')

@push('styles')
    <link rel="preconnect" href="https://api.leadconnectorhq.com" crossorigin>
    <link rel="preconnect" href="https://link.msgsndr.com" crossorigin>
    <link rel="dns-prefetch" href="//api.leadconnectorhq.com">
    <link rel="dns-prefetch" href="//link.msgsndr.com">
    @vite('resources/css/modules/pricing.css')
@endpush

@section('content')

<section class="page-hero page-hero--success">
    <div class="container-sm">
        <span class="eyebrow">Payment Confirmed</span>
        <h1>Your {{ $package->displayName() }} package is active!</h1>
        <p>
            Payment received. Complete the onboarding form below to finalise your account setup.
            Once submitted, you will receive an email with your portal login credentials.
        </p>
    </div>
</section>

@if(!empty($onboardingFormUrl))

<section class="section package-checkout-section package-checkout-section--premium">
    <div class="container package-checkout-grid package-checkout-grid--premium">

        {{-- Left: next-steps panel --}}
        <article class="package-plan-detail">
            <h2>What Happens Next</h2>
            <ol class="package-process-list">
                <li>Complete the secure onboarding form on the right with your details.</li>
                <li>OmniReferral automatically processes your submission.</li>
                <li>A secure password is generated and emailed to <strong>{{ auth()->user()?->email ?? 'your address' }}</strong>.</li>
                <li>Log in to your dashboard and start receiving leads immediately.</li>
            </ol>

            <div class="package-checkout-inline-note" style="margin-top:2rem;">
                <strong>Already have your credentials?</strong>
                <p style="margin-top:0.5rem; font-size:0.875rem; color:var(--color-text-muted, #6b7280);">
                    If you have already completed onboarding and received your email, you can sign in directly.
                </p>
                <a href="{{ $postPurchaseActionUrl }}" class="button button--orange" style="margin-top:0.75rem; display:inline-block;">
                    {{ $postPurchaseActionLabel }}
                </a>
            </div>

            <div class="package-checkout-inline-note" style="margin-top:1.5rem;">
                <strong>Need help?</strong>
                <p style="margin-top:0.5rem; font-size:0.875rem; color:var(--color-text-muted, #6b7280);">
                    Contact our team at
                    <a href="mailto:{{ config('services.omni.support_email', 'admin@omnireferrals.com') }}"
                       style="color:inherit; text-decoration:underline;">
                        {{ config('services.omni.support_email', 'admin@omnireferrals.com') }}
                    </a>
                </p>
            </div>
        </article>

        {{-- Right: embedded onboarding form --}}
        <aside class="package-checkout-form-panel" id="onboarding-form">
            <div class="package-checkout-form-panel__inner">
                <span class="eyebrow">Secure Onboarding Form</span>
                <h2>{{ $onboardingFormTitle ?? 'Complete Your Onboarding' }}</h2>
                <p>{{ $onboardingFormDescription ?? 'Fill in your details to activate your portal access.' }}</p>

                <div class="ghl-checkout-frame is-loading" data-embed-loader aria-busy="true">
                    <div class="ghl-form-loader" data-embed-loader-indicator role="status" aria-live="polite">
                        <span class="ghl-form-loader__spinner" aria-hidden="true"></span>
                        <span class="ghl-form-loader__text embed-card__loader-copy">Loading onboarding form</span>
                    </div>
                    <iframe
                        src="{{ $onboardingFormUrl }}"
                        title="{{ $onboardingFormTitle ?? 'Onboarding form' }}"
                        loading="eager"
                        fetchpriority="high"
                        data-embed-loader-frame
                        referrerpolicy="no-referrer"
                    ></iframe>
                </div>
            </div>
        </aside>

    </div>
</section>

<script src="https://link.msgsndr.com/js/form_embed.js" defer></script>

@else

{{-- Fallback when no onboarding form URL is available --}}
<section class="section">
    <div class="container-sm" style="text-align:center; padding:3rem 0;">
        <h2>Your package is confirmed!</h2>
        <p>The OmniReferral team has been notified and will reach out to complete your onboarding within one business day.</p>
        <div style="margin-top:2rem; display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
            <a href="{{ $postPurchaseActionUrl }}" class="button button--orange">{{ $postPurchaseActionLabel }}</a>
            <a href="{{ route('contact') }}" class="button button--ghost-blue">Contact Support</a>
        </div>
    </div>
</section>

@endif

@endsection
