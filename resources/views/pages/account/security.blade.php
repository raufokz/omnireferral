@extends('layouts.app')

@section('content')
<section class="page-hero dashboard-page-hero dashboard-page-hero--agent">
    <div class="container page-hero__content">
        <span class="eyebrow">Account Security</span>
        <h1>Update your password</h1>
        <p>For your protection, set a new password before continuing into your workspace.</p>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width: 720px;">
        <div class="cockpit-table-card" style="padding: 1.75rem;">
            <h2 style="margin-bottom: 0.5rem;">Password</h2>
            <p style="margin-top: 0; color: rgba(15, 23, 42, 0.7);">Choose a strong password you don’t use elsewhere.</p>

            <form action="{{ route('account.password.update') }}" method="POST" class="mt-6" style="display: grid; gap: 1rem;">
                @csrf
                <label class="floating-group" style="margin-bottom: 0;">
                    <input type="password" name="current_password" required placeholder=" ">
                    <span>Current password</span>
                </label>

                <label class="floating-group" style="margin-bottom: 0;">
                    <input type="password" name="password" required minlength="8" placeholder=" ">
                    <span>New password</span>
                </label>

                <label class="floating-group" style="margin-bottom: 0;">
                    <input type="password" name="password_confirmation" required minlength="8" placeholder=" ">
                    <span>Confirm new password</span>
                </label>

                <div class="flex items-center justify-between gap-3" style="margin-top: 0.5rem;">
                    <button type="submit" class="button button--orange">Save password</button>
                    <a class="button button--ghost-blue" href="{{ route('dashboard') }}">Back to dashboard</a>
                </div>
            </form>

            <form action="{{ route('logout') }}" method="POST" style="margin-top: 1rem;">
                @csrf
                <button type="submit" class="button button--ghost-blue">Logout</button>
            </form>
        </div>
    </div>
</section>
@endsection

