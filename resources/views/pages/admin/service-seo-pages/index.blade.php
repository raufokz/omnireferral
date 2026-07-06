@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Content Management')
@section('dashboard_title', 'Service SEO Pages')
@section('dashboard_description', 'Manage hidden service landing pages for SEO traffic.')

@section('dashboard_actions')
    <a href="{{ route('admin.service-seo-pages.create') }}" class="button">+ Create Service Page</a>
@endsection

@section('content')
<section class="workspace-card">
    <div class="workspace-table-wrap">
        <table class="workspace-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Primary Keyword</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                    <tr>
                        <td data-label="Title"><strong>{{ $page->title }}</strong></td>
                        <td data-label="Slug"><code>/services/{{ $page->slug }}</code></td>
                        <td data-label="Primary Keyword">{{ $page->primary_keyword ?: 'Not set' }}</td>
                        <td data-label="Status">
                            <form method="POST" action="{{ route('admin.service-seo-pages.toggle-publish', $page) }}" style="display:inline;">
                                @csrf
                                @if($page->is_published)
                                    <button type="submit" class="button button--ghost-blue" style="background:#d4edda; color:#155724; padding:.2rem .6rem; border-radius:50px; font-size:.8rem; border:none; cursor:pointer;" title="Click to unpublish">Published</button>
                                @else
                                    <button type="submit" class="button button--ghost-blue" style="background:#f8d7da; color:#721c24; padding:.2rem .6rem; border-radius:50px; font-size:.8rem; border:none; cursor:pointer;" title="Click to publish">Draft</button>
                                @endif
                            </form>
                        </td>
                        <td data-label="Updated">{{ $page->updated_at->format('M d, Y') }}</td>
                        <td data-label="Actions">
                            <div class="workspace-actions">
                                <a href="{{ route('admin.service-seo-pages.edit', $page) }}" class="button button--ghost-blue">Edit</a>
                                <a href="{{ route('service-seo-pages.show', $page->slug) }}" target="_blank" class="button button--ghost-blue">View</a>
                                <form method="POST" action="{{ route('admin.service-seo-pages.destroy', $page) }}" onsubmit="return confirm('Delete this service SEO page? This cannot be undone.');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button button--ghost-blue" style="color:#dc2626;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="workspace-empty">No service SEO pages yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="workspace-pagination">{{ $pages->links() }}</div>
</section>
@endsection
