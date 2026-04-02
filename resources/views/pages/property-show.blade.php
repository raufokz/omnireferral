@extends('layouts.app')

@section('content')
<section class="page-hero property-hero">
    <div class="container property-hero__content">
        <span class="eyebrow">Property Details</span>
        <h1>{{ $property->title }}</h1>
        <p>{{ $property->location }} · {{ $property->zip_code }} · {{ $property->property_type }}</p>
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
                        <a href="{{ route('contact') }}?property={{ urlencode($property->title) }}" class="button">Contact Agent</a>
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
                <iframe title="Property location map" src="https://www.google.com/maps?q={{ urlencode($property->zip_code) }}&output=embed" loading="lazy"></iframe>
            </aside>
        </div>

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
