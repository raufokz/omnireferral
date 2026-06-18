@extends('layouts.app')

@section('content')
@php
    $audienceMeta = [
        'buyer'     => ['label' => 'Buyer',     'heading' => 'Buyer Stories',     'copy' => 'Trusted support, cleaner handoffs, and a smoother home-buying journey.',                               'focus' => 'Guided search, cleaner communication, and more confidence in the next step.'],
        'seller'    => ['label' => 'Seller',    'heading' => 'Seller Stories',    'copy' => 'More clarity, better communication, and stronger listing confidence.',                                'focus' => 'Premium-feeling intake, stronger updates, and smoother handoffs to the right team.'],
        'agent'     => ['label' => 'Agent',     'heading' => 'Agent Stories',     'copy' => 'Higher-quality lead flow, better context, and faster follow-through.',                                'focus' => 'Better-qualified opportunities, stronger notes, and cleaner operational follow-up.'],
        'community' => ['label' => 'Community', 'heading' => 'Community Stories', 'copy' => 'Broader feedback from OmniReferral users and partners across the experience.',                       'focus' => 'Credibility, usability, and a stronger overall platform experience.'],
    ];

    $activeAudienceMeta = $selectedAudience === 'all'
        ? ['label' => 'All', 'heading' => 'All Published Stories', 'copy' => 'A full library of approved buyer, seller, agent, and community testimonials from the OmniReferral network.', 'focus' => 'Approved public feedback from across the OmniReferral experience.']
        : $audienceMeta[$selectedAudience];
@endphp

<div class="omni-testimonial-page">

{{-- ===== HERO ===== --}}
<section class="omni-agent-hero omni-testimonial-hero">
    <div class="omni-agent-hero__bg" aria-hidden="true">
        <img src="{{ asset('images/home/hero_backdrop_v2.png') }}" alt="">
    </div>
    <div class="container omni-agent-hero__inner">
        <div class="omni-agent-hero__copy" data-animate="left">
            @if(session('success'))
                <div class="omni-testimonial-alert omni-testimonial-alert--success">
                    {{ session('success') }}
                </div>
            @endif
            <span class="agent-kicker">Testimonials</span>
            <h1>Real feedback that moves referrals forward.</h1>
            <p>Read focused buyer, seller, agent, and community stories that show how OmniReferral creates clearer handoffs and stronger trust.</p>
            <div class="omni-agent-hero__actions">
                <a href="#testimonial-library" class="agent-btn agent-btn--orange">Explore Testimonials</a>
                <a href="#share-review" class="agent-btn agent-btn--light">Share Your Review</a>
            </div>
        </div>

        @php $heroT = $featuredTestimonials->first(); @endphp
        <div class="omni-testimonial-hero__preview" data-animate="right">
            <div class="omni-testimonial-hero__preview-label">&#10003; Featured Review</div>
            <div class="omni-testimonial-hero__preview-stars" aria-hidden="true">★★★★★</div>
            <p class="omni-testimonial-hero__preview-quote">
                @if($heroT)
                    {{ \Illuminate\Support\Str::limit($heroT->quote, 200) }}
                @else
                    OmniReferral completely changed how I handle referrals. The handoff is seamless, lead quality is higher, and my clients feel taken care of from day one.
                @endif
            </p>
            <div class="omni-testimonial-hero__preview-person">
                @if($heroT && $heroT->photo_url)
                    <img src="{{ $heroT->photo_url }}" alt="{{ $heroT->name }}" loading="lazy">
                @else
                    <div class="omni-testimonial-hero__preview-initials" aria-hidden="true">JM</div>
                @endif
                <div>
                    <strong>{{ $heroT->name ?? 'James Mitchell' }}</strong>
                    <span>{{ $heroT ? $heroT->audience_label . ' · OmniReferral Network' : 'Agent · OmniReferral Network' }}</span>
                </div>
            </div>
            <div class="omni-testimonial-hero__preview-badges">
                <span>Verified Review</span>
                <span>Featured Story</span>
                <span>5-Star Rating</span>
            </div>
        </div>
    </div>
</section>

{{-- ===== FILTER ===== --}}
<section class="omni-testimonial-filter">
    <div class="container">
        <div class="omni-testimonial-filter__card">
            <div class="omni-testimonial-filter__copy">
                <span class="eyebrow">Browse By Audience</span>
                <h2>See what matters most to each side of the journey</h2>
                <p>Filter by buyer, seller, agent, or community to find the stories most relevant to your experience.</p>
            </div>
            <div class="omni-testimonial-filter__chips">
                <a href="{{ route('reviews') }}" class="testimonial-filter-chip {{ $selectedAudience === 'all' ? 'is-active' : '' }}">
                    All Stories
                </a>
                @foreach(['buyer', 'seller', 'agent', 'community'] as $audience)
                    <a href="{{ route('reviews', ['audience' => $audience]) }}" class="testimonial-filter-chip {{ $selectedAudience === $audience ? 'is-active' : '' }}">
                        {{ $audienceMeta[$audience]['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ===== SPOTLIGHT ===== --}}
@if($featuredTestimonials->isNotEmpty())
<section class="omni-testimonial-spotlight">
    <div class="container">
        <div class="omni-testimonial-section-head">
            <span class="eyebrow">Featured Testimonials</span>
            <h2>Stories worth spotlighting</h2>
            <p>Selected approved testimonials from buyers, sellers, agents, and community members.</p>
        </div>
        <div class="omni-testimonial-spotlight-grid">
            @foreach($featuredTestimonials as $testimonial)
                <article class="omni-testimonial-spotlight-card">
                    <div class="omni-testimonial-spotlight-card__stars" aria-label="{{ $testimonial->rating }} out of 5 stars">
                        @for($i = 0; $i < (int) $testimonial->rating; $i++)
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <p class="omni-testimonial-spotlight-card__quote">{{ $testimonial->quote }}</p>
                    <div class="omni-testimonial-spotlight-card__footer">
                        <img src="{{ $testimonial->photo_url }}" alt="{{ $testimonial->name }}" loading="lazy">
                        <div>
                            <strong>{{ $testimonial->name }}</strong>
                            <span>{{ $testimonial->company ?: $testimonial->audience_label . ' Client' }}</span>
                            <small>{{ $testimonial->location ?: 'OmniReferral Network' }}</small>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== VIDEO TESTIMONIALS ===== --}}
@if($videoTestimonials->isNotEmpty())
<section class="omni-testimonial-videos" id="testimonial-videos">
    <div class="container">
        <div class="omni-testimonial-section-head">
            <span class="eyebrow">Video Testimonials</span>
            <h2>Watch the story behind the results</h2>
            <p>Video testimonials help buyers, sellers, and agents explain what felt different about the OmniReferral experience.</p>
        </div>
        <div class="omni-testimonial-video-grid">
            @foreach($videoTestimonials as $testimonial)
                <article class="omni-testimonial-video-card">
                    <div class="omni-testimonial-video-card__media">
                        @if($testimonial->video_embed_url)
                            <iframe src="{{ $testimonial->video_embed_url }}" title="{{ $testimonial->name }} testimonial video" loading="lazy" allowfullscreen></iframe>
                        @elseif($testimonial->video_playback_url)
                            <video controls preload="metadata" poster="{{ $testimonial->photo_url }}">
                                <source src="{{ $testimonial->video_playback_url }}">
                            </video>
                        @endif
                    </div>
                    <div class="omni-testimonial-video-card__body">
                        <div class="omni-testimonial-video-card__meta">
                            <span class="status-pill status-pill--assigned">{{ $testimonial->audience_label }}</span>
                            @if($testimonial->is_featured)
                                <span class="status-pill status-pill--qualified">Featured</span>
                            @endif
                        </div>
                        <h3>{{ $testimonial->name }}</h3>
                        <p>{{ $testimonial->company ?: $testimonial->audience_label . ' Client' }}</p>
                        @if($testimonial->location)
                            <small>{{ $testimonial->location }}</small>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== LIBRARY ===== --}}
<section class="omni-testimonial-library" id="testimonial-library">
    <div class="container">
        <div class="omni-testimonial-section-head omni-testimonial-library__heading">
            <span class="eyebrow">{{ $activeAudienceMeta['label'] }}</span>
            <h2>{{ $activeAudienceMeta['heading'] }}</h2>
            <p>
                @if($showingFallbackTestimonials)
                    No published {{ strtolower($activeAudienceMeta['label']) }} testimonials are available yet — showing recent approved testimonials from the full library.
                @else
                    {{ $activeAudienceMeta['copy'] }}
                @endif
            </p>
            @if($testimonials->total() > 0)
                <div class="omni-testimonial-library__meta">
                    Showing {{ number_format($testimonials->firstItem()) }}&ndash;{{ number_format($testimonials->lastItem()) }}
                    of {{ number_format($testimonials->total()) }} approved testimonials
                </div>
            @endif
        </div>

        @if($showingFallbackTestimonials)
            <div class="omni-testimonial-fallback">
                Recent approved testimonials are shown here until this category has its own published reviews.
            </div>
        @endif

        <div class="omni-testimonial-masonry">
            @forelse($testimonials as $testimonial)
                @php
                    $cardAudienceMeta = $audienceMeta[$testimonial->audience_key] ?? $activeAudienceMeta;
                @endphp
                <article class="omni-testimonial-masonry-card">
                    <div class="omni-testimonial-masonry-card__header">
                        <img src="{{ $testimonial->photo_url }}" alt="{{ $testimonial->name }}" loading="lazy" width="88" height="88">
                        <div class="omni-testimonial-masonry-card__info">
                            <div class="omni-testimonial-masonry-card__chips">
                                <span class="status-pill status-pill--assigned">{{ $testimonial->audience_label }}</span>
                                @if($testimonial->is_featured)
                                    <span class="status-pill status-pill--qualified">Featured</span>
                                @endif
                                @if($testimonial->has_video)
                                    <span class="status-pill status-pill--new">Video</span>
                                @endif
                            </div>
                            <h2>{{ $testimonial->name }}</h2>
                            <p>{{ $testimonial->company ?: $testimonial->audience_label . ' Client' }}</p>
                            <small>{{ $testimonial->location ?: 'OmniReferral Network' }}</small>
                        </div>
                    </div>
                    <p class="omni-testimonial-masonry-card__focus">{{ $cardAudienceMeta['focus'] }}</p>
                    <div class="omni-testimonial-masonry-card__stars" aria-label="{{ $testimonial->rating }} out of 5 stars">
                        @for($i = 0; $i < (int) $testimonial->rating; $i++)
                            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <p class="omni-testimonial-masonry-card__quote">"{{ $testimonial->quote }}"</p>
                </article>
            @empty
                <div class="omni-testimonial-empty">
                    <h3>Testimonials are being reviewed</h3>
                    <p>Approved and published testimonials will appear here automatically as soon as they are available.</p>
                </div>
            @endforelse
        </div>

        @if($testimonials->hasPages())
            <div class="omni-testimonial-pagination">
                {{ $testimonials->links() }}
            </div>
        @endif
    </div>
</section>

{{-- ===== SUBMIT ===== --}}
<section class="omni-testimonial-submit" id="share-review">
    <div class="container">
        <div class="omni-testimonial-submit__card">
            <div class="omni-testimonial-submit__copy">
                <span class="eyebrow">Share Your Review</span>
                <h2>Let OmniReferral know how the experience felt</h2>
                <p>Buyers, sellers, agents, and general community users can submit their own review here. Every submission goes to the admin team first, and only approved reviews are published on the site.</p>
                @if($errors->any())
                    <div class="omni-testimonial-alert omni-testimonial-alert--error">
                        <strong>Please review your testimonial submission.</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('reviews.store') }}" enctype="multipart/form-data" class="omni-testimonial-submit__form">
                @csrf
                <div class="omni-testimonial-submit__form-grid">
                    <label>
                        <span>Name</span>
                        <input type="text" name="name" value="{{ $reviewDraft['name'] }}" required>
                    </label>
                    <label>
                        <span>Email</span>
                        <input type="email" name="email" value="{{ $reviewDraft['email'] }}" required>
                    </label>
                    <label>
                        <span>Review Type</span>
                        <select name="audience" required>
                            @foreach($reviewAudienceOptions as $value => $label)
                                <option value="{{ $value }}" {{ $reviewDraft['audience'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>Role / Company</span>
                        <input type="text" name="company" value="{{ $reviewDraft['company'] }}" placeholder="e.g. Buyer Client, Agent, OmniReferral User">
                    </label>
                    <label>
                        <span>Location</span>
                        <input type="text" name="location" value="{{ $reviewDraft['location'] }}" placeholder="e.g. Dallas, TX">
                    </label>
                    <label>
                        <span>Rating</span>
                        <select name="rating" required>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" {{ (int) $reviewDraft['rating'] === $i ? 'selected' : '' }}>{{ $i }} stars</option>
                            @endfor
                        </select>
                    </label>
                    <label class="omni-testimonial-submit__form-full">
                        <span>Your Review</span>
                        <textarea name="quote" rows="5" required placeholder="Tell OmniReferral what worked well, what felt polished, and what made the experience better.">{{ $reviewDraft['quote'] }}</textarea>
                    </label>
                    <label class="omni-testimonial-submit__form-full">
                        <span>Photo</span>
                        <input type="file" name="photo" accept="image/*">
                        <small>Optional. If you are signed in and skip this, OmniReferral can use your current avatar when available.</small>
                    </label>
                </div>
                <div class="omni-testimonial-submit__actions">
                    <button type="submit" class="agent-btn agent-btn--orange">Send Review To Admin</button>
                    <a href="{{ route('contact') }}" class="agent-btn agent-btn--ghost">Need Help Instead?</a>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- ===== CTA ===== --}}
<section class="omni-testimonial-cta">
    <div class="container">
        <div class="omni-testimonial-cta__card">

            <div class="omni-testimonial-cta__copy">
                <span class="agent-kicker">Next Step</span>
                <h2>Ready to build a better client story?</h2>
                <p>Explore the package mix, see how the handoff works, and move from generic leads to a cleaner experience for buyers, sellers, and agents.</p>
                <div class="omni-testimonial-cta__actions">
                    <a href="{{ route('pricing') }}" class="agent-btn agent-btn--orange">Explore Pricing</a>
                    <a href="#share-review" class="agent-btn agent-btn--light">Write A Review</a>
                    <a href="{{ route('contact') }}" class="agent-btn agent-btn--light">Talk To Our Team</a>
                </div>
            </div>

            <div class="omni-testimonial-cta__pillars" aria-label="Why OmniReferral">
                <div class="omni-testimonial-cta__pillar">
                    <div class="omni-testimonial-cta__pillar-icon" aria-hidden="true">★</div>
                    <div class="omni-testimonial-cta__pillar-body">
                        <strong>Verified Reviews Only</strong>
                        <p>Every testimonial is reviewed and approved before it appears publicly on the site.</p>
                    </div>
                </div>
                <div class="omni-testimonial-cta__pillar">
                    <div class="omni-testimonial-cta__pillar-icon" aria-hidden="true">&#8635;</div>
                    <div class="omni-testimonial-cta__pillar-body">
                        <strong>Cleaner Handoffs</strong>
                        <p>Buyers, sellers, and agents get a smoother, more transparent referral process.</p>
                    </div>
                </div>
                <div class="omni-testimonial-cta__pillar">
                    <div class="omni-testimonial-cta__pillar-icon" aria-hidden="true">&#10003;</div>
                    <div class="omni-testimonial-cta__pillar-body">
                        <strong>Real Results</strong>
                        <p>Feedback that reflects genuine experiences from across the referral network.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

</div>
@endsection
