@extends('layouts.app')

@section('content')
<section class="section dashboard-page lead-ops-page">
    <div class="container">
        <div class="lead-ops-header">
            <div>
                <span class="eyebrow">Admin / Staff</span>
                <h1>Testimonial Studio</h1>
                <p>Manage written reviews and video testimonials for buyers, sellers, agents, and community users from one content workspace.</p>
            </div>
            <div class="lead-ops-header__actions">
                <a href="{{ route('admin.dashboard') }}" class="button button--ghost-blue">Dashboard</a>
                <a href="{{ route('admin.testimonials.create') }}" class="button button--orange">Add Testimonial</a>
                <a href="{{ route('admin.testimonials.create', ['video' => 1]) }}" class="button button--ghost-blue">Add Video</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert--success testimonial-admin-alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="lead-ops-stats-grid testimonial-admin-stats-grid">
            <article class="cockpit-table-card lead-ops-stat-card">
                <span>Total Testimonials</span>
                <strong>{{ number_format($stats['total']) }}</strong>
                <span>Published and draft stories together.</span>
            </article>
            <article class="cockpit-table-card lead-ops-stat-card">
                <span>Video Testimonials</span>
                <strong>{{ number_format($stats['videos']) }}</strong>
                <span>Stories with YouTube, Vimeo, or uploaded video.</span>
            </article>
            <article class="cockpit-table-card lead-ops-stat-card">
                <span>Published</span>
                <strong>{{ number_format($stats['published']) }}</strong>
                <span>Currently visible on the public testimonials page.</span>
            </article>
            <article class="cockpit-table-card lead-ops-stat-card">
                <span>Pending Review</span>
                <strong>{{ number_format($stats['pending']) }}</strong>
                <span>Fresh submissions waiting for admin approval.</span>
            </article>
        </div>

        <div class="cockpit-table-card lead-ops-table-wrap">
            <table class="cockpit-table lead-ops-table">
                <thead>
                    <tr>
                        <th>Person</th>
                        <th>Audience</th>
                        <th>Proof</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testimonials as $testimonial)
                        <tr>
                            <td>
                                <div class="lead-ops-cell-stack">
                                    <strong>{{ $testimonial->name }}</strong>
                                    <span class="cockpit-secondary-data">{{ $testimonial->company ?: 'No role or company set' }}</span>
                                    <span class="cockpit-secondary-data">{{ $testimonial->location ?: 'Location not set' }}</span>
                                    @if($testimonial->submitted_by_email)
                                        <span class="cockpit-secondary-data">{{ $testimonial->submitted_by_email }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="lead-ops-cell-stack">
                                    <span class="status-pill status-pill--assigned">{{ $testimonial->audience_label }}</span>
                                    <span class="cockpit-secondary-data">{{ $testimonial->rating }}/5 rating</span>
                                </div>
                            </td>
                            <td>
                                <div class="lead-ops-cell-stack">
                                    @if($testimonial->has_video)
                                        <span class="status-pill status-pill--qualified">Video attached</span>
                                    @else
                                        <span class="status-pill status-pill--new">Quote only</span>
                                    @endif
                                    <span class="cockpit-secondary-data">{{ \Illuminate\Support\Str::limit($testimonial->quote, 90) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="lead-ops-cell-stack">
                                    <span class="status-pill status-pill--{{ $testimonial->submissionStatusTone() }}">{{ $testimonial->submissionStatusLabel() }}</span>
                                    <span class="status-pill status-pill--{{ $testimonial->is_published ? 'qualified' : 'rejected' }}">{{ $testimonial->is_published ? 'Published' : 'Hidden' }}</span>
                                    <span class="cockpit-secondary-data">Sort: {{ $testimonial->sort_order }}</span>
                                    @if($testimonial->reviewer)
                                        <span class="cockpit-secondary-data">Reviewed by {{ $testimonial->reviewer->name }}</span>
                                    @endif
                                    @if($testimonial->is_featured)
                                        <span class="cockpit-secondary-data">Featured placement enabled</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="testimonial-admin-actions">
                                    @if($testimonial->submission_status !== \App\Models\Testimonial::STATUS_APPROVED)
                                        <form action="{{ route('admin.testimonials.review', $testimonial) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="decision" value="approve">
                                            <button type="submit" class="button button--orange button--compact">Approve</button>
                                        </form>
                                    @endif
                                    @if($testimonial->submission_status !== \App\Models\Testimonial::STATUS_REJECTED)
                                        <form action="{{ route('admin.testimonials.review', $testimonial) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="decision" value="reject">
                                            <button type="submit" class="button button--ghost button--compact">Reject</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="button button--ghost-blue button--compact">Edit</a>
                                    <form action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST" onsubmit="return confirm('Delete this testimonial?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button button--ghost button--compact">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="lead-ops-empty">
                                    <h3>No testimonials yet</h3>
                                    <p>Create your first written or video testimonial to populate the public testimonials page.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $testimonials->links() }}
        </div>
    </div>
</section>
@endsection
