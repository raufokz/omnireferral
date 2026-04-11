<?php

namespace App\Http\Controllers;

use App\Jobs\SyncLeadToGoHighLevel;
use App\Models\Lead;
use App\Models\Package;
use App\Models\User;
use App\Notifications\NewLeadCreatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class LeadController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:30'],
            'intent' => ['required', 'in:buyer,seller,investor,other'],
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

        $normalizedEmail = Lead::normalizeEmail($validated['email'] ?? null);
        $normalizedPhone = Lead::normalizePhone($validated['phone'] ?? null);
        $existingLead = Lead::duplicateQuery($normalizedEmail, $normalizedPhone)->latest('id')->first();

        if ($existingLead) {
            return back()->with('info', 'This lead is already in the system. We kept the original record and avoided a duplicate entry.');
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

        // Notify admin/staff operations throughput.
        $watchers = User::whereIn('role', ['admin', 'staff'])->get();
        Notification::send($watchers, new NewLeadCreatedNotification($lead));

        SyncLeadToGoHighLevel::dispatch($lead->id);

        return back()->with('success', 'Welcome aboard! Your request has been captured in the OmniReferral review queue and is currently unassigned until operations routes it.');
    }
}
