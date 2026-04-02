@extends('layouts.app')

@section('content')
<div class="auth-shell" x-data="{ showPassword: false }">
    <section class="auth-hero">
        <span class="eyebrow eyebrow--light">Join OmniReferral</span>
        <div class="brand-mark">
            <span class="brand-mark__omni" style="color:#fff;">Omni</span><span class="brand-mark__referral">Referral</span>
        </div>
        <h1>Build your premium real estate workspace in minutes.</h1>
        <p>Create a role-based account for buying, selling, or growing your referral business through a modern US real estate ecosystem.</p>

        <div class="auth-proof-list">
            @foreach(['Tailored dashboards for buyers, sellers, agents, and admin teams', 'Stripe-ready packages and GoHighLevel onboarding built into the journey', 'Designed to route qualified leads with more structure and less friction'] as $point)
                <div class="auth-proof-item">
                    <div class="auth-proof-bullet">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <span>{{ $point }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-card">
            <div class="section-heading" style="margin:0 0 1.5rem;text-align:left;max-width:none;">
                <span class="eyebrow">Create Account</span>
                <h2>Set up your role-based access</h2>
                <p>Pick your experience, complete your core details, and unlock the workspace designed for your next step.</p>
            </div>

            <div class="auth-social-grid" style="margin-bottom:1.25rem;">
                <a href="#" class="gateway-social-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M23.745 12.27c0-.79-.07-1.54-.19-2.27h-11.55v4.51h6.62c-.29 1.5-1.1 2.78-2.32 3.64v2.99h3.76c2.19-2.01 3.45-4.97 3.45-8.87zm-11.75 9.97c3.13 0 5.77-1.04 7.69-2.83l-3.76-2.99c-1.04.7-2.37 1.11-3.93 1.11-3.02 0-5.59-2.04-6.5-4.78H1.49v2.85a11.593 11.593 0 0 0 10.5 6.64zm-6.5-8.5c-.23-.7-.36-1.44-.36-2.2s.13-1.5.36-2.2V5.97H1.49a11.59 11.59 0 0 0 0 10.43l3.99-3.13zm6.5-10.05c1.71 0 3.25.59 4.45 1.74l3.33-3.33C17.77 1.19 15.13 0 11.995 0A11.59 11.59 0 0 0 1.49 6.47l3.99 3.13c.91-2.74 3.47-4.78 6.5-4.78z"/></svg>
                    <span>Continue with Google</span>
                </a>
                <a href="#" class="gateway-social-btn">
                    <svg width="18" height="18" fill="#1877F2" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    <span>Continue with Facebook</span>
                </a>
            </div>

            <div class="gateway-divider"><span>Or create your profile manually</span></div>

            @if ($errors->any())
                <div class="form-error-box" style="margin-top:1rem;">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="auth-form-shell" style="margin-top:1.25rem;">
                @csrf

                <div class="auth-role-tabs" role="tablist" aria-label="Registration role type">
                    <button type="button" class="auth-role-tab is-active" data-role-tab="client">Buyer / Seller</button>
                    <button type="button" class="auth-role-tab" data-role-tab="team">Agent / Team</button>
                </div>

                <div class="floating-group">
                    <select name="role" id="registerRoleSelect" required>
                        <option value="buyer" data-role-group="client" @selected(old('role') === 'buyer')>I am a Buyer</option>
                        <option value="seller" data-role-group="client" @selected(old('role') === 'seller')>I am a Seller</option>
                        <option value="agent" data-role-group="team" @selected(old('role', 'agent') === 'agent')>I am an Agent</option>
                    </select>
                    <label for="registerRoleSelect">Choose your experience</label>
                </div>

                <div class="field-grid">
                    <div class="floating-group">
                        <input type="text" id="registerName" name="name" value="{{ old('name') }}" placeholder=" " required autocomplete="name">
                        <label for="registerName">Full name</label>
                    </div>
                    <div class="floating-group">
                        <input type="tel" id="registerPhone" name="phone" value="{{ old('phone') }}" placeholder=" " required autocomplete="tel">
                        <label for="registerPhone">Phone number</label>
                    </div>
                </div>

                <div class="floating-group">
                    <input type="email" id="registerEmail" name="email" value="{{ old('email') }}" placeholder=" " required autocomplete="email">
                    <label for="registerEmail">Email address</label>
                </div>

                <div class="field-grid">
                    <div class="floating-group">
                        <input type="text" id="registerCity" name="city" value="{{ old('city') }}" placeholder=" ">
                        <label for="registerCity">City</label>
                    </div>
                    <div class="floating-group">
                        <input type="text" id="registerState" name="state" value="{{ old('state') }}" placeholder=" " maxlength="2">
                        <label for="registerState">State</label>
                    </div>
                </div>

                <div class="field-grid">
                    <div class="floating-group">
                        <input type="text" id="registerZip" name="zip_code" value="{{ old('zip_code') }}" placeholder=" ">
                        <label for="registerZip">ZIP code</label>
                    </div>
                    <div class="floating-group password-wrap">
                        <input :type="showPassword ? 'text' : 'password'" id="registerPassword" name="password" placeholder=" " required autocomplete="new-password">
                        <label for="registerPassword">Password</label>
                        <button type="button" class="password-toggle" @click="showPassword = !showPassword" x-text="showPassword ? 'Hide' : 'Show'"></button>
                    </div>
                </div>

                <div class="floating-group">
                    <input type="file" id="registerImage" name="profile_image" accept="image/*" class="pt-4">
                    <label for="registerImage">Profile image (optional)</label>
                </div>

                <label class="helper-copy" style="display:flex;align-items:flex-start;gap:.55rem;">
                    <input type="checkbox" name="terms" required checked>
                    <span>I agree to the <a href="{{ route('terms') }}" style="color:var(--color-secondary);font-weight:700;">Terms of Service</a> and understand OmniReferral uses my info to set up my workspace.</span>
                </label>

                <button type="submit" class="button button--orange">Create My Account</button>
                <p class="helper-copy" style="text-align:center;">Already registered? <a href="{{ route('login') }}" style="color:var(--color-primary);font-weight:700;">Sign in instead</a></p>
            </form>
        </div>
    </section>
</div>
@endsection
