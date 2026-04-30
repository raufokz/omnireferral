<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserModerationController extends Controller
{
    public function review(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isStaff(), 403, 'Unauthorized action.');

        if ($user->id === $request->user()->id) {
            abort(403, 'You cannot change your own account status here.');
        }

        if ($user->role === 'admin') {
            abort(403, 'Administrator accounts cannot be moderated from this queue.');
        }

        if ($user->role === 'staff' && ! $request->user()->isAdmin()) {
            abort(403, 'Only a full administrator can approve or suspend staff accounts.');
        }

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'suspend'])],
        ]);

        $beforeStatus = $user->status;
        $user->update([
            'status' => $validated['decision'] === 'approve' ? 'active' : 'suspended',
        ]);

        AdminAudit::log($request, 'user.moderation.' . $validated['decision'], 'user', $user->id, [
            'before_status' => $beforeStatus,
            'after_status' => $user->status,
            'target_email' => $user->email,
            'target_role' => $user->role,
        ]);

        return back()->with(
            'success',
            $validated['decision'] === 'approve'
                ? "{$user->name}'s account is now active. They can sign in."
                : "{$user->name}'s account has been suspended and cannot sign in."
        );
    }
}
