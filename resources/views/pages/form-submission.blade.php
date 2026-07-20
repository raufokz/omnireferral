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

    .form-submission-credentials {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin: 1.25rem auto;
        max-width: 480px;
        text-align: left;
    }

    .form-submission-credentials dt {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
        margin-top: 0.75rem;
    }

    .form-submission-credentials dt:first-child {
        margin-top: 0;
    }

    .form-submission-credentials dd {
        margin: 0.15rem 0 0 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: #111827;
        word-break: break-all;
    }

    .form-submission-success-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #bbf7d0;
        color: #166534;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 1rem;
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
<section class="page-hero page-hero--success">
    <div class="container-sm">
        <span class="eyebrow">Form Submitted</span>
        <h1>Thank You For Completing Your Onboarding</h1>
        <p>Your OmniReferral account has been created. Login details have been sent to your email.</p>
    </div>
</section>

<section class="section">
    <div class="container form-submission-wrap">
        <article class="card-panel form-submission-card">
            <div class="form-submission-success-icon">&#10003;</div>
            <h2>Your account is ready</h2>
            <p>
                We have received your onboarding information and your OmniReferral account is now active.
                You can access your portal immediately using the credentials sent to your email.
            </p>

            @if($email ?? false)
            <div class="form-submission-credentials">
                <dl>
                    <dt>Portal URL</dt>
                    <dd><a href="{{ route('login') }}" style="color:#c2410c;text-decoration:underline;">{{ route('login') }}</a></dd>
                    <dt>Email</dt>
                    <dd>{{ $email }}</dd>
                    <dt>Password</dt>
                    <dd>Check your email for the temporary password</dd>
                </dl>
            </div>
            @endif

            <div class="form-submission-actions">
                <a href="{{ route('login') }}" class="button button--orange">Go to Login</a>
                <a href="{{ route('contact') }}" class="button button--ghost-blue">Contact Support</a>
            </div>
        </article>
    </div>
</section>
@endsection
