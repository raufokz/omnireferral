@extends('layouts.app')

@section('content')
<section class="page-hero dashboard-page-hero">
    <div class="container page-hero__content">
        <span class="eyebrow">Property Management</span>
        <h1>Update Property Listing</h1>
        <p>Ensure pricing, status, and descriptive details are current to maximize match rates and buyer interest.</p>
    </div>
</section>

<section class="section dashboard-page dashboard-page--premium">
    <div class="container dashboard-shell-grid dashboard-shell-grid--centered">
        
        <div class="dashboard-main-panel">
            <div class="dashboard-surface">
                <div class="dashboard-surface__header">
                    <div>
                        <span class="eyebrow">Edit Details</span>
                        <h3>{{ $property->title }}</h3>
                    </div>
                </div>

                @if($property->approval_status !== \App\Models\Property::APPROVAL_APPROVED)
                    <div class="agent-portal-warning" style="margin-bottom: 1.5rem;">
                        <strong>{{ $property->approvalStatusLabel() }}</strong>
                        <p>
                            Saving changes will submit this listing back to admin for review before it appears publicly.
                            @if($property->approval_notes)
                                Latest note: {{ $property->approval_notes }}
                            @endif
                        </p>
                    </div>
                @endif

                <form action="{{ route('properties.update', $property) }}" method="POST" class="auth-form-shell dashboard-form">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-grid-2">
                        <label>
                            <span>Property Title</span>
                            <input type="text" name="title" value="{{ old('title', $property->title) }}" required>
                        </label>
                        <label>
                            <span>Location Name</span>
                            <input type="text" name="location" value="{{ old('location', $property->location) }}" required>
                        </label>
                        <label>
                            <span>Asking Price</span>
                            <input type="number" name="price" value="{{ old('price', $property->price) }}" min="0" required>
                        </label>
                        <label>
                            <span>Status</span>
                            <select name="status" required>
                                <option value="Active" {{ old('status', $property->status) === 'Active' ? 'selected' : '' }}>Active</option>
                                @if(auth()->user()?->isStaff())
                                    <option value="Pending" {{ old('status', $property->status) === 'Pending' ? 'selected' : '' }}>Pending</option>
                                @endif
                                <option value="Sold" {{ old('status', $property->status) === 'Sold' ? 'selected' : '' }}>Sold</option>
                                <option value="Off-Market" {{ old('status', $property->status) === 'Off-Market' ? 'selected' : '' }}>Off-Market</option>
                            </select>
                        </label>
                    </div>

                    <label class="form-full-row">
                        <span>Property Description</span>
                        <textarea name="description" rows="5">{{ old('description', $property->description) }}</textarea>
                    </label>

                    <div class="dashboard-form__actions" style="display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-top: 2rem;">
                        <a href="{{ url()->previous() }}" class="button button--ghost">Cancel Update</a>
                        <button type="submit" class="button button--orange">{{ $property->approval_status === \App\Models\Property::APPROVAL_APPROVED ? 'Save Property' : 'Save And Resubmit' }}</button>
                    </div>
                </form>
            </div>

            <div class="dashboard-surface mt-6" style="border-top: 4px solid var(--color-red-500);">
                <div class="dashboard-surface__header">
                    <div>
                        <h3 style="color: var(--color-red-600);">Danger Zone</h3>
                        <p>Removing this property will take it offline completely. This action cannot be undone.</p>
                    </div>
                    <form action="{{ route('properties.destroy', $property) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this listing?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="button" style="background: var(--color-red-500); color: white; border-color: var(--color-red-500);">Delete Listing</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
