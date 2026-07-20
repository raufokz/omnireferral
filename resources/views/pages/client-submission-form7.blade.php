@extends('layouts.app')

@push('styles')
<style>
    .client-submission-page {
        background: linear-gradient(180deg, #f7f9fc 0%, #ffffff 34%, #f7f9fc 100%);
    }

    .client-submission-hero {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        padding-block: clamp(3.4rem, 7vw, 6.5rem);
        background: #06152b;
        color: #ffffff;
    }

    .client-submission-hero::before,
    .client-submission-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
    }

    .client-submission-hero::before {
        background:
            linear-gradient(135deg, rgba(2, 8, 20, 0.96) 0%, rgba(10, 26, 56, 0.86) 48%, rgba(10, 26, 56, 0.34) 100%),
            url("{{ asset('images/home/hero_backdrop_v2.png') }}") center / cover no-repeat;
        transform: scale(1.03);
    }

    .client-submission-hero::after {
        background:
            radial-gradient(circle at 82% 18%, rgba(255, 107, 0, 0.26), transparent 22rem),
            radial-gradient(circle at 18% 88%, rgba(59, 130, 246, 0.22), transparent 24rem);
    }

    .client-submission-hero .eyebrow,
    .client-submission-hero h1,
    .client-submission-hero p {
        color: #ffffff;
    }

    .client-submission-hero p {
        color: rgba(255, 255, 255, 0.86);
    }

    .client-submission-hero .container-sm,
    .client-submission-shell {
        width: min(100% - 2rem, 820px);
        margin-inline: auto;
    }

    .client-submission-shell {
        display: block;
    }

    .client-submission-card {
        width: 100%;
        padding: clamp(0.9rem, 2vw, 1.25rem);
        border-radius: 18px;
        margin-top: clamp(-3.25rem, -4vw, -2rem);
        position: relative;
        z-index: 2;
    }

    .client-submission-card .panel-header {
        margin-bottom: 1rem;
    }

    .client-submission-card .panel-header h2 {
        font-size: clamp(1.15rem, 2vw, 1.45rem);
        line-height: 1.2;
    }

    .client-submission-card .panel-header p {
        max-width: 44rem;
    }

    .client-submission-form-wrap {
        --ghl-form-min-height: 1780px;
        width: 100%;
        min-height: var(--ghl-form-min-height);
        border: 1px solid var(--color-border);
        border-radius: 14px;
        padding: 0;
        background: #ffffff;
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.07);
        overflow: hidden;
    }

    .client-submission-form-wrap iframe {
        width: 100%;
        height: 100%;
        min-height: var(--ghl-form-min-height);
        border: none;
        border-radius: 3px;
        background: #ffffff;
    }

    .client-submission-actions {
        margin-top: 1rem;
    }

    @media (max-width: 1024px) {
        .client-submission-form-wrap {
            --ghl-form-min-height: 2100px;
        }
    }

    @media (max-width: 768px) {
        .client-submission-hero .container-sm,
        .client-submission-shell {
            width: min(100% - 1rem, 820px);
        }

        .client-submission-card {
            padding: 0.75rem;
            border-radius: 14px;
        }

        .client-submission-form-wrap {
            --ghl-form-min-height: 2460px;
            border-radius: 10px;
        }
    }

    @media (max-width: 420px) {
        .client-submission-form-wrap {
            --ghl-form-min-height: 2780px;
        }
    }
</style>
@endpush

@section('content')
<div class="client-submission-page">
    <section class="page-hero client-submission-hero">
        <div class="container-sm">
            <span class="eyebrow">Client Submission Form 7</span>
            <h1>Complete Your Onboarding Form</h1>
            <p>Finish this form so OmniReferral can finalize your setup and route you to the right dashboard workflow.</p>
        </div>
    </section>

    <section class="section client-submission-section">
        <div class="client-submission-shell">
            <article class="card-panel client-submission-card">
                <div class="panel-header">
                    <div>
                        <span class="eyebrow">GoHighLevel Form</span>
                        <h2>Onboarding Form</h2>
                        <p>Responsive embed is optimized for mobile, tablet, laptop, and large desktop screens.</p>
                    </div>
                </div>

                <div class="client-submission-form-wrap">
                    <iframe
                        src="{{ $onboardingFormSrc }}"
                        style="width:100%;height:100%;border:none;border-radius:3px"
                        id="inline-K1yKrK1hQrLDUM2Sz0Tz"
                        data-layout="{'id':'INLINE'}"
                        data-trigger-type="alwaysShow"
                        data-trigger-value=""
                        data-activation-type="alwaysActivated"
                        data-activation-value=""
                        data-deactivation-type="neverDeactivate"
                        data-deactivation-value=""
                        data-form-name="Onboarding Form"
                        data-height="2867"
                        data-layout-iframe-id="inline-K1yKrK1hQrLDUM2Sz0Tz"
                        data-form-id="K1yKrK1hQrLDUM2Sz0Tz"
                        title="Onboarding Form"
                        loading="lazy">
                    </iframe>
                </div>

                <div class="package-modal-card__actions client-submission-actions">
                    <a href="{{ route('form.submission', ['email' => auth()->user()?->email]) }}" class="button">I Completed Form</a>
                    <a href="{{ route('contact') }}" class="button button--ghost-blue">Need Help?</a>
                </div>
            </article>
        </div>
    </section>
</div>
<script src="https://link.msgsndr.com/js/form_embed.js"></script>
@endsection
