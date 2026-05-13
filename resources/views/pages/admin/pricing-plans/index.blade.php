@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Pricing Plans')
@section('dashboard_description', 'Manage pricing cards displayed on the public pricing page.')

@section('dashboard_actions')
    <a href="{{ route('admin.dashboard') }}" class="button button--ghost-blue">Overview</a>
    @can('packages.manage')
        <a href="{{ route('admin.pricing-plans.create') }}" class="button">Add Plan</a>
    @endcan
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <span class="eyebrow">Filters</span>
        <h2>Search pricing plans</h2>
        <form method="GET" action="{{ route('admin.pricing-plans.index') }}">
            <div class="workspace-form-grid">
                <label class="workspace-field workspace-field--full">
                    <span>Keyword</span>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Name, slug, or tier">
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
                <a href="{{ route('admin.pricing-plans.index') }}" class="button button--ghost-blue">Reset</a>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Tier</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td data-label="Name"><strong>{{ $plan->name }}</strong><div class="workspace-property__meta">{{ $plan->slug }}</div></td>
                            <td data-label="Category">{{ \Illuminate\Support\Str::headline($plan->category) }}</td>
                            <td data-label="Tier">{{ $plan->tier ?: '—' }}</td>
                            <td data-label="Price">
                                ${{ number_format($plan->price) }}
                                @if($plan->price_note) <div class="workspace-property__meta">{{ $plan->price_note }}</div> @endif
                                @if($plan->value_price) <div class="workspace-property__meta"><s>Value ${{ number_format($plan->value_price) }}</s></div> @endif
                            </td>
                            <td data-label="Status">
                                <span class="workspace-pill">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
                                @if($plan->is_featured)
                                    <span class="workspace-pill workspace-pill--accent">Featured</span>
                                @endif
                            </td>
                            <td data-label="">
                                <a href="{{ route('admin.pricing-plans.edit', $plan) }}" class="button button--ghost-blue">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><div class="workspace-empty">No pricing plans found.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="workspace-pagination">{{ $plans->links() }}</div>
    </section>
</div>
@endsection
