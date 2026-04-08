@extends('layouts.app')

@section('content')
@php
    $blogCollection = $blogs->getCollection();
    $featuredBlog = $blogCollection->first();
    $blogCategoryCount = $blogCollection->pluck('category')->filter()->unique()->count();
@endphp

<section class="page-hero blog-hub-hero">
    <div class="blog-hub-hero__glow" aria-hidden="true"></div>
    <div class="container blog-hub-hero__inner">
        <div class="blog-hub-hero__copy" data-animate="left">
            <span class="eyebrow">Insights Library</span>
            <h1>Real estate growth insights for a stronger pipeline</h1>
            <p>Practical articles for teams that care about better routing, cleaner handoffs, and more predictable buyer and seller conversion.</p>

            <div class="blog-hub-hero__actions">
                <a href="#blog-library" class="button button--orange">Browse Articles</a>
                <a href="{{ route('contact') }}" class="button button--ghost-light">Talk To Sales</a>
            </div>

            <div class="blog-hub-hero__proof">
                <span>{{ method_exists($blogs, 'total') ? number_format($blogs->total()) : number_format($blogs->count()) }} published insights</span>
                <span>{{ $blogCategoryCount }} categories on this page</span>
                <span>Built for agents and teams</span>
            </div>
        </div>

        @if($featuredBlog)
            <aside class="blog-hub-hero__panel" data-animate="right">
                <a href="{{ route('blog.show', $featuredBlog) }}" class="blog-hub-hero__panel-media">
                    <img src="{{ $featuredBlog->image_url }}" alt="{{ $featuredBlog->title }} featured image" loading="lazy">
                </a>
                <div class="blog-hub-hero__panel-body">
                    <span class="blog-hub-hero__panel-eyebrow">Featured Read</span>
                    <p class="blog-hub-hero__panel-meta">{{ $featuredBlog->category }} | {{ $featuredBlog->created_at->format('M d, Y') }}</p>
                    <h2>{{ $featuredBlog->title }}</h2>
                    <p>{{ \Illuminate\Support\Str::limit($featuredBlog->excerpt, 150) }}</p>
                    <a href="{{ route('blog.show', $featuredBlog) }}" class="button button--blue">Read Article</a>
                </div>
            </aside>
        @endif
    </div>
</section>

<section class="section blog-library-section" id="blog-library">
    <div class="container">
        <div class="section-heading blog-library-section__head" data-animate="up">
            <span class="eyebrow">Latest Articles</span>
            <h2>Actionable content, not filler</h2>
            <p>Explore articles designed to help teams improve response speed, lead quality, handoff clarity, and local market execution.</p>
        </div>

        <div class="blog-library-grid" data-stagger>
            @forelse($blogs as $blog)
                @php
                    $readTime = max(1, (int) ceil(str_word_count(strip_tags($blog->excerpt . ' ' . $blog->content)) / 220));
                    $authorName = $blog->author ?: 'OmniReferral Team';
                @endphp
                <article class="blog-post-card">
                    <a href="{{ route('blog.show', $blog) }}" class="blog-post-card__media">
                        <img src="{{ $blog->image_url }}" alt="{{ $blog->title }} featured image" loading="lazy">
                    </a>

                    <div class="blog-post-card__body">
                        <div class="blog-post-card__meta">
                            <span class="blog-post-card__category">{{ $blog->category }}</span>
                            <span>{{ $blog->created_at->format('M d, Y') }}</span>
                            <span>{{ $readTime }} min read</span>
                        </div>

                        <h2><a href="{{ route('blog.show', $blog) }}">{{ $blog->title }}</a></h2>
                        <p>{{ \Illuminate\Support\Str::limit($blog->excerpt, 150) }}</p>

                        <div class="blog-post-card__footer">
                            <div class="blog-post-card__author">
                                <span class="blog-post-card__author-mark">{{ strtoupper(\Illuminate\Support\Str::substr($authorName, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $authorName }}</strong>
                                    <span>OmniReferral Editorial</span>
                                </div>
                            </div>
                            <a href="{{ route('blog.show', $blog) }}" class="button button--ghost-blue">Read More</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="blog-library-empty">
                    <span class="blog-library-empty__badge">No Posts Yet</span>
                    <h3>Fresh articles are on the way</h3>
                    <p>Check back soon for new content on lead generation, routing strategy, and better referral operations.</p>
                </div>
            @endforelse
        </div>

        <div class="pagination-wrap">{{ $blogs->links() }}</div>
    </div>
</section>
@endsection
