<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RouteLeadToAgent;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\NewLeadAssignedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function route(Lead $lead): RedirectResponse
    {
        if ($lead->assigned_agent_id) {
            return back()->with('info', 'Lead is already assigned.');
        }

        $agent = User::where('role', 'agent')->inRandomOrder()->first();
        if (! $agent) {
            return back()->with('error', 'No agent available for assignment yet.');
        }

        $lead->update([
            'assigned_agent_id' => $agent->id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        $agent->notify(new NewLeadAssignedNotification($lead));

        // keep compatibility with existing behavior
        RouteLeadToAgent::dispatchSync($lead->id);

        return back()->with('success', 'Lead assigned to ' . $agent->name . ' and routed.');
    }

    public function status(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,qualified,assigned,contacted,closed'],
        ]);

        $lead->update(['status' => $validated['status']]);

        return back()->with('success', 'Lead status updated to ' . ucfirst($validated['status']) . '.');
    }

    public function assign(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id' => ['required', 'exists:users,id'],
        ]);

        $agent = User::find($validated['agent_id']);
        if (! $agent || $agent->role !== 'agent') {
            return back()->with('error', 'Selected user is not a valid agent.');
        }

        $lead->update([
            'assigned_agent_id' => $agent->id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        $agent->notify(new NewLeadAssignedNotification($lead));

        return back()->with('success', 'Lead explicitly assigned to ' . $agent->name . '.');
    }

    public function activity(Request $request, Lead $lead): RedirectResponse
    {
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
