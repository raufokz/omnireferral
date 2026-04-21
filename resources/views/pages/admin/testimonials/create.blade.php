@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Create Testimonial')
@section('dashboard_description', 'Add a new testimonial with optional image and video proof.')

@section('dashboard_actions')
    <a href="{{ route('admin.testimonials.index') }}" class="button button--ghost-blue">Back To Studio</a>
@endsection

@section('content')
<section class="workspace-card">
    <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data">
        @csrf
        @include('pages.admin.testimonials._form')
    </form>
</section>
@endsection
