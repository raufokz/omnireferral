@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Edit package')
@section('dashboard_description', 'Update pricing, Stripe IDs, GoHighLevel mappings, and publication flags.')

@section('dashboard_actions')
    <a href="{{ route('admin.packages.index') }}" class="button button--ghost-blue">Back</a>
@endsection

@section('content')
<section class="workspace-card">
    <form method="POST" action="{{ route('admin.packages.update', $package) }}">
        @csrf
        @method('PUT')
        @include('pages.admin.packages._form', ['package' => $package])
        <div class="workspace-actions" style="margin-top:0.9rem;">
            <button type="submit" class="button">Save</button>
            <a href="{{ route('admin.packages.index') }}" class="button button--ghost-blue">Cancel</a>
        </div>
    </form>

    @can('packages.manage')
        <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" style="margin-top:1rem;">
            @csrf
            @method('DELETE')
            <button type="submit" class="button button--ghost-blue" onclick="return confirm('Delete this package?')">Delete package</button>
        </form>
    @endcan
</section>
@endsection

