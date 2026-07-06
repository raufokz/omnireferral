@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Service SEO Pages')
@section('dashboard_title', 'Edit Service SEO Page')
@section('dashboard_description', 'Update service landing content, metadata, and publish status.')

@section('dashboard_actions')
    <a href="{{ route('admin.service-seo-pages.index') }}" class="button button--ghost-blue">Back to List</a>
    <a href="{{ route('service-seo-pages.show', $page->slug) }}" target="_blank" class="button">{{ $page->is_published ? 'View Live' : 'Preview Draft' }}</a>
@endsection

@section('content')
    @if ($errors->any())
        <div class="app-flash app-flash--error" role="alert">
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    @if(! $page->is_published)
        <div class="app-flash" role="alert" style="background:#fff3cd;border:1px solid #ffc107;color:#856404;">
            <span><strong>Draft Mode</strong> — This page is not published and is only visible to admin users.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.service-seo-pages.update', $page) }}">
        @csrf
        @method('PUT')
        @include('pages.admin.service-seo-pages._form', ['submitLabel' => 'Save Changes'])
    </form>
@endsection
