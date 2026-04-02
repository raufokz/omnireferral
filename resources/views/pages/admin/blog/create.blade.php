@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Admin</span>
        <h1>Create New Post</h1>
        <p>Draft your next insight into the real estate referral ecosystem.</p>
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
                        <h3>Post Details</h3>
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

                        <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" class="admin-form">
                            @csrf
                            <div class="form-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
                                <label><span>Title</span><input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. 5 Strategies for High-Conversion Leads"></label>
                                <label><span>Category</span><input type="text" name="category" value="{{ old('category') }}" required placeholder="e.g. Lead Gen"></label>
                            </div>
                            
                            <label style="display:block; margin-bottom:1.5rem;"><span>Excerpt</span><textarea name="excerpt" rows="3" required placeholder="Short summary for the blog listing page">{{ old('excerpt') }}</textarea></label>
                            
                            <label style="display:block; margin-bottom:1.5rem;"><span>Content (HTML or Markdown)</span><textarea name="content" rows="12" required placeholder="Write your full post content here...">{{ old('content') }}</textarea></label>
                            
                            <label style="display:block; margin-bottom:2rem;"><span>Featured Image</span><input type="file" name="image" accept="image/*"></label>

                            <div class="form-actions">
                                <button type="submit" class="button button--orange">Publish Post</button>
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
