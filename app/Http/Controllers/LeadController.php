<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Jobs\SyncLeadToGoHighLevel;
use App\Models\Lead;
use App\Services\LeadRoutingService;
use App\Models\Package;
use App\Models\User;
use App\Notifications\NewLeadCreatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request, LeadRoutingService $leadRouting): RedirectResponse
    {
        $validated = $request->validated();

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
            'lead_number' => Lead::generateLeadNumber(),
            'package_type' => $package ? $this->normalizePackageType($package->slug) : 'starter',
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
            'lead_score' => $this->scoreLeadFromRequest($request),
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

    private function normalizePackageType(string $slug): string
    {
        return match ($slug) {
            'starter-leads', 'quick-leads' => 'starter',
            'growth-leads', 'power-leads' => 'growth',
            'elite-leads', 'prime-leads' => 'elite',
            default => str($slug)->before('-')->toString(),
        };
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

    /**
     * Heuristic priority score derived from populated form fields (0–100).
     */
    private function scoreLeadFromRequest(Request $request): int
    {
        $score = 45;

        if ($request->filled('timeline')) {
            $score += 12;
        }

        if ($request->filled('budget') || $request->filled('asking_price')) {
            $score += 15;
        }

        if ($request->filled('financing_status')) {
            $score += 8;
        }

        if ($request->filled('contact_preference')) {
            $score += 6;
        }

        if ($request->filled('property_type')) {
            $score += 6;
        }

        if ($request->filled('zip_code')) {
            $score += 4;
        }

        $preferencesLength = strlen(trim((string) $request->input('preferences', '')));
        if ($preferencesLength > 40) {
            $score += 8;
        } elseif ($preferencesLength > 12) {
            $score += 4;
        }

        if ($request->hasFile('property_image')) {
            $score += 6;
        }

        return (int) min(100, $score);
    }
}
