@extends('layouts.app')
@section('content')
<section class="page-hero">
    <div class="container-sm page-hero__content">
        <span class="eyebrow">Activate Your Portal</span>
        <h1>Set your password</h1>
        <p>Your onboarding is complete. Choose a secure password to finish activating your OmniReferral portal access.</p>
    </div>
</section>
<section class="section auth-section">
    <div class="container-sm">
        <form class="contact-form profile-card form-card auth-form-shell auth-form-shell--single" method="POST" action="{{ route('password.setup.store', ['token' => $token]) }}">
            @csrf
            <div class="form-intro">
                <h2>Create your password</h2>
                <p>Use at least 8 characters and confirm the new password below.</p>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger auth-alert" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if ($email)
            <label>
                <span>Email address</span>
                <input type="email" value="{{ $email }}" readonly autocomplete="email">
            </label>
            @endif
            <label>
                <span>New password</span>
                <input type="password" name="password" placeholder="Create a secure password" required autocomplete="new-password">
            </label>
            <label>
                <span>Confirm new password</span>
                <input type="password" name="password_confirmation" placeholder="Re-enter your password" required autocomplete="new-password">
            </label>
            <button class="button" type="submit">Set Password &amp; Continue</button>
            <p class="form-help auth-switch-copy"><a href="{{ route('login') }}">Back to sign in</a></p>
        </form>
    </div>
</section>
@endsection
