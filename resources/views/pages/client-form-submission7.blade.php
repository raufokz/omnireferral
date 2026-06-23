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
        <p>Finish your final setup so OmniReferral can unlock the right dashboard, notifications, and workflow tools for your account.</p>
    </div>
</section>

<section class="section onboarding-page-body">
    <div class="container-sm onboarding-stack">
        <div class="onboarding-role-switch" data-dashboard-switch>
            <button type="button" class="onboarding-role-pill {{ $role === 'agent' ? 'is-active' : '' }}" data-dashboard-target="{{ route('dashboard.agent') }}">Agent</button>
            <button type="button" class="onboarding-role-pill {{ $role === 'buyer' ? 'is-active' : '' }}" data-dashboard-target="{{ route('dashboard.buyer') }}">Buyer</button>
            <button type="button" class="onboarding-role-pill {{ $role === 'seller' ? 'is-active' : '' }}" data-dashboard-target="{{ route('dashboard.seller') }}">Seller</button>
        </div>

        <div class="card-panel onboarding-form-card" data-onboarding-embed data-dashboard-url="{{ $dashboardRoute }}">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Guided Setup</span>
                    <h2>Finish the final details</h2>
                    <p>Use the embedded onboarding form below. Once it is submitted, we will take you to the right dashboard automatically.</p>
                </div>
            </div>

            <div class="embed-card embed-card--onboarding">
                <iframe
                    src="{{ $onboardingFormSrc }}"
                    style="width:100%;height:100%;border:none;border-radius:8px"
                    id="inline-1KzI6i1lZ4rDTDZF02ot"
                    title="Complete your onboarding form"
                    loading="lazy">
                </iframe>
            </div>

            <div class="package-modal-card__actions">
                <a href="{{ $dashboardRoute }}" class="button" id="onboardingContinueButton">I Completed Onboarding</a>
                <a href="{{ route('contact') }}" class="button button--ghost nav-button-dark">Need Help?</a>
            </div>
        </div>
    </div>
</section>
<script src="https://link.msgsndr.com/js/form_embed.js"></script>
@endsection
