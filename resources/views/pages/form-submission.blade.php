@extends('layouts.app')

@push('styles')
<style>
    .form-submission-wrap {
        display: grid;
        place-items: center;
    }

    .form-submission-card {
        width: min(100%, 860px);
        padding: clamp(1rem, 2.2vw, 2rem);
        text-align: center;
    }

    .form-submission-card h1 {
        margin-bottom: 0.75rem;
    }

    .form-submission-card p {
        margin-inline: auto;
        max-width: 62ch;
    }

    .form-submission-actions {
        margin-top: 1.25rem;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.75rem;
    }

    @media (max-width: 640px) {
        .form-submission-actions {
            flex-direction: column;
        }

        .form-submission-actions .button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<section class="page-hero">
    <div class="container-sm">
        <span class="eyebrow">Form Submitted</span>
        <h1>Thank You For Your Submission</h1>
        <p>Your onboarding form has been received successfully.</p>
    </div>
</section>

<section class="section">
    <div class="container form-submission-wrap">
        <article class="card-panel form-submission-card">
            <h2>We got everything we need</h2>
            <p>You will receive a confirmation email shortly, and an OmniReferral onboarding specialist will contact you with next steps.</p>
            <p>If you do not see our email, please check your spam or promotions folder.</p>

            <div class="form-submission-actions">
                <a href="{{ route('home') }}" class="button">Back to Home</a>
                <a href="{{ route('contact') }}" class="button button--ghost-blue">Contact Support</a>
            </div>
        </article>
    </div>
</section>
@endsection
