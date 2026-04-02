@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container-sm">
        <span class="eyebrow">Payment Success</span>
        <h1>Welcome aboard! Your package is confirmed.</h1>
        <p>Your {{ $package->name }} checkout{{ $sessionId ? ' (session ' . $sessionId . ')' : '' }} is complete. The next step is onboarding so we can provision your workspace and sync your CRM details.</p>
        <div class="hero__actions">
            <a href="{{ $onboardingUrl }}" class="button button--orange">Start Onboarding</a>
            <a href="{{ route('dashboard.agent') }}" class="button button--ghost-blue">View Agent Dashboard</a>
        </div>
    </div>
</section>
@endsection
