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
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_active'] = ($validated['is_active'] ?? '1') === '1';
        $validated['is_featured'] = ($validated['is_featured'] ?? '0') === '1';

        return $validated;
    }
}
