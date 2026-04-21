@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Listing Management')
@section('dashboard_title', 'Edit Property Listing')
@section('dashboard_description', 'Update status, pricing, and listing details from a dedicated property management page.')

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <span class="eyebrow">Property Details</span>
        <h2>{{ $property->title }}</h2>

        @if($property->approval_status !== \App\Models\Property::APPROVAL_APPROVED)
            <div class="workspace-empty" style="margin-bottom: 0.8rem;">
                {{ $property->approvalStatusLabel() }}. Saving changes will resubmit this listing for review.
                @if($property->approval_notes)
                    Latest note: {{ $property->approval_notes }}
                @endif
            </div>
        @endif

        <form action="{{ route('properties.update', $property) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Property Title</span>
                    <input type="text" name="title" value="{{ old('title', $property->title) }}" required>
                </label>
                <label class="workspace-field">
                    <span>Location</span>
                    <input type="text" name="location" value="{{ old('location', $property->location) }}" required>
                </label>
                <label class="workspace-field">
                    <span>Asking Price</span>
                    <input type="number" name="price" value="{{ old('price', $property->price) }}" min="0" required>
                </label>
                <label class="workspace-field">
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
                <label class="workspace-field workspace-field--full">
                    <span>Description</span>
                    <textarea name="description">{{ old('description', $property->description) }}</textarea>
                </label>
            </div>

            <div class="workspace-actions" style="margin-top: 0.9rem;">
                <a href="{{ url()->previous() }}" class="button button--ghost-blue">Cancel</a>
                <button type="submit" class="button">
                    {{ $property->approval_status === \App\Models\Property::APPROVAL_APPROVED ? 'Save Property' : 'Save And Resubmit' }}
                </button>
            </div>
        </form>
    </section>

    <section class="workspace-card">
        <span class="eyebrow">Danger Zone</span>
        <h2>Delete Listing</h2>
        <p>This action permanently removes this property listing.</p>
        <form action="{{ route('properties.destroy', $property) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this listing?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="button" style="background:#b91c1c; border-color:#b91c1c; color:#fff;">Delete Listing</button>
        </form>
    </section>
</div>
@endsection
