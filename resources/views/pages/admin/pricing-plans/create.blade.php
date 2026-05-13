@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Create pricing plan')
@section('dashboard_description', 'Add a new pricing card to the public pricing page.')

@section('dashboard_actions')
    <a href="{{ route('admin.pricing-plans.index') }}" class="button button--ghost-blue">Back</a>
@endsection

@section('content')
<section class="workspace-card">
    <form method="POST" action="{{ route('admin.pricing-plans.store') }}">
        @csrf
        @include('pages.admin.pricing-plans._form', ['plan' => $plan])
        <div class="workspace-actions" style="margin-top:0.9rem;">
            <button type="submit" class="button">Create</button>
            <a href="{{ route('admin.pricing-plans.index') }}" class="button button--ghost-blue">Cancel</a>
        </div>
    </form>
</section>
@endsection
