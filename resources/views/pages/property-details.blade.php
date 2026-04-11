@extends('layouts.app')

@section('content')
<section class="page-hero property-hero">
    <div class="container property-hero__content">
        <span class="eyebrow">Property Details</span>
        <h1>{{ $property->title }}</h1>
        <p>{{ $property->location }} &middot; {{ $property->zip_code }} &middot; {{ $property->property_type }}</p>
    </div>
</section>

<section class="section property-page">
    <div class="container">
        <div class="property-layout">
            <article class="card-panel property-panel">
                <div class="property-panel__media">
                    <img src="{{ $property->image_url }}" alt="{{ $property->title }} listing image" loading="lazy">
                </div>
                <div class="property-panel__body">
                    <div class="property-panel__headline">
                        <div>
                            <span class="pricing-label">Verified Listing</span>
                            <h2>${{ number_format($property->price) }}</h2>
                            <p>{{ $property->location }}</p>
                        </div>
                        <span class="listing-badge">{{ $property->status }}</span>
                    </div>
                    <div class="listing-card__meta listing-card__meta--pills">
                        <span>{{ $property->beds }} bd</span>
                        <span>{{ $property->baths }} ba</span>
                        <span>{{ number_format($property->sqft) }} sqft</span>
                    </div>
                    <p>
                        This listing is part of the OmniReferral marketplace experience, designed to give buyers and agents a clearer handoff between
                        discovery, qualification, and direct follow-up.
                    </p>
                    <div class="hero__actions">
                        <a href="#property-contact" class="button">Contact Agent</a>
                        @if($property->realtorProfile)
                            <a href="{{ route('agents.show', $property->realtorProfile) }}" class="button button--ghost-blue">View Agent Profile</a>
                        @else
                            <a href="{{ route('agents.index') }}" class="button button--ghost-blue">Browse Agents</a>
                        @endif
                    </div>
                </div>
            </article>

            <aside class="card-panel property-sidebar">
                <span class="eyebrow">Assigned Realtor</span>
                <div class="property-sidebar__agent">
                    <img
                        src="{{ asset(optional($property->realtorProfile)->headshot ?: 'images/realtors/3.png') }}"
                        alt="{{ optional(optional($property->realtorProfile)->user)->name ?? 'OmniReferral partner agent' }}"
                        loading="lazy"
                    >
                    <div>
                        <h3>{{ optional(optional($property->realtorProfile)->user)->name ?? 'OmniReferral Partner' }}</h3>
                        <p>{{ optional($property->realtorProfile)->brokerage_name ?? 'OmniReferral Network' }}</p>
                        <span>{{ optional($property->realtorProfile)->city }}{{ optional($property->realtorProfile)->city ? ',' : '' }} {{ optional($property->realtorProfile)->state }}</span>
                    </div>
                </div>
                <ul class="feature-list compact">
                    <li>Fast response routing for qualified opportunities.</li>
                    <li>Lead handoff visibility across buyer, seller, and agent workflows.</li>
                    <li>Support available through onboarding, package upgrades, and marketplace discovery.</li>
                </ul>
                <div class="property-sidebar__map">
                    <iframe title="Property location map" src="https://www.google.com/maps?q={{ urlencode($property->zip_code) }}&output=embed" loading="lazy"></iframe>
                </div>
            </aside>
        </div>

        @if($property->realtorProfile)
            <div class="property-contact-grid" id="property-contact">
                <section class="card-panel property-contact-card">
                    <div class="section-heading" style="text-align:left; margin: 0 0 1.5rem;">
                        <span class="eyebrow">Direct Inquiry</span>
                        <h2>Message the listing agent</h2>
                    </div>

                    <form action="{{ route('contact.submit') }}" method="POST" class="agent-contact-form">
                        @csrf
                        <input type="hidden" name="property_id" value="{{ $property->id }}">
                        <input type="hidden" name="realtor_profile_id" value="{{ $property->realtorProfile->id }}">
                        <input type="hidden" name="recipient_user_id" value="{{ $property->realtorProfile->user_id }}">
                        <input type="hidden" name="source" value="website_property_inquiry">

                        <div class="form-grid-2">
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
                                <input type="text" name="subject" value="{{ old('subject', 'Inquiry about ' . $property->title) }}">
                            </label>
                            <label class="form-full-row">
                                <span>Message</span>
                                <textarea name="message" rows="5" required>{{ old('message') }}</textarea>
                            </label>
                        </div>

                        <button type="submit" class="button">Send Message To Agent</button>
                    </form>
                </section>

                <aside class="card-panel property-contact-aside">
                    <span class="eyebrow">Agent Snapshot</span>
                    <h3>{{ optional(optional($property->realtorProfile)->user)->name ?? 'OmniReferral Partner' }}</h3>
                    <p>{{ optional($property->realtorProfile)->bio ?: 'Reach out here to start the conversation about this property and get a direct response from the listing agent.' }}</p>
                    <div class="agent-public-profile__meta">
                        <div>
                            <dt>Brokerage</dt>
                            <dd>{{ optional($property->realtorProfile)->brokerage_name ?: 'OmniReferral Network' }}</dd>
                        </div>
                        <div>
                            <dt>Service Area</dt>
                            <dd>{{ optional($property->realtorProfile)->city }}{{ optional($property->realtorProfile)->city ? ', ' : '' }}{{ optional($property->realtorProfile)->state }}</dd>
                        </div>
                    </div>
                </aside>
            </div>
        @endif

        @if($relatedProperties->isNotEmpty())
            <div class="property-related">
                <div class="section-heading" style="text-align:left; margin: 0 0 2rem;">
                    <span class="eyebrow">More In This Area</span>
                    <h2>Related listings near {{ $property->zip_code }}</h2>
                </div>
                <div class="listing-grid listing-grid--showcase">
                    @foreach($relatedProperties as $relatedProperty)
                        <article class="listing-card listing-card--showcase">
                            <div class="listing-card__media">
                                <img src="{{ $relatedProperty->image_url }}" alt="{{ $relatedProperty->title }}" loading="lazy">
                                <span class="listing-badge">{{ $relatedProperty->status }}</span>
                                <div class="listing-card__save-group">
                                    <form method="POST" action="{{ route('properties.favorite.toggle', $relatedProperty) }}" class="listing-card__save-form">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="listing-card__save {{ $relatedProperty->is_favorited ? 'is-active' : '' }}"
                                            aria-label="{{ $relatedProperty->is_favorited ? 'Remove property from favorites' : 'Add property to favorites' }}"
                                        >
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="{{ $relatedProperty->is_favorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                        </button>
                                    </form>
                                    <span class="listing-card__save-count">{{ number_format($relatedProperty->favorites_count ?? 0) }}</span>
                                </div>
                            </div>
                            <div class="listing-card__body">
                                <div class="listing-card__top">
                                    <strong>${{ number_format($relatedProperty->price) }}</strong>
                                    <span class="listing-type">{{ $relatedProperty->property_type }}</span>
                                </div>
                                <h3>{{ $relatedProperty->title }}</h3>
                                <p class="listing-location">{{ $relatedProperty->location }}</p>
                                <a href="{{ route('properties.show', $relatedProperty) }}" class="button button--ghost-blue">View Details</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
