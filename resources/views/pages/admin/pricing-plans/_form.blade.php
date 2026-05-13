@php
    $isEdit = isset($plan) && $plan->exists;
    $existingFeatures = old('features', $plan->features ?? []);
    if (!is_array($existingFeatures)) {
        $existingFeatures = [];
    }
@endphp

<div class="workspace-form-grid">
    <label class="workspace-field workspace-field--full">
        <span>Name</span>
        <input type="text" name="name" value="{{ old('name', $plan->name) }}" required>
    </label>

    <label class="workspace-field workspace-field--full">
        <span>Slug</span>
        <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}" required>
    </label>

    <label class="workspace-field">
        <span>Category</span>
        <select name="category" required>
            @foreach(['real_estate' => 'Real Estate', 'virtual_assistance' => 'Virtual Assistance'] as $value => $label)
                <option value="{{ $value }}" {{ old('category', $plan->category) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <label class="workspace-field">
        <span>Tier label</span>
        <input type="text" name="tier" value="{{ old('tier', $plan->tier) }}" placeholder="e.g. Starter Tier">
    </label>

    <label class="workspace-field">
        <span>Display price (USD)</span>
        <input type="number" min="0" name="price" value="{{ old('price', $plan->price) }}" required>
    </label>

    <label class="workspace-field">
        <span>Value price (USD, strike-through)</span>
        <input type="number" min="0" name="value_price" value="{{ old('value_price', $plan->value_price) }}" placeholder="Optional original price">
    </label>

    <label class="workspace-field workspace-field--full">
        <span>Price note</span>
        <input type="text" name="price_note" value="{{ old('price_note', $plan->price_note) }}" placeholder="e.g. / month - 2 Areas">
    </label>

    <label class="workspace-field workspace-field--full">
        <span>Summary</span>
        <textarea name="summary" rows="3">{{ old('summary', $plan->summary) }}</textarea>
    </label>

    <label class="workspace-field">
        <span>Active</span>
        <select name="is_active">
            <option value="1" {{ old('is_active', $plan->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ old('is_active', $plan->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>No</option>
        </select>
    </label>

    <label class="workspace-field">
        <span>Featured</span>
        <select name="is_featured">
            <option value="0" {{ old('is_featured', $plan->is_featured ? '1' : '0') === '0' ? 'selected' : '' }}>No</option>
            <option value="1" {{ old('is_featured', $plan->is_featured ? '1' : '0') === '1' ? 'selected' : '' }}>Yes</option>
        </select>
    </label>

    <label class="workspace-field">
        <span>CTA label</span>
        <input type="text" name="cta_label" value="{{ old('cta_label', $plan->cta_label) }}" placeholder="Get Started">
    </label>

    <label class="workspace-field">
        <span>CTA URL (leave blank for default)</span>
        <input type="url" name="cta_url" value="{{ old('cta_url', $plan->cta_url) }}" placeholder="https://...">
    </label>

    <label class="workspace-field">
        <span>Sort order</span>
        <input type="number" min="0" max="100000" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}">
    </label>
</div>

<div style="margin-top:1.25rem;">
    <span class="eyebrow">Features</span>
    <p style="font-size:0.85rem; color:var(--muted); margin-bottom:0.5rem;">
        Each line becomes a bullet point on the pricing card. Use the buttons to add or remove features.
    </p>

    <div id="pricing-features-list">
        @foreach($existingFeatures as $i => $feature)
            <div class="workspace-form-grid" style="margin-bottom:0.35rem; align-items:center;">
                <label class="workspace-field workspace-field--full" style="margin-bottom:0;">
                    <input type="text" name="features[]" value="{{ $feature }}" placeholder="Feature description">
                </label>
                <button type="button" class="button button--ghost-blue" onclick="this.closest('.workspace-form-grid').remove()" style="flex:0 0 auto; padding:0.35rem 0.75rem;">Remove</button>
            </div>
        @endforeach
    </div>

    <button type="button" class="button button--ghost-blue" id="add-feature-btn" style="margin-top:0.5rem;">
        Add feature
    </button>
</div>

<script>
    document.getElementById('add-feature-btn').addEventListener('click', function () {
        var list = document.getElementById('pricing-features-list');
        var row = document.createElement('div');
        row.className = 'workspace-form-grid';
        row.style.cssText = 'margin-bottom:0.35rem; align-items:center;';
        row.innerHTML =
            '<label class="workspace-field workspace-field--full" style="margin-bottom:0;">' +
                '<input type="text" name="features[]" value="" placeholder="Feature description">' +
            '</label>' +
            '<button type="button" class="button button--ghost-blue" onclick="this.closest(\'.workspace-form-grid\').remove()" style="flex:0 0 auto; padding:0.35rem 0.75rem;">Remove</button>';
        list.appendChild(row);
        row.querySelector('input').focus();
    });
</script>
