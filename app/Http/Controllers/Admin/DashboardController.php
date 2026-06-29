<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\AgentSubscription;
use App\Models\Contact;
use App\Models\Enquiry;
use App\Models\Lead;
use App\Models\Package;
use App\Models\Property;
use App\Models\PropertyFavorite;
use App\Models\RealtorProfile;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $workspaceUser = auth()->user();

        if ($workspaceUser?->isSuperAdmin()) {
            return redirect()->route('super-admin.dashboard');
        }

        if ($workspaceUser?->role === 'staff') {
            return redirect()->route('staff.dashboard');
        }

        return $this->dashboard('pages.admin-dashboard');
    }

    public function staff(): View
    {
        abort_unless(auth()->user()?->role === 'staff', 403);

        return $this->dashboard('pages.staff-dashboard');
    }

    public function superAdmin(): View
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        return $this->dashboard('pages.super-admin-dashboard');
    }

    private function dashboard(string $viewName): View
    {
        $workspaceUser = auth()->user();
        $isStaffView = $workspaceUser?->role === 'staff';
        $isSuperAdminView = (bool) ($workspaceUser?->is_super_admin);
        $revenueMap = [
            'starter' => 199,
            'growth' => 349,
            'elite' => 549,
            'quick' => 199,
            'power' => 349,
            'prime' => 549,
        ];

        $recentLeads = Lead::latest()->take(6)->get();
        $estimatedRevenue = $recentLeads->sum(function (Lead $lead) use ($revenueMap) {
            return $revenueMap[strtolower((string) $lead->package_type)] ?? 0;
        });

        $leadStages = collect([
            ['label' => 'New', 'count' => Lead::where('status', 'new')->count(), 'tone' => 'blue'],
            ['label' => 'Qualified', 'count' => Lead::where('status', 'qualified')->count(), 'tone' => 'orange'],
            ['label' => 'Assigned', 'count' => Lead::where('status', 'assigned')->count(), 'tone' => 'navy'],
            ['label' => 'Closed', 'count' => Lead::where('status', 'closed')->count(), 'tone' => 'slate'],
        ]);

        $teamQueues = collect([
            [
                'team' => 'ISA Qualification Desk',
                'copy' => 'Fresh inbound and outbound leads waiting for budget, ZIP, and intent review.',
                'count' => Lead::whereIn('status', ['new', 'contacted'])->count(),
            ],
            [
                'team' => 'Sales Packaging Queue',
                'copy' => 'Qualified leads ready to be matched into Starter, Growth, or Elite sales conversations.',
                'count' => Lead::where('status', 'qualified')->count(),
            ],
            [
                'team' => 'Agent Delivery Queue',
                'copy' => 'Assigned opportunities moving into realtor dashboards and onboarding follow-through.',
                'count' => Lead::where('status', 'assigned')->count(),
            ],
            [
                'team' => 'Content & Growth Ops',
                'copy' => 'Campaigns, surveys, and site content that support conversion and retention.',
                'count' => Enquiry::count() + Contact::count() + Package::count(),
            ],
        ]);

        $maxLeadStage = max(1, $leadStages->max('count'));
        $pipelineHealth = $leadStages->map(function (array $stage) use ($maxLeadStage) {
            $stage['percent'] = (int) round(($stage['count'] / $maxLeadStage) * 100);
            return $stage;
        });
        $userSubmittedListings = Property::query()
            ->userSubmitted()
            ->with(['realtorProfile.user', 'owner'])
            ->withFavoriteSummary()
            ->latest()
            ->take(25)
            ->get();

        $pendingAccounts = User::query()
            ->where('status', 'pending')
            ->whereIn('role', ['buyer', 'seller', 'agent'])
            ->orderBy('created_at')
            ->take(12)
            ->get();
        $recentEnquiries = Enquiry::query()
            ->with(['property:id,title,slug', 'receiver:id,name,email', 'sender:id,name'])
            ->latest()
            ->take(6)
            ->get();

        $leadPipelineValue = 0;
        foreach (Lead::query()->select(['id', 'package_type'])->cursor() as $lead) {
            $leadPipelineValue += $revenueMap[strtolower((string) $lead->package_type)] ?? 0;
        }

        $mrrEstimate = (float) User::query()
            ->whereNotNull('current_plan_id')
            ->join('packages', 'packages.id', '=', 'users.current_plan_id')
            ->sum(DB::raw('COALESCE(packages.monthly_price, 0)'));

        $paidAgentCount = AgentSubscription::where('payment_status', 'paid')->where('is_active', true)->count();
        $pendingPaymentCount = AgentSubscription::where('payment_status', 'pending')->count();
        $ghlPaidAgentCount = AgentSubscription::where('payment_provider', 'gohighlevel')->where('payment_status', 'paid')->count();

        $analyticsTrends = collect(['daily', 'weekly', 'monthly', 'yearly'])
            ->mapWithKeys(fn (string $period) => [
                $period => [
                    'revenue' => $this->revenueTrendFor($period, $revenueMap)->values(),
                    'users' => $this->countTrendFor(User::class, $period)->values(),
                    'enquiries' => $this->countTrendFor(Enquiry::class, $period)->values(),
                ],
            ])
            ->toArray();

        $leadTrend = $this->countTrendFor(Lead::class, 'monthly');
        $userGrowthTrend = collect($analyticsTrends['monthly']['users']);
        $revenueTrend = collect($analyticsTrends['monthly']['revenue']);
        $enquiryTrend = collect($analyticsTrends['monthly']['enquiries']);

        $propertyTypeDistribution = Property::query()
            ->select('property_type', DB::raw('COUNT(*) as total'))
            ->groupBy('property_type')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->property_type ?: 'Other',
                'count' => (int) $row->total,
            ]);

        $propertyTypeTotal = max(1, $propertyTypeDistribution->sum('count'));
        $propertyTypeDistribution = $propertyTypeDistribution->map(fn (array $row) => $row + [
            'percent' => (int) round(($row['count'] / $propertyTypeTotal) * 100),
        ]);

        $recentAudit = $workspaceUser?->can('audit.view')
            ? AdminActivityLog::query()
                ->with('actor:id,name')
                ->latest('created_at')
                ->take(12)
                ->get()
            : collect();

        return view($viewName, [
            'stats' => [
                'leads' => Lead::count(),
                'realtors' => RealtorProfile::count(),
                'properties' => Property::count(),
                'activeListings' => Property::where('status', 'Active')->count(),
                'featuredListings' => Property::where('is_featured', true)->count(),
                'pendingListings' => Property::userSubmitted()->pendingReview()->count(),
                'pendingAccounts' => User::where('status', 'pending')
                    ->whereIn('role', ['buyer', 'seller', 'agent'])
                    ->count(),
                'draftAgentProfiles' => RealtorProfile::draft()->count(),
                'publishedAgentProfiles' => RealtorProfile::published()->count(),
                'featuredAgentProfiles' => RealtorProfile::featured()->count(),
                'userSubmittedListingsTotal' => Property::userSubmitted()->count(),
                'contacts' => Contact::count(),
                'enquiries' => Enquiry::count(),
                'packages' => Package::count(),
                'propertyFavorites' => PropertyFavorite::count(),
                'estimatedRevenue' => $estimatedRevenue,
                'leadPipelineValue' => $leadPipelineValue,
                'mrrEstimate' => $mrrEstimate,
                'usersTotal' => User::count(),
                'usersActive' => User::where('status', 'active')->count(),
                'usersSuspended' => User::where('status', 'suspended')->count(),
                'testimonials' => Testimonial::count(),
                'paidAgents' => $paidAgentCount,
                'pendingPaymentAgents' => $pendingPaymentCount,
                'ghlPaidAgents' => $ghlPaidAgentCount,
            ],
            'recentLeads' => $recentLeads,
            'pendingAccounts' => $pendingAccounts,
            'userSubmittedListings' => $userSubmittedListings,
            'testimonialStats' => [
                'video' => Testimonial::whereNotNull('video_url')->where('video_url', '!=', '')->count(),
                'published' => Testimonial::where('is_published', true)->count(),
                'featured' => Testimonial::where('is_featured', true)->count(),
            ],
            'latestTestimonials' => Testimonial::orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->latest()
                ->take(4)
                ->get(),
            'assignableAgents' => User::where('role', 'agent')->orderBy('name')->limit(12)->get(),
            'workspaceUser' => $workspaceUser,
            'isStaffView' => $isStaffView,
            'pipelineHealth' => $pipelineHealth,
            'teamQueues' => $teamQueues,
            'recentEnquiries' => $recentEnquiries,
            'leadTrend' => $leadTrend,
            'enquiryTrend' => $enquiryTrend,
            'userGrowthTrend' => $userGrowthTrend,
            'revenueTrend' => $revenueTrend,
            'analyticsTrends' => $analyticsTrends,
            'propertyTypeDistribution' => $propertyTypeDistribution,
            'recentAudit' => $recentAudit,
            'canViewFullAudit' => $workspaceUser?->can('audit.view') ?? false,
            'meta' => [
                'title' => match (true) {
                    $isSuperAdminView => 'Super Admin Dashboard | OmniReferral',
                    $isStaffView => 'Staff Dashboard | OmniReferral',
                    default => 'Admin Dashboard | OmniReferral',
                },
                'description' => match (true) {
                    $isSuperAdminView => 'Oversee system health, audit logs, users, leads, listings, and full-platform operations.',
                    $isStaffView => 'Coordinate operations queues, lead follow-up, and internal workflows across OmniReferral.',
                    default => 'Manage leads, agents, listings, and growth across OmniReferral.',
                },
            ],
        ]);
    }

    private function countTrendFor(string $modelClass, string $period): Collection
    {
        $trend = $this->dashboardTrendWindows($period)->map(fn (array $window) => [
            'label' => $window['label'],
            'count' => $modelClass::query()
                ->whereBetween('created_at', [$window['start'], $window['end']])
                ->count(),
        ]);

        return $this->withTrendPercent($trend, 'count');
    }

    private function revenueTrendFor(string $period, array $revenueMap): Collection
    {
        $trend = $this->dashboardTrendWindows($period)->map(function (array $window) use ($revenueMap) {
            $leads = Lead::query()
                ->whereBetween('created_at', [$window['start'], $window['end']])
                ->get(['package_type']);

            return [
                'label' => $window['label'],
                'amount' => (int) $leads->sum(fn (Lead $lead) => $revenueMap[strtolower((string) $lead->package_type)] ?? 0),
            ];
        });

        return $this->withTrendPercent($trend, 'amount');
    }

}
