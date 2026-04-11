<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Package;
use App\Models\Property;
use App\Models\RealtorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->role === 'buyer') {
            return redirect()->route('dashboard.buyer');
        }

        if ($user->role === 'seller') {
            return redirect()->route('dashboard.seller');
        }

        if ($user->role === 'agent') {
            return redirect()->route('dashboard.agent');
        }

        if (in_array($user->role, ['admin', 'staff'], true)) {
            return redirect()->route('admin.dashboard');
        }

        $allRoleCards = [
            'buyer' => [
                'title' => 'Buyer Workspace',
                'copy' => 'Track saved homes, live request updates, and market activity without losing momentum.',
                'route' => route('dashboard.buyer'),
            ],
            'seller' => [
                'title' => 'Seller Workspace',
                'copy' => 'Manage listing visibility, inbound interest, and pricing conversations from one cleaner workspace.',
                'route' => route('dashboard.seller'),
            ],
            'agent' => [
                'title' => 'Agent Workspace',
                'copy' => 'Stay focused on verified leads, package visibility, VA support, and the next best action to close.',
                'route' => route('dashboard.agent'),
            ],
            'admin' => [
                'title' => 'Control Center',
                'copy' => 'Oversee the entire lead funnel across ISA, sales, realtor delivery, pricing, and growth operations.',
                'route' => route('admin.dashboard'),
            ],
            'staff' => [
                'title' => 'Operations Workspace',
                'copy' => 'Coordinate qualification queues, packaging workflows, and the handoff from outreach to revenue.',
                'route' => route('admin.dashboard'),
            ],
        ];

        $roleCards = collect([
            $allRoleCards[$user->role] ?? null,
            $user->isStaff() ? $allRoleCards['admin'] : null,
        ])->filter()->unique('route')->values()->all();

        $quickActions = match ($user->role) {
            'buyer' => [
                ['label' => 'Browse Listings', 'route' => route('listings')],
                ['label' => 'Update Preferences', 'route' => route('contact')],
            ],
            'seller' => [
                ['label' => 'Add Listing', 'route' => route('dashboard.seller')],
                ['label' => 'Talk to Support', 'route' => route('contact')],
            ],
            'agent' => [
                ['label' => 'Review Packages', 'route' => route('pricing')],
                ['label' => 'Open Surveys', 'route' => route('surveys')],
            ],
            default => [
                ['label' => 'Admin Overview', 'route' => route('admin.dashboard')],
                ['label' => 'Edit Blog Content', 'route' => route('admin.blog.index')],
            ],
        };

        return view('pages.dashboard', [
            'leadCount' => Lead::count(),
            'packageCount' => Package::count(),
            'propertyCount' => Property::count(),
            'agentCount' => RealtorProfile::count(),
            'roleCards' => $roleCards,
            'quickActions' => $quickActions,
            'currentRoleLabel' => $user?->roleLabel() ?? 'Platform User',
            'meta' => [
                'title' => 'Dashboard Overview | OmniReferral',
                'description' => 'See OmniReferral dashboard summaries for buyers, sellers, agents, and operations teams.',
            ],
        ]);
    }

    public function buyer(): View
    {
        $buyer = Auth::user();
        $favoritePropertiesQuery = $buyer
            ? $buyer->favoriteProperties()
                ->with('realtorProfile.user')
                ->withFavoriteSummary($buyer)
                ->marketplaceVisible()
            : Property::query()->whereRaw('1 = 0');
        $properties = $favoritePropertiesQuery
            ->orderByPivot('created_at', 'desc')
            ->take(6)
            ->get();
        $buyerRequests = Lead::where('intent', 'buyer')->latest()->take(5)->get();
        $buyerJourney = [
            ['label' => 'Submitted', 'count' => Lead::where('intent', 'buyer')->whereIn('status', ['new', 'contacted'])->count()],
            ['label' => 'Qualified', 'count' => Lead::where('intent', 'buyer')->where('status', 'qualified')->count()],
            ['label' => 'Agent Match', 'count' => Lead::where('intent', 'buyer')->where('status', 'assigned')->count()],
            ['label' => 'Closed', 'count' => Lead::where('intent', 'buyer')->where('status', 'closed')->count()],
        ];
        $favoriteCount = $buyer ? $buyer->propertyFavorites()->count() : 0;

        return view('pages.dashboards.buyer', [
            'properties' => $properties,
            'buyerRequests' => $buyerRequests,
            'buyerJourney' => $buyerJourney,
            'buyerStats' => [
                'saved_listings' => $favoriteCount,
                'favorites' => $favoriteCount,
                'saved_searches' => 3,
                'new_alerts' => Lead::where('intent', 'buyer')->count(),
            ],
            'meta' => [
                'title' => 'Buyer Dashboard | OmniReferral',
                'description' => 'Track saved listings, favorite properties, map-based search, and buyer notifications.',
            ],
        ]);
    }

    public function seller(): View
    {
        $properties = Property::marketplaceVisible()->latest()->take(4)->get();
        $sellerRequests = Lead::where('intent', 'seller')->latest()->take(5)->get();
        $sellerJourney = [
            ['label' => 'Submitted', 'count' => Lead::where('intent', 'seller')->whereIn('status', ['new', 'contacted'])->count()],
            ['label' => 'Qualified', 'count' => Lead::where('intent', 'seller')->where('status', 'qualified')->count()],
            ['label' => 'In Market', 'count' => Lead::where('intent', 'seller')->where('status', 'assigned')->count()],
            ['label' => 'Closed', 'count' => Lead::where('intent', 'seller')->where('status', 'closed')->count()],
        ];

        return view('pages.dashboards.seller', [
            'properties' => $properties,
            'sellerRequests' => $sellerRequests,
            'sellerJourney' => $sellerJourney,
            'sellerStats' => [
                'active_listings' => $properties->count(),
                'open_inquiries' => Lead::where('intent', 'seller')->count(),
                'price_updates' => 2,
                'buyer_matches' => Lead::where('intent', 'buyer')->count(),
            ],
            'meta' => [
                'title' => 'Seller Dashboard | OmniReferral',
                'description' => 'Manage your property listings, prices, images, and inquiries in one seller dashboard.',
            ],
        ]);
    }

    public function agent(): View
    {
        $user = Auth::user();
        $agentProfile = $user?->realtorProfile;
        $agentUserId = $agentProfile?->user_id;
        $agentLeadsQuery = Lead::query()->when(
            $agentUserId,
            fn ($query) => $query->where('assigned_agent_id', $agentUserId),
            fn ($query) => $query->whereRaw('1 = 0')
        );
        $agentLeads = (clone $agentLeadsQuery)->latest()->take(8)->get();
        $allPackages = Package::active()->leadPlans()->orderBy('sort_order')->orderBy('one_time_price')->get();
        $assistantPackages = Package::active()->assistantPlans()->orderBy('sort_order')->orderBy('monthly_price')->take(2)->get();
        $totalLeads = (clone $agentLeadsQuery)->count();
        $contactedLeadCount = (clone $agentLeadsQuery)->whereIn('status', ['contacted', 'qualified', 'closed'])->count();
        $qualifiedLeadCount = (clone $agentLeadsQuery)->where('status', 'qualified')->count();
        $closedLeadCount = (clone $agentLeadsQuery)->where('status', 'closed')->count();
        $pipeline = [
            ['label' => 'New', 'count' => (clone $agentLeadsQuery)->where('status', 'new')->count()],
            ['label' => 'Contacted', 'count' => (clone $agentLeadsQuery)->where('status', 'contacted')->count()],
            ['label' => 'Qualified', 'count' => $qualifiedLeadCount],
            ['label' => 'Closed', 'count' => $closedLeadCount],
        ];

        return view('pages.dashboards.agent', [
            'agent' => $agentProfile,
            'agentUser' => $user,
            'leads' => $agentLeads,
            'packages' => $allPackages,
            'assistantPackages' => $assistantPackages,
            'pipeline' => $pipeline,
            'activePlan' => $user?->currentPlan,
            'agentStats' => [
                'score' => number_format((float) ($agentProfile?->rating ?? 4.9), 1),
                'leads_received' => $totalLeads,
                'response_rate' => $totalLeads > 0 ? round(($contactedLeadCount / $totalLeads) * 100) . '%' : '0%',
                'rewards' => max(1, min(5, (int) ceil(($qualifiedLeadCount + $closedLeadCount) / 2))),
            ],
            'meta' => [
                'title' => 'Agent Dashboard | OmniReferral',
                'description' => 'Review leads, packages, analytics, onboarding status, and campaign tools in the OmniReferral agent dashboard.',
            ],
        ]);
    }

    public function affiliate(): View
    {
        $user = Auth::user();

        // Ensure user has an affiliate profile generated
        $affiliateProfile = \App\Models\AffiliateProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'slug' => \Illuminate\Support\Str::slug($user->name . '-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6))),
                'referral_code' => $user->affiliate_code ?: strtoupper(\Illuminate\Support\Str::random(8)),
            ]
        );

        // Make sure user record matches the generated affiliate code if it was missing
        if (!$user->affiliate_code) {
            $user->update(['affiliate_code' => $affiliateProfile->referral_code]);
        }

        $referrals = \App\Models\User::where('referred_by_user_id', $user->id)->latest()->take(10)->get();
        $conversions = \App\Models\Lead::whereHas('reviewedBy', function($q) use ($user) {
            // Just an example placeholder logic for now. Actual conversions are users who bought packages.
        })->count();

        return view('pages.dashboards.affiliate', [
            'profile' => $affiliateProfile,
            'referrals' => $referrals,
            'meta' => [
                'title' => 'Affiliate Hub | OmniReferral',
                'description' => 'Manage your referral links, track clicks, and view commissions.',
            ],
        ]);
    }
}

