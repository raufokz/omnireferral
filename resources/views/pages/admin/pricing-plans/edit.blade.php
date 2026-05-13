@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Edit pricing plan')
@section('dashboard_description', 'Update this pricing card. Changes appear on the public pricing page immediately.')

@section('dashboard_actions')
    <a href="{{ route('admin.pricing-plans.index') }}" class="button button--ghost-blue">Back</a>
@endsection

@section('content')
<section class="workspace-card">
    <form method="POST" action="{{ route('admin.pricing-plans.update', $plan) }}">
        @csrf
        @method('PUT')
        @include('pages.admin.pricing-plans._form', ['plan' => $plan])
        <div class="workspace-actions" style="margin-top:0.9rem;">
            <button type="submit" class="button">Save</button>
            <a href="{{ route('admin.pricing-plans.index') }}" class="button button--ghost-blue">Cancel</a>
        </div>
    </form>

    @can('packages.manage')
        <form method="POST" action="{{ route('admin.pricing-plans.destroy', $plan) }}" style="margin-top:1rem;">
            @csrf
            @method('DELETE')
            <button type="submit" class="button button--ghost-blue" onclick="return confirm('Delete this pricing plan? This will remove the card from the pricing page.')">Delete plan</button>
        </form>
    @endcan
</section>
@endsection
