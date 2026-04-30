@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Add Property Listing')
@section('dashboard_description', 'Create a listing with full address, ZIP code, ownership assignment, and multiple listing images.')

@section('dashboard_actions')
    <a href="{{ route('admin.properties.index') }}" class="button button--ghost-blue">Back To Registry</a>
@endsection

@section('content')
<div class="workspace-stack">
    <form method="POST" action="{{ route('admin.properties.store') }}" enctype="multipart/form-data" class="workspace-stack">
        @csrf
        @include('pages.admin.properties._form', [
            'canManageListedBy' => true,
            'listingUsers' => $listingUsers,
        ])
    </form>
</div>
@endsection
