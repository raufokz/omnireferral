<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentLeadQuota;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentLeadQuotaController extends Controller
{
    public function index(Request $request): View
    {
        $query = AgentLeadQuota::query()->with(['user.realtorProfile', 'package']);

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        } else {
            $query->where('month', now()->format('Y-m'));
        }

        if ($request->filled('agent_id')) {
            $query->where('user_id', $request->agent_id);
        }

        if ($request->filled('under_quota')) {
            $query->where('remaining_count', '>', 0);
        }

        if ($request->filled('over_quota')) {
            $query->where('remaining_count', '<', 0);
        }

        $quotas = $query->latest()->paginate(20)->withQueryString();

        return view('pages.admin.agent-lead-quotas.index', [
            'quotas' => $quotas,
            'agents' => User::where('role', 'agent')->orderBy('name')->get(['id', 'name']),
            'meta' => [
                'title' => 'Agent Lead Quotas | OmniReferral',
                'description' => 'Monitor monthly lead quotas for all agents.',
            ],
        ]);
    }

    public function edit(AgentLeadQuota $quota): View
    {
        $quota->load(['user.realtorProfile', 'package']);

        return view('pages.admin.agent-lead-quotas.edit', [
            'quota' => $quota,
            'meta' => [
                'title' => "Edit Quota - {$quota->user->name} | OmniReferral",
                'description' => 'Override monthly lead quota for an agent.',
            ],
        ]);
    }

    public function update(Request $request, AgentLeadQuota $quota): RedirectResponse
    {
        $validated = $request->validate([
            'monthly_quota' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $newQuota = $validated['monthly_quota'];
        $quota->update([
            'monthly_quota' => $newQuota,
            'remaining_count' => $newQuota - $quota->assigned_count,
        ]);

        return redirect()->route('admin.agent-lead-quotas.index')
            ->with('success', 'Quota updated successfully.');
    }
}
