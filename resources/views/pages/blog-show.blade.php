@extends('layouts.app')

@section('content')
@php
    $authorName = $blog->author ?: 'OmniReferral Team';
    $readingTime = max(1, (int) ceil(str_word_count(strip_tags($blog->content)) / 220));
    $articleParagraphs = preg_split("/(\r\n){2,}|\n{2,}|\r{2,}/", trim($blog->content)) ?: [];
@endphp

<section class="page-hero blog-post-hero">
    <div class="blog-post-hero__glow" aria-hidden="true"></div>
    <div class="container blog-post-hero__inner">
        <div class="blog-post-hero__copy" data-animate="left">
            <a href="{{ route('blog.index') }}" class="blog-post-hero__back">Back to Blog</a>
            <span class="eyebrow">{{ $blog->category }}</span>
            <h1>{{ $blog->title }}</h1>
            <p class="blog-post-hero__excerpt">{{ $blog->excerpt }}</p>

            <div class="blog-post-hero__meta">
                <span>By {{ $authorName }}</span>
                <span>{{ $blog->created_at->format('F d, Y') }}</span>
                <span>{{ $readingTime }} min read</span>
            </div>
        </div>

        <aside class="blog-post-hero__panel" data-animate="right">
            <img src="{{ $blog->image_url }}" alt="{{ $blog->title }} article image" loading="lazy">
            <div class="blog-post-hero__panel-card">
                <span class="blog-post-hero__panel-label">Article Snapshot</span>
                <div class="blog-post-hero__panel-metrics">
                    <div class="blog-post-hero__panel-metric">
                        <strong>{{ $blog->category }}</strong>
                        <span>Topic</span>
                    </div>
                    <div class="blog-post-hero__panel-metric">
                        <strong>{{ $readingTime }} min</strong>
                        <span>Read time</span>
                    </div>
                    <div class="blog-post-hero__panel-metric">
                        <strong>{{ $related->count() }}</strong>
                        <span>Related reads</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</section>

<section class="section blog-article-shell">
    <div class="container blog-article-layout">
        <article class="blog-article-card">
            <div class="blog-article-card__head">
                <span class="eyebrow">Article</span>
                <h2>What this piece covers</h2>
                <p>{{ $blog->excerpt }}</p>
            </div>

            <div class="blog-article-content">
                @forelse($articleParagraphs as $paragraph)
                    @php $paragraph = trim($paragraph); @endphp
                    @continue($paragraph === '')
                    <p>{!! nl2br(e($paragraph)) !!}</p>
                @empty
                    <p>{!! nl2br(e($blog->content)) !!}</p>
                @endforelse
            </div>
        </article>

        <aside class="blog-article-sidebar">
            <div class="blog-article-sidebar__card">
                <span class="eyebrow">Details</span>
                <h3>Quick snapshot</h3>
                <div class="blog-article-sidebar__list">
                    <div>
                        <span>Author</span>
                        <strong>{{ $authorName }}</strong>
                    </div>
                    <div>
                        <span>Published</span>
                        <strong>{{ $blog->created_at->format('M d, Y') }}</strong>
                    </div>
                    <div>
                        <span>Category</span>
                        <strong>{{ $blog->category }}</strong>
                    </div>
                    <div>
                        <span>Read Time</span>
                        <strong>{{ $readingTime }} min</strong>
                    </div>
                </div>
                <div class="blog-article-sidebar__actions">
                    <a href="{{ route('blog.index') }}" class="button button--ghost-blue">More Articles</a>
                    <a href="{{ route('contact') }}" class="button button--orange">Talk To Sales</a>
                </div>
            </div>

            @if($related->count())
                <div class="blog-article-related">
                    <div class="blog-article-related__head">
                        <span class="eyebrow">Keep Reading</span>
                        <h3>Related insights</h3>
                    </div>

                    <div class="blog-article-related__list">
                        @foreach($related as $item)
                            <article class="blog-article-related__item">
                                <a href="{{ route('blog.show', $item) }}" class="blog-article-related__media">
                                    <img src="{{ $item->image_url }}" alt="{{ $item->title }} image" loading="lazy">
                                </a>
                                <div class="blog-article-related__body">
                                    <span class="blog-article-related__meta">{{ $item->category }} | {{ $item->created_at->format('M d, Y') }}</span>
                                    <h4><a href="{{ route('blog.show', $item) }}">{{ $item->title }}</a></h4>
                                    <p>{{ \Illuminate\Support\Str::limit($item->excerpt, 100) }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
</section>
@endsection
