@extends('layouts.app')

@push('styles')
<style>
    .client-submission-shell {
        display: grid;
        place-items: center;
    }

    .client-submission-card {
        width: min(100%, 1120px);
        padding: clamp(1rem, 2vw, 1.6rem);
    }

    .client-submission-form-wrap {
        --ghl-form-min-height: 2478px;
        width: 100%;
        min-height: var(--ghl-form-min-height);
        border: 1px solid var(--color-border);
        border-radius: 18px;
        padding: clamp(0.3rem, 1vw, 0.7rem);
        background: linear-gradient(145deg, #f7fbff 0%, #ffffff 100%);
        box-shadow: 0 22px 45px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .client-submission-form-wrap iframe {
        width: 100%;
        height: 100%;
        min-height: var(--ghl-form-min-height);
        border: none;
        border-radius: 12px;
        background: #ffffff;
    }

    @media (max-width: 1024px) {
        .client-submission-form-wrap {
            --ghl-form-min-height: 2760px;
        }
    }

    @media (max-width: 768px) {
        .client-submission-card {
            padding: 1rem;
        }

        .client-submission-form-wrap {
            --ghl-form-min-height: 3060px;
            border-radius: 14px;
        }
    }

    @media (max-width: 420px) {
        .client-submission-form-wrap {
            --ghl-form-min-height: 3340px;
            padding: 0.2rem;
        }
    }
</style>
@endpush

@section('content')
<section class="page-hero">
    <div class="container-sm">
        <span class="eyebrow">Client Submission Form 7</span>
        <h1>Complete Your Onboarding Form</h1>
        <p>Finish this form so OmniReferral can finalize your setup and route you to the right dashboard workflow.</p>
    </div>
</section>

<section class="section">
    <div class="container client-submission-shell">
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
                    data-height="2478"
                    data-layout-iframe-id="inline-K1yKrK1hQrLDUM2Sz0Tz"
                    data-form-id="K1yKrK1hQrLDUM2Sz0Tz"
                    title="Onboarding Form"
                    loading="lazy">
                </iframe>
            </div>

            <div class="package-modal-card__actions">
                <a href="{{ $dashboardRoute }}" class="button">I Completed Form</a>
                <a href="{{ route('contact') }}" class="button button--ghost-blue">Need Help?</a>
            </div>
        </article>
    </div>
</section>
<script src="https://link.msgsndr.com/js/form_embed.js"></script>
@endsection
