<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('packages.manage'), 403);

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'category' => (string) $request->query('category', ''),
            'active' => (string) $request->query('active', ''),
        ];

        $query = Package::query();

        if ($filters['search'] !== '') {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('slug', 'like', "%{$s}%");
            });
        }

        if ($filters['category'] !== '') {
            $query->where('category', $filters['category']);
        }

        if ($filters['active'] !== '') {
            $query->where('is_active', $filters['active'] === '1');
        }

        $packages = $query->orderBy('sort_order')->orderBy('name')->paginate(25)->withQueryString();

        return view('pages.admin.packages.index', [
            'packages' => $packages,
            'filters' => $filters,
            'categories' => ['lead', 'virtual_assistant'],
            'meta' => [
                'title' => 'Packages | OmniReferral',
                'description' => 'Manage pricing plans, Stripe identifiers, and GoHighLevel mappings.',
            ],
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('packages.manage'), 403);

        return view('pages.admin.packages.create', [
            'package' => new Package([
                'category' => 'lead',
                'billing_type' => 'one_time',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 10,
            ]),
            'meta' => [
                'title' => 'Create Package | OmniReferral',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('packages.manage'), 403);

        $validated = $this->validatePayload($request, null);
        $package = Package::create($validated);

        AdminAudit::log($request, 'package.created', 'package', $package->id, [
            'slug' => $package->slug,
        ]);

        return redirect()->route('admin.packages.index')->with('success', 'Package created.');
    }

    public function edit(Request $request, Package $package): View
    {
        abort_unless($request->user()?->can('packages.manage'), 403);

        return view('pages.admin.packages.edit', [
            'package' => $package,
            'meta' => [
                'title' => 'Edit Package | OmniReferral',
            ],
        ]);
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        abort_unless($request->user()?->can('packages.manage'), 403);

        $validated = $this->validatePayload($request, $package);
        $package->update($validated);

        AdminAudit::log($request, 'package.updated', 'package', $package->id, [
            'slug' => $package->slug,
        ]);

        return redirect()->route('admin.packages.index')->with('success', 'Package updated.');
    }

    public function destroy(Request $request, Package $package): RedirectResponse
    {
        abort_unless($request->user()?->can('packages.manage'), 403);

        $package->delete();

        AdminAudit::log($request, 'package.deleted', 'package', $package->id, [
            'slug' => $package->slug,
        ]);

        return back()->with('success', 'Package deleted.');
    }

    private function validatePayload(Request $request, ?Package $package): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('packages', 'slug')->ignore($package?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', Rule::in(['lead', 'virtual_assistant'])],
            'billing_type' => ['required', Rule::in(['one_time', 'monthly', 'hourly', 'hybrid'])],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
            'is_featured' => ['nullable', Rule::in(['0', '1'])],
            'one_time_price' => ['nullable', 'integer', 'min:0'],
            'monthly_price' => ['nullable', 'integer', 'min:0'],
            'hourly_price' => ['nullable', 'integer', 'min:0'],
            'stripe_price_id' => ['nullable', 'string', 'max:255'],
            'stripe_product_id' => ['nullable', 'string', 'max:255'],
            'ghl_form_url' => ['nullable', 'url', 'max:255'],
            'ghl_pipeline_stage' => ['nullable', 'string', 'max:255'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],

            // Lead routing (previously only settable via database/seeder — see
            // audit-confirmed gap).
            'monthly_lead_quota' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'lead_priority' => ['nullable', 'integer', 'min:0', 'max:100'],

            // Capability switches — single source of truth for feature gating
            // everywhere (App\Support\PlanCapabilities reads these columns live).
            'portal_access' => ['nullable', Rule::in(['0', '1'])],
            'property_listings' => ['nullable', Rule::in(['0', '1'])],
            'listing_limit' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'virtual_assistant' => ['nullable', Rule::in(['0', '1'])],
            'priority_routing' => ['nullable', Rule::in(['0', '1'])],
            'featured_placement' => ['nullable', Rule::in(['0', '1'])],
            'premium_seo' => ['nullable', Rule::in(['0', '1'])],
            'advanced_reporting' => ['nullable', Rule::in(['0', '1'])],
            'dashboard_access' => ['nullable', Rule::in(['0', '1'])],
            'advanced_qualification' => ['nullable', Rule::in(['0', '1'])],
            'dedicated_account_manager' => ['nullable', Rule::in(['0', '1'])],
            'verified_referral_access' => ['nullable', Rule::in(['0', '1'])],

            // Tiers & referral limits
            'referral_fee_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
            'city_limit' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'free_referrals' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'referral_capacity' => ['nullable', 'string', 'max:50'],
            'verification_steps' => ['nullable', 'integer', 'min:0', 'max:20'],
            'marketing_tier' => ['nullable', Rule::in(['none', 'basic', 'better', 'premium'])],
            'profile_tier' => ['nullable', Rule::in(['none', 'basic', 'premium'])],
            'support_tier' => ['nullable', Rule::in(['none', 'email', 'email_sms', 'priority'])],
            'analytics_level' => ['nullable', Rule::in(['none', 'basic', 'enhanced', 'advanced'])],

            // VA plan deliverables (only relevant for category = virtual_assistant)
            'services' => ['nullable', 'array'],
            'services.*' => ['string', 'max:255'],
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_active'] = ($validated['is_active'] ?? '1') === '1';
        $validated['is_featured'] = ($validated['is_featured'] ?? '0') === '1';

        foreach ([
            'portal_access', 'property_listings', 'virtual_assistant', 'priority_routing',
            'featured_placement', 'premium_seo', 'advanced_reporting', 'dashboard_access',
            'advanced_qualification', 'dedicated_account_manager', 'verified_referral_access',
        ] as $flag) {
            $validated[$flag] = ($validated[$flag] ?? '0') === '1';
        }

        $validated['services'] = array_values(array_filter($validated['services'] ?? [], fn ($s) => trim((string) $s) !== ''));

        return $validated;
    }
}
