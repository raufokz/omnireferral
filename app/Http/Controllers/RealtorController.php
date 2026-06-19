<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Lead;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Support\AgentDirectory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    public function submitAgentProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:agent'],
            'agent_directory_submission' => ['required', 'accepted'],
            'website' => ['nullable', 'max:0'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'profile_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'brokerage_name' => ['required', 'string', 'max:255'],
            'is_active_agent' => ['required', 'boolean'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],
            'terms_accepted' => ['required', 'accepted'],
            'communication_accepted' => ['nullable', 'accepted'],
        ], [
            'profile_image.required' => 'Please upload a profile image for your agent listing.',
            'profile_image.image' => 'The profile image must be a valid image file (JPG, PNG, or WEBP).',
        ]);

        $email = trim((string) ($validated['email'] ?? ''));
        $phone = trim((string) $validated['phone']);
        $name = trim((string) $validated['name']);
        $brokerage = trim((string) $validated['brokerage_name']);

        DB::transaction(function () use ($request, $validated, $email, $phone, $name, $brokerage) {
            $existingProfile = RealtorProfile::query()
                ->with('user')
                ->where(function ($query) use ($email, $phone, $name, $brokerage) {
                    if ($email !== '') {
                        $query->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', $email));
                    }

                    $query->orWhereHas('user', fn ($userQuery) => $userQuery->where('phone', $phone));
                    $query->orWhere(function ($profileQuery) use ($name, $brokerage) {
                        $profileQuery
                            ->whereRaw('LOWER(brokerage_name) = ?', [mb_strtolower($brokerage)])
                            ->whereHas('user', fn ($userQuery) => $userQuery->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]));
                    });
                })
                ->first();

            if ($existingProfile?->profile_status === RealtorProfile::STATUS_SUSPENDED) {
                throw ValidationException::withMessages([
                    'phone' => 'This agent profile already exists. Contact OmniReferral to update a suspended profile.',
                ]);
            }

            $stored = $request->hasFile('profile_image')
                ? $request->file('profile_image')->store('avatars', 'public')
                : null;

            $user = $existingProfile?->user;

            if ($user) {
                // Public listing update only — never mutate login/account state (password, status,
                // email) of an existing account from an unauthenticated public form.
                $user->display_name = $name;
                $user->phone = $phone;
                $user->city = $validated['city'];
                $user->state = strtoupper($validated['state']);
                if ($stored) {
                    $user->avatar = $stored;
                }
                $user->save();
            } else {
                // New account stays PENDING: the public directory listing is separate from portal
                // access. Login is unlocked only after plan purchase + GoHighLevel onboarding.
                $user = User::create([
                    'name' => $name,
                    'display_name' => $name,
                    'email' => $email !== '' ? $email : 'agent+'.Str::lower(Str::random(12)).'@public-agents.omnireferral.local',
                    'password' => Str::password(32),
                    'phone' => $phone,
                    'city' => $validated['city'],
                    'state' => strtoupper($validated['state']),
                    'role' => 'agent',
                    'status' => 'pending',
                    'must_reset_password' => true,
                    'email_verified_at' => null,
                    'notify_email' => true,
                    'notify_marketing' => true,
                    'avatar' => $stored,
                ]);
            }

            RealtorProfile::updateOrCreate(['user_id' => $user->id], [
                'user_id' => $user->id,
                'slug' => $existingProfile?->slug ?: RealtorProfile::generateUniqueSlug($name),
                'service_city' => $validated['city'],
                'service_state' => strtoupper($validated['state']),
                'service_zip_code' => null,
                'brokerage_name' => $brokerage,
                'license_number' => $existingProfile?->license_number,
                'specialties' => 'Buyer Representation, Seller Strategy, Lead Conversion',
                'bio' => 'Preferred agent profile submitted from the public OmniReferral directory.',
                'headshot' => $stored ? 'storage/'.$stored : ($existingProfile?->headshot ?? null),
                'profile_status' => RealtorProfile::STATUS_PUBLISHED,
                'is_active_agent' => (bool) $validated['is_active_agent'],
                'approved_at' => now(),
                'approved_by_user_id' => null,
                'rejected_at' => null,
                'rejected_by_user_id' => null,
                'source_url' => null,
                'submission_source' => 'public_agents_page',
                'approval_notes' => 'Auto-approved from public Preferred Agents submission.',
            ]);
        });

        return redirect()
            ->route('agents.index')
            ->with('success', 'Your agent profile has been added successfully and is now visible on the Preferred Agents page.');
    }

    private function renderDirectory(Request $request, ?array $location = null): View
    {
        $query = AgentDirectory::publicQuery()
            ->with(['user' => fn ($userQuery) => $userQuery->select([
                'id', 'name', 'display_name', 'avatar', 'role', 'status',
            ])])
            ->select([
                'id', 'user_id', 'slug', 'brokerage_name', 'service_city', 'service_state',
                'service_zip_code', 'rating', 'review_count',
                'leads_closed', 'specialties', 'bio', 'headshot', 'profile_status', 'is_active_agent',
                'years_of_experience', 'license_number', 'languages', 'market_areas', 'social_links',
                'created_at', 'approved_at', 'rejected_at',
                'rejected_by_user_id',

            ]);


        AgentDirectory::applySearch($query, $request->query('q'));
        AgentDirectory::applyLocationFilter(
            $query,
            $request->query('state'),
            $request->query('city')
        );
        AgentDirectory::applyAttributeFilters(
            $query,
            $request->query('name'),
            $request->query('brokerage'),
            $request->query('zip'),
            $request->query('specialty'),
            $request->query('rating'),
            $request->query('featured')
        );

        $profiles = $query
            ->whereNotNull('bio')
            ->whereRaw('LENGTH(TRIM(bio)) > 0')
            ->whereNotNull('service_city')
            ->whereRaw('LENGTH(TRIM(service_city)) > 0')
            ->whereNotNull('service_state')
            ->whereRaw('LENGTH(TRIM(service_state)) > 0')
            ->tap(fn ($q) => AgentDirectory::applyFeaturedSort($q))
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

        $directoryStats = Cache::remember('agents:directory-stats:v2', now()->addHour(), function () {
            $base = AgentDirectory::publicQuery();

            return [
                'total_agents' => (clone $base)->count(),
                'cities_covered' => (clone $base)
                    ->whereNotNull('service_city')
                    ->whereRaw('LENGTH(TRIM(service_city)) > 0')
                    ->distinct()
                    ->count('service_city'),
                'referral_matches' => (int) (clone $base)->sum('leads_closed'),
                'featured_agents' => (clone $base)->featured()->count(),
            ];
        });

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
            'directoryStats' => $directoryStats,
            'location' => $location,
            'activeFilters' => [
                'q' => (string) $request->query('q', ''),
                'name' => (string) $request->query('name', ''),
                'brokerage' => (string) $request->query('brokerage', ''),
                'city' => (string) $request->query('city', ''),
                'state' => (string) $request->query('state', ''),
                'zip' => (string) $request->query('zip', ''),
                'specialty' => (string) $request->query('specialty', ''),
                'rating' => (string) $request->query('rating', ''),
                'featured' => (string) $request->query('featured', ''),
            ],
            'meta' => [
                'title' => $title,
                'description' => $description,
            ],
        ]);
    }
}
