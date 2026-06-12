@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Packages')
@section('dashboard_description', 'Manage pricing plans, Stripe IDs, GoHighLevel form URLs, and plan visibility.')

@section('dashboard_actions')
    <a href="{{ route('admin.dashboard') }}" class="button button--ghost-blue">Overview</a>
    @can('packages.manage')
        <a href="{{ route('admin.packages.create') }}" class="button">Add Package</a>
    @endcan
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <span class="eyebrow">Filters</span>
        <h2>Search packages</h2>
        <form method="GET" action="{{ route('admin.packages.index') }}">
            <div class="workspace-form-grid">
                <label class="workspace-field workspace-field--full">
                    <span>Keyword</span>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Name or slug">
                </label>
                <label class="workspace-field">
                    <span>Category</span>
                    <select name="category">
                        <option value="">All</option>
                        @foreach($categories as $c)
                            <option value="{{ $c }}" {{ $filters['category'] === $c ? 'selected' : '' }}>{{ \Illuminate\Support\Str::headline($c) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Active</span>
                    <select name="active">
                        <option value="">Any</option>
                        <option value="1" {{ $filters['active'] === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $filters['active'] === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </label>
            </div>
            <div class="workspace-actions" style="margin-top:0.75rem;">
                <button type="submit" class="button">Apply</button>
                <a href="{{ route('admin.packages.index') }}" class="button button--ghost-blue">Reset</a>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Pricing</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                        <tr>
                            <td data-label="Name"><strong>{{ $package->name }}</strong></td>
                            <td data-label="Slug">{{ $package->slug }}</td>
                            <td data-label="Category">{{ \Illuminate\Support\Str::headline($package->category) }}</td>
                            <td data-label="Status">
                                <span class="workspace-pill">{{ $package->is_active ? 'Active' : 'Inactive' }}</span>
                                @if($package->is_featured)
                                    <span class="workspace-pill workspace-pill--accent">Featured</span>
                                @endif
                            </td>
                            <td data-label="Pricing">
                                @if(!is_null($package->one_time_price)) One-time: ${{ number_format($package->one_time_price) }} @endif
                                @if(!is_null($package->monthly_price)) <div class="workspace-property__meta">Monthly: ${{ number_format($package->monthly_price) }}</div> @endif
                                @if(!is_null($package->hourly_price)) <div class="workspace-property__meta">Hourly: ${{ number_format($package->hourly_price) }}</div> @endif
                            </td>
                            <td data-label="">
                                <a href="{{ route('admin.packages.edit', $package) }}" class="button button--ghost-blue">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><div class="workspace-empty">No packages found.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="workspace-pagination">{{ $packages->links() }}</div>
    </section>
</div>
@endsection
