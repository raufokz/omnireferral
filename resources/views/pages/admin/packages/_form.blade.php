@php
    $isEdit = isset($package) && $package->exists;
@endphp

<div class="workspace-form-grid">
    <label class="workspace-field workspace-field--full">
        <span>Name</span>
        <input type="text" name="name" value="{{ old('name', $package->name) }}" required>
    </label>

    <label class="workspace-field workspace-field--full">
        <span>Slug</span>
        <input type="text" name="slug" value="{{ old('slug', $package->slug) }}" required>
    </label>

    <label class="workspace-field">
        <span>Category</span>
        <select name="category" required>
            @foreach(['lead' => 'Lead', 'virtual_assistant' => 'Virtual assistant'] as $value => $label)
                <option value="{{ $value }}" {{ old('category', $package->category) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <label class="workspace-field">
        <span>Billing type</span>
        <select name="billing_type" required>
            @foreach(['one_time' => 'One-time', 'monthly' => 'Monthly', 'hybrid' => 'Hybrid'] as $value => $label)
                <option value="{{ $value }}" {{ old('billing_type', $package->billing_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <label class="workspace-field">
        <span>Active</span>
        <select name="is_active">
            <option value="1" {{ old('is_active', $package->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ old('is_active', $package->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>No</option>
        </select>
    </label>

    <label class="workspace-field">
        <span>Featured</span>
        <select name="is_featured">
            <option value="1" {{ old('is_featured', $package->is_featured ? '1' : '0') === '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ old('is_featured', $package->is_featured ? '1' : '0') === '0' ? 'selected' : '' }}>No</option>
        </select>
    </label>

    <label class="workspace-field">
        <span>One-time price (USD)</span>
        <input type="number" min="0" name="one_time_price" value="{{ old('one_time_price', $package->one_time_price) }}">
    </label>

    <label class="workspace-field">
        <span>Monthly price (USD)</span>
        <input type="number" min="0" name="monthly_price" value="{{ old('monthly_price', $package->monthly_price) }}">
    </label>

    <label class="workspace-field workspace-field--full">
        <span>Description</span>
        <textarea name="description" rows="4">{{ old('description', $package->description) }}</textarea>
    </label>

    <label class="workspace-field">
        <span>Stripe product id</span>
        <input type="text" name="stripe_product_id" value="{{ old('stripe_product_id', $package->stripe_product_id) }}">
    </label>

    <label class="workspace-field">
        <span>Stripe price id</span>
        <input type="text" name="stripe_price_id" value="{{ old('stripe_price_id', $package->stripe_price_id) }}">
    </label>

    <label class="workspace-field workspace-field--full">
        <span>GoHighLevel form URL</span>
        <input type="url" name="ghl_form_url" value="{{ old('ghl_form_url', $package->ghl_form_url) }}">
    </label>

    <label class="workspace-field workspace-field--full">
        <span>GoHighLevel pipeline stage</span>
        <input type="text" name="ghl_pipeline_stage" value="{{ old('ghl_pipeline_stage', $package->ghl_pipeline_stage) }}">
    </label>

    <label class="workspace-field">
        <span>CTA label</span>
        <input type="text" name="cta_label" value="{{ old('cta_label', $package->cta_label) }}">
    </label>

    <label class="workspace-field">
        <span>Duration (days)</span>
        <input type="number" min="1" max="5000" name="duration_days" value="{{ old('duration_days', $package->duration_days) }}">
    </label>

    <label class="workspace-field">
        <span>Sort order</span>
        <input type="number" min="0" max="100000" name="sort_order" value="{{ old('sort_order', $package->sort_order) }}">
    </label>
</div>

