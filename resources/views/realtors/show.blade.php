@extends('layouts.app')

@push('styles')
    @vite(['resources/css/modules/realtor-profile.css'])
@endpush

@php
    $agentName = $card['name'] ?? ($user?->publicDisplayName() ?? 'Real Estate Agent');
    $agentLocation = $card['service_area'] ?: ($profile->service_city . ', ' . $profile->service_state);
    $headshot = $card['headshot_url'] ?? asset('images/realtors/logo-bydefault_agent.png');
    $rating = $card['rating'] ?? '4.9';
    $reviewCount = $card['review_count'] ?? 12;
    $specialties = $card['specialties'] ?? ['Buyer Representation', 'Seller Strategy', 'Relocation'];
    $languagesList = $card['languages_list'] ?? ['English'];
    $serviceAreas = $card['service_areas'] ?? [$agentLocation];
    $phone = $user?->phone ?: null;
    $email = $user?->email ?: null;
    $socials = $profile->social_links ?? [];
@endphp

@section('head')
    <meta name="robots" content="index, follow, max-image-preview:large">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    
    <!-- Open Graph Tags -->
    <meta property="og:type" content="profile">
    <meta property="og:title" content="{{ $meta['title'] }}">
    <meta property="og:description" content="{{ $meta['description'] }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $headshot }}">
    <meta property="og:site_name" content="OmniReferral">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $meta['title'] }}">
    <meta name="twitter:description" content="{{ $meta['description'] }}">
    <meta name="twitter:image" content="{{ $headshot }}">
@endsection

@section('schema')
    @if(!empty($schemas))
        @foreach($schemas as $schema)
            <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
        @endforeach
    @endif
@endsection

@section('content')
<div class="realtor-profile-page" x-data="{ inquiryOpen: false, inquiryType: 'contact', submitting: false, successMsg: '' }">
    <div class="container">
        
        <!-- Breadcrumbs -->
        <nav class="realtor-breadcrumbs" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><span class="separator">/</span></li>
                <li><a href="{{ route('agents.index') }}">Agents</a></li>
                <li><span class="separator">/</span></li>
                <li aria-current="page" class="text-white fw-bold">{{ $agentName }}</li>
            </ol>
        </nav>

        <!-- Hero Header -->
        <header class="realtor-hero">
            <div class="realtor-hero__content">
                <div class="realtor-hero__avatar-wrap">
                    <img src="{{ $headshot }}" alt="{{ $agentName }}" class="realtor-hero__avatar" loading="eager" width="180" height="180" onerror="this.onerror=null;this.src='{{ asset('images/realtors/logo-bydefault_agent.png') }}'">
                </div>
                
                <div class="realtor-hero__info">
                    <div class="realtor-hero__badges">
                        <span class="realtor-badge realtor-badge--verified">✓ Verified Realtor</span>
                        <span class="realtor-badge realtor-badge--plan">⚡ Active Plan Member</span>
                        @if($card['is_elite'] ?? false)
                            <span class="realtor-badge realtor-badge--elite">⭐ Elite Tier</span>
                        @endif
                    </div>
                    
                    <h1 class="realtor-hero__name">{{ $agentName }}</h1>
                    <p class="realtor-hero__brokerage">{{ $profile->brokerage_name ?: 'Independent Brokerage' }}</p>
                    
                    <div class="realtor-hero__location">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ $agentLocation }}</span>
                        @if($profile->license_number)
                            <span class="ms-2 opacity-75">· License #{{ $profile->license_number }}</span>
                        @endif
                    </div>

                    <div class="realtor-hero__stats-row">
                        <div class="realtor-stat-pill">
                            <span class="realtor-rating-stars">★★★★★</span>
                            <strong>{{ $rating }}</strong>
                            <span>({{ number_format($reviewCount) }} reviews)</span>
                        </div>
                        <div class="realtor-stat-pill">
                            <strong>{{ $stats['years_experience'] }}+</strong>
                            <span>Years Experience</span>
                        </div>
                        <div class="realtor-stat-pill">
                            <strong>{{ implode(', ', array_slice($languagesList, 0, 2)) }}</strong>
                            <span>Languages</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Layout Grid -->
        <div class="realtor-grid">
            
            <!-- Left Main Column -->
            <main class="realtor-main-col">
                
                <!-- About Section -->
                <section class="realtor-card" id="about">
                    <h2 class="realtor-card__title">
                        <svg width="22" height="22" fill="none" stroke="#38bdf8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        About {{ $agentName }}
                    </h2>
                    <div class="realtor-bio-text">
                        {{ $profile->bio ?: 'Dedicated real estate professional committed to delivering seamless client experiences, top-tier property marketing, and expert local market guidance across all buying, selling, and referral requirements.' }}
                    </div>
                </section>

                <!-- Specialties Section -->
                <section class="realtor-card" id="specialties">
                    <h2 class="realtor-card__title">
                        <svg width="22" height="22" fill="none" stroke="#38bdf8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        Specialties & Expertise
                    </h2>
                    <div class="realtor-chips-container">
                        @foreach($specialties as $spec)
                            <span class="realtor-chip">{{ $spec }}</span>
                        @endforeach
                    </div>
                </section>

                <!-- Service Areas Section -->
                <section class="realtor-card" id="service-areas">
                    <h2 class="realtor-card__title">
                        <svg width="22" height="22" fill="none" stroke="#38bdf8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        Service Areas & Market Coverage
                    </h2>
                    <div class="realtor-chips-container">
                        @foreach($serviceAreas as $area)
                            <span class="realtor-chip">📍 {{ $area }}</span>
                        @endforeach
                    </div>
                </section>

                <!-- Recent Listings Section -->
                <section class="realtor-card" id="listings">
                    <h2 class="realtor-card__title">
                        <svg width="22" height="22" fill="none" stroke="#38bdf8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Recent Property Listings ({{ count($properties) }})
                    </h2>

                    @if(count($properties) > 0)
                        <div class="realtor-listings-grid">
                            @foreach($properties as $listing)
                                <article class="realtor-property-card">
                                    <div class="realtor-property-card__img-wrap">
                                        <img src="{{ $listing->image_url }}" alt="{{ $listing->title }}" class="realtor-property-card__img" loading="lazy" width="300" height="200">
                                        <span class="realtor-property-card__price-badge">{{ $listing->formattedPrice() }}</span>
                                    </div>
                                    <div class="realtor-property-card__body">
                                        <h3 class="realtor-property-card__title">{{ Str::limit($listing->title, 45) }}</h3>
                                        <p class="realtor-property-card__address">{{ $listing->fullAddress() }}</p>
                                        
                                        <div class="realtor-property-card__features">
                                            @if($listing->beds)<span><strong>{{ $listing->beds }}</strong> Beds</span>@endif
                                            @if($listing->baths)<span><strong>{{ $listing->baths }}</strong> Baths</span>@endif
                                            @if($listing->sqft)<span><strong>{{ number_format($listing->sqft) }}</strong> Sq Ft</span>@endif
                                        </div>

                                        <a href="{{ route('properties.show', $listing) }}" class="realtor-property-card__btn">View Property</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-slate-400 bg-slate-800/50 rounded-xl">
                            <p class="m-0">No active public property listings available right now for this realtor.</p>
                        </div>
                    @endif
                </section>

            </main>

            <!-- Right Sidebar Column -->
            <aside class="realtor-sidebar-col">
                
                <!-- Quick Contact Info Card -->
                <div class="realtor-card">
                    <h3 class="realtor-card__title">Contact Information</h3>
                    
                    <div class="realtor-contact-list">
                        <div class="realtor-contact-item">
                            <div class="realtor-contact-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div class="realtor-contact-details">
                                <strong>Phone</strong>
                                @if($phone)
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a>
                                @else
                                    <span>Routed via OmniReferral</span>
                                @endif
                            </div>
                        </div>

                        <div class="realtor-contact-item">
                            <div class="realtor-contact-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="realtor-contact-details">
                                <strong>Email</strong>
                                @if($email)
                                    <a href="mailto:{{ $email }}">{{ $email }}</a>
                                @else
                                    <span>Protected OmniReferral Lead</span>
                                @endif
                            </div>
                        </div>

                        <div class="realtor-contact-item">
                            <div class="realtor-contact-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            </div>
                            <div class="realtor-contact-details">
                                <strong>Brokerage</strong>
                                <span>{{ $profile->brokerage_name ?: 'Independent' }}</span>
                            </div>
                        </div>
                    </div>

                    @if(!empty($socials))
                        <div class="realtor-social-bar">
                            @foreach($socials as $platform => $url)
                                @if($url)
                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="realtor-social-icon" title="{{ ucfirst($platform) }}">
                                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Realtor Performance Stats Box -->
                <div class="realtor-card">
                    <h3 class="realtor-card__title">Performance Metrics</h3>
                    <div class="realtor-sidebar-stats">
                        <div class="realtor-stat-box">
                            <div class="realtor-stat-box__val">{{ $stats['active_listings'] }}</div>
                            <div class="realtor-stat-box__lbl">Active Listings</div>
                        </div>
                        <div class="realtor-stat-box">
                            <div class="realtor-stat-box__val">{{ $stats['sold_listings'] }}</div>
                            <div class="realtor-stat-box__lbl">Sold Properties</div>
                        </div>
                        <div class="realtor-stat-box">
                            <div class="realtor-stat-box__val">{{ $stats['total_leads'] }}+</div>
                            <div class="realtor-stat-box__lbl">Deals Closed</div>
                        </div>
                        <div class="realtor-stat-box">
                            <div class="realtor-stat-box__val">{{ $stats['years_experience'] }} yrs</div>
                            <div class="realtor-stat-box__lbl">Experience</div>
                        </div>
                    </div>
                </div>

            </aside>
        </div>

        <!-- Large Contact CTA Section -->
        <section class="realtor-cta-banner" id="contact-cta">
            <h2>Contact {{ $agentName }}</h2>
            <p>Connect directly with {{ $agentName }} for buying, selling, or strategic real estate referral inquiries.</p>

            <div class="realtor-cta-actions">
                <button type="button" class="realtor-btn realtor-btn--orange" x-on:click="inquiryOpen = !inquiryOpen; inquiryType = 'contact'">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Send Inquiry
                </button>

                @if($phone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="realtor-btn realtor-btn--blue">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        Call {{ $phone }}
                    </a>
                @endif

                @if($email)
                    <a href="mailto:{{ $email }}" class="realtor-btn realtor-btn--ghost">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Email Agent
                    </a>
                @endif
            </div>

            <!-- Inquiry Form -->
            <div class="realtor-inquiry-box" x-show="inquiryOpen" x-transition x-cloak>
                <h3 class="text-white text-xl font-bold mb-4">Send a Direct Inquiry</h3>
                <form method="POST" action="{{ route('agents.inquiry', $profile) }}">
                    @csrf
                    <input type="hidden" name="inquiry_type" :value="inquiryType">
                    
                    <label>Your Full Name *<input type="text" name="name" required placeholder="John Doe"></label>
                    <label>Your Email *<input type="email" name="email" required placeholder="john@example.com"></label>
                    <label>Phone Number<input type="tel" name="phone" placeholder="(555) 000-0000"></label>
                    <label>City<input type="text" name="city" placeholder="{{ $profile->service_city }}"></label>
                    <label>Message *<textarea name="message" rows="4" required placeholder="How can {{ $agentName }} assist you?"></textarea></label>
                    
                    <div class="mt-4">
                        <button type="submit" class="realtor-btn realtor-btn--orange w-full">Submit Inquiry to {{ $agentName }}</button>
                    </div>
                </form>
            </div>
        </section>

    </div>
</div>
@endsection
