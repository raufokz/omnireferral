<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\LeadCustomerNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(
        protected LeadCustomerNotifier $leadCustomerNotifier,
    ) {
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        abort_unless((int) $lead->assigned_agent_id === (int) $user->id, 403, 'You can only update leads assigned to you.');

        $validated = $request->validate([
            'status' => ['required', 'in:new,contacted,in_progress,qualified,closed,not_interested'],
        ]);

        $previousStatus = $lead->status;

        $lead->status = $validated['status'];

        if ($lead->status === 'contacted' && ! $lead->contacted_at) {
            $lead->contacted_at = \Illuminate\Support\Carbon::now();
        }

        if ($lead->status === 'closed' && ! $lead->closed_at) {
            $lead->closed_at = \Illuminate\Support\Carbon::now();
        }

        if ($lead->status === 'qualified' && ! $lead->reviewed_at) {
            $lead->reviewed_at = \Illuminate\Support\Carbon::now();
        }

        $lead->save();

        $this->leadCustomerNotifier->notifyStatusChangeIfNeeded($lead->fresh(), $previousStatus);

        return back()->with('success', 'Lead status updated to ' . $lead->statusLabel() . '.');
    }

    public function updateNotes(Request $request, Lead $lead): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        abort_unless((int) $lead->assigned_agent_id === (int) $user->id, 403, 'You can only update leads assigned to you.');

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:5000'],
        ]);

        $lead->notes = $validated['notes'];
        $lead->save();

        return back()->with('success', 'Lead notes updated.');
    }
}
