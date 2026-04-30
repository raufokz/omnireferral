@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Edit Property Listing')
@section('dashboard_description', 'Update listing details, image gallery, moderation state, and ownership based on your permissions.')

@section('dashboard_actions')
    <a href="{{ route('admin.properties.index') }}" class="button button--ghost-blue">Back To Registry</a>
@endsection

@section('content')
<div class="workspace-stack">
    <form method="POST" action="{{ route('admin.properties.update', $property) }}" enctype="multipart/form-data" class="workspace-stack">
        @csrf
        @method('PUT')
        @include('pages.admin.properties._form', [
            'canManageListedBy' => $canManageListedBy ?? false,
            'listingUsers' => $listingUsers ?? collect(),
        ])
    </form>

    @if($canDelete ?? false)
        <section class="workspace-card">
            <span class="eyebrow">Danger Zone</span>
            <h2>Delete Listing</h2>
            <p style="margin: 0 0 0.75rem; color: #64748b; font-size: 0.9rem;">Deleting a listing removes it from the admin registry and marketplace views.</p>
            <form method="POST" action="{{ route('admin.properties.destroy', $property) }}" onsubmit="return confirm('Delete this listing permanently?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="button">Delete Listing</button>
            </form>
        </section>
    @endif
</div>
@endsection
