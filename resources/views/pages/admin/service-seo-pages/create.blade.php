@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Service SEO Pages')
@section('dashboard_title', 'Create Service SEO Page')
@section('dashboard_description', 'Create a hidden service landing page for organic search traffic.')

@section('dashboard_actions')
    <a href="{{ route('admin.service-seo-pages.index') }}" class="button button--ghost-blue">Back to List</a>
@endsection

@section('content')
    @if ($errors->any())
        <div class="app-flash app-flash--error" role="alert">
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.service-seo-pages.store') }}">
        @csrf
        @include('pages.admin.service-seo-pages._form', ['submitLabel' => 'Create Page'])
    </form>
@endsection
