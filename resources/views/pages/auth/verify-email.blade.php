@extends('layouts.app')

@section('content')
<section class="container" style="max-width: 640px; margin: 3rem auto; padding: 0 1rem;">
    <h1 style="margin-bottom: 0.75rem;">Verify your email</h1>
    <p style="margin-bottom: 1.25rem; color: #4b5563;">
        Thanks for joining OmniReferral. Before you can open your workspace, please confirm your email address using the link we sent you.
    </p>

    @if (session('status') || session('success'))
        <p style="margin-bottom: 1rem; color: #059669;">{{ session('success') ?? __('A fresh verification link has been sent to your email address.') }}</p>
    @endif

    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="button button--orange">Resend verification email</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="button button--ghost-blue">Sign out</button>
        </form>
    </div>

    <p style="margin-top: 1.5rem; font-size: 0.9rem; color: #6b7280;">
        Wrong inbox? Check spam or promotions, or sign out and register again with the correct address.
    </p>
</section>
@endsection
