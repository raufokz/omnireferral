@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Resources</span>
        <h1>Guides and playbooks for smarter real estate growth</h1>
    </div>
</section>

<section class="section">
    <div class="container blog-grid">
        @foreach($blogs as $blog)
            <article class="blog-card">
                <img src="{{ $blog->image_url }}" alt="{{ $blog->title }} guide image" loading="lazy">
                <div>
                    <h2>{{ $blog->title }}</h2>
                    <p>{{ $blog->excerpt }}</p>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endsection
