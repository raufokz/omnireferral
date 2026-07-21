<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentLeadQuota;
use App\Models\AgentSubscription;
use App\Models\Package;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Support\AdminAudit;
use App\Support\AgentAvatar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffAgentProfileController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', RealtorProfile::class);

        $status = $request->string('status', 'all')->value();
        $status = array_key_exists($status, ['all' => true] + RealtorProfile::statusOptions()) ? $status : 'all';
        $perPage = (int) $request->integer('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
        $planFilter = $request->string('plan', 'all')->value();
        $availablePlans = Package::leadPlans()->active()->orderBy('sort_order')->get();

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => $status,
            'state' => strtoupper(trim((string) $request->query('state', ''))),
            'market' => trim((string) $request->query('market', '')),
            'brokerage' => trim((string) $request->query('brokerage', '')),
            'featured' => trim((string) $request->query('featured', '')),
            'plan' => $planFilter === 'all' || $availablePlans->contains('slug', $planFilter) ? $planFilter : 'all',
            'per_page' => $perPage,
        ];

        $query = RealtorProfile::query()
            ->with([
                'user:id,name,display_name,email,phone,status,current_plan_id',
                'user.activeAgentSubscription.package:id,name,slug,category',
                'user.currentPlan:id,name,slug',
                'createdByUser:id,name',
            ])
            ->latest();

        if ($status !== 'all') {
            $query->where('profile_status', $status);
        }

        if ($filters['q'] !== '') {
            $search = $filters['q'];
            $query->where(function ($profileQuery) use ($search) {
                $like = '%'.$search.'%';
                $profileQuery
                    ->where('brokerage_name', 'like', $like)
                    ->orWhere('service_city', 'like', $like)
                    ->orWhere('service_state', 'like', $like)
                    ->orWhere('service_zip_code', 'like', $like)
                    ->orWhere('license_number', 'like', $like)
                    ->orWhereHas('user', function ($userQuery) use ($like) {
                        $userQuery
                            ->where('name', 'like', $like)
                            ->orWhere('display_name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('phone', 'like', $like);
                    });
            });
        }

        if ($filters['state'] !== '') {
            $query->where('service_state', $filters['state']);
        }

        if ($filters['market'] !== '') {
            $market = '%'.$filters['market'].'%';
            $query->where(function ($marketQuery) use ($market) {
                $marketQuery
                    ->where('service_city', 'like', $market)
                    ->orWhere('market_areas', 'like', $market);
            });
        }

        if ($filters['brokerage'] !== '') {
            $query->where('brokerage_name', 'like', '%'.$filters['brokerage'].'%');
        }

        if ($filters['featured'] === 'yes') {
            $query->where('profile_status', RealtorProfile::STATUS_FEATURED);
        } elseif ($filters['featured'] === 'no') {
            $query->where('profile_status', '!=', RealtorProfile::STATUS_FEATURED);
        }

        if ($filters['plan'] !== 'all') {
            $query->whereHas('user', function ($userQuery) use ($planFilter) {
                $userQuery->where('current_plan_id', function ($subQuery) use ($planFilter) {
                    $subQuery->select('id')->from('packages')->where('slug', $planFilter)->limit(1);
                });
            });
        }

        return view('pages.admin.agent-profiles.index', [
            'profiles' => $query->paginate($perPage)->withQueryString(),
            'status' => $status,
            'search' => $filters['q'],
            'filters' => $filters,
            'counts' => [
                'all' => RealtorProfile::count(),
                'draft' => RealtorProfile::draft()->count(),
                'approved' => RealtorProfile::query()->where('profile_status', RealtorProfile::STATUS_APPROVED)->count(),
                'published' => RealtorProfile::published()->count(),
                'featured' => RealtorProfile::featured()->count(),
                'suspended' => RealtorProfile::suspended()->count(),
            ],
            'filterStates' => RealtorProfile::query()
                ->whereNotNull('service_state')
                ->select('service_state')
                ->distinct()
                ->orderBy('service_state')
                ->pluck('service_state'),
            'filterBrokerages' => RealtorProfile::query()
                ->whereNotNull('brokerage_name')
                ->select('brokerage_name')
                ->distinct()
                ->orderBy('brokerage_name')
                ->limit(100)
                ->pluck('brokerage_name'),
            'filterMarkets' => RealtorProfile::query()
                ->whereNotNull('service_city')
                ->select('service_city')
                ->distinct()
                ->orderBy('service_city')
                ->limit(100)
                ->pluck('service_city'),
            'availablePlans' => $availablePlans,
            'planCounts' => collect($availablePlans->mapWithKeys(fn ($plan) => [
                $plan->slug => User::where('current_plan_id', $plan->id)->count(),
            ]))->merge([
                'none' => User::whereNull('current_plan_id')->count(),
            ]),
            'meta' => [
                'title' => 'Agent Profiles | Admin | OmniReferral',
                'description' => 'Staff workspace for creating and publishing agent directory profiles.',
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('viewAny', RealtorProfile::class);

        return view('pages.admin.agent-profiles.create', [
            'statusOptions' => RealtorProfile::statusOptions(),
            'availablePlans' => Package::leadPlans()->active()->orderBy('sort_order')->get(),
            'meta' => ['title' => 'Add Agent Profile | OmniReferral'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', RealtorProfile::class);

        $validated = $this->validatedProfilePayload($request);

        $profile = DB::transaction(function () use ($request, $validated) {
            $slug = RealtorProfile::generateUniqueSlug($validated['name']);
            $email = $validated['email'] ?? ('agent+'.$slug.'@directory.omnireferral.local');

            $headshotPath = AgentAvatar::defaultStorageHeadshot();
            if ($request->hasFile('headshot')) {
                $stored = $request->file('headshot')->store('avatars', 'public');
                $headshotPath = 'storage/'.$stored;
            } elseif (! empty($validated['headshot_url'])) {
                $headshotPath = $validated['headshot_url'];
            }

            $profileStatus = $validated['profile_status'];
            $isApproved = in_array($profileStatus, RealtorProfile::publicStatusValues(), true);
            $newPackageId = $validated['package_id'] ?? null;

            $user = User::create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'] ?? null,
                'email' => $email,
                'password' => Str::password(32),
                'phone' => $validated['phone'] ?? null,
                'city' => $validated['service_city'],
                'state' => strtoupper($validated['service_state']),
                'zip_code' => $validated['service_zip_code'] ?? null,
                'role' => 'agent',
                'status' => $profileStatus === RealtorProfile::STATUS_SUSPENDED
                    ? 'suspended'
                    : ($isApproved ? 'active' : 'pending'),
                'must_reset_password' => true,
                'email_verified_at' => $isApproved ? now() : null,
                'current_plan_id' => $newPackageId ?: null,
            ]);

            if ($newPackageId) {
                $newPackage = Package::findOrFail($newPackageId);
                AgentSubscription::create([
                    'user_id'           => $user->id,
                    'package_id'        => $newPackage->id,
                    'payment_status'    => 'paid',
                    'payment_provider'  => 'admin',
                    'payment_reference' => 'ADMIN-PLAN-CREATE-' . $user->id . '-' . $newPackage->id . '-' . now()->timestamp,
                    'payment_amount'    => $newPackage->preferredCheckoutAmount(),
                    'starts_at'         => now(),
                    'ends_at'           => $newPackage->billing_type === 'yearly' ? now()->addYear() : null,
                    'is_active'         => true,
                ]);

                $month = now()->format('Y-m');
                AgentLeadQuota::updateOrCreate(
                    ['user_id' => $user->id, 'month' => $month],
                    [
                        'package_id'      => $newPackage->id,
                        'monthly_quota'   => $newPackage->monthly_lead_quota ?? 0,
                        'remaining_count' => $newPackage->monthly_lead_quota ?? 0,
                        'overdue_count'   => 0,
                    ]
                );
            }

            return RealtorProfile::updateOrCreate(['user_id' => $user->id], [
                'user_id' => $user->id,
                'created_by_user_id' => $request->user()?->id,
                'slug' => $slug,
                'service_city' => $validated['service_city'],
                'service_state' => strtoupper($validated['service_state']),
                'service_zip_code' => $validated['service_zip_code'] ?? null,
                'brokerage_name' => $validated['brokerage_name'],
                'license_number' => $validated['license_number'] ?? null,
                'is_active_agent' => (bool) ($validated['is_active_agent'] ?? true),
                'years_of_experience' => $validated['years_of_experience'] ?? null,
                'languages' => $validated['languages'] ?? null,
                'market_areas' => $validated['market_areas'] ?? null,
                'specialties' => RealtorProfile::normalizeSpecialties($validated['specialties'] ?? $validated['specialties_text'] ?? ''),
                'bio' => $validated['bio'],
                'headshot' => $headshotPath,
                'rating' => $validated['rating'] ?? 4.5,
                'review_count' => $validated['review_count'] ?? 0,
                'leads_closed' => $validated['leads_closed'] ?? 0,
                'social_links' => $this->socialLinksFromPayload($validated),
                'profile_status' => $profileStatus,
                'approved_at' => $isApproved ? now() : null,
                'approved_by_user_id' => $isApproved ? $request->user()?->id : null,
                'rejected_at' => $profileStatus === RealtorProfile::STATUS_SUSPENDED ? now() : null,
                'rejected_by_user_id' => $profileStatus === RealtorProfile::STATUS_SUSPENDED ? $request->user()?->id : null,
                'source_url' => $validated['source_url'] ?? null,
                'submission_source' => $validated['submission_source'] ?? null,
            ]);
        });

        AdminAudit::log($request, 'realtor_profile.created', 'realtor_profile', $profile->id, [
            'slug' => $profile->slug,
            'profile_status' => $profile->profile_status,
        ]);

        return redirect()
            ->route('admin.agent-profiles.show', $profile)
            ->with('success', 'Agent profile created.');
    }

    public function show(RealtorProfile $agentProfile): View
    {
        $this->authorize('view', $agentProfile);
        $agentProfile->load([
            'user',
            'user.activeAgentSubscription.package',
            'user.currentPlan',
            'createdByUser',
        ]);

        return view('pages.admin.agent-profiles.show', [
            'profile' => $agentProfile,
            'user' => $agentProfile->user,
            'statusOptions' => RealtorProfile::statusOptions(),
            'canEdit' => auth()->user()?->can('update', $agentProfile) ?? false,
            'availablePlans' => Package::leadPlans()->active()->orderBy('sort_order')->get(),
            'meta' => [
                'title' => ($agentProfile->user?->publicDisplayName() ?: 'Agent').' | Profile',
            ],
        ]);
    }

    public function update(Request $request, RealtorProfile $agentProfile): RedirectResponse
    {
        $this->authorize('update', $agentProfile);
        $user = $agentProfile->user;
        abort_unless($user, 404);

        $validated = $this->validatedProfilePayload($request, $user->id);

        if ($request->hasFile('headshot')) {
            $stored = $request->file('headshot')->store('avatars', 'public');
            $agentProfile->headshot = 'storage/'.$stored;
        } elseif (! empty($validated['headshot_url'])) {
            $agentProfile->headshot = $validated['headshot_url'];
        }

        $user->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'] ?? null,
            'email' => $validated['email'] ?? $user->email,
            'phone' => $validated['phone'] ?? null,
            'city' => $validated['service_city'],
            'state' => strtoupper($validated['service_state']),
            'zip_code' => $validated['service_zip_code'] ?? null,
            'status' => $this->userStatusForProfileStatus($validated['profile_status']),
            'email_verified_at' => in_array($validated['profile_status'], RealtorProfile::publicStatusValues(), true)
                ? ($user->email_verified_at ?: now())
                : $user->email_verified_at,
        ]);

        $newPackageId = $validated['package_id'] ?? null;
        $oldSubscription = $user->activeAgentSubscription;
        $currentPlanId = $user->current_plan_id;

        if ($newPackageId != $currentPlanId) {
            DB::transaction(function () use ($user, $newPackageId, $oldSubscription) {
                if ($oldSubscription) {
                    $oldSubscription->update(['is_active' => false, 'payment_status' => 'cancelled']);
                }

                if ($newPackageId) {
                    $newPackage = Package::findOrFail($newPackageId);
                    AgentSubscription::create([
                        'user_id'           => $user->id,
                        'package_id'        => $newPackage->id,
                        'payment_status'    => 'paid',
                        'payment_provider'  => 'admin',
                        'payment_reference' => 'ADMIN-PLAN-CHANGE-' . $user->id . '-' . $newPackage->id . '-' . now()->timestamp,
                        'payment_amount'    => $newPackage->preferredCheckoutAmount(),
                        'starts_at'         => now(),
                        'ends_at'           => $newPackage->billing_type === 'yearly' ? now()->addYear() : null,
                        'is_active'         => true,
                    ]);

                    $user->update(['current_plan_id' => $newPackage->id]);

                    $month = now()->format('Y-m');
                    AgentLeadQuota::updateOrCreate(
                        ['user_id' => $user->id, 'month' => $month],
                        [
                            'package_id'      => $newPackage->id,
                            'monthly_quota'   => $newPackage->monthly_lead_quota ?? 0,
                            'remaining_count' => $newPackage->monthly_lead_quota ?? 0,
                            'overdue_count'   => 0,
                        ]
                    );
                } else {
                    $user->update(['current_plan_id' => null]);
                }
            });
        }

        $approvalFields = $this->approvalFieldsForStatus($request, $agentProfile, $validated['profile_status']);

        $agentProfile->update([
            'brokerage_name' => $validated['brokerage_name'],
            'license_number' => $validated['license_number'] ?? null,
            'is_active_agent' => (bool) ($validated['is_active_agent'] ?? true),
            'service_city' => $validated['service_city'],
            'service_state' => strtoupper($validated['service_state']),
            'service_zip_code' => $validated['service_zip_code'] ?? null,
            'years_of_experience' => $validated['years_of_experience'] ?? null,
            'languages' => $validated['languages'] ?? null,
            'market_areas' => $validated['market_areas'] ?? null,
            'specialties' => RealtorProfile::normalizeSpecialties($validated['specialties'] ?? $validated['specialties_text'] ?? ''),
            'bio' => $validated['bio'],
            'rating' => $validated['rating'] ?? $agentProfile->rating,
            'review_count' => $validated['review_count'] ?? $agentProfile->review_count,
            'leads_closed' => $validated['leads_closed'] ?? $agentProfile->leads_closed,
            'social_links' => $this->socialLinksFromPayload($validated),
            'profile_status' => $validated['profile_status'],
            'source_url' => $validated['source_url'] ?? null,
            'submission_source' => $validated['submission_source'] ?? $agentProfile->submission_source,
        ] + $approvalFields);

        AdminAudit::log($request, 'realtor_profile.updated', 'realtor_profile', $agentProfile->id);

        return back()->with('success', 'Agent profile updated.');
    }

    public function feature(Request $request, RealtorProfile $agentProfile)
    {
        $this->authorize('update', $agentProfile);

        $agentProfile->update($this->approvalFieldsForStatus($request, $agentProfile, RealtorProfile::STATUS_FEATURED) + [
            'profile_status' => RealtorProfile::STATUS_FEATURED,
        ]);
        $agentProfile->user?->update(['status' => 'active']);

        AdminAudit::log($request, 'realtor_profile.featured', 'realtor_profile', $agentProfile->id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile marked as Featured.',
                'profile_status' => RealtorProfile::STATUS_FEATURED,
                'status_label' => $agentProfile->statusLabel(),
                'counts' => [
                    'all' => RealtorProfile::count(),
                    'draft' => RealtorProfile::draft()->count(),
                    'approved' => RealtorProfile::query()->where('profile_status', RealtorProfile::STATUS_APPROVED)->count(),
                    'published' => RealtorProfile::published()->count(),
                    'featured' => RealtorProfile::featured()->count(),
                    'suspended' => RealtorProfile::suspended()->count(),
                ]
            ]);
        }

        return back()->with('success', 'Profile marked as Featured.');
    }

    public function publish(Request $request, RealtorProfile $agentProfile)
    {
        $this->authorize('update', $agentProfile);

        $agentProfile->update($this->approvalFieldsForStatus($request, $agentProfile, RealtorProfile::STATUS_APPROVED) + [
            'profile_status' => RealtorProfile::STATUS_APPROVED,
        ]);
        $agentProfile->user?->update(['status' => 'active']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile approved and published.',
                'profile_status' => RealtorProfile::STATUS_APPROVED,
                'status_label' => $agentProfile->statusLabel(),
                'counts' => [
                    'all' => RealtorProfile::count(),
                    'draft' => RealtorProfile::draft()->count(),
                    'approved' => RealtorProfile::query()->where('profile_status', RealtorProfile::STATUS_APPROVED)->count(),
                    'published' => RealtorProfile::published()->count(),
                    'featured' => RealtorProfile::featured()->count(),
                    'suspended' => RealtorProfile::suspended()->count(),
                ]
            ]);
        }

        return back()->with('success', 'Profile published.');
    }

    public function suspend(Request $request, RealtorProfile $agentProfile)
    {
        $this->authorize('update', $agentProfile);

        $agentProfile->update($this->approvalFieldsForStatus($request, $agentProfile, RealtorProfile::STATUS_SUSPENDED) + [
            'profile_status' => RealtorProfile::STATUS_SUSPENDED,
        ]);
        $agentProfile->user?->update(['status' => 'suspended']);

        AdminAudit::log($request, 'realtor_profile.suspended', 'realtor_profile', $agentProfile->id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile suspended and removed from public directory.',
                'profile_status' => RealtorProfile::STATUS_SUSPENDED,
                'status_label' => $agentProfile->statusLabel(),
                'counts' => [
                    'all' => RealtorProfile::count(),
                    'draft' => RealtorProfile::draft()->count(),
                    'approved' => RealtorProfile::query()->where('profile_status', RealtorProfile::STATUS_APPROVED)->count(),
                    'published' => RealtorProfile::published()->count(),
                    'featured' => RealtorProfile::featured()->count(),
                    'suspended' => RealtorProfile::suspended()->count(),
                ]
            ]);
        }

        return back()->with('success', 'Profile suspended and removed from public directory.');
    }

    public function changePlan(Request $request, RealtorProfile $agentProfile): RedirectResponse
    {
        $this->authorize('update', $agentProfile);

        $user = $agentProfile->user;
        abort_unless($user, 404);

        $validated = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
        ]);

        $newPackage = Package::findOrFail($validated['package_id']);
        $oldSubscription = $user->activeAgentSubscription;

        DB::transaction(function () use ($user, $newPackage, $oldSubscription) {
            if ($oldSubscription) {
                $oldSubscription->update(['is_active' => false, 'payment_status' => 'cancelled']);
            }

            $subscription = AgentSubscription::create([
                'user_id'           => $user->id,
                'package_id'        => $newPackage->id,
                'payment_status'    => 'paid',
                'payment_provider'  => 'admin',
                'payment_reference' => 'ADMIN-PLAN-CHANGE-' . $user->id . '-' . $newPackage->id . '-' . now()->timestamp,
                'payment_amount'    => $newPackage->preferredCheckoutAmount(),
                'starts_at'         => now(),
                'ends_at'           => $newPackage->billing_type === 'yearly' ? now()->addYear() : null,
                'is_active'         => true,
            ]);

            $user->update(['current_plan_id' => $newPackage->id]);

            $month = now()->format('Y-m');
            AgentLeadQuota::updateOrCreate(
                ['user_id' => $user->id, 'month' => $month],
                [
                    'package_id'      => $newPackage->id,
                    'monthly_quota'   => $newPackage->monthly_lead_quota ?? 0,
                    'remaining_count' => $newPackage->monthly_lead_quota ?? 0,
                    'overdue_count'   => 0,
                ]
            );
        });

        AdminAudit::log($request, 'realtor_profile.plan_changed', 'realtor_profile', $agentProfile->id, [
            'new_package' => $newPackage->slug,
        ]);

        return back()->with('success', 'Plan changed to ' . $newPackage->displayName() . '.');
    }

    private function validatedProfilePayload(Request $request, ?int $userId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'.($userId ? ','.$userId : '')],
            'phone' => ['nullable', 'string', 'max:20'],
            'brokerage_name' => ['required', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'is_active_agent' => ['nullable', 'boolean'],
            'service_city' => ['required', 'string', 'max:100'],
            'service_state' => ['required', 'string', 'size:2'],
            'service_zip_code' => ['nullable', 'string', 'max:10'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'languages' => ['nullable', 'string', 'max:255'],
            'market_areas' => ['nullable', 'string', 'max:1000'],
            'social_facebook_url' => ['nullable', 'url', 'max:500'],
            'social_linkedin_url' => ['nullable', 'url', 'max:500'],
            'social_instagram_url' => ['nullable', 'url', 'max:500'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:100'],
            'specialties_text' => ['nullable', 'string', 'max:500'],
            'bio' => ['required', 'string', 'min:40', 'max:2000'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'review_count' => ['nullable', 'integer', 'min:0'],
            'leads_closed' => ['nullable', 'integer', 'min:0'],
            'profile_status' => ['required', Rule::in(array_keys(RealtorProfile::statusOptions()))],
            'source_url' => ['nullable', 'url', 'max:500'],
            'submission_source' => ['nullable', 'string', 'max:80'],
            'headshot' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'headshot_url' => ['nullable', 'string', 'max:500'],
            'package_id' => ['nullable', 'exists:packages,id'],
        ]);
    }

    private function userStatusForProfileStatus(string $profileStatus): string
    {
        return match ($profileStatus) {
            RealtorProfile::STATUS_SUSPENDED => 'suspended',
            RealtorProfile::STATUS_DRAFT => 'pending',
            default => 'active',
        };
    }

    private function approvalFieldsForStatus(Request $request, RealtorProfile $profile, string $profileStatus): array
    {
        if ($profileStatus === RealtorProfile::STATUS_SUSPENDED) {
            return [
                'rejected_at' => $profile->rejected_at ?: now(),
                'rejected_by_user_id' => $profile->rejected_by_user_id ?: $request->user()?->id,
            ];
        }

        if (in_array($profileStatus, RealtorProfile::publicStatusValues(), true)) {
            return [
                'approved_at' => $profile->approved_at ?: now(),
                'approved_by_user_id' => $profile->approved_by_user_id ?: $request->user()?->id,
                'rejected_at' => null,
                'rejected_by_user_id' => null,
            ];
        }

        return [
            'approved_at' => null,
            'approved_by_user_id' => null,
            'rejected_at' => null,
            'rejected_by_user_id' => null,
        ];
    }

    private function socialLinksFromPayload(array $validated): array
    {
        return array_filter([
            'facebook' => $validated['social_facebook_url'] ?? null,
            'linkedin' => $validated['social_linkedin_url'] ?? null,
            'instagram' => $validated['social_instagram_url'] ?? null,
            'website' => $validated['website_url'] ?? null,
        ]);
    }
}
