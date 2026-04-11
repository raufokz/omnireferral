@extends('layouts.app')

@section('content')
<section class="section dashboard-page lead-ops-page">
    <div class="container">
        <div class="lead-ops-header">
            <div>
                <span class="eyebrow">Admin / Staff</span>
                <h1>Edit Testimonial</h1>
                <p>Update quote content, audience grouping, publishing status, and video proof from one place.</p>
            </div>
            <div class="lead-ops-header__actions">
                <a href="{{ route('admin.testimonials.index') }}" class="button button--ghost-blue">Back to Studio</a>
            </div>
        </div>

        <div class="cockpit-table-card testimonial-admin-shell">
            <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('pages.admin.testimonials._form')
            </form>
        </div>
    </div>
</section>
@endsection
