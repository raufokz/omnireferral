@php
    $testimonialData = collect($testimonial ?? []);
    $name = trim((string) $testimonialData->get('name', 'OmniReferral Client')) ?: 'OmniReferral Client';
    $role = trim((string) $testimonialData->get('role', $testimonialData->get('audience', 'Verified Client'))) ?: 'Verified Client';
    $location = trim((string) $testimonialData->get('location', 'OmniReferral Network')) ?: 'OmniReferral Network';
    $audience = trim((string) $testimonialData->get('audience', 'Client')) ?: 'Client';
    $quote = trim((string) $testimonialData->get('quote', 'A trusted client shared a positive OmniReferral experience.')) ?: 'A trusted client shared a positive OmniReferral experience.';
    $avatar = trim((string) $testimonialData->get('path', ''));
    $rating = max(1, min(5, (int) $testimonialData->get('rating', 5)));
    $hasVideo = (bool) $testimonialData->get('has_video', false);
    $featured = (bool) ($featured ?? false);
    $initials = collect(preg_split('/\s+/', $name) ?: [])
        ->filter()
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<article class="testimonial-card homepage-testimonial-card homepage-testimonial-card--modern {{ $featured ? 'homepage-testimonial-card--featured' : '' }}" data-animate="{{ $featured ? 'left' : 'up' }}">
    <div class="homepage-testimonial-card__meta">
        <span class="homepage-testimonial-card__badge">{{ $audience }}</span>
        @if($featured)
            <span class="homepage-testimonial-card__badge homepage-testimonial-card__badge--featured">Featured</span>
        @endif
        @if($hasVideo)
            <span class="homepage-testimonial-card__badge homepage-testimonial-card__badge--video">Video story</span>
        @endif
    </div>

    <div class="testimonial-stars homepage-testimonial-card__stars" aria-label="{{ $rating }} out of 5 star rating">
        @for($star = 1; $star <= 5; $star++)
            <span class="{{ $star > $rating ? 'is-muted' : '' }}">&#9733;</span>
        @endfor
    </div>

    <p class="testimonial-card__quote homepage-testimonial-card__quote">&ldquo;{{ $quote }}&rdquo;</p>

    <footer class="testimonial-card__footer homepage-testimonial-card__footer">
        <span class="homepage-testimonial-card__avatar">
            @if($avatar)
                <img src="{{ $avatar }}" alt="{{ $name }} testimonial profile photo" loading="lazy" decoding="async" width="64" height="64">
            @else
                {{ $initials ?: 'OR' }}
            @endif
        </span>
        <div class="homepage-testimonial-card__person">
            <strong>{{ $name }}</strong>
            <span>{{ $role }}</span>
            <small>{{ $location }}</small>
        </div>
    </footer>
</article>
