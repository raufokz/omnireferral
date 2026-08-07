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
            @foreach(['one_time' => 'One-time', 'monthly' => 'Monthly', 'hourly' => 'Hourly', 'hybrid' => 'Hybrid'] as $value => $label)
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

    <label class="workspace-field">
        <span>Hourly price (USD)</span>
        <input type="number" min="0" name="hourly_price" value="{{ old('hourly_price', $package->hourly_price) }}">
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

@php
    $yesNo = fn (string $field) => old($field, $package->{$field} ? '1' : '0');
@endphp

<h3 style="margin-top:2rem; margin-bottom:0.5rem;">Lead Routing</h3>
<div class="workspace-form-grid">
    <label class="workspace-field">
        <span>Monthly Lead Quota</span>
        <input type="number" min="0" max="10000" name="monthly_lead_quota" value="{{ old('monthly_lead_quota', $package->monthly_lead_quota) }}">
    </label>
    <label class="workspace-field">
        <span>Lead Priority</span>
        <input type="number" min="0" max="100" name="lead_priority" value="{{ old('lead_priority', $package->lead_priority) }}">
        <small>Higher number = routed leads first.</small>
    </label>
</div>

<h3 style="margin-top:2rem; margin-bottom:0.5rem;">Listings</h3>
<div class="workspace-form-grid">
    <label class="workspace-field">
        <span>Property Listings Enabled</span>
        <select name="property_listings">
            <option value="1" {{ $yesNo('property_listings') === '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ $yesNo('property_listings') === '0' ? 'selected' : '' }}>No</option>
        </select>
    </label>
    <label class="workspace-field">
        <span>Listing Limit (active listings / month)</span>
        <input type="number" min="0" max="10000" name="listing_limit" value="{{ old('listing_limit', $package->listing_limit) }}">
    </label>
</div>

<h3 style="margin-top:2rem; margin-bottom:0.5rem;">Access &amp; Features</h3>
<div class="workspace-form-grid">
    @foreach([
        'portal_access' => 'Portal Access',
        'virtual_assistant' => 'Virtual Assistant',
        'priority_routing' => 'Priority Routing',
        'featured_placement' => 'Featured Placement',
        'premium_seo' => 'Premium SEO',
        'advanced_reporting' => 'Advanced Reporting',
        'dashboard_access' => 'Dashboard Access',
        'advanced_qualification' => 'Advanced Qualification',
        'dedicated_account_manager' => 'Dedicated Account Manager',
        'verified_referral_access' => 'Verified Referral Access',
    ] as $field => $label)
        <label class="workspace-field">
            <span>{{ $label }}</span>
            <select name="{{ $field }}">
                <option value="1" {{ $yesNo($field) === '1' ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ $yesNo($field) === '0' ? 'selected' : '' }}>No</option>
            </select>
        </label>
    @endforeach
</div>

<h3 style="margin-top:2rem; margin-bottom:0.5rem;">Tiers</h3>
<div class="workspace-form-grid">
    <label class="workspace-field">
        <span>Support Level</span>
        <select name="support_tier">
            @foreach(['none' => 'None', 'email' => 'Email', 'email_sms' => 'Email + SMS', 'priority' => 'Priority'] as $value => $label)
                <option value="{{ $value }}" {{ old('support_tier', $package->support_tier) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label class="workspace-field">
        <span>Profile Visibility</span>
        <select name="profile_tier">
            @foreach(['none' => 'None', 'basic' => 'Basic', 'premium' => 'Premium'] as $value => $label)
                <option value="{{ $value }}" {{ old('profile_tier', $package->profile_tier) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label class="workspace-field">
        <span>Analytics Level</span>
        <select name="analytics_level">
            @foreach(['none' => 'None', 'basic' => 'Basic', 'enhanced' => 'Enhanced', 'advanced' => 'Advanced'] as $value => $label)
                <option value="{{ $value }}" {{ old('analytics_level', $package->analytics_level) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label class="workspace-field">
        <span>Marketing Tier</span>
        <select name="marketing_tier">
            @foreach(['none' => 'None', 'basic' => 'Basic', 'better' => 'Better', 'premium' => 'Premium'] as $value => $label)
                <option value="{{ $value }}" {{ old('marketing_tier', $package->marketing_tier) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </label>
</div>

<h3 style="margin-top:2rem; margin-bottom:0.5rem;">Referrals</h3>
<div class="workspace-form-grid">
    <label class="workspace-field">
        <span>Referral Fee %</span>
        <input type="number" min="0" max="100" name="referral_fee_pct" value="{{ old('referral_fee_pct', $package->referral_fee_pct) }}">
    </label>
    <label class="workspace-field">
        <span>City / ZIP Limit</span>
        <input type="number" min="0" max="1000" name="city_limit" value="{{ old('city_limit', $package->city_limit) }}">
    </label>
    <label class="workspace-field">
        <span>Free Referrals</span>
        <input type="number" min="0" max="1000" name="free_referrals" value="{{ old('free_referrals', $package->free_referrals) }}">
    </label>
    <label class="workspace-field">
        <span>Referral Capacity (label)</span>
        <input type="text" name="referral_capacity" placeholder="e.g. 30+" value="{{ old('referral_capacity', $package->referral_capacity) }}">
    </label>
    <label class="workspace-field">
        <span>Verification Steps</span>
        <input type="number" min="0" max="20" name="verification_steps" value="{{ old('verification_steps', $package->verification_steps) }}">
    </label>
</div>

<h3 style="margin-top:2rem; margin-bottom:0.5rem;">Virtual Assistant Services</h3>
<p style="font-size:0.85rem; color:var(--dash-shell-muted, #64748b); margin-top:-0.5rem;">Only relevant for Virtual Assistant category packages — one deliverable per line.</p>
<div class="workspace-form-grid">
    <label class="workspace-field workspace-field--full">
        <span>Service Deliverables</span>
        <textarea id="servicesTextarea" rows="4">{{ implode("\n", old('services', $package->services ?? [])) }}</textarea>
        <div id="servicesHiddenInputs"></div>
    </label>
</div>

<script>
    // Convert the one-per-line textarea into services[] hidden inputs on submit
    // (validation expects an array field, not a single JSON string).
    document.currentScript.closest('form')?.addEventListener('submit', function () {
        var textarea = document.getElementById('servicesTextarea');
        var container = document.getElementById('servicesHiddenInputs');
        if (!textarea || !container) return;

        container.innerHTML = '';
        textarea.value.split('\n').map(function (s) { return s.trim(); }).filter(Boolean).forEach(function (service) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'services[]';
            input.value = service;
            container.appendChild(input);
        });
    });
</script>
