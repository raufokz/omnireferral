<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function show(Request $request): View
    {
        return view('pages.account.security', [
            'meta' => [
                'title' => 'Security Settings | OmniReferral',
                'description' => 'Update your password and secure your OmniReferral account.',
            ],
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.confirmed' => 'Your password confirmation does not match yet.',
        ]);

        $user = $request->user();
        abort_unless($user, 401);

        if (! $user->passwordMatches($validated['current_password'])) {
            return back()->withErrors(['current_password' => 'Your current password is not correct.']);
        }

        $user->forceFill([
            'password' => $validated['password'],
            'must_reset_password' => false,
            'password_set_at' => now(),
        ])->save();

        return redirect()
            ->to($user->dashboardRoute())
            ->with('success', 'Password updated successfully.');
    }
}
