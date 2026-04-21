@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Create Blog Post')
@section('dashboard_description', 'Draft and publish long-form content with a clean form-first editing experience.')

@section('dashboard_actions')
    <a href="{{ route('admin.blog.index') }}" class="button button--ghost-blue">Back To Posts</a>
@endsection

@section('content')
<section class="workspace-card">
    <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="workspace-form-grid">
            <label class="workspace-field">
                <span>Title</span>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. 5 Strategies for High-Conversion Leads">
            </label>
            <label class="workspace-field">
                <span>Category</span>
                <input type="text" name="category" value="{{ old('category') }}" required placeholder="e.g. Lead Gen">
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Excerpt</span>
                <textarea name="excerpt" rows="3" required>{{ old('excerpt') }}</textarea>
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Content (HTML or Markdown)</span>
                <textarea name="content" rows="12" required>{{ old('content') }}</textarea>
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Featured Image</span>
                <input type="file" name="image" accept="image/*">
            </label>
        </div>

        <div class="workspace-actions" style="margin-top: 0.8rem;">
            <button type="submit" class="button">Publish Post</button>
            <a href="{{ route('admin.blog.index') }}" class="button button--ghost-blue">Cancel</a>
        </div>
    </form>
</section>
@endsection
