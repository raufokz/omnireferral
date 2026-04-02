@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Reviews</span>
        <h1>Trusted feedback from agents and partner teams</h1>
        <p>See how OmniReferral is helping real estate professionals improve lead quality, move faster, and create a more polished client experience.</p>
    </div>
</section>

<section class="section section--gray">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">Client Feedback</span>
            <h2>Real-world praise built on clearer handoffs and better lead quality</h2>
            <p>These reviews reflect what matters most to our users: trust, speed, professionalism, and a smoother path from prospect to conversation.</p>
        </div>
        <div class="review-grid review-grid--premium">
            @foreach($testimonials as $testimonial)
                <article class="review-card review-card--premium">
                    <div class="review-card__header">
                        <img src="{{ $testimonial->photo_url }}" alt="{{ $testimonial->name }} review profile photo" loading="lazy" width="88" height="88">
                        <div>
                            <h2>{{ $testimonial->name }}</h2>
                            <p class="review-card__role">{{ $testimonial->company }}</p>
                            <span class="review-card__location">{{ $testimonial->location }}</span>
                        </div>
                    </div>
                    <div class="testimonial-stars" aria-label="{{ $testimonial->rating }} out of 5 stars">
                        @for($i = 0; $i < (int) $testimonial->rating; $i++)
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="review-card__quote">“{{ $testimonial->quote }}”</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection

