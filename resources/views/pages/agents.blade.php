@extends('layouts.app')
@section('content')
<section class="page-hero agent-directory-hero">
    <div class="container page-hero__content">
        <span class="eyebrow">Agent Directory</span>
        <h1>Meet the vetted agents delivering OmniReferral opportunities</h1>
        <p>Search by city, specialty, and active listings to find the agent most aligned with your buyer, seller, or market strategy.</p>
    </div>
</section>
<section class="section agent-directory-section">
    <div class="container">
        <div class="agent-directory-toolbar" data-animate>
            <div>
                <span class="eyebrow">Search agents</span>
                <h2>Filter by city and specialty</h2>
                <p>Find agents with the right market focus and experience for your real estate goals.</p>
            </div>
            <div class="agent-directory-filters">
                <label>
                    <span>City</span>
                    <select id="agentCityFilter">
                        <option value="">All cities</option>
                        @foreach($agents->pluck('city')->filter()->unique()->sort()->values() as $city)
                            <option value="{{ strtolower($city) }}">{{ $city }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Specialty</span>
                    <select id="agentSpecialtyFilter">
                        <option value="">All specialties</option>
                        @foreach($agents->pluck('specialty')->filter()->unique()->sort()->values() as $specialty)
                            <option value="{{ strtolower($specialty) }}">{{ $specialty }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>

        <div class="agent-directory agent-directory--grid" id="agentDirectoryGrid" data-stagger>
            @foreach($agents as $agent)
                <article class="agent-card agent-card--profile" data-agent-card data-city="{{ strtolower($agent->city ?? '') }}" data-specialty="{{ strtolower($agent->specialty ?? 'buyer\'s agent') }}">
                    <img src="{{ asset($agent->headshot ?? 'images/realtors/1.png') }}" alt="{{ $agent->user->name }} profile image" loading="lazy">
                    <div class="agent-card__body">
                        <h2>{{ $agent->user->name }}</h2>
                        <p class="agent-card__meta">{{ $agent->brokerage_name }}</p>
                        <div class="agent-card__stats">
                            <span class="agent-card__rating">{{ str_repeat('★', max(0, min(5, (int) $agent->rating))) }}{{ str_repeat('☆', max(0, 5 - min(5, (int) $agent->rating))) }}</span>
                            <span>{{ $agent->rating ? number_format($agent->rating, 1) : '4.7' }} rating</span>
                            <span>{{ $agent->specialty ?? 'Buyer's Agent' }}</span>
                        </div>
                        <p class="agent-card__location">{{ $agent->city }}, {{ $agent->state }}</p>
                        <div class="agent-card__actions">
                            <a href="{{ route('agents.show', $agent) }}" class="button button--ghost-blue">View Profile</a>
                            <a href="{{ route('contact') }}?agent={{ urlencode($agent->user->name) }}" class="button button--orange">Contact Agent</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="pagination-wrap">{{ $agents->links() }}</div>
    </div>
</section>
@endsection
