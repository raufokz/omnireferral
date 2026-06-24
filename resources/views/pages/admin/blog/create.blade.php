@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Create Blog Post')
@section('dashboard_description', 'Draft and publish long-form content with a clean form-first editing experience.')

@section('dashboard_actions')
    <a href="{{ route('admin.blog.index') }}" class="button button--ghost-blue">Back To Posts</a>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<section class="workspace-card">
    <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" id="blog-form">
        @csrf
        <div class="workspace-form-grid">
            <label class="workspace-field">
                <span>Title</span>
                <input type="text" name="title" id="blog-title" value="{{ old('title') }}" required placeholder="e.g. 5 Strategies for High-Conversion Leads">
            </label>
            <label class="workspace-field">
                <span>Slug</span>
                <input type="text" name="slug" id="blog-slug" value="{{ old('slug') }}" placeholder="Auto-generated from title">
            </label>
            <label class="workspace-field">
                <span>Category</span>
                <input type="text" name="category" value="{{ old('category') }}" required placeholder="e.g. Lead Gen">
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Excerpt</span>
                <textarea name="excerpt" rows="3" required>{{ old('excerpt') }}</textarea>
            </label>
            <div class="workspace-field workspace-field--full">
                <span>Content</span>
                <div id="quill-editor" style="height: 360px;">{!! old('content') !!}</div>
                <textarea name="content" id="quill-content" rows="12" style="display:none;">{{ old('content') }}</textarea>
            </div>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
const titleInput = document.getElementById('blog-title');
const slugInput = document.getElementById('blog-slug');
let slugEdited = Boolean(slugInput?.value);

function normalizeSlug(value) {
    return value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

titleInput?.addEventListener('input', function() {
    if (!slugEdited && slugInput) {
        slugInput.value = normalizeSlug(this.value);
    }
});

slugInput?.addEventListener('input', function() {
    slugEdited = true;
    this.value = normalizeSlug(this.value);
});

const quill = new Quill('#quill-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'header': [1, 2, 3, false] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link', 'image', 'blockquote', 'code-block'],
            [{ 'align': [] }],
            ['clean']
        ]
    }
});
quill.format('bold', false);
quill.format('header', false);

document.getElementById('blog-form').addEventListener('submit', function() {
    if (!slugInput.value) {
        slugInput.value = normalizeSlug(titleInput.value);
    }
    document.getElementById('quill-content').value = quill.root.innerHTML;
});
</script>
@endpush
