<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Package;
use App\Models\Property;
use App\Models\PropertyFavorite;
use App\Models\RealtorProfile;
use App\Models\Testimonial;
use App\Models\User;
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
                'count' => Contact::count() + Package::count(),
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
        $recentListingMessages = Contact::query()
            ->with(['property', 'recipient', 'realtorProfile.user'])
            ->where(function ($query) {
                $query->whereNotNull('property_id')
                    ->orWhereNotNull('realtor_profile_id');
            })
            ->latest()
            ->take(6)
            ->get();

        return view('pages.admin-dashboard', [
            'stats' => [
                'leads' => Lead::count(),
                'realtors' => RealtorProfile::count(),
                'properties' => Property::count(),
                'pendingListings' => Property::userSubmitted()->pendingReview()->count(),
                'pendingAccounts' => User::where('status', 'pending')
                    ->whereIn('role', ['buyer', 'seller', 'agent'])
                    ->count(),
                'userSubmittedListingsTotal' => Property::userSubmitted()->count(),
                'contacts' => Contact::count(),
                'packages' => Package::count(),
                'propertyFavorites' => PropertyFavorite::count(),
                'estimatedRevenue' => $estimatedRevenue,
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
            'recentListingMessages' => $recentListingMessages,
            'meta' => [
                'title' => $isStaffView ? 'Staff Dashboard | OmniReferral' : 'Admin Dashboard | OmniReferral',
                'description' => $isStaffView
                    ? 'Coordinate operations queues, lead follow-up, and internal workflows across OmniReferral.'
                    : 'Manage leads, agents, listings, and growth across OmniReferral.',
            ],
        ]);
    }
}
