@extends('layouts.app')

@section('content')
@php
    $galleryUrls = $property->galleryImageUrls();
    $mainImage = $galleryUrls->first();
    $amenityLabels = collect($property->amenities ?? [])
        ->filter()
        ->values()
        ->whenEmpty(fn ($collection) => $collection->push('Electricity', 'Gas', 'Water', 'Parking', 'Internet', 'Security'));
    $locationHighlights = collect(preg_split('/\r\n|\r|\n/', (string) $property->location_highlights))
        ->map(fn ($item) => trim($item))
        ->filter()
        ->values()
        ->whenEmpty(fn ($collection) => $collection->push(
            'Nearby restaurants, shopping, and daily essentials',
            'Local school and commute details available from the agent',
            'ZIP-focused market guidance from OmniReferral',
            'Neighborhood context available on request'
        ));
    $agentName = optional(optional($property->realtorProfile)->user)->name ?? 'OmniReferral Partner';
    $agentBrokerage = optional($property->realtorProfile)->brokerage_name ?: 'OmniReferral Network';
    $agentHeadshot = optional($property->realtorProfile)->headshot ?: 'images/realtors/3.png';
    $agentHeadshotUrl = \Illuminate\Support\Str::startsWith($agentHeadshot, ['http://', 'https://'])
        ? $agentHeadshot
        : asset($agentHeadshot);
@endphp

<section class="property-detail-hero">
    <div class="property-detail-hero__glow" aria-hidden="true"></div>
    <div class="container property-detail-hero__inner">
        <div class="property-detail-hero__copy" data-animate="left">
            <a href="{{ route('listings') }}" class="property-back-link">← Back to listings</a>
            <div class="property-detail-hero__badges">
                <span>{{ $property->listingIntentLabel() }}</span>
                @if($property->is_featured)
                    <span>Featured</span>
                @endif
                <span>Verified Listing</span>
            </div>
            <h1>{{ $property->title }}</h1>
            <p>{{ $property->fullAddress() }}</p>
        </div>
        <div class="property-detail-hero__price-card" data-animate="right">
            <span>Listing Price</span>
            <strong>{{ $property->formattedPrice() }}</strong>
            <a href="#property-contact" class="button button--orange">Contact Agent</a>
        </div>
    </div>
</section>

<section class="property-detail-v2">
    <div class="container property-detail-v2__layout">
        <main class="property-detail-v2__main">
            <section class="pd-gallery-card" data-property-gallery>
                <div class="pd-gallery-card__stage">
                    <img src="{{ $mainImage }}" alt="{{ $property->title }} featured image" data-gallery-main loading="eager" decoding="async">
                    @if($galleryUrls->count() > 1)
                        <button type="button" class="pd-gallery-card__control pd-gallery-card__control--prev" data-gallery-prev aria-label="Previous image">‹</button>
                        <button type="button" class="pd-gallery-card__control pd-gallery-card__control--next" data-gallery-next aria-label="Next image">›</button>
                    @endif
                    <div class="pd-gallery-card__actions">
                        @if($property->video_tour_url)
                            <a href="{{ $property->video_tour_url }}" target="_blank" rel="noopener" class="pd-media-chip">Video Tour</a>
                        @endif
                        @if($property->view_360_url)
                            <a href="{{ $property->view_360_url }}" target="_blank" rel="noopener" class="pd-media-chip">360° View</a>
                        @endif
                    </div>
                </div>

                @if($galleryUrls->count() > 1)
                    <div class="pd-gallery-card__thumbs" aria-label="Property image thumbnails">
                        @foreach($galleryUrls->take(8) as $galleryUrl)
                            <button type="button" class="pd-gallery-thumb {{ $loop->first ? 'is-active' : '' }}" data-gallery-thumb data-gallery-src="{{ $galleryUrl }}" aria-label="Show image {{ $loop->iteration }}">
                                <img src="{{ $galleryUrl }}" alt="{{ $property->title }} thumbnail {{ $loop->iteration }}" loading="lazy" decoding="async">
                            </button>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="pd-info-card">
                <div class="pd-info-card__head">
                    <div>
                        <span class="eyebrow">Property Overview</span>
                        <h2>{{ $property->title }}</h2>
                        <p>{{ $property->fullAddress() }}</p>
                    </div>
                    <div class="pd-info-card__price">{{ $property->formattedPrice() }}</div>
                </div>

                <div class="pd-quick-facts" aria-label="Quick property facts">
                    <div class="pd-fact">
                        <span>🛏</span>
                        <strong>{{ $property->beds ?? '-' }}</strong>
                        <small>Bedrooms</small>
                    </div>
                    <div class="pd-fact">
                        <span>🛁</span>
                        <strong>{{ $property->baths ?? '-' }}</strong>
                        <small>Bathrooms</small>
                    </div>
                    <div class="pd-fact">
                        <span>↔</span>
                        <strong>{{ $property->sqft ? number_format($property->sqft) : '-' }}</strong>
                        <small>Sqft</small>
                    </div>
                    <div class="pd-fact">
                        <span>⌂</span>
                        <strong>{{ \Illuminate\Support\Str::headline($property->property_type) }}</strong>
                        <small>Property Type</small>
                    </div>
                </div>

                <div class="pd-spec-grid">
                    <span>{{ $property->area_size ?: number_format((int) $property->sqft) }} {{ strtoupper($property->area_unit ?: 'sqft') }}</span>
                    @if($property->year_built)<span>Built {{ $property->year_built }}</span>@endif
                    @if(!is_null($property->parking_spaces))<span>{{ $property->parking_spaces }} Parking</span>@endif
                    @if(!is_null($property->garage_spaces))<span>{{ $property->garage_spaces }} Garage</span>@endif
                    @if($property->furnishing_status)<span>{{ \Illuminate\Support\Str::headline($property->furnishing_status) }}</span>@endif
                    @if($property->property_condition)<span>{{ \Illuminate\Support\Str::headline($property->property_condition) }}</span>@endif
                </div>
            </section>

            <section class="pd-section-card">
                <div class="pd-section-card__head">
                    <span class="eyebrow">Description</span>
                    <h2>About this property</h2>
                </div>
                <div class="pd-description-text is-collapsed" data-expandable-text>
                    {!! nl2br(e($property->description ?: 'This listing is part of the OmniReferral marketplace experience, designed to give buyers and agents a clearer handoff between discovery, qualification, and direct follow-up.')) !!}
                </div>
                <button type="button" class="pd-text-toggle" data-expand-toggle data-expanded-label="Show less" data-collapsed-label="Read full description">Read full description</button>
            </section>

            <section class="pd-section-card">
                <div class="pd-section-card__head">
                    <span class="eyebrow">Amenities</span>
                    <h2>Features that matter</h2>
                </div>
                <div class="pd-amenities-grid">
                    @foreach($amenityLabels as $amenity)
                        @php
                            $amenityKey = \Illuminate\Support\Str::lower((string) $amenity);
                            $amenityIcon = match (true) {
                                str_contains($amenityKey, 'parking') || str_contains($amenityKey, 'garage') => 'P',
                                str_contains($amenityKey, 'water') => 'W',
                                str_contains($amenityKey, 'gas') => 'G',
                                str_contains($amenityKey, 'security') => 'S',
                                str_contains($amenityKey, 'internet') || str_contains($amenityKey, 'wifi') => 'Wi',
                                default => '✓',
                            };
                        @endphp
                        <div class="pd-amenity">
                            <span>{{ $amenityIcon }}</span>
                            <strong>{{ \Illuminate\Support\Str::headline((string) $amenity) }}</strong>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="pd-section-card">
                <div class="pd-section-card__head">
                    <span class="eyebrow">Neighborhood</span>
                    <h2>Nearby places and local context</h2>
                    @if($property->walk_score)
                        <p>Walk score: {{ $property->walk_score }}/100</p>
                    @endif
                </div>
                <div class="pd-neighborhood-grid">
                    @foreach($locationHighlights as $highlight)
                        <article>
                            <span>⌖</span>
                            <p>{{ $highlight }}</p>
                        </article>
                    @endforeach
                </div>
                @if($property->neighborhood_info)
                    <p class="pd-neighborhood-copy">{{ $property->neighborhood_info }}</p>
                @endif
            </section>

            <section class="pd-section-card pd-map-card">
                <div class="pd-section-card__head">
                    <span class="eyebrow">Map Location</span>
                    <h2>{{ $property->fullAddress() }}</h2>
                </div>
                <iframe title="Property location map" src="https://www.google.com/maps?q={{ urlencode($property->fullAddress()) }}&output=embed" loading="lazy"></iframe>
            </section>

            <section class="pd-section-card property-comments-section" aria-labelledby="property-comments-heading">
                <div class="pd-section-card__head">
                    <span class="eyebrow">Community Feedback</span>
                    <h2 id="property-comments-heading">Comments on this listing</h2>
                </div>

                <ul class="property-comment-list">
                    @forelse($property->listingComments as $comment)
                        <li class="property-comment-item">
                            <strong>{{ $comment->displayAuthor() }}</strong>
                            <time datetime="{{ $comment->created_at->toAtomString() }}">{{ $comment->created_at->diffForHumans() }}</time>
                            <p>{{ $comment->body }}</p>
                        </li>
                    @empty
                        <li class="property-comment-item">No comments yet. Be the first to leave feedback on this listing.</li>
                    @endforelse
                </ul>

                <form method="POST" action="{{ route('properties.comments.store', $property) }}" class="agent-contact-form pd-comment-form">
                    @csrf
                    @guest
                        <label>
                            <span>Your name</span>
                            <input type="text" name="author_name" value="{{ old('author_name') }}" maxlength="120" required>
                        </label>
                    @endguest
                    <label class="form-full-row">
                        <span>Your comment</span>
                        <textarea name="body" rows="4" required maxlength="2000">{{ old('body') }}</textarea>
                    </label>
                    <button type="submit" class="button">Post comment</button>
                </form>
            </section>
        </main>

        <aside class="property-detail-v2__sidebar">
            <section class="pd-agent-card" id="property-contact">
                <span class="eyebrow">Listed By</span>
                <div class="pd-agent-card__profile">
                    <img src="{{ $agentHeadshotUrl }}" alt="{{ $agentName }}" loading="lazy" decoding="async">
                    <div>
                        <h2>{{ $agentName }}</h2>
                        <p>{{ $agentBrokerage }}</p>
                        <small>{{ optional($property->realtorProfile)->city }}{{ optional($property->realtorProfile)->city ? ', ' : '' }}{{ optional($property->realtorProfile)->state }}</small>
                    </div>
                </div>
                <div class="pd-listed-by">
                    <span>Listing owner</span>
                    <strong>{{ $property->listedByLabel() }}</strong>
                </div>

                @if($property->realtorProfile)
                    <form action="{{ route('contact.submit') }}" method="POST" class="agent-contact-form pd-agent-card__form">
                        @csrf
                        <input type="hidden" name="property_id" value="{{ $property->id }}">
                        <input type="hidden" name="realtor_profile_id" value="{{ $property->realtorProfile->id }}">
                        <input type="hidden" name="recipient_user_id" value="{{ $property->realtorProfile->user_id }}">
                        <input type="hidden" name="source" value="website_property_inquiry">

                        <label>
                            <span>Full Name</span>
                            <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" required>
                        </label>
                        <label>
                            <span>Email</span>
                            <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required>
                        </label>
                        <label>
                            <span>Phone</span>
                            <input type="text" name="phone" value="{{ old('phone', auth()->user()?->phone) }}">
                        </label>
                        <label>
                            <span>Message</span>
                            <textarea name="message" rows="4" required>{{ old('message', 'Hi, I would like more information about ' . $property->title . '.') }}</textarea>
                        </label>
                        <input type="hidden" name="role" value="{{ old('role', auth()->user()?->role ?: 'buyer') }}">
                        <input type="hidden" name="subject" value="{{ old('subject', 'Inquiry about ' . $property->title) }}">
                        <button type="submit" class="button button--orange">Contact Agent</button>
                    </form>
                @else
                    <a href="{{ route('contact') }}?property={{ urlencode($property->title) }}" class="button button--orange">Contact OmniReferral</a>
                @endif

                <form method="POST" action="{{ route('properties.favorite.toggle', $property) }}" class="pd-agent-card__favorite">
                    @csrf
                    <button
                        type="submit"
                        class="button button--ghost-blue {{ $property->is_favorited ? 'is-active' : '' }}"
                        aria-label="{{ $property->is_favorited ? 'Remove listing from favorites' : 'Add listing to favorites' }}"
                    >
                        {{ $property->is_favorited ? 'Saved to Favorites' : 'Add to Favorites' }}
                    </button>
                    <span>{{ number_format($property->favorites_count ?? 0) }} saved</span>
                </form>

                @if($property->realtorProfile)
                    <a href="{{ route('agents.show', $property->realtorProfile) }}" class="pd-agent-profile-link">View Agent Profile →</a>
                @endif
            </section>
        </aside>
    </div>

    @if($relatedProperties->isNotEmpty())
        <section class="container property-related property-related-v2">
            <div class="section-heading" style="text-align:left; margin: 0 0 2rem;">
                <span class="eyebrow">More In This Area</span>
                <h2>Related listings near {{ $property->zip_code }}</h2>
            </div>
            <div class="listing-grid listing-grid--showcase">
                @foreach($relatedProperties as $relatedProperty)
                    <article class="listing-card listing-card--showcase">
                        <div class="listing-card__media">
                            <img src="{{ $relatedProperty->image_url }}" alt="{{ $relatedProperty->title }}" loading="lazy" decoding="async">
                            <span class="listing-badge">{{ $relatedProperty->listingIntentLabel() }}</span>
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
                                <strong>{{ $relatedProperty->formattedPrice() }}</strong>
                                <span class="listing-type">{{ $relatedProperty->property_type }}</span>
                            </div>
                            <h3>{{ $relatedProperty->title }}</h3>
                            <p class="listing-location">{{ $relatedProperty->location }}</p>
                            <a href="{{ route('properties.show', $relatedProperty) }}" class="button button--ghost-blue">View Details</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</section>

<a href="#property-contact" class="property-mobile-cta">Contact Agent</a>
@endsection
