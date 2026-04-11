@extends('layouts.app')
@section('content')
@php
    $agentCollection = $agents->getCollection();
    $agentCityCount = $agentCollection->pluck('city')->filter()->unique()->count();
    $agentSpecialtyCount = $agentCollection->pluck('specialty')->filter()->unique()->count();
@endphp
<section class="page-hero agent-directory-hero">
    <div class="agent-directory-hero__glow" aria-hidden="true"></div>
    <div class="container agent-directory-hero__inner">
        <div class="agent-directory-hero__copy" data-animate="left">
            <span class="eyebrow">Agent Directory</span>
            <h1>Meet the vetted agents delivering OmniReferral opportunities</h1>
            <p>Search by city, specialty, and active listings to find the agent most aligned with your buyer, seller, or market strategy.</p>

            <div class="agent-directory-hero__actions">
                <a href="#agent-directory-results" class="button button--orange">Browse Agents</a>
                <a href="{{ route('contact') }}" class="button button--ghost-light">Talk To Our Team</a>
            </div>

            <div class="agent-directory-hero__proof">
                <span>Verified agent profiles</span>
                <span>Market-specific specialties</span>
                <span>Direct contact paths</span>
            </div>
        </div>

        <aside class="agent-directory-hero__panel" data-animate="right">
            <span class="agent-directory-hero__panel-eyebrow">Directory Snapshot</span>
            <h2>Find the right partner faster</h2>
            <p>Every profile is positioned to make local expertise, specialty fit, and the next contact step obvious.</p>

            <div class="agent-directory-hero__stats">
                <div class="agent-directory-hero__stat">
                    <strong>{{ method_exists($agents, 'total') ? number_format($agents->total()) : number_format($agents->count()) }}</strong>
                    <span>Agent profiles</span>
                </div>
                <div class="agent-directory-hero__stat">
                    <strong>{{ $agentCityCount }}</strong>
                    <span>Cities on this page</span>
                </div>
                <div class="agent-directory-hero__stat">
                    <strong>{{ $agentSpecialtyCount }}</strong>
                    <span>Specialties represented</span>
                </div>
            </div>
        </aside>
    </div>
</section>

<section class="section agent-directory-section" id="agent-directory-results">
    <div class="container">
        <div class="agent-directory-toolbar" data-animate>
            <div class="agent-directory-toolbar__copy">
                <span class="eyebrow">Search agents</span>
                <h2>Filter by city and specialty</h2>
                <p>Find agents with the right market focus and experience for your real estate goals.</p>
            </div>

            <div class="agent-directory-toolbar__panel">
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

                <div class="agent-directory-toolbar__footer">
                    <p class="agent-directory-results" id="agentDirectoryCount">Showing {{ $agents->count() }} agents on this page</p>
                    <button type="button" class="button button--ghost-blue agent-directory-reset" id="agentFilterReset">Reset filters</button>
                </div>
            </div>
        </div>

        <div class="agent-directory agent-directory--grid" id="agentDirectoryGrid" data-stagger>
            @foreach($agents as $agent)
                @php
                    $ratingValue = $agent->rating ? number_format($agent->rating, 1) : '4.7';
                    $specialty = $agent->specialty ?: 'Buyers Agent';
                    $brokerage = $agent->brokerage_name ?: 'OmniReferral Partner Brokerage';
                    $marketLabel = trim(collect([$agent->city, $agent->state])->filter()->implode(', '));
                    $bio = $agent->bio ?: 'Experienced local partner helping buyers and sellers move through qualified opportunities with more clarity and confidence.';
                @endphp
                <article
                    class="agent-card agent-card--profile"
                    data-agent-card
                    data-city="{{ strtolower($agent->city ?? '') }}"
                    data-specialty="{{ strtolower($agent->specialty ?? 'buyers agent') }}"
                >
                    <div class="agent-card__media">
                        <img src="{{ asset($agent->headshot ?? 'images/realtors/1.png') }}" alt="{{ $agent->user->name }} profile image" loading="lazy">
                        <span class="agent-card__badge">Verified Agent</span>
                        <span class="agent-card__rating-chip" aria-label="{{ $ratingValue }} rating">
                            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.037 3.19a1 1 0 00.95.69h3.355c.969 0 1.371 1.24.588 1.81l-2.714 1.972a1 1 0 00-.364 1.118l1.037 3.19c.3.921-.755 1.688-1.54 1.118l-2.714-1.972a1 1 0 00-1.176 0l-2.714 1.972c-.784.57-1.838-.197-1.539-1.118l1.037-3.19a1 1 0 00-.364-1.118L2.17 8.617c-.783-.57-.38-1.81.588-1.81h3.356a1 1 0 00.95-.69l1.037-3.19z" />
                            </svg>
                            {{ $ratingValue }}
                        </span>
                    </div>

                    <div class="agent-card__body">
                        <div class="agent-card__topline">
                            <span class="agent-card__specialty">{{ $specialty }}</span>
                            <span class="agent-card__market">{{ $marketLabel ?: 'Local Market' }}</span>
                        </div>

                        <h2>{{ $agent->user->name }}</h2>
                        <p class="agent-card__meta">{{ $brokerage }}</p>

                        <div class="agent-card__stats">
                            <span>{{ $ratingValue }} client rating</span>
                            <span>{{ $specialty }}</span>
                            <span>{{ $marketLabel ?: 'Market ready' }}</span>
                        </div>

                        <p class="agent-card__bio">{{ \Illuminate\Support\Str::limit($bio, 120) }}</p>

                        <div class="agent-card__actions">
                            <a href="{{ route('agents.show', $agent) }}" class="button button--ghost-blue">View Profile</a>
                            <a href="{{ route('agents.show', $agent) }}#agent-contact" class="button button--orange">Contact Agent</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="agent-directory-empty-state" id="agentDirectoryEmpty" @if($agents->count() > 0) hidden @endif>
            <span class="agent-directory-empty-state__icon">No Match</span>
            <h3>No agents match this filter yet</h3>
            <p>Try another city or specialty to see more vetted partners from this page of the directory.</p>
            <button type="button" class="button button--orange" id="agentEmptyReset">Clear filters</button>
        </div>

        <div class="pagination-wrap">{{ $agents->links() }}</div>
    </div>
</section>
@endsection
