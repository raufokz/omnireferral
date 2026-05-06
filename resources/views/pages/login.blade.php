@extends('layouts.app')

@section('content')
    <div class="auth-custom-card">
        <!-- Left Column (Navy background) -->
        <div class="auth-col-left">
   <div class="auth-logo-header">
    <a href="{{ url('/') }}">
        <img src="{{ asset('images/omnireferral-logo.png') }}" alt="OmniReferral Logo"
            style="height: 100px; width: auto; object-fit: contain;">
    </a>
</div>
            <div class="auth-hero-arch">
                <img src="{{ asset('images/auth/arch-city.jpg') }}" alt="Cityscape">
            </div>

            <h1>OMNIREFERRAL.<br>Your Gateway to the Network.</h1>
            <p>Access your personalized workspace, manage your referrals, and connect with top-tier professionals across the
                nation.</p>
        </div>

        <!-- Right Column (White background) -->
        <div class="auth-col-right" x-data="{ userType: @js($selectedWorkspace ?? '') }">
            <h2>Welcome Back</h2>
            <p class="auth-subtitle">Sign in to your account</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                @include('partials.auth.workspace-selector', [
                    'fieldId' => 'login-workspace',
                    'label' => 'Select your workspace',
                    'description' => 'Pick the area you want to enter. We will remember it during your session.',
                    'selected' => $selectedWorkspace ?? '',
                    'workspaces' => $workspaces ?? [],
                ])

                @if ($errors->any())
                    <div
                        style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1rem;">
                        <ul style="padding-left: 1rem; margin: 0; list-style-type: disc;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required autocomplete="current-password">
                </div>

                <div style="text-align: right; margin-bottom: 1.5rem; margin-top: -0.5rem;">
                    <a href="{{ route('password.request') }}"
                        style="font-size: 0.8rem; color: #64748B; text-decoration: none; font-weight: 600;">Forgot
                        Password?</a>
                </div>

                <button type="submit" class="btn-submit">Log In</button>

                <div class="auth-bottom-links">
                    Don't have an account? <a href="{{ route('register') }}">Sign Up</a>
                </div>
            </form>
        </div>
    </div>
@endsection
