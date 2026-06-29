<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    public function show(): View
    {
        return view('pages.auth.password-change', [
            'meta' => [
                'title' => 'Change Password | OmniReferral',
                'description' => 'Set a new password for your OmniReferral account.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($user->password_set_at) {
            $rules['current_password'] = ['required'];
        }

        $validated = $request->validate($rules, [
            'password.confirmed' => 'Your password confirmation does not match yet.',
        ]);

        if (isset($validated['current_password']) && ! $user->passwordMatches($validated['current_password'])) {
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
