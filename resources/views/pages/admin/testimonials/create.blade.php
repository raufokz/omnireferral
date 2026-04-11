@extends('layouts.app')

@section('content')
<section class="section dashboard-page lead-ops-page">
    <div class="container">
        <div class="lead-ops-header">
            <div>
                <span class="eyebrow">Admin / Staff</span>
                <h1>Add Testimonial</h1>
                <p>Create a new buyer, seller, agent, or community testimonial and optionally attach a video link or uploaded clip.</p>
            </div>
            <div class="lead-ops-header__actions">
                <a href="{{ route('admin.testimonials.index') }}" class="button button--ghost-blue">Back to Studio</a>
            </div>
        </div>

        <div class="cockpit-table-card testimonial-admin-shell">
            <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data">
                @csrf
                @include('pages.admin.testimonials._form')
            </form>
        </div>
    </div>
</section>
@endsection
