<?php

namespace App\Http\Controllers;

use App\Jobs\SyncLeadToGoHighLevel;
use App\Models\Lead;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:30'],
            'intent' => ['required', 'in:buyer,seller'],
            'zip_code' => ['required', 'string', 'max:10'],
            'property_type' => ['nullable', 'string', 'max:100'],
            'budget' => ['nullable', 'integer'],
            'asking_price' => ['nullable', 'integer'],
            'timeline' => ['nullable', 'string', 'max:100'],
            'financing_status' => ['nullable', 'string', 'max:100'],
            'contact_preference' => ['nullable', 'string', 'max:50'],
            'package_slug' => ['nullable', 'string', 'exists:packages,slug'],
            'property_image' => ['nullable', 'image', 'max:4096'],
            'preferences' => ['nullable', 'string'],
        ], [
            'email.required' => 'Oops, looks like you missed your email!',
            'name.required' => 'We need your name so we know who to help.',
            'property_image.image' => 'Please upload a valid property photo.',
        ]);

        if ($request->hasFile('property_image')) {
            $validated['property_image'] = $request->file('property_image')->store('properties/leads', 'public');
        }

        $package = isset($validated['package_slug'])
            ? Package::where('slug', $validated['package_slug'])->first()
            : Package::leadPlans()->orderBy('sort_order')->first();

        $lead = Lead::create([
            ...$validated,
            'lead_number' => 'OMNI-' . now()->format('Ymd') . '-' . str_pad((string) (Lead::withTrashed()->count() + 1), 4, '0', STR_PAD_LEFT),
            'package_type' => $package ? str($package->slug)->before('-')->toString() : 'quick',
            'package_id' => $package?->id,
            'status' => 'new',
            'source' => 'website',
            'form_data' => [
                'submitted_from' => url()->previous(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'intent' => $request->input('intent'),
                'timeline' => $request->input('timeline'),
                'financing_status' => $request->input('financing_status'),
                'contact_preference' => $request->input('contact_preference'),
            ],
            'lead_score' => $request->filled('timeline') && $request->filled('budget') ? 82 : 68,
            'is_priority' => in_array($request->input('timeline'), ['ASAP', '0-30 days'], true),
        ]);

        SyncLeadToGoHighLevel::dispatch($lead->id);

        return back()->with('success', 'Welcome aboard! Your request has been captured and routed into the OmniReferral review queue.');
    }
}
