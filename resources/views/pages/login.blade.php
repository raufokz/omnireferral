@extends('layouts.app')

@section('content')
<div class="gateway-shell" x-data="{ showPassword: false, role: '{{ old('role', 'buyer') }}' }">
    <!-- Left Column: Brand Story (60%) -->
    <div class="gateway-brand-col">
        <img src="{{ asset('images/auth/gateway-hero.png') }}" alt="OmniReferral Success" class="gateway-brand-image">
        <div class="gateway-brand-content">
            <div class="brand-mark mb-8">
                <span class="brand-mark__omni" style="color: #fff;">Omni</span><span class="brand-mark__referral" style="color: var(--color-gateway-accent);">Referral</span>
            </div>
            <h1>Your Next Deal Awaits.</h1>
            <p>Access the world's most intelligent real estate lead matching ecosystem. Designed for those who value speed, quality, and partnership.</p>
        </div>
    </div>

    <!-- Right Column: Action Zone (40%) -->
    <div class="gateway-action-col">
        <div class="gateway-form-container">
            <div class="form-intro mb-8">
                <span class="eyebrow">Secure Access</span>
                <h2>Welcome Back</h2>
                <p>Select your workspace to continue your momentum.</p>
            </div>

            <!-- Social Logins (Primary Path) -->
            <div class="grid grid-cols-2 gap-4 mb-8">
                <a href="#" class="gateway-social-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M23.745 12.27c0-.79-.07-1.54-.19-2.27h-11.55v4.51h6.62c-.29 1.5-.1.1.1 1.54 1.11-1.39 1.11-1.39 2.01-2.32h5.11zm-11.75 4.69c-2.32 0-4.28-1.56-4.98-3.69H2.09v3.48a12.016 12.016 0 0010.42 5.51c3.25 0 5.97-1.08 7.96-2.91l-3.23-2.51c-1.12.75-2.55 1.12-4.11 1.12zM5.33 14.24c-.18-.55-.29-1.13-.29-1.74s.11-1.19.29-1.74V7.28H2.09A11.96 11.96 0 000 12.5c0 1.92.45 3.74 1.25 5.35l3.08-2.61zm6.54-9.35c1.77 0 3.35.61 4.6 1.8l3.45-3.45C17.84 1.19 15.11 0 11.87 0a12.016 12.016 0 00-10.42 5.51l3.24 2.51c.7-2.13 2.66-3.69 4.98-3.69z"/></svg>
                    <span>Google</span>
                </a>
                <a href="#" class="gateway-social-btn">
                    <svg width="20" height="20" fill="#1877F2" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    <span>Facebook</span>
                </a>
            </div>

            <div class="gateway-divider"><span>Or use your email</span></div>

            @if ($errors->any())
                <div class="alert alert-danger mb-6" style="padding: 1rem; border-radius: 12px; font-size: 0.9rem;">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="gateway-form">
                @csrf
                
                <div class="floating-group">
                    <select name="role" x-model="role" required>
                        <option value="buyer">Buyer Workspace</option>
                        <option value="seller">Seller Workspace</option>
                        <option value="agent">Agent Workspace</option>
                        <option value="admin">Admin / Staff</option>
                    </select>
                    <label>Select Workspace</label>
                </div>

                <div class="floating-group">
                    <input type="email" name="email" value="{{ old('email') }}" placeholder=" " required autocomplete="email">
                    <label>Email Address</label>
                </div>

                <div class="floating-group password-wrap">
                    <input :type="showPassword ? 'text' : 'password'" name="password" placeholder=" " required autocomplete="current-password">
                    <label>Password</label>
                    <button type="button" class="password-toggle" @click="showPassword = !showPassword" x-text="showPassword ? 'Hide' : 'Show'"></button>
                </div>

                <div class="flex items-center justify-between mt-6 mb-8 text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-gray-300">
                        <span class="text-gray-600">Keep me signed in</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-blue-700 font-semibold hover:underline">Forgot?</a>
                </div>

                <button type="submit" class="button w-full" style="padding: 1.25rem;">
                    Sign In to <span x-text="role.charAt(0).toUpperCase() + role.slice(1)"></span>
                </button>

                <p class="text-center mt-8 text-gray-500 font-medium">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="text-orange-600 hover:underline">Create one now</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
