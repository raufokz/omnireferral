@props([
    'property',
    'variant' => 'marketplace',
    'withFilters' => false,
    'showActions' => true,
    'animate' => false,
])

@php
    $locationLine = $property->city
        ? trim($property->city . ', ' . ($property->state ?: '') . ' ' . $property->zip_code)
        : trim(($property->location ?: '') . ($property->zip_code ? ' - ' . $property->zip_code : ''));

    $priceLabel = method_exists($property, 'formattedPrice')
        ? $property->formattedPrice()
        : ('$' . number_format((float) $property->price));

    $typeLabel = \Illuminate\Support\Str::headline($property->property_type ?: 'Property');
    $statusLabel = $property->status ?: 'Available';
    $intentLabel = method_exists($property, 'listingIntentLabel') ? $property->listingIntentLabel() : $statusLabel;
    $cardClasses = trim('property-card property-card--' . $variant . ($property->is_featured ? ' is-featured' : ''));
@endphp

<article
    class="{{ $cardClasses }}"
    @if($withFilters) data-listing-card @endif
    @if($withFilters) data-zip="{{ $property->zip_code }}" @endif
    @if($withFilters) data-type="{{ strtolower((string) $property->property_type) }}" @endif
    @if($withFilters) data-price="{{ $property->price }}" @endif
    @if($withFilters) data-beds="{{ (int) ($property->beds ?? 0) }}" @endif
    @if($withFilters) data-baths="{{ (float) ($property->baths ?? 0) }}" @endif
    @if($withFilters) data-area="{{ (int) ($property->sqft ?? 0) }}" @endif
    @if($animate) data-animate @endif
>
    <a href="{{ route('properties.show', $property) }}" class="property-card__media" aria-label="View {{ $property->title }}">
        <img src="{{ $property->image_url }}" alt="{{ $property->title }} property image" loading="lazy" decoding="async">
        <span class="property-card__status">{{ $statusLabel }}</span>
        @if($property->is_featured)
            <span class="property-card__featured">Featured</span>
        @endif
    </a>

    <div class="property-card__save-group">
        {{-- Cookie-based guest favourite button --}}
        <button
            data-listing-id="{{ $property->id }}"
            class="property-card__save favourite-btn {{ $property->is_favorited ? 'is-favourite' : '' }}"
            title="Save to Favourites"
            aria-label="Toggle Favourite"
        >
            <svg class="heart-outline" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
            </svg>
            <svg class="heart-filled" width="18" height="18" viewBox="0 0 24 24" fill="#e53e3e" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
            </svg>
        </button>
        <span class="property-card__save-count">{{ number_format($property->favorites_count ?? 0) }}</span>
    </div>

    <div class="property-card__body">
        <div class="property-card__headline">
            <span class="property-card__price">{{ $priceLabel }}</span>
            <span class="property-card__type">{{ $typeLabel }}</span>
        </div>

        <div class="property-card__summary">
            <h3><a href="{{ route('properties.show', $property) }}">{{ $property->title }}</a></h3>
            <p>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-5.1 7-11a7 7 0 1 0-14 0c0 5.9 7 11 7 11Z"/><path d="M12 10.5h.01"/></svg>
                <span>{{ $locationLine ?: 'Address available on request' }}</span>
            </p>
        </div>

        <div class="property-card__facts" aria-label="Property facts">
            <span class="property-card__fact">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11V5a2 2 0 0 1 2-2h5a3 3 0 0 1 3 3v5"/><path d="M13 8h6a2 2 0 0 1 2 2v8"/><path d="M3 18h18"/><path d="M3 21v-8h18v8"/></svg>
                <strong>{{ $property->beds ?? '-' }}</strong>
                <span>Beds</span>
            </span>
            <span class="property-card__fact">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6 6.5 8.5"/><path d="M14 6 8.5 11.5"/><path d="M3 13h18"/><path d="M5 13v3a4 4 0 0 0 4 4h6a4 4 0 0 0 4-4v-3"/><path d="M7 21h10"/></svg>
                <strong>{{ $property->baths ?? '-' }}</strong>
                <span>Baths</span>
            </span>
            <span class="property-card__fact">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V4h16v16H4Z"/><path d="M8 4v16"/><path d="M16 4v16"/><path d="M4 12h16"/></svg>
                <strong>{{ $property->sqft ? number_format($property->sqft) : '-' }}</strong>
                <span>Sqft</span>
            </span>
        </div>

        <div class="property-card__listed">
            @include('partials.property.listed-by', ['property' => $property, 'variant' => 'property-card'])
            <span class="property-card__listed-signal">{{ $intentLabel }}</span>
        </div>

        @if($showActions)
            <div class="property-card__actions">
                <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">Details</a>
                <a href="{{ route('properties.show', $property) }}#property-contact" class="button button--orange">Contact</a>
            </div>
        @endif
    </div>
</article>
