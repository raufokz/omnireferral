@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Blog Management')
@section('dashboard_description', 'Create, edit, and organize blog content in a dedicated page-driven workflow.')

@section('dashboard_actions')
    <a href="{{ route('admin.blog.create') }}" class="button">+ New Post</a>
@endsection

@section('content')
<section class="workspace-card">
    <div class="workspace-table-wrap">
        <table class="workspace-table">
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
                        <td data-label="Title"><strong>{{ $blog->title }}</strong></td>
                        <td data-label="Category">{{ $blog->category }}</td>
                        <td data-label="Author">{{ $blog->author }}</td>
                        <td data-label="Date">{{ $blog->created_at->format('M d, Y') }}</td>
                        <td data-label="Actions">
                            <div class="workspace-actions">
                                <a href="{{ route('admin.blog.edit', $blog) }}" class="button button--ghost-blue">Edit</a>
                                <form action="{{ route('admin.blog.destroy', $blog) }}" method="POST" onsubmit="return confirm('Delete this post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button" style="background:#b91c1c; border-color:#b91c1c; color:#fff;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="workspace-empty">No blog posts found yet. Create your first post to start publishing.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="workspace-pagination">
        {{ $blogs->links() }}
    </div>
</section>
@endsection
