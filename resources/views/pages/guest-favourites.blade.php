@extends('layouts.app')

@section('title', 'My Favourites')

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header">
            <h1>My Favourites</h1>
            <p>Your saved property listings.</p>
        </div>

        @if($listings->isEmpty())
            <div class="empty-state">
                <p>You haven't saved any properties yet.</p>
                <a href="{{ route('listings') }}" class="button">Browse Listings</a>
            </div>
        @else
            <div class="listing-grid">
                @foreach($listings as $listing)
                    <article class="listing-card">
                        <div class="listing-card__image">
                            <img src="{{ $listing->image_url }}" alt="{{ $listing->title }}">
                            <button
                                data-listing-id="{{ $listing->id }}"
                                class="favourite-btn"
                                title="Save to Favourites"
                                aria-label="Toggle Favourite"
                            >
                                <svg class="heart-outline" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                                <svg class="heart-filled" width="24" height="24" viewBox="0 0 24 24" fill="#e53e3e" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="listing-card__content">
                            <h3>{{ $listing->title }}</h3>
                            <div class="listing-card__meta listing-card__meta--pills">
                                <span>{{ $listing->beds }} bd</span>
                                <span>{{ $listing->baths }} ba</span>
                                <span>{{ number_format($listing->sqft) }} sqft</span>
                            </div>
                            <p class="listing-card__price">{{ $listing->formattedPrice() }}</p>
                            <p class="listing-card__location">{{ $listing->location }}</p>
                            <a href="{{ route('properties.show', $listing) }}" class="button button--ghost-blue">View Details</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

<div id="fav-toast"></div>

@push('scripts')
<script src="{{ asset('js/favourites.js') }}"></script>
@endpush

@push('styles')
<style>
.favourite-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transition: transform 0.2s;
}
.favourite-btn:hover {
    transform: scale(1.1);
}
.favourite-btn .heart-filled { display: none; }
.favourite-btn.is-favourite .heart-outline { display: none; }
.favourite-btn.is-favourite .heart-filled { display: block; }

#fav-toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #1a202c;
    color: white;
    padding: 10px 18px;
    border-radius: 8px;
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
    z-index: 9999;
}
#fav-toast.show { opacity: 1; }

.listing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}
.listing-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}
.listing-card__image {
    position: relative;
    height: 200px;
}
.listing-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.listing-card__content {
    padding: 1.5rem;
}
.listing-card__content h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
}
.listing-card__meta {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    color: #64748b;
}
.listing-card__price {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0.5rem 0;
}
.listing-card__location {
    color: #64748b;
    margin-bottom: 1rem;
}
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}
.empty-state p {
    margin-bottom: 1.5rem;
    color: #64748b;
}
</style>
@endpush
