<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\NewLeadAssignedNotification;
use App\Services\LeadAssignmentService;
use App\Services\LeadCustomerNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function __construct(
        protected LeadCustomerNotifier $leadCustomerNotifier,
        protected LeadAssignmentService $leadAssignmentService,
    ) {
    }

    public function status(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('updateStatus', $lead);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Lead::statusList())],
        ]);

        $previousStatus = $lead->status;

        $updates = Lead::applyStatusTimestamps(
            ['status' => $validated['status']],
            $validated['status'],
        );

        // Preserve the "only stamp contacted_at if unset" nuance of the original
        // single-lead endpoint (bulk updates always stamp on transition instead).
        if ($validated['status'] === 'contacted' && $lead->contacted_at) {
            unset($updates['contacted_at']);
        }

        $lead->update($updates);

        $this->leadCustomerNotifier->notifyStatusChangeIfNeeded($lead->fresh(), $previousStatus);

        return back()->with('success', 'Lead status updated to ' . $lead->statusLabel() . '.');
    }

    public function assign(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('assign', $lead);

        $validated = $request->validate([
            'agent_id' => ['required', 'exists:users,id'],
        ]);

        $agent = User::find($validated['agent_id']);
        if (! $agent || $agent->role !== 'agent') {
            return back()->with('error', 'Selected user is not a valid agent.');
        }

        $previousStatus = $lead->status;

        $this->leadAssignmentService->assign($lead, $agent, $request->user());

        $agent->notify(new NewLeadAssignedNotification($lead));

        $this->leadCustomerNotifier->notifyStatusChangeIfNeeded($lead->fresh(), $previousStatus);

        return back()->with('success', 'Lead explicitly assigned to ' . $agent->name . '.');
    }

    public function activity(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('addActivity', $lead);

        $validated = $request->validate([
            'type' => ['required', 'in:note,tag,reminder'],
            'content' => ['nullable', 'string', 'max:1000'],
            'value' => ['nullable', 'string', 'max:250'],
            'due_at' => ['nullable', 'date'],
        ]);

        $lead->activities()->create([
            'user_id' => $request->user()?->id,
            'type' => $validated['type'],
            'content' => $validated['content'] ?? null,
            'value' => $validated['value'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
        ]);

        return back()->with('success', 'Lead activity added successfully.');
    }
}
