<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentLeadQuota;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\Package;
use App\Models\User;
use App\Notifications\NewLeadAssignedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadAssignmentController extends Controller
{
    private const STATUSES = ['assigned', 'sent', 'accepted', 'rejected', 'no_response', 'reassigned', 'removed', 'closed'];

    public function index(Request $request): View
    {
        $workspaceUser = $request->user();
        $isStaffView = $workspaceUser?->role === 'staff';

        $query = LeadAssignment::query()
            ->with(['lead', 'assignedTo.realtorProfile', 'assignedBy', 'package']);

        // Staff members only see assignments they created.
        if ($isStaffView) {
            $query->where('assigned_by_user_id', $workspaceUser->id);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->whereHas('lead', function ($leadQuery) use ($search) {
                $leadQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('lead_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('month')) {
            $query->where('assignment_month', $request->month);
        }

        if ($request->filled('status')) {
            $query->where('assignment_status', $request->status);
        }

        if ($request->filled('agent_id')) {
            $query->where('assigned_to_user_id', $request->agent_id);
        }

        if ($request->filled('staff_id')) {
            $query->where('assigned_by_user_id', $request->staff_id);
        }

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        if ($request->filled('state')) {
            $state = $request->state;
            $query->whereHas('lead', function ($q) use ($state) {
                $q->where('state', $state);
            });
        }

        if ($request->filled('city')) {
            $city = $request->city;
            $query->whereHas('lead', function ($q) use ($city) {
                $q->where('city', $city);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('assigned_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('assigned_at', '<=', $request->date_to);
        }

        $assignments = $query->latest()->paginate(20)->withQueryString();

        if ($isStaffView) {
            $totals = [
                'total_leads' => LeadAssignment::where('assigned_by_user_id', $workspaceUser->id)->count(),
                'assigned_leads' => LeadAssignment::where('assigned_by_user_id', $workspaceUser->id)->whereNotIn('assignment_status', ['rejected', 'removed'])->count(),
                'unassigned_leads' => LeadAssignment::where('assigned_by_user_id', $workspaceUser->id)->whereIn('assignment_status', ['rejected', 'removed'])->count(),
                'closed_leads' => LeadAssignment::where('assigned_by_user_id', $workspaceUser->id)->where('assignment_status', 'closed')->count(),
            ];
        } else {
            $totals = [
                'total_leads' => Lead::count(),
                'assigned_leads' => Lead::whereNotNull('assigned_agent_id')->count(),
                'unassigned_leads' => Lead::whereNull('assigned_agent_id')->count(),
                'closed_leads' => Lead::where('status', 'closed')->count(),
            ];
        }

        return view('pages.admin.lead-assignments.index', [
            'assignments' => $assignments,
            'agents' => User::where('role', 'agent')->orderBy('name')->get(['id', 'name']),
            'staffList' => User::where('role', 'staff')->orderBy('name')->get(['id', 'name']),
            'packages' => Package::orderBy('name')->get(['id', 'name']),
            'states' => Lead::whereNotNull('state')->where('state', '!=', '')->distinct()->orderBy('state')->pluck('state'),
            'cities' => Lead::whereNotNull('city')->where('city', '!=', '')->distinct()->orderBy('city')->pluck('city'),
            'statuses' => self::STATUSES,
            'totals' => $totals,
            'workspaceUser' => $workspaceUser,
            'isStaffView' => $isStaffView,
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

        $previousAgentId = $lead->assigned_agent_id;
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
            'previous_agent_id' => $previousAgentId,
            'package_id' => $package->id,
            'assignment_month' => $month,
            'assignment_status' => 'assigned',
            'sent_at' => now(),
            'assigned_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        $quota->increment('assigned_count');
        $quota->decrement('remaining_count');

        $agent->notify(new NewLeadAssignedNotification($lead->fresh()));

        return redirect()->route('admin.lead-assignments.show', $assignment)
            ->with('success', "Lead assigned to {$agent->name}.");
    }

    public function show(LeadAssignment $assignment): View
    {
        $workspaceUser = auth()->user();
        if ($workspaceUser && $workspaceUser->role === 'staff' && $assignment->assigned_by_user_id !== $workspaceUser->id) {
            abort(403, 'Unauthorized.');
        }

        $assignment->load(['lead', 'assignedTo.realtorProfile', 'assignedBy', 'package']);

        $history = LeadAssignment::query()
            ->where('lead_id', $assignment->lead_id)
            ->with(['assignedTo.realtorProfile', 'assignedBy', 'package'])
            ->latest()
            ->get();

        $reassignableAgents = User::where('role', 'agent')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('pages.admin.lead-assignments.show', [
            'assignment' => $assignment,
            'history' => $history,
            'reassignableAgents' => $reassignableAgents,
            'meta' => [
                'title' => "Assignment #{$assignment->id} | OmniReferral",
                'description' => 'View lead assignment details.',
            ],
        ]);
    }

    public function updateStatus(Request $request, LeadAssignment $assignment): RedirectResponse
    {
        $workspaceUser = $request->user();
        if ($workspaceUser && $workspaceUser->role === 'staff' && $assignment->assigned_by_user_id !== $workspaceUser->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'assignment_status' => ['required', 'in:'.implode(',', self::STATUSES)],
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

        if (in_array($validated['assignment_status'], ['rejected', 'removed', 'closed'])) {
            $assignment->lead->update([
                'assigned_agent_id' => null,
                'assigned_at' => null,
                'assignment' => null,
            ]);
        }

        return back()->with('success', "Assignment status updated to {$validated['assignment_status']}.");
    }

    public function reassign(Request $request, LeadAssignment $assignment): RedirectResponse
    {
        $workspaceUser = $request->user();
        if ($workspaceUser && $workspaceUser->role === 'staff' && $assignment->assigned_by_user_id !== $workspaceUser->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'agent_id' => ['required', 'exists:users,id'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $agent = User::findOrFail($validated['agent_id']);
        if ($agent->role !== 'agent') {
            return back()->withErrors(['agent_id' => 'Selected user is not a valid agent.']);
        }

        if ((int) $agent->id === (int) $assignment->assigned_to_user_id) {
            return back()->withErrors(['agent_id' => 'This lead is already assigned to that agent.']);
        }

        $lead = $assignment->lead;
        $previousAgentId = $lead->assigned_agent_id;
        $package = $assignment->package ?? $agent->activeAgentSubscription?->package;
        if (! $package) {
            return back()->withErrors(['agent_id' => 'Selected agent has no active subscription package.']);
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

        $assignment->update([
            'assignment_status' => 'reassigned',
            'admin_notes' => trim((string) ($validated['admin_notes'] ?? '')) !== ''
                ? $validated['admin_notes']
                : ($assignment->admin_notes ?: 'Reassigned to '.$agent->name),
        ]);

        $lead->update([
            'assigned_agent_id' => $agent->id,
            'status' => 'assigned',
            'assigned_at' => now(),
            'assignment' => 'Assigned to '.$agent->name,
        ]);

        $newAssignment = LeadAssignment::create([
            'lead_id' => $lead->id,
            'assigned_to_user_id' => $agent->id,
            'assigned_by_user_id' => $request->user()?->id,
            'previous_agent_id' => $previousAgentId,
            'package_id' => $package->id,
            'assignment_month' => $month,
            'assignment_status' => 'assigned',
            'sent_at' => now(),
            'assigned_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        $quota->increment('assigned_count');
        $quota->decrement('remaining_count');

        $agent->notify(new NewLeadAssignedNotification($lead->fresh()));

        return redirect()->route('admin.lead-assignments.show', $newAssignment)
            ->with('success', "Lead reassigned to {$agent->name}.");
    }

    public function remove(Request $request, LeadAssignment $assignment): RedirectResponse
    {
        $workspaceUser = $request->user();
        if ($workspaceUser && $workspaceUser->role === 'staff' && $assignment->assigned_by_user_id !== $workspaceUser->id) {
            abort(403, 'Unauthorized.');
        }

        $adminNote = trim((string) $request->string('admin_notes'));
        $assignment->update([
            'assignment_status' => 'removed',
            'admin_notes' => $adminNote !== '' ? $adminNote : 'Assignment removed by '.($request->user()?->name ?? 'admin'),
        ]);

        $assignment->lead()->update([
            'assigned_agent_id' => null,
            'assigned_at' => null,
            'assignment' => null,
        ]);

        return redirect()->route('admin.lead-assignments.index')
            ->with('success', 'Assignment removed. The lead is back in the unassigned pool.');
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
                /** @var \App\Models\User $agent */
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
                    'previous_agent_id' => null,
                    'package_id' => $package->id,
                    'assignment_month' => $month,
                    'assignment_status' => 'assigned',
                    'sent_at' => now(),
                    'assigned_at' => now(),
                ]);

                $quota->increment('assigned_count');
                $quota->decrement('remaining_count');
                $assigned = true;
                $count++;

                $agent->notify(new NewLeadAssignedNotification($lead->fresh()));

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
