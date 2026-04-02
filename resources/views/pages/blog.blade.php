@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Blog</span>
        <h1>Real estate growth insights for a stronger pipeline</h1>
    </div>
</section>

<section class="section">
    <div class="container blog-grid">
        @foreach($blogs as $blog)
            <article class="blog-card">
                <img src="{{ $blog->image_url }}" alt="{{ $blog->title }} featured image" loading="lazy">
                <div>
                    <span>{{ $blog->category }} · {{ $blog->created_at->format('M d, Y') }}</span>
                    <h2><a href="{{ route('blog.show', $blog) }}">{{ $blog->title }}</a></h2>
                    <p>{{ $blog->excerpt }}</p>
                </div>
            </article>
        @endforeach
    </div>
    <div class="pagination-wrap">{{ $blogs->links() }}</div>
</section>
@endsection
