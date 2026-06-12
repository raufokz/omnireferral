@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/agent-directory.css')
@endpush

@section('content')
<section class="section">
    <div class="container" style="max-width:900px;">
        <article class="card-panel">
            @if($card['is_featured'])
                <span class="agent-card-premium__featured">⭐ Featured Agent</span>
            @endif
            <div style="display:grid;grid-template-columns:200px 1fr;gap:1.25rem;margin-top:1rem;">
                <img src="{{ $card['headshot_url'] }}" alt="{{ $card['name'] }}" style="width:100%;border-radius:16px;object-fit:cover;" loading="lazy">
                <div>
                    <h1 style="margin:0 0 0.35rem;">{{ $card['name'] }}</h1>
                    <p>{{ $card['brokerage'] }}</p>
                    <p>{{ $card['service_area'] }}</p>
                    <p><strong>{{ $card['rating'] }}</strong> rating · {{ $card['review_count'] }} reviews</p>
                    @if($card['years_of_experience'])
                        <p>{{ $card['years_of_experience'] }}+ years experience</p>
                    @endif
                </div>
            </div>
            <p style="margin-top:1.25rem;">{{ e($profile->bio) }}</p>
            @if($profile->languages)<p><strong>Languages:</strong> {{ e($profile->languages) }}</p>@endif
            @if($profile->market_areas)<p><strong>Market areas:</strong> {{ e($profile->market_areas) }}</p>@endif
            <div class="hero__actions" style="margin-top:1.25rem;">
                <a href="{{ route('agents.index') }}" class="button button--ghost-blue">Browse all agents</a>
                <a href="{{ route('agents.location', Str::slug(strtolower($profile->service_city ?? 'agents'))) }}" class="button button--orange">More in {{ $profile->service_city }}</a>
            </div>
        </article>

        <article class="card-panel" style="margin-top:1.5rem;" id="contact">
            <h2>Contact OmniReferral about this agent</h2>
            <p class="text-muted">Requests are handled by our team. Agent contact details are not shown publicly.</p>
            <form method="POST" action="{{ route('agents.inquiry', $profile) }}" class="agent-inquiry-form">
                @csrf
                <input type="hidden" name="inquiry_type" value="contact">
                <label>Your name<input type="text" name="name" value="{{ old('name') }}" required></label>
                <label>Email<input type="email" name="email" value="{{ old('email') }}" required></label>
                <label>Phone<input type="tel" name="phone" value="{{ old('phone') }}"></label>
                <label>Your city<input type="text" name="city" value="{{ old('city') }}"></label>
                <label>Message<textarea name="message" rows="4" required>{{ old('message') }}</textarea></label>
                <label>Property requirements<textarea name="property_requirements" rows="3">{{ old('property_requirements') }}</textarea></label>
                <button type="submit" class="button button--orange">Submit to OmniReferral Team</button>
            </form>
            @if(session('success'))<p style="color:#0b3668;font-weight:600;">{{ session('success') }}</p>@endif
        </article>
    </div>
</section>
@endsection
