@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container" style="max-width: 720px;">
        <div class="card-panel">
            <span class="eyebrow">Application Received</span>
            <h1>Thank you for applying</h1>
            @if(session('success'))
                <p>{{ session('success') }}</p>
            @else
                <p>Your agent profile has been submitted for admin review.</p>
            @endif
            <p>An administrator will review your brokerage details, service area, and bio. You will receive access once your account and profile are approved.</p>
            <div class="hero__actions" style="margin-top: 1.5rem;">
                <a href="{{ route('login') }}" class="button button--orange">Go to Sign In</a>
                <a href="{{ route('home') }}" class="button button--ghost-blue">Back to Home</a>
            </div>
        </div>
    </div>
</section>
@endsection
