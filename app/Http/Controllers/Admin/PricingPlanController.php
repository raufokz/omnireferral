<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PricingPlanController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('packages.manage'), 403);

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'category' => (string) $request->query('category', ''),
            'active' => (string) $request->query('active', ''),
        ];

        $query = PricingPlan::query();

        if ($filters['search'] !== '') {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('slug', 'like', "%{$s}%")
                    ->orWhere('tier', 'like', "%{$s}%");
            });
        }

        if ($filters['category'] !== '') {
            $query->where('category', $filters['category']);
        }

        if ($filters['active'] !== '') {
            $query->where('is_active', $filters['active'] === '1');
        }

        $plans = $query->orderBy('category')->orderBy('sort_order')->orderBy('price')->paginate(25)->withQueryString();

        return view('pages.admin.pricing-plans.index', [
            'plans' => $plans,
            'filters' => $filters,
            'categories' => ['real_estate', 'virtual_assistance'],
            'meta' => [
                'title' => 'Pricing Plans | OmniReferral',
                'description' => 'Manage pricing cards displayed on the public pricing page.',
            ],
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('packages.manage'), 403);

        return view('pages.admin.pricing-plans.create', [
            'plan' => new PricingPlan([
                'category' => 'real_estate',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 10,
                'cta_label' => 'Get Started',
            ]),
            'meta' => [
                'title' => 'Create Pricing Plan | OmniReferral',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('packages.manage'), 403);

        $validated = $this->validatePayload($request, null);
        $plan = PricingPlan::create($validated);

        AdminAudit::log($request, 'pricing_plan.created', 'pricing_plan', $plan->id, [
            'slug' => $plan->slug,
        ]);

        return redirect()->route('admin.pricing-plans.index')->with('success', 'Pricing plan created.');
    }

    public function edit(Request $request, PricingPlan $pricingPlan): View
    {
        abort_unless($request->user()?->can('packages.manage'), 403);

        return view('pages.admin.pricing-plans.edit', [
            'plan' => $pricingPlan,
            'meta' => [
                'title' => 'Edit Pricing Plan | OmniReferral',
            ],
        ]);
    }

    public function update(Request $request, PricingPlan $pricingPlan): RedirectResponse
    {
        abort_unless($request->user()?->can('packages.manage'), 403);

        $validated = $this->validatePayload($request, $pricingPlan);
        $pricingPlan->update($validated);

        AdminAudit::log($request, 'pricing_plan.updated', 'pricing_plan', $pricingPlan->id, [
            'slug' => $pricingPlan->slug,
        ]);

        return redirect()->route('admin.pricing-plans.index')->with('success', 'Pricing plan updated.');
    }

    public function destroy(Request $request, PricingPlan $pricingPlan): RedirectResponse
    {
        abort_unless($request->user()?->can('packages.manage'), 403);

        $pricingPlan->delete();

        AdminAudit::log($request, 'pricing_plan.deleted', 'pricing_plan', $pricingPlan->id, [
            'slug' => $pricingPlan->slug,
        ]);

        return back()->with('success', 'Pricing plan deleted.');
    }

    private function validatePayload(Request $request, ?PricingPlan $plan): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pricing_plans', 'slug')->ignore($plan?->id),
            ],
            'category' => ['required', Rule::in(['real_estate', 'virtual_assistance'])],
            'tier' => ['nullable', 'string', 'max:255'],
            'value_price' => ['nullable', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'price_note' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'cta_url' => ['nullable', 'url', 'max:500'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
            'is_featured' => ['nullable', Rule::in(['0', '1'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:500'],
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_active'] = ($validated['is_active'] ?? '1') === '1';
        $validated['is_featured'] = ($validated['is_featured'] ?? '0') === '1';

        return $validated;
    }
}
