@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Package Lead Settings')
@section('dashboard_description', 'Configure monthly lead quotas and priority for each package.')

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Package</th>
                        <th>Category</th>
                        <th>Monthly Lead Quota</th>
                        <th>Lead Priority</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                        <tr>
                            <td>{{ $package->name }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $package->category)) }}</td>
                            <td>{{ $package->monthly_lead_quota }}</td>
                            <td>{{ $package->lead_priority }}</td>
                            <td>
                                @if($package->is_active)
                                    <span class="badge badge--active">Active</span>
                                @else
                                    <span class="badge badge--inactive">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.package-lead-settings.edit', $package) }}" class="link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center muted">No packages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
