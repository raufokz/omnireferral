<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Lead;
use App\Models\RealtorProfile;
use App\Support\AgentDirectory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RealtorController extends Controller
{
    public function index(Request $request): View
    {
        return $this->renderDirectory($request);
    }

    public function location(Request $request, string $location): View
    {
        $resolved = AgentDirectory::resolveLocationSlug($location);
        abort_unless($resolved, 404);

        if ($resolved['type'] === 'state') {
            $request->merge(['state' => $resolved['state']]);
        } else {
            $request->merge(['city' => $resolved['city']]);
        }

        return $this->renderDirectory($request, $resolved);
    }

    public function profile(RealtorProfile $agent): View
    {
        abort_unless($agent->isPublicVisible(), 404);
        $agent->load(['user:id,name,display_name,avatar']);

        return view('pages.agent-profile-seo', [
            'profile' => $agent,
            'user' => $agent->user,
            'card' => AgentDirectory::publicCardPayload($agent),
            'meta' => [
                'title' => ($agent->user?->publicDisplayName() ?: 'Agent').' | '.$agent->serviceAreaLabel().' | OmniReferral',
                'description' => Str::limit(strip_tags((string) $agent->bio), 155),
            ],
        ]);
    }

    public function preview(RealtorProfile $agent): JsonResponse
    {
        abort_unless($agent->isPublicVisible(), 404);
        $agent->load(['user:id,name,display_name,avatar']);

        return response()->json([
            'profile' => AgentDirectory::publicCardPayload($agent),
        ]);
    }

    public function inquiry(Request $request, RealtorProfile $agent): RedirectResponse|JsonResponse
    {
        abort_unless($agent->isPublicVisible(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:2000'],
            'property_requirements' => ['nullable', 'string', 'max:2000'],
            'inquiry_type' => ['required', 'in:contact,referral'],
        ]);

        $agentName = $agent->user?->publicDisplayName() ?: 'Selected Agent';
        $inquiryLabel = $validated['inquiry_type'] === 'referral' ? 'Referral request' : 'Contact request';

        $contact = Contact::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'sender_role' => 'buyer',
            'zip_code' => null,
            'subject' => $inquiryLabel.' for '.$agentName,
            'message' => $validated['message'],
            'source' => 'agent_directory_'.$validated['inquiry_type'],
            'recipient_user_id' => null,
            'realtor_profile_id' => $agent->id,
            'message_status' => 'new',
        ]);

        Lead::create([
            'lead_number' => 'AGT-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'intent' => 'buyer',
            'package_type' => 'directory',
            'status' => 'new',
            'source' => 'agent_directory',
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'zip_code' => $validated['city'] ?? $agent->service_zip_code,
            'preferences' => $validated['property_requirements'] ?? null,
            'notes' => $validated['message'],
            'form_data' => [
                'inquiry_type' => $validated['inquiry_type'],
                'buyer_city' => $validated['city'] ?? null,
                'property_requirements' => $validated['property_requirements'] ?? null,
                'target_agent_profile_id' => $agent->id,
                'target_agent_slug' => $agent->slug,
                'target_agent_name' => $agentName,
                'target_brokerage' => $agent->brokerage_name,
                'target_service_area' => $agent->serviceAreaLabel(),
                'contact_id' => $contact->id,
            ],
            'route_notes' => 'Captured from agent directory — routed to OmniReferral admin team.',
        ]);

        $message = 'Thanks! Our team received your request and will follow up shortly.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    private function renderDirectory(Request $request, ?array $location = null): View
    {
        $query = AgentDirectory::publicQuery()
            ->with(['user' => fn ($userQuery) => $userQuery->select([
                'id', 'name', 'display_name', 'avatar',
            ])])
            ->select([
                'id', 'user_id', 'slug', 'brokerage_name', 'service_city', 'service_state',
                'service_zip_code', 'rating', 'review_count', 'years_of_experience',
                'specialties', 'bio', 'headshot', 'profile_status', 'created_at',
            ]);

        AgentDirectory::applySearch($query, $request->query('q'));
        AgentDirectory::applyLocationFilter(
            $query,
            $request->query('state'),
            $request->query('city')
        );

        if ($specialty = trim((string) $request->query('specialty', ''))) {
            $query->whereRaw('LOWER(specialties) LIKE ?', ['%'.mb_strtolower($specialty).'%']);
        }

        $profiles = AgentDirectory::applyFeaturedSort($query)
            ->paginate(12)
            ->withQueryString();

        $filterCities = Cache::remember('agents:filter-cities', now()->addHour(), fn () => AgentDirectory::publicQuery()
            ->select('service_city')
            ->distinct()
            ->orderBy('service_city')
            ->pluck('service_city')
            ->filter()
            ->values());

        $filterStates = Cache::remember('agents:filter-states', now()->addHour(), fn () => AgentDirectory::publicQuery()
            ->select('service_state')
            ->distinct()
            ->orderBy('service_state')
            ->pluck('service_state')
            ->filter()
            ->values());

        $title = 'Agent Directory | OmniReferral';
        $description = 'Browse real estate agents across the United States. Find local expertise by city, state, and specialty.';

        if ($location) {
            $title = 'Real Estate Agents in '.$location['label'].' | OmniReferral';
            $description = 'Discover agents serving '.$location['label'].'. Featured agents appear first with priority placement.';
        }

        return view('pages.agents', [
            'profiles' => $profiles,
            'filterCities' => $filterCities,
            'filterStates' => $filterStates,
            'location' => $location,
            'meta' => [
                'title' => $title,
                'description' => $description,
            ],
        ]);
    }
}
