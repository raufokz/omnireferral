@extends('layouts.app')

@section('content')
<section class="hero hero--premium account-page-hero">
    <div class="hero__backdrop">
        <img src="{{ asset('images/home/hero_backdrop_v2.png') }}" alt="" class="hero__backdrop-img" aria-hidden="true">
        <div class="hero__backdrop-overlay"></div>
    </div>
    <div class="container account-page-hero__content">
        <div class="account-page-hero__copy">
            <span class="eyebrow">Set Your Password</span>
            <h1>Choose a new password</h1>
            <p>For security, please set a new password before continuing to your dashboard.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width: 720px;">
        <div class="cockpit-table-card" style="padding: 1.75rem;">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert" style="margin-bottom: 1.5rem;">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h2 style="margin-bottom: 0.5rem;">Password</h2>
            <p style="margin-top: 0; color: rgba(15, 23, 42, 0.7);">Choose a strong password you do not use elsewhere.</p>

            <form action="{{ route('password.change.update') }}" method="POST" style="display: grid; gap: 1rem;">
                @csrf

                @if(auth()->user()->password_set_at)
                    <label class="floating-group" style="margin-bottom: 0;">
                        <input type="password" name="current_password" required placeholder=" ">
                        <span>Current password</span>
                    </label>
                @endif

                <label class="floating-group" style="margin-bottom: 0;">
                    <input type="password" name="password" required minlength="8" placeholder=" ">
                    <span>New password</span>
                </label>

                <label class="floating-group" style="margin-bottom: 0;">
                    <input type="password" name="password_confirmation" required minlength="8" placeholder=" ">
                    <span>Confirm new password</span>
                </label>

                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 0.5rem;">
                    <button type="submit" class="button button--orange">Save password</button>
                    <a class="button button--ghost-blue" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>
                </div>
            </form>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>
</section>
@endsection
