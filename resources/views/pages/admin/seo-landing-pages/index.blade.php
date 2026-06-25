@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Content Management')
@section('dashboard_title', 'SEO Landing Pages')
@section('dashboard_description', 'Manage SEO-optimized landing pages for local real estate markets.')

@section('dashboard_actions')
    <a href="{{ route('admin.seo-landing-pages.create') }}" class="button">+ Create New Page</a>
@endsection

@section('content')
<section class="workspace-card">
    <div class="workspace-table-wrap">
        <table class="workspace-table">
            <thead>
                <tr>
                    <th>City</th>
                    <th>State</th>
                    <th>Slug</th>
                    <th>Primary Keyword</th>
                    <th>Realtor</th>
                    <th>Status</th>
                    <th>Leads</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $p)
                    <tr>
                        <td data-label="City"><strong>{{ $p->city }}</strong></td>
                        <td data-label="State">{{ $p->state }}</td>
                        <td data-label="Slug"><code>{{ $p->slug }}</code></td>
                        <td data-label="Keyword">{{ $p->primary_keyword }}</td>
                        <td data-label="Realtor">
                            @if($p->realtorProfile)
                                <strong>{{ $p->realtorProfile->user?->publicDisplayName() ?? 'Unnamed realtor' }}</strong>
                                <br>
                                <span style="font-size:.8rem; color:#666;">{{ $p->realtorProfile->serviceAreaLabel() ?: $p->realtorProfile->slug }}</span>
                            @else
                                <span style="color:#777;">Not assigned</span>
                            @endif
                        </td>
                        <td data-label="Status">
                            @if($p->is_published)
                                <span style="background:#d4edda; color:#155724; padding:.2rem .6rem; border-radius:50px; font-size:.8rem;">Published</span>
                            @else
                                <span style="background:#f8d7da; color:#721c24; padding:.2rem .6rem; border-radius:50px; font-size:.8rem;">Draft</span>
                            @endif
                        </td>
                        <td data-label="Leads">{{ $p->leads()->count() }}</td>
                        <td data-label="Updated">{{ $p->updated_at->format('M d, Y') }}</td>
                        <td data-label="Actions">
                            <div class="workspace-actions">
                                <a href="{{ route('admin.seo-landing-pages.edit', $p) }}" class="button button--ghost-blue">Edit</a>
                                <a href="{{ route('seo-landing-page.show', $p->slug) }}" target="_blank" class="button button--ghost-blue">View</a>
                                <form method="POST" action="{{ route('admin.seo-landing-pages.destroy', $p) }}" onsubmit="return confirm('Are you sure you want to delete this SEO landing page? This action cannot be undone.');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button button--ghost-blue" style="color:#dc2626;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="workspace-empty">No SEO landing pages found. Run the seeder to create default pages.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="workspace-pagination">{{ $pages->links() }}</div>
</section>
@endsection
