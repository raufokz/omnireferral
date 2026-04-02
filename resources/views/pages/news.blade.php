@extends('layouts.app')
@section('content')
<section class="page-hero"><div class="container"><span class="eyebrow">News & Updates</span><h1>What’s new across OmniReferral</h1></div></section>
<section class="section"><div class="container news-list">@foreach($blogs as $blog)<article class="news-item"><h2>{{ $blog->title }}</h2><p>{{ $blog->excerpt }}</p></article>@endforeach</div></section>
@endsection
