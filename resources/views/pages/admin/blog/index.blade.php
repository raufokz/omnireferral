@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Admin</span>
        <h1>Manage Blog Posts</h1>
        <p>Create, edit, and manage your SEO insights and referral strategies.</p>
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
                        <h3>All Posts</h3>
                        <a href="{{ route('admin.blog.create') }}" class="button button--orange" style="padding:.5rem 1.25rem; font-size:.85rem;">+ New Post</a>
                    </div>
                    <div class="admin-panel__body">
                        @if(session('success'))
                            <div class="alert alert--success" style="background:#f0fdf4; color:#16a34a; padding:1rem; border-radius:8px; margin-bottom:1.5rem;">
                                {{ session('success') }}
                            </div>
                        @endif

                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Author</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($blogs as $blog)
                                    <tr>
                                        <td><strong>{{ $blog->title }}</strong></td>
                                        <td>{{ $blog->category }}</td>
                                        <td>{{ $blog->author }}</td>
                                        <td>{{ $blog->created_at->format('M d, Y') }}</td>
                                        <td style="display:flex; gap:0.5rem;">
                                            <a href="{{ route('admin.blog.edit', $blog) }}" class="button button--ghost" style="padding:.35rem .75rem; font-size:.75rem;">Edit</a>
                                            <form action="{{ route('admin.blog.destroy', $blog) }}" method="POST" onsubmit="return confirm('Delete this post?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="button button--ghost" style="padding:.35rem .75rem; font-size:.75rem; color:#dc2626;">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center; padding:3rem;">No blog posts found. Start by creating one!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div style="margin-top:2rem;">
                            {{ $blogs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
