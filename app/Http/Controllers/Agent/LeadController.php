<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Services\LeadCustomerNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function __construct(
        protected LeadCustomerNotifier $leadCustomerNotifier,
    ) {
    }

    public function show(Request $request, Lead $lead): View
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless((int) $lead->assigned_agent_id === (int) $user->id || $user->isAdmin() || $user->isStaff(), 403, 'You do not have access to view this lead.');

        $lead->load(['assignedAgent', 'property', 'package', 'activities.user']);

        $relatedMessages = Contact::query()
            ->where(function ($query) use ($lead) {
                $hasFilter = false;
                if ($lead->email) {
                    $query->where('email', $lead->email);
                    $hasFilter = true;
                }
                if ($lead->phone) {
                    if ($hasFilter) {
                        $query->orWhere('phone', $lead->phone);
                    } else {
                        $query->where('phone', $lead->phone);
                    }
                }
            })
            ->latest()
            ->get();

        $agentProfile = $user->realtorProfile;
        $activities = $lead->activities()->with('user')->latest()->get();

        return view('pages.dashboards.agent-lead-detail', [
            'lead' => $lead,
            'agentUser' => $user,
            'agentProfile' => $agentProfile,
            'activeAgentPage' => 'leads',
            'relatedMessages' => $relatedMessages,
            'activities' => $activities,
            'meta' => [
                'title' => 'Lead #' . ($lead->lead_number ?: $lead->id) . ' - ' . $lead->name . ' | OmniReferral',
                'description' => 'Detailed lead page, quick communication actions, and activity timeline for ' . $lead->name . '.',
            ],
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless((int) $lead->assigned_agent_id === (int) $user->id || $user->isAdmin(), 403, 'You can only edit leads assigned to you.');

        if ($request->has('budget') && $request->input('budget') !== null) {
            $request->merge(['budget' => (string) $request->input('budget')]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'intent' => ['required', 'in:buyer,seller,investor,other'],
            'property_address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:50'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'budget' => ['nullable', 'string', 'max:100'],
            'asking_price' => ['nullable', 'numeric', 'min:0'],
            'beds_baths' => ['nullable', 'string', 'max:50'],
            'property_type' => ['nullable', 'string', 'max:100'],
            'timeline' => ['nullable', 'string', 'max:100'],
            'financing_status' => ['nullable', 'string', 'max:100'],
            'credit_score' => ['nullable', 'string', 'max:50'],
            'dop' => ['nullable', 'date'],
            'contact_preference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $lead->update($validated);

        $lead->activities()->create([
            'user_id' => $user->id,
            'type' => 'edit',
            'value' => 'Lead Details Updated',
            'content' => 'Lead profile and preferences updated by ' . $user->name . '.',
        ]);

        return back()->with('success', 'Lead details updated successfully.');
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        abort_unless((int) $lead->assigned_agent_id === (int) $user->id || $user->isAdmin(), 403, 'You can only update leads assigned to you.');

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_diff(Lead::statusList(), ['assigned']))],
        ]);

        $previousStatus = $lead->status;

        $lead->status = $validated['status'];

        if ($lead->status === 'contacted' && ! $lead->contacted_at) {
            $lead->contacted_at = Carbon::now();
        }

        if ($lead->status === 'closed' && ! $lead->closed_at) {
            $lead->closed_at = Carbon::now();
        }

        if ($lead->status === 'qualified' && ! $lead->reviewed_at) {
            $lead->reviewed_at = Carbon::now();
        }

        $lead->save();

        $lead->activities()->create([
            'user_id' => $user->id,
            'type' => 'status_change',
            'value' => $lead->statusLabel(),
            'content' => 'Status changed from ' . Str::headline($previousStatus) . ' to ' . $lead->statusLabel() . '.',
        ]);

        $this->leadCustomerNotifier->notifyStatusChangeIfNeeded($lead->fresh(), $previousStatus);

        return back()->with('success', 'Lead status updated to ' . $lead->statusLabel() . '.');
    }

    public function updateNotes(Request $request, Lead $lead): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        abort_unless((int) $lead->assigned_agent_id === (int) $user->id || $user->isAdmin(), 403, 'You can only update leads assigned to you.');

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:5000'],
        ]);

        $lead->notes = $validated['notes'];
        $lead->save();

        $lead->activities()->create([
            'user_id' => $user->id,
            'type' => 'note',
            'value' => 'Note Added',
            'content' => $validated['notes'],
        ]);

        return back()->with('success', 'Lead notes updated.');
    }

    public function storeActivity(Request $request, Lead $lead): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        abort_unless((int) $lead->assigned_agent_id === (int) $user->id || $user->isAdmin(), 403, 'You can only log activity for leads assigned to you.');

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'content' => ['nullable', 'string', 'max:5000'],
            'value' => ['nullable', 'string', 'max:255'],
            'due_at' => ['nullable', 'date'],
        ]);

        $lead->activities()->create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'content' => $validated['content'] ?? null,
            'value' => $validated['value'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
        ]);

        $message = match ($validated['type']) {
            'call' => 'Call logged successfully.',
            'email' => 'Email interaction logged successfully.',
            'sms' => 'SMS interaction logged successfully.',
            'reminder' => 'Follow-up reminder scheduled successfully.',
            'tag' => 'Tag updated.',
            default => 'Activity logged successfully.',
        };

        return back()->with('success', $message);
    }
}
