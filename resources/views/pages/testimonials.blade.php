@extends('layouts.app')

@section('content')
@php
    $audienceMeta = [
        'buyer' => ['label' => 'Buyer', 'heading' => 'Buyer Stories', 'copy' => 'Trusted support, cleaner handoffs, and a smoother home-buying journey.', 'focus' => 'Guided search, cleaner communication, and more confidence in the next step.'],
        'seller' => ['label' => 'Seller', 'heading' => 'Seller Stories', 'copy' => 'More clarity, better communication, and stronger listing confidence.', 'focus' => 'Premium-feeling intake, stronger updates, and smoother handoffs to the right team.'],
        'agent' => ['label' => 'Agent', 'heading' => 'Agent Stories', 'copy' => 'Higher-quality lead flow, better context, and faster follow-through.', 'focus' => 'Better-qualified opportunities, stronger notes, and cleaner operational follow-up.'],
        'community' => ['label' => 'Community', 'heading' => 'Community Stories', 'copy' => 'Broader feedback from OmniReferral users and partners across the experience.', 'focus' => 'Credibility, usability, and a stronger overall platform experience.'],
    ];

    $selectedScope = $selectedAudience === 'all'
        ? 'buyers, sellers, agents, and community members together'
        : strtolower($audienceMeta[$selectedAudience]['label']) . ' experiences';
@endphp

<div class="testimonials-page">
<section class="page-hero agent-directory-hero testimonials-hero">
    <div class="agent-directory-hero__glow" aria-hidden="true"></div>
    <div class="container agent-directory-hero__inner">
        <div class="agent-directory-hero__copy">
            @if(session('success'))
                <div class="alert alert--success testimonial-submit-alert">
                    {{ session('success') }}
                </div>
            @endif
            <span class="eyebrow">Testimonials</span>
            <h1>Real feedback from buyers, sellers, agents, and community members</h1>
            <p>See how OmniReferral is helping every side of the real estate journey with cleaner lead quality, better communication, stronger handoffs, and a more polished client experience.</p>
            <div class="agent-directory-hero__actions">
                <a href="#testimonial-library" class="button button--orange">Explore Testimonials</a>
                <a href="#share-review" class="button button--ghost-light">Share Your Review</a>
            </div>
            <div class="agent-directory-hero__proof testimonials-hero__proof">
                <span>{{ number_format($counts['buyer']) }} buyer stories</span>
                <span>{{ number_format($counts['seller']) }} seller stories</span>
                <span>{{ number_format($counts['agent']) }} agent reviews</span>
                <span>{{ number_format($counts['community']) }} community notes</span>
            </div>
        </div>

        <aside class="agent-directory-hero__panel testimonials-hero__panel">
            <span class="agent-directory-hero__panel-eyebrow">Trust Snapshot</span>
            <h2>Specific proof beats generic claims.</h2>
            <p>The strongest reviews show what actually changed: clearer next steps, better handoffs, faster follow-up, stronger context, and a noticeably more premium feel across the journey.</p>
            <div class="agent-directory-hero__stats testimonials-hero__stats">
                <div class="agent-directory-hero__stat">
                    <strong>{{ number_format($counts['all']) }}</strong>
                    <span>total published stories</span>
                </div>
                <div class="agent-directory-hero__stat">
                    <strong>{{ number_format($averageRating, 1) }}/5</strong>
                    <span>average rating across audiences</span>
                </div>
                <div class="agent-directory-hero__stat">
                    <strong>{{ number_format($videoTestimonials->count()) }}</strong>
                    <span>video highlights available</span>
                </div>
            </div>
            <p class="testimonials-hero__note">{{ ucfirst($selectedScope) }} are currently in focus through the active filter.</p>
        </aside>
    </div>
</section>

<section class="section section--gray testimonials-page__filter-section">
    <div class="container">
        <div class="testimonial-filter-shell cockpit-table-card">
            <div class="testimonial-filter-shell__copy">
                <span class="eyebrow">Browse By Audience</span>
                <h2>See what matters most to each side of the journey</h2>
                <p>Filter the library to focus on buyer experiences, seller handoffs, or the agent stories that speak directly to lead quality and conversion.</p>
            </div>
            <div class="testimonial-filter-bar">
                <a href="{{ route('reviews') }}" class="testimonial-filter-chip {{ $selectedAudience === 'all' ? 'is-active' : '' }}">All <span>{{ $counts['all'] }}</span></a>
                @foreach(['buyer', 'seller', 'agent', 'community'] as $audience)
                    <a href="{{ route('reviews', ['audience' => $audience]) }}" class="testimonial-filter-chip {{ $selectedAudience === $audience ? 'is-active' : '' }}">
                        {{ $audienceMeta[$audience]['label'] }} <span>{{ $counts[$audience] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>

@if($featuredTestimonials->isNotEmpty())
<section class="section testimonial-spotlight testimonials-page__spotlight-section">
    <div class="container testimonial-spotlight__grid">
        <article class="cockpit-table-card testimonial-spotlight__intro">
            <span class="eyebrow">Spotlight Stories</span>
            <h2>Specific proof beats generic claims</h2>
            <p>The strongest reviews speak to real moments: faster follow-up, clearer next steps, cleaner lead context, and a noticeably more premium feel.</p>
            <div class="testimonial-spotlight__highlights">
                <div class="testimonial-spotlight__highlight">
                    <strong>{{ number_format($counts['buyer']) }}</strong>
                    <span>Buyer stories about confidence and guidance</span>
                </div>
                <div class="testimonial-spotlight__highlight">
                    <strong>{{ number_format($counts['seller']) }}</strong>
                    <span>Seller reviews focused on clarity and communication</span>
                </div>
                <div class="testimonial-spotlight__highlight">
                    <strong>{{ number_format($counts['agent']) }}</strong>
                    <span>Agent proof centered on qualification and conversion quality</span>
                </div>
                <div class="testimonial-spotlight__highlight">
                    <strong>{{ number_format($counts['community']) }}</strong>
                    <span>Community feedback that sharpens the product experience</span>
                </div>
            </div>
        </article>

        <div class="testimonial-spotlight__cards">
            @foreach($featuredTestimonials as $testimonial)
                <article class="testimonial-spotlight-card review-card review-card--premium">
                    <div class="testimonial-library-card__chips">
                        <span class="status-pill status-pill--assigned">{{ $testimonial->audience_label }}</span>
                        <span class="status-pill status-pill--qualified">Spotlight</span>
                        @if($testimonial->has_video)
                            <span class="status-pill status-pill--new">Video</span>
                        @endif
                    </div>
                    <div class="testimonial-stars" aria-label="{{ $testimonial->rating }} out of 5 stars">
                        @for($i = 0; $i < (int) $testimonial->rating; $i++)
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="testimonial-spotlight-card__quote">"{{ $testimonial->quote }}"</p>
                    <div class="testimonial-card__footer">
                        <img src="{{ $testimonial->photo_url }}" alt="{{ $testimonial->name }} testimonial profile photo" loading="lazy">
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

@if($videoTestimonials->isNotEmpty())
<section class="section testimonials-page__video-section" id="testimonial-videos">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">Video Testimonials</span>
            <h2>Watch the story behind the results</h2>
            <p>Video testimonials help buyers, sellers, and agents explain what felt different about the OmniReferral experience.</p>
        </div>
        <div class="testimonial-video-grid">
            @foreach($videoTestimonials as $testimonial)
                <article class="testimonial-video-card cockpit-table-card">
                    <div class="testimonial-video-card__media">
                        @if($testimonial->video_embed_url)
                            <iframe src="{{ $testimonial->video_embed_url }}" title="{{ $testimonial->name }} testimonial video" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        @elseif($testimonial->video_playback_url)
                            <video controls preload="metadata" poster="{{ $testimonial->photo_url }}">
                                <source src="{{ $testimonial->video_playback_url }}">
                            </video>
                        @endif
                    </div>
                    <div class="testimonial-video-card__body">
                        <div class="testimonial-video-card__meta">
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

<section class="section section--gray testimonials-page__library-section" id="testimonial-library">
    <div class="container">
        @foreach(['buyer', 'seller', 'agent', 'community'] as $audience)
            @continue($selectedAudience !== 'all' && $selectedAudience !== $audience)

            <div class="section-heading testimonial-section-heading">
                <span class="eyebrow">{{ $audienceMeta[$audience]['label'] }}</span>
                <h2>{{ $audienceMeta[$audience]['heading'] }}</h2>
                <p>{{ $audienceMeta[$audience]['copy'] }}</p>
            </div>

            <div class="review-grid review-grid--premium testimonial-library-grid">
                @forelse($groupedTestimonials[$audience] as $testimonial)
                    <article class="review-card review-card--premium testimonial-library-card">
                        <div class="review-card__header">
                            <img src="{{ $testimonial->photo_url }}" alt="{{ $testimonial->name }} testimonial profile photo" loading="lazy" width="88" height="88">
                            <div>
                                <div class="testimonial-library-card__chips">
                                    <span class="status-pill status-pill--assigned">{{ $testimonial->audience_label }}</span>
                                    @if($testimonial->has_video)
                                        <span class="status-pill status-pill--qualified">Video</span>
                                    @endif
                                </div>
                                <h2>{{ $testimonial->name }}</h2>
                                <p class="review-card__role">{{ $testimonial->company ?: $testimonial->audience_label . ' Client' }}</p>
                                <span class="review-card__location">{{ $testimonial->location ?: 'OmniReferral Network' }}</span>
                            </div>
                        </div>
                        <p class="testimonial-library-card__focus">{{ $audienceMeta[$audience]['focus'] }}</p>
                        <div class="testimonial-stars" aria-label="{{ $testimonial->rating }} out of 5 stars">
                            @for($i = 0; $i < (int) $testimonial->rating; $i++)
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <p class="review-card__quote">"{{ $testimonial->quote }}"</p>
                    </article>
                @empty
                    <article class="testimonial-empty-state cockpit-table-card">
                        <h3>No {{ $audienceMeta[$audience]['label'] }} testimonials yet</h3>
                        <p>As new {{ strtolower($audienceMeta[$audience]['label']) }} reviews are published, they will appear here automatically.</p>
                    </article>
                @endforelse
            </div>
        @endforeach
    </div>
</section>

<section class="section testimonials-page__submit-section" id="share-review">
    <div class="container">
        <div class="testimonial-submit-shell cockpit-table-card">
            <div class="testimonial-submit-shell__copy">
                <span class="eyebrow">Share Your Review</span>
                <h2>Let OmniReferral know how the experience felt</h2>
                <p>Buyers, sellers, agents, and general community users can submit their own review here. Every submission goes to the admin team first, and only approved reviews are published on the site.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert--error testimonial-submit-alert">
                    <strong>Please review your testimonial submission.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('reviews.store') }}" enctype="multipart/form-data" class="testimonial-submit-form">
                @csrf
                <div class="testimonial-submit-form__grid">
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
                    <label class="testimonial-submit-form__full">
                        <span>Your Review</span>
                        <textarea name="quote" rows="5" required placeholder="Tell OmniReferral what worked well, what felt polished, and what made the experience better.">{{ $reviewDraft['quote'] }}</textarea>
                    </label>
                    <label class="testimonial-submit-form__full">
                        <span>Photo</span>
                        <input type="file" name="photo" accept="image/*">
                        <small>Optional. If you are signed in and skip this, OmniReferral can use your current avatar when available.</small>
                    </label>
                </div>

                <div class="testimonial-submit-form__actions">
                    <button type="submit" class="button button--orange">Send Review To Admin</button>
                    <a href="{{ route('contact') }}" class="button button--ghost-blue">Need Help Instead?</a>
                </div>
            </form>
        </div>
    </div>
</section>

<section class="section testimonial-cta testimonials-page__cta-section">
    <div class="container">
        <div class="testimonial-cta__card cockpit-table-card">
            <div>
                <span class="eyebrow">Next Step</span>
                <h2>Ready to build a better client story?</h2>
                <p>Explore the package mix, see how the handoff works, and move from generic leads to a cleaner experience for buyers, sellers, and agents.</p>
            </div>
            <div class="testimonial-cta__actions">
                <a href="{{ route('pricing') }}" class="button button--orange">Explore Pricing</a>
                <a href="#share-review" class="button button--ghost-blue">Write A Review</a>
                <a href="{{ route('contact') }}" class="button button--ghost-blue">Talk To Our Team</a>
            </div>
        </div>
    </div>
</section>
 </div>
@endsection
