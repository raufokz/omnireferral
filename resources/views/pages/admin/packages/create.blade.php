@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Create package')
@section('dashboard_description', 'Add a new pricing plan and map it to Stripe and GoHighLevel.')

@section('dashboard_actions')
    <a href="{{ route('admin.packages.index') }}" class="button button--ghost-blue">Back</a>
@endsection

@section('content')
<section class="workspace-card">
    <form method="POST" action="{{ route('admin.packages.store') }}">
        @csrf
        @include('pages.admin.packages._form', ['package' => $package])
        <div class="workspace-actions" style="margin-top:0.9rem;">
            <button type="submit" class="button">Create</button>
            <a href="{{ route('admin.packages.index') }}" class="button button--ghost-blue">Cancel</a>
        </div>
    </form>
</section>
@endsection

