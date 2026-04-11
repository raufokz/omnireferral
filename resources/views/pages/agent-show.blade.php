@extends('layouts.app')

@section('content')
@php
    $agentHeadshot = $agent->headshot;
    $agentImage = $agentHeadshot
        ? (\Illuminate\Support\Str::startsWith($agentHeadshot, ['http://', 'https://', '/storage/', 'storage/']) ? $agentHeadshot : asset($agentHeadshot))
        : ($agent->user?->avatar ? asset('storage/' . ltrim($agent->user->avatar, '/')) : asset('images/realtors/3.png'));

    $agentLocation = collect([$agent->city, $agent->state, $agent->zip_code])->filter()->implode(', ');
    $specialties = collect(explode(',', (string) $agent->specialties))
        ->map(fn ($item) => trim($item))
        ->filter()
        ->take(4);
@endphp

<section class="page-hero page-hero--split agent-public-hero">
    <div class="container page-hero__split">
        <div class="page-hero__copy agent-public-hero__copy">
            <span class="eyebrow">Agent Profile</span>
            <h1>{{ $agent->user->name }}</h1>
            <p>
                {{ $agent->brokerage_name ?: 'OmniReferral Partner Network' }}
                @if($agentLocation)
                    <span>&middot;</span> {{ $agentLocation }}
                @endif
            </p>

            <div class="agent-public-hero__chips">
                <span>{{ number_format((float) $agent->rating, 1) }} rating</span>
                <span>{{ $agent->review_count }} reviews</span>
                <span>{{ $agent->properties->count() }} active listings</span>
            </div>

            <div class="hero__actions">
                <a href="#agent-contact" class="button">Contact This Agent</a>
                <a href="{{ route('listings') }}" class="button button--ghost-blue">Browse Listings</a>
            </div>
        </div>

        <div class="card-panel agent-public-hero__panel">
            <div class="agent-public-hero__panel-head">
                <img src="{{ $agentImage }}" alt="{{ $agent->user->name }} headshot" loading="lazy">
                <div>
                    <span class="eyebrow">Profile Snapshot</span>
                    <h2>{{ $agent->user->name }}</h2>
                    <p>{{ $agent->brokerage_name ?: 'OmniReferral Partner' }}</p>
                </div>
            </div>

            <div class="agent-public-hero__stats">
                <div>
                    <span>Rating</span>
                    <strong>{{ number_format((float) $agent->rating, 1) }}</strong>
                </div>
                <div>
                    <span>Reviews</span>
                    <strong>{{ $agent->review_count }}</strong>
                </div>
                <div>
                    <span>Closed Leads</span>
                    <strong>{{ $agent->leads_closed }}</strong>
                </div>
                <div>
                    <span>Listings</span>
                    <strong>{{ $agent->properties->count() }}</strong>
                </div>
            </div>

            <div class="agent-public-hero__footer">
                <strong>Why users choose this profile</strong>
                <p>Direct messaging, live listing visibility, and a cleaner path from discovery to agent follow-up.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container agent-public-grid">
        <article class="card-panel agent-public-profile">
            <div class="section-heading agent-public-section-heading">
                <span class="eyebrow">About</span>
                <h2>Meet {{ $agent->user->name }}</h2>
            </div>

            <p>{{ $agent->bio ?: 'This OmniReferral partner agent is ready to support buyers and sellers with a more responsive, qualification-first workflow.' }}</p>

            @if($specialties->isNotEmpty())
                <div class="agent-public-specialties">
                    @foreach($specialties as $specialty)
                        <span>{{ $specialty }}</span>
                    @endforeach
                </div>
            @endif

            <div class="agent-public-profile__meta">
                <div>
                    <dt>Brokerage</dt>
                    <dd>{{ $agent->brokerage_name ?: 'OmniReferral Partner Network' }}</dd>
                </div>
                <div>
                    <dt>Service Area</dt>
                    <dd>{{ $agentLocation ?: 'Area pending update' }}</dd>
                </div>
                <div>
                    <dt>Specialties</dt>
                    <dd>{{ $agent->specialties ?: 'Buyer representation, seller strategy, relocation, lead conversion' }}</dd>
                </div>
                <div>
                    <dt>Contact</dt>
                    <dd>
                        {{ $agent->user->email }}
                        @if($agent->user->phone)
                            <span>&middot;</span> {{ $agent->user->phone }}
                        @endif
                    </dd>
                </div>
            </div>
        </article>

        <aside class="card-panel agent-public-contact" id="agent-contact">
            <span class="eyebrow">Direct Inquiry</span>
            <h2>Send a message to {{ $agent->user->name }}</h2>
            <p>Your message will go directly into this agent's inbox inside OmniReferral.</p>

            <form action="{{ route('contact.submit') }}" method="POST" class="agent-contact-form">
                @csrf
                <input type="hidden" name="realtor_profile_id" value="{{ $agent->id }}">
                <input type="hidden" name="recipient_user_id" value="{{ $agent->user_id }}">
                <input type="hidden" name="source" value="website_agent_profile">

                <label>
                    <span>Full Name</span>
                    <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" required>
                </label>
                <label>
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required>
                </label>
                <label>
                    <span>Phone Number</span>
                    <input type="text" name="phone" value="{{ old('phone', auth()->user()?->phone) }}">
                </label>
                <label>
                    <span>I am a</span>
                    <select name="role">
                        @foreach(['buyer' => 'Buyer', 'seller' => 'Seller', 'agent' => 'Agent', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" {{ old('role', auth()->user()?->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Subject</span>
                    <input type="text" name="subject" value="{{ old('subject', 'Inquiry for ' . $agent->user->name) }}">
                </label>
                <label>
                    <span>Message</span>
                    <textarea name="message" rows="5" required>{{ old('message') }}</textarea>
                </label>

                <button type="submit" class="button">Send Direct Message</button>
            </form>

            <div class="agent-public-contact__assurance">
                <strong>Clean handoff promise</strong>
                <p>Messages from this page are routed directly to the agent workspace so follow-up stays visible and organized.</p>
            </div>
        </aside>
    </div>
</section>

<section class="section section--soft agent-public-listings-section">
    <div class="container">
        <div class="section-heading agent-public-section-heading">
            <span class="eyebrow">Current Listings</span>
            <h2>Properties represented by {{ $agent->user->name }}</h2>
            <p>Explore active inventory connected to this agent profile and continue the conversation from the listing details page.</p>
        </div>

        <div class="listing-grid listing-grid--showcase agent-public-listing-grid">
            @forelse($agent->properties as $property)
                <article class="listing-card listing-card--showcase agent-public-listing-card">
                    <div class="listing-card__media">
                        <img src="{{ $property->image_url }}" alt="{{ $property->title }}" loading="lazy">
                        <span class="listing-badge">{{ $property->status }}</span>
                        <div class="listing-card__save-group">
                            <form method="POST" action="{{ route('properties.favorite.toggle', $property) }}" class="listing-card__save-form">
                                @csrf
                                <button
                                    type="submit"
                                    class="listing-card__save {{ $property->is_favorited ? 'is-active' : '' }}"
                                    aria-label="{{ $property->is_favorited ? 'Remove property from favorites' : 'Add property to favorites' }}"
                                >
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="{{ $property->is_favorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                </button>
                            </form>
                            <span class="listing-card__save-count">{{ number_format($property->favorites_count ?? 0) }}</span>
                        </div>
                    </div>
                    <div class="listing-card__body">
                        <div class="listing-card__top">
                            <strong>${{ number_format($property->price) }}</strong>
                            <span class="listing-type">{{ $property->property_type }}</span>
                        </div>
                        <h3>{{ $property->title }}</h3>
                        <p class="listing-location">{{ $property->location }}</p>
                        <div class="listing-card__meta listing-card__meta--pills">
                            <span>{{ $property->beds }} bd</span>
                            <span>{{ $property->baths }} ba</span>
                            <span>{{ number_format($property->sqft) }} sqft</span>
                        </div>
                        <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">View Details</a>
                    </div>
                </article>
            @empty
                <div class="cockpit-empty-state">
                    <h3>No public listings yet</h3>
                    <p class="text-gray-500">This agent has not published listings to the marketplace yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
