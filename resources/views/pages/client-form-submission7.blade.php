@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container-sm">
        <span class="eyebrow">Onboarding</span>
        <h1>Complete Your Onboarding</h1>
        <p>Finish your final setup so OmniReferral can unlock the right dashboard, notifications, and workflow tools for your account.</p>
    </div>
</section>

<section class="section">
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
