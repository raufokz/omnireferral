@php
    $toggleGroup = $toggleGroup ?? 'pricing-page';
    $defaultCategory = $defaultCategory ?? 'real_estate';
    $toggleId = preg_replace('/[^A-Za-z0-9_-]/', '-', $toggleGroup);
    $pricingPlans = $pricingPlans ?? \App\Support\PricingContent::plans();
    $leadPlans = array_values($leadPlans ?? ($pricingPlans['real_estate'] ?? []));
    $vaPlans = array_values($vaPlans ?? ($pricingPlans['virtual_assistance'] ?? []));

    $billingLabel = function (array $plan): string {
        if (!empty($plan['billing_label'])) {
            return (string) $plan['billing_label'];
        }

        $note = strtolower((string) ($plan['price_note'] ?? ''));

        return match (true) {
            str_contains($note, 'year') => 'Per Year',
            str_contains($note, 'one') => 'One-Time',
            str_contains($note, 'hour') => 'Per Hour',
            str_contains($note, 'month') => 'Per Month',
            default => trim((string) ($plan['price_note'] ?? '')),
        };
    };

    $planIcon = function (string $slug): string {
        return match ($slug) {
            'quick-leads' => 'chart',
            'power-leads' => 'rocket',
            'prime-leads' => 'crown',
            'cold-calling-isa', 'va-calling' => 'phone',
            'social-media-mgmt', 'va-social' => 'social',
            'individual-va', 'va-individual' => 'clock',
            default => 'spark',
        };
    };

    $cardMeta = function (array $plan, string $category) use ($billingLabel, $planIcon): array {
        $slug = (string) ($plan['slug'] ?? '');
        $isFeatured = (bool) ($plan['is_featured'] ?? false);
        $tag = $plan['card_tag']
            ?? $plan['badge']
            ?? ($isFeatured ? 'Most Popular' : ($plan['tier'] ?? 'Plan'));
        $audience = $plan['card_best_for']
            ?? match ($slug) {
                'quick-leads' => 'New Agents',
                'power-leads' => 'Growing Teams',
                'prime-leads' => 'High Volume Agents',
                'cold-calling-isa', 'va-calling' => 'Busy Agents',
                'social-media-mgmt', 'va-social' => 'Brand Growth',
                'individual-va', 'va-individual' => 'Lean Teams',
                default => $category === 'virtual_assistance' ? 'Lean Teams' : 'Real Estate Teams',
            };
        $valueStatement = $plan['value_statement'] ?? $plan['summary'] ?? '';
        $description = $plan['card_description'] ?? $plan['summary'] ?? 'A focused OmniReferral package designed to help your team grow with cleaner handoff and support.';
        $highlights = array_values(array_filter((array) ($plan['highlights'] ?? array_slice((array) ($plan['features'] ?? []), 0, 3))));

        if (!empty($plan['cta_url'])) {
            $ctaUrl = str_starts_with((string) $plan['cta_url'], 'http') || str_starts_with((string) $plan['cta_url'], '/')
                ? (string) $plan['cta_url']
                : url((string) $plan['cta_url']);
        } elseif (in_array($slug, ['individual-va', 'va-individual'], true)) {
            $ctaUrl = route('contact');
        } elseif ($slug !== '') {
            $ctaUrl = route('packages.checkout', ['packageSlug' => $slug]);
        } else {
            $ctaUrl = route('pricing');
        }

        return [
            'audience' => $audience,
            'billing' => $billingLabel($plan),
            'cta_url' => $ctaUrl,
            'description' => $description,
            'highlights' => $highlights,
            'icon' => $planIcon($slug),
            'is_featured' => $isFeatured,
            'tag' => $tag,
            'value_statement' => $valueStatement,
        ];
    };
@endphp

<div class="pricing-toggle-shell pricing-toggle-shell--packages">
    <div class="pricing-toggle-row pricing-toggle-row--segmented" data-pricing-toggle="{{ $toggleGroup }}" data-default="{{ $defaultCategory }}" role="tablist" aria-label="Pricing categories">
        <button
            type="button"
            id="pricing-tab-{{ $toggleId }}-real-estate"
            class="pricing-toggle-option {{ $defaultCategory === 'real_estate' ? 'is-active' : '' }}"
            data-category="real_estate"
            data-helper="Lead packages for qualified referrals, territory routing, and real estate pipeline growth."
            role="tab"
            aria-selected="{{ $defaultCategory === 'real_estate' ? 'true' : 'false' }}"
            aria-controls="pricing-panel-{{ $toggleId }}-real-estate"
        >
            <span>Real Estate Plans</span>
            <small>Quick, Power, Prime</small>
        </button>
        <button
            type="button"
            id="pricing-tab-{{ $toggleId }}-va-services"
            class="pricing-toggle-option {{ $defaultCategory === 'virtual_assistance' ? 'is-active' : '' }}"
            data-category="virtual_assistance"
            data-helper="VA services for outbound calling, social content, operations, and flexible execution support."
            role="tab"
            aria-selected="{{ $defaultCategory === 'virtual_assistance' ? 'true' : 'false' }}"
            aria-controls="pricing-panel-{{ $toggleId }}-va-services"
        >
            <span>VA Services</span>
            <small>ISA, Social, Hourly VA</small>
        </button>
    </div>
    <p class="pricing-toggle-helper" data-pricing-toggle-helper>Lead packages for qualified referrals, territory routing, and real estate pipeline growth.</p>
</div>

<div
    id="pricing-panel-{{ $toggleId }}-real-estate"
    class="pricing-grid pricing-grid--spotlight pricing-grid--lead-only pricing-grid--tabbed"
    data-pricing-grid="{{ $toggleGroup }}"
    data-category="real_estate"
    role="tabpanel"
    aria-labelledby="pricing-tab-{{ $toggleId }}-real-estate"
    @if($defaultCategory !== 'real_estate') hidden @endif
    data-stagger
>
    @forelse($leadPlans as $plan)
        @php($card = $cardMeta($plan, 'real_estate'))
        @include('partials.pricing-plan-card', ['plan' => $plan, 'card' => $card, 'category' => 'real_estate'])
    @empty
        <div class="pricing-empty-state">Pricing plans are being prepared. Please contact sales for package guidance.</div>
    @endforelse
</div>

<div
    id="pricing-panel-{{ $toggleId }}-va-services"
    class="pricing-grid pricing-grid--spotlight pricing-grid--lead-only pricing-grid--tabbed"
    data-pricing-grid="{{ $toggleGroup }}"
    data-category="virtual_assistance"
    role="tabpanel"
    aria-labelledby="pricing-tab-{{ $toggleId }}-va-services"
    @if($defaultCategory !== 'virtual_assistance') hidden @endif
    data-stagger
>
    @forelse($vaPlans as $plan)
        @php($card = $cardMeta($plan, 'virtual_assistance'))
        @include('partials.pricing-plan-card', ['plan' => $plan, 'card' => $card, 'category' => 'virtual_assistance'])
    @empty
        <div class="pricing-empty-state">VA service plans are being prepared. Please contact sales for support options.</div>
    @endforelse
</div>
