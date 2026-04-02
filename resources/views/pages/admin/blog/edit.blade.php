@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Admin</span>
        <h1>Edit Post</h1>
        <p>Refine your insights and keep your content up to date.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="admin-layout">
            <aside class="admin-sidebar">
                <div class="admin-sidebar__brand">Admin Panel</div>
                <nav class="admin-nav">
                    <a href="{{ route('admin.dashboard') }}">Overview</a>
                    <a href="{{ route('admin.blog.index') }}" class="is-active">Blog Posts</a>
                </nav>
            </aside>

            <div class="admin-main">
                <div class="admin-panel">
                    <div class="admin-panel__header">
                        <h3>Edit: {{ $blog->title }}</h3>
                        <a href="{{ route('admin.blog.index') }}" class="button button--ghost" style="padding:.5rem 1.25rem; font-size:.85rem;">Cancel</a>
                    </div>
                    <div class="admin-panel__body">
                        @if ($errors->any())
                            <div class="alert alert--danger" style="background:#fef2f2; color:#b91c1c; padding:1rem; border-radius:8px; margin-bottom:1.5rem;">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.blog.update', $blog) }}" method="POST" enctype="multipart/form-data" class="admin-form">
                            @csrf
                            @method('PUT')
                            <div class="form-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
                                <label><span>Title</span><input type="text" name="title" value="{{ old('title', $blog->title) }}" required></label>
                                <label><span>Category</span><input type="text" name="category" value="{{ old('category', $blog->category) }}" required></label>
                            </div>
                            
                            <label style="display:block; margin-bottom:1.5rem;"><span>Excerpt</span><textarea name="excerpt" rows="3" required>{{ old('excerpt', $blog->excerpt) }}</textarea></label>
                            
                            <label style="display:block; margin-bottom:1.5rem;"><span>Content (HTML or Markdown)</span><textarea name="content" rows="12" required>{{ old('content', $blog->content) }}</textarea></label>
                            
                            <div style="margin-bottom:2rem;">
                                <label style="display:block; margin-bottom:0.5rem;"><span>Featured Image</span><input type="file" name="image" accept="image/*"></label>
                                @if($blog->image)
                                    <div style="margin-top:1rem;">
                                        <p style="font-size:0.8rem; color:var(--color-text-muted); margin-bottom:0.5rem;">Current Image:</p>
                                        <img src="{{ $blog->image_url }}" alt="Preview" style="max-height:150px; border-radius:8px; border:1px solid var(--color-border);">
                                    </div>
                                @endif
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="button button--orange">Update Post</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .admin-form label span { display: block; font-weight: 700; color: var(--color-brand-blue); margin-bottom: .5rem; font-size: .9rem; }
    .admin-form input, .admin-form select, .admin-form textarea { width: 100%; padding: .85rem; border: 1px solid var(--color-border); border-radius: 8px; font-family: inherit; }
    .admin-form textarea { resize: vertical; }
</style>
@endsection
