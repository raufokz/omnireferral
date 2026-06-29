<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentLeadQuota;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = LeadAssignment::query()
            ->with(['lead', 'assignedTo.realtorProfile', 'assignedBy', 'package']);

        if ($request->filled('month')) {
            $query->where('assignment_month', $request->month);
        }

        if ($request->filled('status')) {
            $query->where('assignment_status', $request->status);
        }

        if ($request->filled('agent_id')) {
            $query->where('assigned_to_user_id', $request->agent_id);
        }

        $assignments = $query->latest()->paginate(20)->withQueryString();

        return view('pages.admin.lead-assignments.index', [
            'assignments' => $assignments,
            'agents' => User::where('role', 'agent')->orderBy('name')->get(['id', 'name']),
            'statuses' => ['assigned', 'sent', 'accepted', 'rejected', 'no_response', 'reassigned', 'closed'],
            'meta' => [
                'title' => 'Lead Assignments | OmniReferral',
                'description' => 'Manage lead-to-agent assignments and track lifecycle.',
            ],
        ]);
    }

    public function create(): View
    {
        $agents = User::where('role', 'agent')
            ->where('status', 'active')
            ->whereNotNull('onboarding_completed_at')
            ->whereHas('realtorProfile')
            ->whereHas('activeAgentSubscription')
            ->with('activeAgentSubscription.package')
            ->orderBy('name')
            ->get();

        $unassignedLeads = Lead::whereNull('assigned_agent_id')
            ->where('is_assignable', true)
            ->latest()
            ->get();

        $packages = Package::where('is_active', true)
            ->where('monthly_lead_quota', '>', 0)
            ->orderBy('lead_priority', 'desc')
            ->get();

        return view('pages.admin.lead-assignments.create', [
            'agents' => $agents,
            'leads' => $unassignedLeads,
            'packages' => $packages,
            'meta' => [
                'title' => 'Assign Lead | OmniReferral',
                'description' => 'Manually assign a lead to an agent.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lead_id' => ['required', 'exists:leads,id'],
            'agent_id' => ['required', 'exists:users,id'],
            'package_id' => ['nullable', 'exists:packages,id'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);
        $agent = User::findOrFail($validated['agent_id']);
        $packageId = $validated['package_id'] ?? $agent->activeAgentSubscription?->package_id;

        if (! $packageId) {
            return back()->withErrors(['agent_id' => 'Selected agent has no active subscription package.']);
        }

        $package = Package::findOrFail($packageId);

        if ($lead->assigned_agent_id) {
            return back()->withErrors(['lead_id' => 'This lead is already assigned.']);
        }

        $month = now()->format('Y-m');

        $quota = AgentLeadQuota::firstOrCreate(
            ['user_id' => $agent->id, 'month' => $month],
            [
                'package_id' => $package->id,
                'monthly_quota' => $package->monthly_lead_quota,
                'assigned_count' => 0,
                'remaining_count' => $package->monthly_lead_quota,
                'overdue_count' => 0,
            ]
        );

        if ($quota->remaining_count <= 0 && ! $request->boolean('override_quota')) {
            return back()->withErrors(['agent_id' => 'This agent has reached their monthly lead quota. Enable override to assign anyway.']);
        }

        $lead->update([
            'assigned_agent_id' => $agent->id,
            'status' => 'assigned',
            'assigned_at' => now(),
            'assignment' => 'Assigned to ' . $agent->name,
        ]);

        $assignment = LeadAssignment::create([
            'lead_id' => $lead->id,
            'assigned_to_user_id' => $agent->id,
            'assigned_by_user_id' => $request->user()->id,
            'package_id' => $package->id,
            'assignment_month' => $month,
            'assignment_status' => 'assigned',
            'sent_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        $quota->increment('assigned_count');
        $quota->decrement('remaining_count');

        return redirect()->route('admin.lead-assignments.show', $assignment)
            ->with('success', "Lead assigned to {$agent->name}.");
    }

    public function show(LeadAssignment $assignment): View
    {
        $assignment->load(['lead', 'assignedTo.realtorProfile', 'assignedBy', 'package']);

        return view('pages.admin.lead-assignments.show', [
            'assignment' => $assignment,
            'meta' => [
                'title' => "Assignment #{$assignment->id} | OmniReferral",
                'description' => 'View lead assignment details.',
            ],
        ]);
    }

    public function updateStatus(Request $request, LeadAssignment $assignment): RedirectResponse
    {
        $validated = $request->validate([
            'assignment_status' => ['required', 'in:assigned,sent,accepted,rejected,no_response,reassigned,closed'],
            'response_from_realtor' => ['nullable', 'string', 'max:2000'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $timestamps = [];
        if ($validated['assignment_status'] === 'sent' && ! $assignment->sent_at) {
            $timestamps['sent_at'] = now();
        }
        if ($validated['assignment_status'] === 'accepted' && ! $assignment->accepted_at) {
            $timestamps['accepted_at'] = now();
        }
        if ($validated['assignment_status'] === 'rejected' && ! $assignment->rejected_at) {
            $timestamps['rejected_at'] = now();
        }

        $assignment->update([
            'assignment_status' => $validated['assignment_status'],
            'response_from_realtor' => $validated['response_from_realtor'] ?? $assignment->response_from_realtor,
            'admin_notes' => $validated['admin_notes'] ?? $assignment->admin_notes,
            ...$timestamps,
        ]);

        if (in_array($validated['assignment_status'], ['rejected', 'closed'])) {
            $assignment->lead->update([
                'assigned_agent_id' => null,
                'assignment' => null,
            ]);
        }

        return back()->with('success', "Assignment status updated to {$validated['assignment_status']}.");
    }

    public function autoAssign(Request $request): RedirectResponse
    {
        $count = 0;

        $assignableLeads = Lead::whereNull('assigned_agent_id')
            ->where('is_assignable', true)
            ->latest()
            ->get();

        $month = now()->format('Y-m');

        $agents = User::where('role', 'agent')
            ->where('status', 'active')
            ->whereNotNull('onboarding_completed_at')
            ->whereHas('realtorProfile')
            ->whereHas('activeAgentSubscription')
            ->with('activeAgentSubscription.package')
            ->orderBy('name')
            ->get()
            ->sortByDesc(fn ($agent) => $agent->activeAgentSubscription?->package?->lead_priority ?? 0);

        foreach ($assignableLeads as $lead) {
            $assigned = false;

            foreach ($agents as $agent) {
                $package = $agent->activeAgentSubscription?->package;

                if (! $package || $package->monthly_lead_quota <= 0) {
                    continue;
                }

                $quota = AgentLeadQuota::firstOrCreate(
                    ['user_id' => $agent->id, 'month' => $month],
                    [
                        'package_id' => $package->id,
                        'monthly_quota' => $package->monthly_lead_quota,
                        'assigned_count' => 0,
                        'remaining_count' => $package->monthly_lead_quota,
                        'overdue_count' => 0,
                    ]
                );

                if ($quota->remaining_count <= 0) {
                    continue;
                }

                $adminUser = $request->user();

                $lead->update([
                    'assigned_agent_id' => $agent->id,
                    'status' => 'assigned',
                    'assigned_at' => now(),
                    'assignment' => 'Assigned to ' . $agent->name,
                ]);

                LeadAssignment::create([
                    'lead_id' => $lead->id,
                    'assigned_to_user_id' => $agent->id,
                    'assigned_by_user_id' => $adminUser?->id,
                    'package_id' => $package->id,
                    'assignment_month' => $month,
                    'assignment_status' => 'assigned',
                    'sent_at' => now(),
                ]);

                $quota->increment('assigned_count');
                $quota->decrement('remaining_count');
                $assigned = true;
                $count++;

                break;
            }

            if (! $assigned) {
                break;
            }
        }

        return redirect()->route('admin.lead-assignments.index')
            ->with('success', "Auto-assigned {$count} lead(s).");
    }

}
