<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthLog;
use App\Services\PasswordSetupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordSetupController extends Controller
{
    public function __construct(
        private readonly PasswordSetupService $setupService,
    ) {}

    /** Show the "set your password" form for a valid setup token. */
    public function show(string $token): View|RedirectResponse
    {
        $record = $this->setupService->findValid($token);

        if (! $record) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'This password setup link is invalid or has expired. Request a new reset link below.']);
        }

        return view('pages.auth.password-setup', [
            'token' => $token,
            'email' => $record->user?->email,
        ]);
    }

    /** Consume the token and set the password, then sign the user in. */
    public function store(Request $request, string $token): RedirectResponse
    {
        $record = $this->setupService->findValid($token);

        if (! $record) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'This password setup link is invalid or has expired. Request a new reset link below.']);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $this->setupService->consume($record, $request->string('password')->value());

        AuthLog::record('password_set', 'success', [
            'user_id' => $user->id, 'email' => $user->email, 'request' => $request,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->to($user->dashboardRoute())
            ->with('success', 'Your password has been set. Welcome to your OmniReferral portal!');
    }
}
