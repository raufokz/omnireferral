<?php

namespace App\Http\Controllers;

use App\Models\AffiliateProfile;
use App\Models\AffiliateReferralClick;
use App\Models\Enquiry;
use App\Models\Lead;
use App\Models\Package;
use App\Models\Property;
use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

        if ($user->isSuperAdmin()) {
            return redirect()->route('super-admin.dashboard');
        }

        if ($user->role === 'staff') {
            return redirect()->route('staff.dashboard');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        abort(403, 'Your account does not have a dashboard role assigned.');

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
                'route' => route('staff.dashboard'),
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
        $workspace = $this->buyerWorkspace();

        return view('pages.dashboards.buyer', $workspace + [
            'meta' => [
                'title' => 'Buyer Dashboard | OmniReferral',
                'description' => 'Track your saved homes, request progress, and live market activity.',
            ],
        ]);
    }

    public function buyerSavedHomes(): View
    {
        $workspace = $this->buyerWorkspace();

        return view('pages.dashboards.buyer-saved', $workspace + [
            'savedHomes' => $workspace['favoritePropertiesQuery']
                ->orderByPivot('created_at', 'desc')
                ->paginate(12),
            'meta' => [
                'title' => 'Saved Homes | OmniReferral',
                'description' => 'Review and manage your saved buyer properties.',
            ],
        ]);
    }

    public function buyerRequests(): View
    {
        $workspace = $this->buyerWorkspace();

        return view('pages.dashboards.buyer-requests', $workspace + [
            'requests' => Lead::query()
                ->matchingIdentityForUser(Auth::user())
                ->where('intent', 'buyer')
                ->latest()
                ->paginate(12),
            'meta' => [
                'title' => 'Buyer Requests | OmniReferral',
                'description' => 'Track your buyer request flow across every stage.',
            ],
        ]);
    }

    public function seller(): View
    {
        $workspace = $this->sellerWorkspace();

        return view('pages.dashboards.seller', $workspace + [
            'meta' => [
                'title' => 'Seller Dashboard | OmniReferral',
                'description' => 'Track listing readiness, requests, and market visibility from one seller workspace.',
            ],
        ]);
    }

    public function sellerListings(): View
    {
        $workspace = $this->sellerWorkspace();

        return view('pages.dashboards.seller-listings', $workspace + [
            'listingAgents' => RealtorProfile::query()->with('user')->orderBy('brokerage_name')->get(),
            'marketplaceProperties' => Property::query()
                ->withFavoriteSummary()
                ->marketplaceVisible()
                ->latest()
                ->paginate(9),
            'meta' => [
                'title' => 'Seller Listings | OmniReferral',
                'description' => 'Submit a new listing and review the latest active marketplace properties.',
            ],
        ]);
    }

    public function sellerRequests(): View
    {
        $workspace = $this->sellerWorkspace();

        return view('pages.dashboards.seller-requests', $workspace + [
            'requests' => Lead::query()
                ->matchingIdentityForUser(Auth::user())
                ->where('intent', 'seller')
                ->latest()
                ->paginate(12),
            'meta' => [
                'title' => 'Seller Requests | OmniReferral',
                'description' => 'Track seller request status and current lead movement.',
            ],
        ]);
    }

    public function affiliate(): View
    {
        $user = Auth::user();

        // Ensure user has an affiliate profile generated
        $affiliateProfile = AffiliateProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'slug' => Str::slug($user->name.'-'.Str::lower(Str::random(6))),
                'referral_code' => $user->affiliate_code ?: strtoupper(Str::random(8)),
            ]
        );

        if (! $user->affiliate_code) {
            $user->update(['affiliate_code' => $affiliateProfile->referral_code]);
        }

        $referrals = User::where('referred_by_user_id', $user->id)->latest()->take(10)->get();
        $referralSignupCount = User::where('referred_by_user_id', $user->id)->count();
        $referralPaidPlanCount = User::where('referred_by_user_id', $user->id)->whereNotNull('current_plan_id')->count();
        $recentClicks = AffiliateReferralClick::query()
            ->where('affiliate_profile_id', $affiliateProfile->id)
            ->latest()
            ->take(8)
            ->get();

        return view('pages.dashboards.affiliate', [
            'profile' => $affiliateProfile,
            'referrals' => $referrals,
            'referralSignupCount' => $referralSignupCount,
            'referralPaidPlanCount' => $referralPaidPlanCount,
            'recentClicks' => $recentClicks,
            'referralShareUrl' => url('/?ref='.$affiliateProfile->referral_code),
            'meta' => [
                'title' => 'Affiliate Hub | OmniReferral',
                'description' => 'Manage your referral links, track clicks, and view commissions.',
            ],
        ]);
    }

    private function buyerWorkspace(): array
    {
        $buyer = Auth::user();
        $favoritePropertiesQuery = $buyer->favoriteProperties()
            ->with('realtorProfile.user')
            ->withFavoriteSummary($buyer)
            ->marketplaceVisible();

        $buyerLeadScope = Lead::query()->matchingIdentityForUser($buyer)->where('intent', 'buyer');
        $enquiriesQuery = Enquiry::query()->forParticipant($buyer);
        $revenueMap = $this->dashboardRevenueMap();
        $leadPipelineValue = (clone $buyerLeadScope)->get(['package_type'])->sum(fn ($lead) => $revenueMap[strtolower((string) $lead->package_type)] ?? 0);

        $buyerRequests = (clone $buyerLeadScope)->latest()->take(6)->get();
        $buyerJourney = [
            ['label' => 'Submitted', 'count' => (clone $buyerLeadScope)->whereIn('status', ['new', 'contacted'])->count()],
            ['label' => 'Qualified', 'count' => (clone $buyerLeadScope)->where('status', 'qualified')->count()],
            ['label' => 'Agent Match', 'count' => (clone $buyerLeadScope)->where('status', 'assigned')->count()],
            ['label' => 'Closed', 'count' => (clone $buyerLeadScope)->where('status', 'closed')->count(), 'tone' => 'slate'],
        ];
        $favoriteCount = $buyer->propertyFavorites()->count();

        $analyticsTrends = collect(['daily', 'weekly', 'monthly', 'yearly'])
            ->mapWithKeys(fn (string $period) => [
                $period => [
                    'revenue' => $this->revenueTrendForQuery((clone $buyerLeadScope), $revenueMap, $period)->values(),
                    'users' => $this->countTrendForQuery(User::query()->where('id', $buyer->id), $period)->values(),
                    'enquiries' => $this->countTrendForQuery((clone $enquiriesQuery), $period)->values(),
                ],
            ]);

        $propertyTypeDistribution = $this->propertyTypeDistributionForQuery((clone $favoritePropertiesQuery));
        $pipelineHealth = $this->pipelineHealthFromCounts($buyerJourney);
        $teamQueues = collect([
            [
                'team' => 'Search Shortlist',
                'copy' => 'Saved homes waiting for comparison, agent contact, or follow-up.',
                'count' => $favoriteCount,
            ],
            [
                'team' => 'Request Matching',
                'copy' => 'Buyer requests moving through submitted, qualified, assigned, and closed stages.',
                'count' => (clone $buyerLeadScope)->whereIn('status', ['new', 'contacted', 'qualified', 'assigned'])->count(),
            ],
            [
                'team' => 'Enquiry Threads',
                'copy' => 'Listing conversations started from marketplace inventory.',
                'count' => (clone $enquiriesQuery)->where('status', 'pending')->count(),
            ],
            [
                'team' => 'Marketplace Watch',
                'copy' => 'Approved listings currently visible in the buyer marketplace.',
                'count' => (clone $favoritePropertiesQuery)->marketplaceVisible()->count(),
            ],
        ]);

        return [
            'favoritePropertiesQuery' => clone $favoritePropertiesQuery,
            'properties' => (clone $favoritePropertiesQuery)
                ->orderByPivot('created_at', 'desc')
                ->take(6)
                ->get(),
            'buyerRequests' => $buyerRequests,
            'buyerJourney' => $buyerJourney,
            'buyerStats' => [
                'saved_listings' => $favoriteCount,
                'favorites' => $favoriteCount,
                'new_alerts' => (clone $buyerLeadScope)->whereIn('status', ['new', 'contacted'])->count(),
            ],
            'stats' => [
                'leads' => (clone $buyerLeadScope)->count(),
                'properties' => $favoriteCount,
                'activeListings' => $favoriteCount,
                'featuredListings' => (clone $favoritePropertiesQuery)->where('is_featured', true)->count(),
                'pendingListings' => 0,
                'pendingAccounts' => 0,
                'userSubmittedListingsTotal' => $favoriteCount,
                'contacts' => (clone $enquiriesQuery)->count(),
                'enquiries' => (clone $enquiriesQuery)->count(),
                'packages' => Package::count(),
                'propertyFavorites' => $favoriteCount,
                'leadPipelineValue' => $leadPipelineValue,
                'mrrEstimate' => 0,
                'usersTotal' => 1,
                'usersActive' => $buyer->status === 'active' ? 1 : 0,
                'usersSuspended' => $buyer->status === 'suspended' ? 1 : 0,
            ],
            'recentLeads' => (clone $buyerLeadScope)->latest()->take(6)->get(),
            'pendingAccounts' => collect(),
            'userSubmittedListings' => (clone $favoritePropertiesQuery)
                ->orderByPivot('created_at', 'desc')
                ->take(6)
                ->get(),
            'recentEnquiries' => (clone $enquiriesQuery)
                ->with(['property:id,title,slug', 'receiver:id,name'])
                ->latest()
                ->take(6)
                ->get(),
            'pipelineHealth' => $pipelineHealth,
            'teamQueues' => $teamQueues,
            'leadTrend' => $this->countTrendForQuery((clone $buyerLeadScope), 'monthly'),
            'enquiryTrend' => collect($analyticsTrends['monthly']['enquiries']),
            'userGrowthTrend' => collect($analyticsTrends['monthly']['users']),
            'revenueTrend' => collect($analyticsTrends['monthly']['revenue']),
            'analyticsTrends' => $analyticsTrends->toArray(),
            'propertyTypeDistribution' => $propertyTypeDistribution,
            'recentAudit' => collect(),
            'canViewFullAudit' => false,
            'listingSectionEyebrow' => 'Saved Inventory',
            'listingSectionTitle' => 'Saved and matched listings',
            'listingSectionCopy' => 'Role-scoped inventory from your buyer workspace shortlist.',
            'listingOwnerLabel' => 'Agent / Partner',
        ];
    }

    private function sellerWorkspace(): array
    {
        $seller = Auth::user();

        $propertiesQuery = Property::query()
            ->with(['realtorProfile.user', 'owner'])
            ->withFavoriteSummary()
            ->where('owner_user_id', $seller->id);

        $properties = (clone $propertiesQuery)
            ->latest()
            ->take(6)
            ->get();

        $sellerLeadScope = Lead::query()->matchingIdentityForUser($seller)->where('intent', 'seller');
        $enquiriesQuery = Enquiry::query()->forParticipant($seller);
        $buyerMatchesScope = Lead::query()->matchingIdentityForUser($seller)->where('intent', 'buyer');
        $revenueMap = $this->dashboardRevenueMap();
        $leadPipelineValue = (clone $sellerLeadScope)->get(['package_type'])->sum(fn ($lead) => $revenueMap[strtolower((string) $lead->package_type)] ?? 0);

        $sellerRequests = (clone $sellerLeadScope)->latest()->take(6)->get();
        $sellerJourney = [
            ['label' => 'Submitted', 'count' => (clone $sellerLeadScope)->whereIn('status', ['new', 'contacted'])->count()],
            ['label' => 'Qualified', 'count' => (clone $sellerLeadScope)->where('status', 'qualified')->count()],
            ['label' => 'In Market', 'count' => (clone $sellerLeadScope)->where('status', 'assigned')->count()],
            ['label' => 'Closed', 'count' => (clone $sellerLeadScope)->where('status', 'closed')->count(), 'tone' => 'slate'],
        ];

        $ownedActiveCount = (clone $propertiesQuery)
            ->where('approval_status', '!=', Property::APPROVAL_REJECTED)
            ->whereNotIn('status', ['Sold', 'Off-Market'])
            ->count();

        $recentOwnedUpdates = (clone $propertiesQuery)
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();

        $analyticsTrends = collect(['daily', 'weekly', 'monthly', 'yearly'])
            ->mapWithKeys(fn (string $period) => [
                $period => [
                    'revenue' => $this->revenueTrendForQuery((clone $sellerLeadScope), $revenueMap, $period)->values(),
                    'users' => $this->countTrendForQuery(User::query()->where('id', $seller->id), $period)->values(),
                    'enquiries' => $this->countTrendForQuery((clone $enquiriesQuery), $period)->values(),
                ],
            ]);

        $propertyTypeDistribution = $this->propertyTypeDistributionForQuery((clone $propertiesQuery));
        $pipelineHealth = $this->pipelineHealthFromCounts($sellerJourney);
        $teamQueues = collect([
            [
                'team' => 'Listing Review',
                'copy' => 'Seller uploads waiting for approval, rejection, or marketplace publication.',
                'count' => (clone $propertiesQuery)->where('approval_status', Property::APPROVAL_PENDING)->count(),
            ],
            [
                'team' => 'Buyer Demand',
                'copy' => 'Buyer-side request signals that may align with your listings.',
                'count' => (clone $buyerMatchesScope)->count(),
            ],
            [
                'team' => 'Inbound Interest',
                'copy' => 'Seller-side lead activity and open inquiry threads.',
                'count' => (clone $sellerLeadScope)->whereNotIn('status', ['closed', 'not_interested'])->count(),
            ],
            [
                'team' => 'Marketplace Visibility',
                'copy' => 'Approved listings currently visible in the public marketplace.',
                'count' => (clone $propertiesQuery)->marketplaceVisible()->count(),
            ],
        ]);

        return [
            'properties' => $properties,
            'sellerRequests' => $sellerRequests,
            'sellerJourney' => $sellerJourney,
            'sellerStats' => [
                'active_listings' => $ownedActiveCount,
                'open_inquiries' => (clone $sellerLeadScope)->whereNotIn('status', ['closed', 'not_interested'])->count(),
                'price_updates' => $recentOwnedUpdates,
                'buyer_matches' => (clone $buyerMatchesScope)->count(),
            ],
            'stats' => [
                'leads' => (clone $sellerLeadScope)->count(),
                'properties' => (clone $propertiesQuery)->count(),
                'activeListings' => $ownedActiveCount,
                'featuredListings' => (clone $propertiesQuery)->where('is_featured', true)->count(),
                'pendingListings' => (clone $propertiesQuery)->where('approval_status', Property::APPROVAL_PENDING)->count(),
                'pendingAccounts' => 0,
                'userSubmittedListingsTotal' => (clone $propertiesQuery)->count(),
                'contacts' => (clone $enquiriesQuery)->count(),
                'enquiries' => (clone $enquiriesQuery)->count(),
                'packages' => Package::count(),
                'propertyFavorites' => (clone $propertiesQuery)->get()->sum(fn ($property) => $property->favorites_count ?? 0),
                'leadPipelineValue' => $leadPipelineValue,
                'mrrEstimate' => 0,
                'usersTotal' => 1,
                'usersActive' => $seller->status === 'active' ? 1 : 0,
                'usersSuspended' => $seller->status === 'suspended' ? 1 : 0,
            ],
            'recentLeads' => (clone $sellerLeadScope)->latest()->take(6)->get(),
            'pendingAccounts' => collect(),
            'userSubmittedListings' => (clone $propertiesQuery)
                ->latest()
                ->take(6)
                ->get(),
            'recentEnquiries' => (clone $enquiriesQuery)
                ->with(['property:id,title,slug', 'receiver:id,name'])
                ->latest()
                ->take(6)
                ->get(),
            'pipelineHealth' => $pipelineHealth,
            'teamQueues' => $teamQueues,
            'leadTrend' => $this->countTrendForQuery((clone $sellerLeadScope), 'monthly'),
            'enquiryTrend' => collect($analyticsTrends['monthly']['enquiries']),
            'userGrowthTrend' => collect($analyticsTrends['monthly']['users']),
            'revenueTrend' => collect($analyticsTrends['monthly']['revenue']),
            'analyticsTrends' => $analyticsTrends->toArray(),
            'propertyTypeDistribution' => $propertyTypeDistribution,
            'recentAudit' => collect(),
            'canViewFullAudit' => false,
            'listingSectionEyebrow' => 'Submitted Listings',
            'listingSectionTitle' => 'User-submitted listings',
            'listingSectionCopy' => 'Role-scoped seller uploads across moderation and marketplace states.',
            'listingOwnerLabel' => 'Owner',
        ];
    }

}
