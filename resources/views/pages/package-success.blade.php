@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container-sm">
        <span class="eyebrow">Package Next Steps</span>
        <h1>Your package workflow is ready.</h1>
        <p>Your {{ $package->displayName() }} details are ready for the OmniReferral team. If you completed the secure form, GoHighLevel will handle the setup handoff automatically.</p>
        <div class="hero__actions">
            <a href="{{ $postPurchaseActionUrl }}" class="button button--orange">{{ $postPurchaseActionLabel }}</a>
            <a href="{{ route('pricing') }}" class="button button--ghost-blue">Back To Pricing</a>
        </div>
    </div>
</section>
@endsection
