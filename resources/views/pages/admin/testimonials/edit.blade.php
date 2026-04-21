@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Edit Testimonial')
@section('dashboard_description', 'Update testimonial content, audience targeting, and publish controls.')

@section('dashboard_actions')
    <a href="{{ route('admin.testimonials.index') }}" class="button button--ghost-blue">Back To Studio</a>
@endsection

@section('content')
<section class="workspace-card">
    <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('pages.admin.testimonials._form')
    </form>
</section>
@endsection
