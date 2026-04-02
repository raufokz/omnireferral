@extends('layouts.app')
@section('content')
<section class="page-hero">
    <div class="container-sm page-hero__content">
        <span class="eyebrow">Set A New Password</span>
        <h1>Create your new password</h1>
        <p>Choose a secure password so you can get back into your OmniReferral workspace quickly.</p>
    </div>
</section>
<section class="section auth-section">
    <div class="container-sm">
        <form class="contact-form profile-card form-card auth-form-shell auth-form-shell--single" method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="form-intro">
                <h2>Reset password</h2>
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
            <label>
                <span>Email address</span>
                <input type="email" name="email" value="{{ old('email', $email) }}" placeholder="you@example.com" required autocomplete="email">
            </label>
            <label>
                <span>New password</span>
                <input type="password" name="password" placeholder="Create a secure password" required autocomplete="new-password">
            </label>
            <label>
                <span>Confirm new password</span>
                <input type="password" name="password_confirmation" placeholder="Re-enter your password" required autocomplete="new-password">
            </label>
            <button class="button" type="submit">Save New Password</button>
            <p class="form-help auth-switch-copy"><a href="{{ route('login') }}">Back to sign in</a></p>
        </form>
    </div>
</section>
@endsection
