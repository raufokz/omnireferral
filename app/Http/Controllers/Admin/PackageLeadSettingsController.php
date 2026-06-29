<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageLeadSettingsController extends Controller
{
    public function index(): View
    {
        $packages = Package::where('is_active', true)
            ->orderBy('lead_priority', 'desc')
            ->orderBy('name')
            ->get();

        return view('pages.admin.package-lead-settings.index', [
            'packages' => $packages,
            'meta' => [
                'title' => 'Package Lead Settings | OmniReferral',
                'description' => 'Configure monthly lead quotas and priority for each package.',
            ],
        ]);
    }

    public function edit(Package $package): View
    {
        return view('pages.admin.package-lead-settings.edit', [
            'package' => $package,
            'meta' => [
                'title' => "Edit {$package->name} Lead Settings | OmniReferral",
                'description' => 'Adjust lead quota and priority for this package.',
            ],
        ]);
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $validated = $request->validate([
            'monthly_lead_quota' => ['required', 'integer', 'min:0', 'max:9999'],
            'lead_priority' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $package->update($validated);

        return redirect()->route('admin.package-lead-settings.index')
            ->with('success', "Lead settings updated for {$package->name}.");
    }
}
