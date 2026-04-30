<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
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
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $workspaceUser = auth()->user();
        $isStaffView = $workspaceUser?->role === 'staff';
        $revenueMap = [
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

        $recentAudit = $workspaceUser?->isAdmin()
            ? AdminActivityLog::query()
                ->with('actor:id,name')
                ->latest('created_at')
                ->take(12)
                ->get()
            : collect();

        return view('pages.admin-dashboard', [
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
            'canViewFullAudit' => $workspaceUser?->isAdmin() ?? false,
            'meta' => [
                'title' => $isStaffView ? 'Staff Dashboard | OmniReferral' : 'Admin Dashboard | OmniReferral',
                'description' => $isStaffView
                    ? 'Coordinate operations queues, lead follow-up, and internal workflows across OmniReferral.'
                    : 'Manage leads, agents, listings, and growth across OmniReferral.',
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

    private function dashboardTrendWindows(string $period): Collection
    {
        $now = now();

        return match ($period) {
            'daily' => collect(range(6, 0))->map(function (int $daysAgo) use ($now) {
                $date = $now->copy()->subDays($daysAgo);

                return [
                    'label' => $date->format('D'),
                    'start' => $date->copy()->startOfDay(),
                    'end' => $date->copy()->endOfDay(),
                ];
            }),
            'weekly' => collect(range(7, 0))->map(function (int $weeksAgo) use ($now) {
                $date = $now->copy()->subWeeks($weeksAgo)->startOfWeek();

                return [
                    'label' => $date->format('M j'),
                    'start' => $date->copy()->startOfWeek(),
                    'end' => $date->copy()->endOfWeek(),
                ];
            }),
            'yearly' => collect(range(4, 0))->map(function (int $yearsAgo) use ($now) {
                $date = $now->copy()->subYears($yearsAgo);

                return [
                    'label' => $date->format('Y'),
                    'start' => $date->copy()->startOfYear(),
                    'end' => $date->copy()->endOfYear(),
                ];
            }),
            default => collect(range(5, 0))->map(function (int $monthsAgo) use ($now) {
                $date = $now->copy()->subMonths($monthsAgo)->startOfMonth();

                return [
                    'label' => $date->format('M'),
                    'start' => $date->copy()->startOfMonth(),
                    'end' => $date->copy()->endOfMonth(),
                ];
            }),
        };
    }

    private function withTrendPercent(Collection $trend, string $valueKey): Collection
    {
        $max = max(1, (int) $trend->max($valueKey));

        return $trend->map(fn (array $row) => $row + [
            'percent' => (int) round(((int) ($row[$valueKey] ?? 0) / $max) * 100),
        ]);
    }
}
