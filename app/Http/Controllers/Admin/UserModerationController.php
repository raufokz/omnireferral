<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        if (in_array($user->role, ['admin', 'staff'], true)) {
            abort(403, 'Staff accounts cannot be moderated from this queue.');
        }

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'suspend'])],
        ]);

        $user->update([
            'status' => $validated['decision'] === 'approve' ? 'active' : 'suspended',
        ]);

        return back()->with(
            'success',
            $validated['decision'] === 'approve'
                ? "{$user->name}'s account is now active. They can sign in."
                : "{$user->name}'s account has been suspended and cannot sign in."
        );
    }
}
