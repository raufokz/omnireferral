@extends('layouts.app')

@push('styles')
<style>
    .onboarding-page-hero {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        padding-block: clamp(3.4rem, 7vw, 6.5rem);
        background: #06152b;
        color: #ffffff;
    }

    .onboarding-page-hero::before,
    .onboarding-page-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
    }

    .onboarding-page-hero::before {
        background:
            linear-gradient(135deg, rgba(2, 8, 20, 0.96) 0%, rgba(10, 26, 56, 0.86) 48%, rgba(10, 26, 56, 0.34) 100%),
            url("{{ asset('images/home/hero_backdrop_v2.png') }}") center / cover no-repeat;
        transform: scale(1.03);
    }

    .onboarding-page-hero::after {
        background:
            radial-gradient(circle at 82% 18%, rgba(255, 107, 0, 0.26), transparent 22rem),
            radial-gradient(circle at 18% 88%, rgba(59, 130, 246, 0.22), transparent 24rem);
    }

    .onboarding-page-hero .eyebrow,
    .onboarding-page-hero h1,
    .onboarding-page-hero p {
        color: #ffffff;
    }

    .onboarding-page-hero p {
        color: rgba(255, 255, 255, 0.86);
    }

    .onboarding-page-body {
        position: relative;
        z-index: 2;
        margin-top: clamp(-3.25rem, -4vw, -2rem);
        background: linear-gradient(180deg, #f7f9fc 0%, #ffffff 38%, #f7f9fc 100%);
    }
</style>
@endpush

@section('content')
<section class="page-hero onboarding-page-hero">
    <div class="container-sm">
        <span class="eyebrow">Onboarding</span>
        <h1>Complete Your Onboarding</h1>
        <p>Finish the final setup steps so OmniReferral can personalize your experience and unlock the right dashboard tools for your {{ $role }} account.</p>
    </div>
</section>

<section class="section onboarding-page-body">
    <div class="container-sm onboarding-stack">
        <div class="onboarding-steps">
            <article class="step-card">
                <div class="step-icon">1</div>
                <h3>Upload profile photo or documents</h3>
                <p>Add your identity, brokerage, or property documents so your profile feels complete and trustworthy.</p>
            </article>
            <article class="step-card">
                <div class="step-icon">2</div>
                <h3>Verify email and phone</h3>
                <p>Confirm your contact details so you receive lead updates, notifications, and onboarding reminders.</p>
            </article>
            <article class="step-card">
                <div class="step-icon">3</div>
                <h3>Complete initial setup</h3>
                <p>Choose service areas, save preferences, and activate the tools relevant to your {{ $role }} account.</p>
            </article>
        </div>

        <div class="card-panel onboarding-form-card" data-onboarding-embed data-dashboard-url="{{ request()->query('next', $dashboardRoute) }}">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Setup Form</span>
                    <h2>Complete your guided onboarding</h2>
                    <p>Fill out the embedded form below. Once you are done, continue directly into your dashboard.</p>
                </div>
            </div>
            <div class="embed-card embed-card--onboarding embed-card--ghl is-loading" data-embed-loader aria-busy="true">
                <div class="embed-card__loader" data-embed-loader-indicator role="status" aria-live="polite">
                    <span class="embed-card__loader-badge">Loading Form</span>
                    <h4 class="embed-card__loader-title">Loading your guided onboarding</h4>
                    <p class="embed-card__loader-copy">We are connecting your secure GoHighLevel onboarding form now so you can complete setup without guessing.</p>
                    <div class="embed-card__loader-skeleton" aria-hidden="true">
                        <span class="embed-card__loader-line embed-card__loader-line--short"></span>
                        <span class="embed-card__loader-line embed-card__loader-line--medium"></span>
                        <div class="embed-card__loader-grid">
                            <span class="embed-card__loader-block"></span>
                            <span class="embed-card__loader-block"></span>
                            <span class="embed-card__loader-block embed-card__loader-block--wide"></span>
                            <span class="embed-card__loader-block embed-card__loader-block--tall"></span>
                        </div>
                    </div>
                </div>
                <iframe
                    src="{{ $onboardingFormSrc }}"
                    id="inline-1KzI6i1lZ4rDTDZF02ot"
                    title="Complete your onboarding form"
                    loading="lazy"
                    data-embed-loader-frame>
                </iframe>
            </div>
            <div class="package-modal-card__actions">
                <a href="{{ request()->query('next', $dashboardRoute) }}" class="button">I Completed Onboarding</a>
                <a href="{{ route('contact') }}" class="button button--ghost nav-button-dark">Need Help?</a>
            </div>
        </div>
    </div>
</section>
<script src="https://link.msgsndr.com/js/form_embed.js"></script>
@endsection
