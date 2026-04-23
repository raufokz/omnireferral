@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Testimonial Studio')
@section('dashboard_description', 'Manage written and video testimonials in a dedicated moderation and publishing workspace.')

@section('dashboard_actions')
    <a href="{{ route('admin.testimonials.create') }}" class="button">Add Testimonial</a>
    <a href="{{ route('admin.testimonials.create', ['video' => 1]) }}" class="button button--ghost-blue">Add Video</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi">
            <span>Total</span>
            <strong>{{ number_format($stats['total']) }}</strong>
            <span>Published and draft combined</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Video</span>
            <strong>{{ number_format($stats['videos']) }}</strong>
            <span>Testimonials with video proof</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Published</span>
            <strong>{{ number_format($stats['published']) }}</strong>
            <span>Visible on the public site</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Pending Review</span>
            <strong>{{ number_format($stats['pending']) }}</strong>
            <span>Awaiting moderation</span>
        </article>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
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
                            <td data-label="Person">
                                <strong>{{ $testimonial->name }}</strong>
                                <div class="workspace-property__meta">{{ $testimonial->company ?: 'No role/company' }}</div>
                                <div class="workspace-property__meta">{{ $testimonial->location ?: 'No location' }}</div>
                            </td>
                            <td data-label="Audience">
                                <span class="status-pill status-pill--assigned">{{ $testimonial->audience_label }}</span>
                                <div class="workspace-property__meta">{{ $testimonial->rating }}/5 rating</div>
                            </td>
                            <td data-label="Proof">
                                @if($testimonial->has_video)
                                    <span class="status-pill status-pill--qualified">Video attached</span>
                                @else
                                    <span class="status-pill status-pill--new">Quote only</span>
                                @endif
                                <div class="workspace-property__meta">{{ \Illuminate\Support\Str::limit($testimonial->quote, 90) }}</div>
                            </td>
                            <td data-label="Status">
                                <span class="status-pill status-pill--{{ $testimonial->submissionStatusTone() }}">{{ $testimonial->submissionStatusLabel() }}</span>
                                <div class="workspace-property__meta">{{ $testimonial->is_published ? 'Published' : 'Hidden' }}</div>
                                <div class="workspace-property__meta">Sort: {{ $testimonial->sort_order }}</div>
                            </td>
                            <td data-label="Actions">
                                <div class="workspace-actions">
                                    @if($testimonial->submission_status !== \App\Models\Testimonial::STATUS_APPROVED)
                                        <form action="{{ route('admin.testimonials.review', $testimonial) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="decision" value="approve">
                                            <button type="submit" class="button">Approve</button>
                                        </form>
                                    @endif
                                    @if($testimonial->submission_status !== \App\Models\Testimonial::STATUS_REJECTED)
                                        <form action="{{ route('admin.testimonials.review', $testimonial) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="decision" value="reject">
                                            <button type="submit" class="button button--ghost-blue">Reject</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="button button--ghost-blue">Edit</a>
                                    <form action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST" onsubmit="return confirm('Delete this testimonial?')">
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
                                <div class="workspace-empty">No testimonials yet. Create your first entry to build social proof.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="workspace-pagination">
            {{ $testimonials->links() }}
        </div>
    </section>
</div>
@endsection
