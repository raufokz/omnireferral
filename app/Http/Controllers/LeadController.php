<?php

namespace App\Http\Controllers;

use App\Jobs\SyncLeadToGoHighLevel;
use App\Models\Lead;
use App\Services\LeadRoutingService;
use App\Models\Package;
use App\Models\User;
use App\Notifications\NewLeadCreatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function store(Request $request, LeadRoutingService $leadRouting): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:30'],
            'intent' => ['required', 'in:buyer,seller,investor,other'],
            'zip_code' => [
                Rule::requiredIf(fn () => $request->input('intent') === 'buyer'),
                'nullable',
                'string',
                'max:10',
                'regex:/^\d{5}(?:-\d{4})?$/',
            ],
            'property_address' => [
                Rule::requiredIf(fn () => $request->input('intent') === 'seller'),
                'nullable',
                'string',
                'max:255',
            ],
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
            'zip_code.required' => 'Add the ZIP code where you want to buy so we can route the request.',
            'zip_code.regex' => 'Enter a valid ZIP code using 5 digits or ZIP+4 format.',
            'property_address.required' => 'Add the full property address so we can review the seller opportunity properly.',
        ]);

        if (($validated['intent'] ?? null) === 'buyer') {
            $validated['zip_code'] = $this->normalizeZipCode($validated['zip_code'] ?? null);
            $validated['property_address'] = null;
        }

        if (($validated['intent'] ?? null) === 'seller') {
            $validated['property_address'] = trim((string) ($validated['property_address'] ?? ''));
            $validated['zip_code'] = $this->extractZipCode($validated['property_address']);
        }

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
                'property_address' => $validated['property_address'] ?? null,
            ],
            'lead_score' => $request->filled('timeline') && ($request->filled('budget') || $request->filled('asking_price')) ? 82 : 68,
            'is_priority' => in_array($request->input('timeline'), ['ASAP', '0-30 days'], true),
        ]);

        // Notify admin/staff operations throughput.
        $watchers = User::whereIn('role', ['admin', 'staff'])->get();
        Notification::send($watchers, new NewLeadCreatedNotification($lead));

        SyncLeadToGoHighLevel::dispatch($lead->id);

        $leadRouting->assignIfConfigured($lead->fresh());

        $lead = $lead->fresh();
        $message = $lead?->assigned_agent_id
            ? 'Welcome aboard! Your request is in our system and has been routed to a partner agent for follow-up.'
            : 'Welcome aboard! Your request has been captured in the OmniReferral review queue and is awaiting routing.';

        return back()->with('success', $message);
    }

    private function normalizeZipCode(?string $zipCode): ?string
    {
        $zipCode = trim((string) $zipCode);

        return $zipCode !== '' ? $zipCode : null;
    }

    private function extractZipCode(?string $address): ?string
    {
        $address = trim((string) $address);

        if ($address === '') {
            return null;
        }

        preg_match('/\b\d{5}(?:-\d{4})?\b/', $address, $matches);

        return $matches[0] ?? null;
    }
}
