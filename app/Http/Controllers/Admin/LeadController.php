<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\NewLeadAssignedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function status(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,contacted,in_progress,qualified,assigned,closed,not_interested'],
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'contacted' && ! $lead->contacted_at) {
            $updates['contacted_at'] = now();
        }

        if ($validated['status'] === 'closed') {
            $updates['closed_at'] = now();
        }

        if ($validated['status'] === 'qualified') {
            $updates['reviewed_at'] = now();
        }

        $lead->update($updates);

        return back()->with('success', 'Lead status updated to ' . $lead->statusLabel() . '.');
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
            'assignment' => 'Assigned to ' . $agent->name,
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
