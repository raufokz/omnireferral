@extends('layouts.app')
@section('content')
<section class="page-hero">
    <div class="container-sm page-hero__content">
        <span class="eyebrow">Password Reset</span>
        <h1>Reset your password</h1>
        <p>Enter your email and we will send the reset instructions to your OmniReferral account.</p>
    </div>
</section>
<section class="section auth-section">
    <div class="container-sm">
        <form class="contact-form profile-card form-card auth-form-shell auth-form-shell--single" method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-intro">
                <h2>Send reset link</h2>
                <p>Use the email attached to your buyer, seller, agent, or admin workspace.</p>
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
                <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
            </label>
            <button class="button" type="submit">Email Reset Link</button>
            <p class="form-help auth-switch-copy">Remembered your password? <a href="{{ route('login') }}">Back to sign in</a>.</p>
        </form>
    </div>
</section>
@endsection
