@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container-sm">
        <span class="eyebrow">Payment Success</span>
        <h1>Welcome aboard! Your package is confirmed.</h1>
        <p>Your {{ $package->name }} checkout{{ $sessionId ? ' (session ' . $sessionId . ')' : '' }} is complete. GoHighLevel will handle the setup automatically, so you can continue with your normal access flow.</p>
        <div class="hero__actions">
            <a href="{{ $postPurchaseActionUrl }}" class="button button--orange">{{ $postPurchaseActionLabel }}</a>
            <a href="{{ route('pricing') }}" class="button button--ghost-blue">Back To Pricing</a>
        </div>
    </div>
</section>
@endsection
