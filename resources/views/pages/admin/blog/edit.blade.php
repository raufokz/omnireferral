@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Edit Blog Post')
@section('dashboard_description', 'Refine existing content while keeping metadata and featured media aligned.')

@section('dashboard_actions')
    <a href="{{ route('admin.blog.index') }}" class="button button--ghost-blue">Back To Posts</a>
@endsection

@section('content')
<section class="workspace-card">
    <form action="{{ route('admin.blog.update', $blog) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="workspace-form-grid">
            <label class="workspace-field">
                <span>Title</span>
                <input type="text" name="title" value="{{ old('title', $blog->title) }}" required>
            </label>
            <label class="workspace-field">
                <span>Category</span>
                <input type="text" name="category" value="{{ old('category', $blog->category) }}" required>
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Excerpt</span>
                <textarea name="excerpt" rows="3" required>{{ old('excerpt', $blog->excerpt) }}</textarea>
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Content (HTML or Markdown)</span>
                <textarea name="content" rows="12" required>{{ old('content', $blog->content) }}</textarea>
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Featured Image</span>
                <input type="file" name="image" accept="image/*">
            </label>
        </div>

        @if($blog->image)
            <div style="margin-top: 0.8rem;">
                <span class="workspace-property__meta">Current image</span>
                <img src="{{ $blog->image_url }}" alt="Current blog image" style="max-height: 180px; margin-top: 0.4rem; border-radius: 12px;">
            </div>
        @endif

        <div class="workspace-actions" style="margin-top: 0.8rem;">
            <button type="submit" class="button">Update Post</button>
            <a href="{{ route('admin.blog.index') }}" class="button button--ghost-blue">Cancel</a>
        </div>
    </form>
</section>
@endsection
