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

            <h1>OMNIREFERRAL.<br>The Complete Real Estate Ecosystem. Connect, Transact, and Grow.</h1>
            <p>Unlock endless possibilities in the property market. Join thousands of professionals, buyers, and sellers
                today.</p>
        </div>

        <!-- Right Column (White background) -->
        <div class="auth-col-right" x-data="{ userType: '{{ old('role', 'agent') }}' }">
            <h2>Create Your Account</h2>
            <p class="auth-subtitle">Welcome to OmniReferral</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Hidden role input that Alpine updates -->
                <input type="hidden" name="role" x-model="userType">

                <span class="user-type-label">Choose a user type</span>
                <div class="user-type-grid">
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
                    <div class="ut-card" :class="{ 'active': userType === 'admin' }" @click="userType = 'admin'">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <span>Admin</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required autocomplete="new-password">
                </div>

                <div class="form-group" x-show="userType === 'agent'">
                    <label>Company (Optional for Agents)</label>
                    <input type="text" name="company">
                </div>

                <button type="submit" class="btn-submit">Continue</button>

                <div class="auth-bottom-links">
                    Already have an account? <a href="{{ route('login') }}">Log in</a>
                </div>
            </form>
        </div>
    </div>
@endsection