@extends('layouts.app')
@section('content')
@php
    $agentCollection = $agents->getCollection();
    $agentCityCount = $agentCollection
        ->map(fn ($agent) => $agent->realtorProfile?->service_city)
        ->filter()
        ->unique()
        ->count();
    $agentSpecialtyCount = $agentCollection
        ->map(fn ($agent) => $agent->realtorProfile?->specialties)
        ->filter()
        ->unique()
        ->count();
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
                            @foreach($agentCollection->map(fn ($agent) => $agent->realtorProfile?->service_city)->filter()->unique()->sort()->values() as $city)
                                <option value="{{ strtolower($city) }}">{{ $city }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>Specialty</span>
                        <select id="agentSpecialtyFilter">
                            <option value="">All specialties</option>
                            @foreach($agentCollection->map(fn ($agent) => $agent->realtorProfile?->specialties)->filter()->unique()->sort()->values() as $specialty)
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
                    $profile = $agent->realtorProfile;
                    $displayName = $agent->publicDisplayName() ?: 'OmniReferral Agent';
                    $profileImage = $agent->profilePhotoPublicUrl();

                    if (! $profileImage && $profile?->headshot) {
                        $profileImage = \Illuminate\Support\Str::startsWith($profile->headshot, ['http://', 'https://'])
                            ? $profile->headshot
                            : asset(ltrim($profile->headshot, '/'));
                    }

                    $profileImage ??= asset('images/realtors/1.png');
                    $ratingValue = $profile?->rating ? number_format($profile->rating, 1) : 'New';
                    $specialty = $profile?->specialties ?: 'Agent';
                    $brokerage = $profile?->brokerage_name ?: 'OmniReferral Agent Network';
                    $marketLabel = trim(collect([$profile?->service_city, $profile?->service_state])->filter()->implode(', '));
                    $bio = $profile?->bio ?: 'Verified OmniReferral agent account ready for buyer, seller, and listing opportunities.';
                    $statusLabel = \Illuminate\Support\Str::title($agent->status ?: 'Pending');
                    $profileRoute = $profile ? route('agents.show', $profile) : null;
                @endphp
                <article
                    class="agent-card agent-card--profile"
                    data-agent-card
                    data-city="{{ strtolower($profile?->service_city ?? '') }}"
                    data-specialty="{{ strtolower($specialty) }}"
                >
                    <div class="agent-card__media">
                        <img src="{{ $profileImage }}" alt="{{ $displayName }} profile image" loading="lazy">
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

                        <h2>{{ $displayName }}</h2>
                        <p class="agent-card__meta">{{ $brokerage }}</p>

                        <div class="agent-card__stats">
                            <span>{{ $agent->email }}</span>
                            <span>{{ $agent->phone ?: 'Phone not added' }}</span>
                            <span>Status: {{ $statusLabel }}</span>
                        </div>

                        <p class="agent-card__bio">{{ \Illuminate\Support\Str::limit($bio, 120) }}</p>

                        <div class="agent-card__actions">
                            @if($profileRoute)
                                <a href="{{ $profileRoute }}" class="button button--ghost-blue">View Profile</a>
                                <a href="{{ $profileRoute }}#agent-contact" class="button button--orange">Contact Agent</a>
                            @else
                                <span class="button button--ghost-blue button--disabled" aria-disabled="true">Profile Pending</span>
                                <a href="mailto:{{ $agent->email }}" class="button button--orange">Email Agent</a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="agent-directory-empty-state" id="agentDirectoryEmpty" @if($agents->count() > 0) hidden @endif>
            <span class="agent-directory-empty-state__icon">No Match</span>
            <h3>{{ $agents->count() > 0 ? 'No agents match this filter yet' : 'No Agents Found' }}</h3>
            <p>{{ $agents->count() > 0 ? 'Try another city or specialty to see more vetted partners from this page of the directory.' : 'Agent accounts will appear here once users with the Agent role are added to the platform.' }}</p>
            @if($agents->count() > 0)
                <button type="button" class="button button--orange" id="agentEmptyReset">Clear filters</button>
            @endif
        </div>

        <div class="pagination-wrap">{{ $agents->links() }}</div>
    </div>
</section>
@endsection
