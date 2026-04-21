@extends('layouts.app')

@section('content')
    <div class="auth-custom-card">
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

            <h1>OMNIREFERRAL.<br>The Complete Real Estate Ecosystem. Connect, Transact, and Grow.</h1>
            <p>Unlock endless possibilities in the property market. Join thousands of professionals, buyers, and sellers
                today.</p>
        </div>

        <div class="auth-col-right" x-data="{ userType: '{{ old('role', 'agent') }}' }">
            <h2>Create Your Account</h2>
            <p class="auth-subtitle">Build a complete profile so your workspace is ready from day one.</p>

            @if ($errors->any())
                <div class="auth-error-summary">
                    <strong>Please review the registration form.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" data-multi-step
                novalidate class="auth-form">
                @csrf

                <input type="hidden" name="role" x-model="userType">

                <div class="form-progress">
                    <div class="form-progress-bar"></div>
                </div>

                <div class="form-step is-active auth-step">
                    <div class="auth-step-meta">
                        <span>Step 1 of 4</span>
                        <strong>Account Basics</strong>
                    </div>

                    <span class="user-type-label">Choose a user type</span>
                    <div class="user-type-grid user-type-grid--register">
                        <div class="ut-card" :class="{ 'active': userType === 'agent' }" @click="userType = 'agent'">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M12 6a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-7 9h14M5 15v6h14v-6M5 15l-1-7h16l-1 7" />
                            </svg>
                            <span>Agent</span>
                        </div>
                        <div class="ut-card" :class="{ 'active': userType === 'buyer' }" @click="userType = 'buyer'">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path
                                    d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4" />
                            </svg>
                            <span>Buyer</span>
                        </div>
                        <div class="ut-card" :class="{ 'active': userType === 'seller' }" @click="userType = 'seller'">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M3 21v-8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8M10 21V9m4 12V9M3 9l9-7 9 7" />
                            </svg>
                            <span>Seller</span>
                        </div>
                    </div>
                    <p class="auth-role-helper" x-text="userType === 'agent'
                        ? 'Agents complete brokerage and license details now so their profile is ready immediately.'
                        : 'Buyers and sellers complete their core profile now so follow-up and onboarding stay smooth.'"></p>

                    <div class="auth-form-grid auth-form-grid--two">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                                placeholder="Taylor Morgan">
                        </div>

                        <div class="form-group">
                            <label>Phone Number *</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel"
                                placeholder="(555) 123-4567">
                        </div>

                        <div class="form-group form-group--full">
                            <label>Email Address *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                                placeholder="you@example.com">
                        </div>
                    </div>

                    <div class="auth-step-actions auth-step-actions--single">
                        <button type="button" class="btn-step btn-step--primary" data-form-next>Continue</button>
                    </div>
                </div>

                <div class="form-step auth-step">
                    <div class="auth-step-meta">
                        <span>Step 2 of 4</span>
                        <strong>Profile & Address</strong>
                    </div>

                    <div class="auth-form-grid auth-form-grid--two">
                        <div class="form-group form-group--full">
                            <label>Profile Image *</label>
                            <div class="auth-upload-panel">
                                <input type="file" name="profile_image" accept="image/*" required>
                                <small>Use a clear headshot or profile photo. JPG, PNG, or WebP up to 4MB.</small>
                            </div>
                        </div>

                        <div class="form-group form-group--full">
                            <label>Street Address *</label>
                            <input type="text" name="address_line_1" value="{{ old('address_line_1') }}" required
                                autocomplete="street-address" placeholder="123 Main Street">
                        </div>

                        <div class="form-group form-group--full">
                            <label>Address Line 2</label>
                            <input type="text" name="address_line_2" value="{{ old('address_line_2') }}"
                                placeholder="Suite, unit, or apartment">
                        </div>

                        <div class="form-group">
                            <label>City *</label>
                            <input type="text" name="city" value="{{ old('city') }}" required
                                autocomplete="address-level2" placeholder="Dallas">
                        </div>

                        <div class="form-group">
                            <label>State *</label>
                            <input type="text" name="state" value="{{ old('state') }}" required maxlength="2"
                                autocomplete="address-level1" placeholder="TX">
                        </div>

                        <div class="form-group">
                            <label>ZIP Code *</label>
                            <input type="text" name="zip_code" value="{{ old('zip_code') }}" required
                                autocomplete="postal-code" placeholder="75201">
                        </div>
                    </div>

                    <div class="auth-step-actions">
                        <button type="button" class="btn-step btn-step--ghost" data-form-prev>Back</button>
                        <button type="button" class="btn-step btn-step--primary" data-form-next>Continue</button>
                    </div>
                </div>

                <div class="form-step auth-step">
                    <div class="auth-step-meta">
                        <span>Step 3 of 4</span>
                        <strong>Credentials & Security</strong>
                    </div>

                    <div x-show="userType === 'agent'" x-cloak>
                        <div class="auth-section-divider">
                            <span>Agent Credentials</span>
                        </div>

                        <div class="auth-form-grid auth-form-grid--two">
                            <div class="form-group">
                                <label>Brokerage *</label>
                                <input type="text" name="brokerage_name" value="{{ old('brokerage_name') }}"
                                    :required="userType === 'agent'" placeholder="Premier Realty Group">
                            </div>

                            <div class="form-group">
                                <label>License Number *</label>
                                <input type="text" name="license_number" value="{{ old('license_number') }}"
                                    :required="userType === 'agent'" placeholder="TX-1234567">
                            </div>
                        </div>
                    </div>

                    <div class="auth-section-divider">
                        <span>Security</span>
                    </div>

                    <div class="auth-form-grid auth-form-grid--two">
                        <div class="form-group">
                            <label>Password *</label>
                            <input type="password" name="password" required autocomplete="new-password"
                                placeholder="Create a secure password">
                        </div>

                        <div class="form-group">
                            <label>Confirm Password *</label>
                            <input type="password" name="password_confirmation" required autocomplete="new-password"
                                placeholder="Re-enter your password">
                        </div>
                    </div>

                    <div class="auth-step-actions">
                        <button type="button" class="btn-step btn-step--ghost" data-form-prev>Back</button>
                        <button type="button" class="btn-step btn-step--primary" data-form-next>Continue</button>
                    </div>
                </div>

                <div class="form-step auth-step">
                    <div class="auth-step-meta">
                        <span>Step 4 of 4</span>
                        <strong>Confirm & Submit</strong>
                    </div>

                    <p class="auth-step-note">
                        Review the details above, then confirm consent so we can activate your workspace and continue
                        onboarding.
                    </p>

                    <ul class="auth-step-summary">
                        <li>Profile image and contact details are required.</li>
                        <li>Address and ZIP code are required for routing.</li>
                        <li>Agent accounts require brokerage and license information.</li>
                    </ul>

                    <div class="auth-checkbox-list">
                        <label class="auth-checkbox-item">
                            <input type="checkbox" name="terms_accepted" value="1" required
                                {{ old('terms_accepted') ? 'checked' : '' }}>
                            <span>I agree to the Terms and Privacy Policy.</span>
                        </label>
                        <label class="auth-checkbox-item">
                            <input type="checkbox" name="communication_accepted" value="1" required
                                {{ old('communication_accepted') ? 'checked' : '' }}>
                            <span>I agree to receive account and onboarding communications by email/SMS.</span>
                        </label>
                    </div>

                    <div class="auth-step-actions">
                        <button type="button" class="btn-step btn-step--ghost" data-form-prev>Back</button>
                        <button type="submit" class="btn-step btn-step--primary">Create Account</button>
                    </div>
                </div>

                <div class="auth-bottom-links">
                    Already have an account? <a href="{{ route('login') }}">Log in</a>
                </div>
            </form>
        </div>
    </div>
@endsection
