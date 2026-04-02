@extends('layouts.app')

@section('content')
<article class="section article-page">
    <div class="container-sm">
        <span class="eyebrow">{{ $blog->category }}</span>
        <h1>{{ $blog->title }}</h1>
        <p class="article-meta">By {{ $blog->author }} · {{ $blog->created_at->format('F d, Y') }}</p>
        <img class="article-image" src="{{ $blog->image_url }}" alt="{{ $blog->title }} article image" loading="lazy">
        <div class="article-content">{!! nl2br(e($blog->content)) !!}</div>
        <div class="article-related">
            <h2>Keep reading</h2>
            @foreach($related as $item)
                <a href="{{ route('blog.show', $item) }}">{{ $item->title }}</a>
            @endforeach
        </div>
    </div>
</article>
@endsection
