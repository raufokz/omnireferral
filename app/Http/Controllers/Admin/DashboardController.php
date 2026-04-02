<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Package;
use App\Models\Property;
use App\Models\RealtorProfile;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
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
                'copy' => 'Qualified leads ready to be matched into Quick, Power, or Prime sales conversations.',
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

        return view('pages.admin-dashboard', [
            'stats' => [
                'leads' => Lead::count(),
                'realtors' => RealtorProfile::count(),
                'properties' => Property::count(),
                'contacts' => Contact::count(),
                'packages' => Package::count(),
                'pending' => RealtorProfile::whereHas('user', function ($query) {
                    $query->where('status', 'pending');
                })->count(),
                'estimatedRevenue' => $estimatedRevenue,
            ],
            'recentLeads' => $recentLeads,
            'pendingRealtors' => RealtorProfile::whereHas('user', function ($query) {
                $query->where('status', 'pending');
            })->latest()->take(4)->get(),
            'pipelineHealth' => $pipelineHealth,
            'teamQueues' => $teamQueues,
            'meta' => [
                'title' => 'Admin Dashboard | OmniReferral',
                'description' => 'Manage leads, agents, listings, and growth across OmniReferral.',
            ],
        ]);
    }
}

