<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        $workspaces = $this->loginWorkspaces();

        return view('pages.login', [
            'workspaces' => $workspaces,
            'selectedWorkspace' => $this->selectedWorkspace($request, $workspaces),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'role' => ['required', 'string', 'in:agent,admin,staff'],
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'role.required' => 'Choose the workspace you want to enter.',
            'role.in' => 'Choose a valid OmniReferral workspace.',
            'email.required' => 'Oops, looks like you missed your email!',
            'password.required' => 'Enter your password to continue.',
        ]);

        $this->rememberSelectedWorkspace($request, $credentials['role']);

        $authenticated = false;
        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->passwordMatches((string) $credentials['password'])) {
            Auth::login($user, (bool) $request->boolean('remember'));
            $authenticated = true;
        }

        if ($authenticated) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user && $user->status === 'pending') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your portal access is not active yet. Your profile may be listed publicly, but signing in requires an active plan and completed GoHighLevel onboarding.',
                ])->onlyInput('email', 'role');
            }

            if ($user && $user->status === 'suspended') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This account is not active. Contact OmniReferral support if you believe this is a mistake.',
                ])->onlyInput('email', 'role');
            }

            if ($user && $user->role !== $credentials['role']) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'role' => 'That account belongs to the ' . $user->roleLabel() . ' workspace. Please choose the correct role and try again.',
                ])->onlyInput('email', 'role');
            }

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'We could not find a matching account with those details.',
        ])->onlyInput('email', 'role');
    }

    public function showForgotPassword(): View
    {
        return view('pages.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Oops, looks like you missed your email!',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'A password reset link has been sent. If mail is set to log in local development, check your Laravel log file.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->value(),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'email.required' => 'Oops, looks like you missed your email!',
            'password.confirmed' => 'Your password confirmation does not match yet.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Your password has been updated. You can sign in now.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function redirectBasedOnRole(User $user): RedirectResponse
    {
        return redirect()->to($user->dashboardRoute());
    }

    private function loginWorkspaces(): array
    {
        return [
            [
                'value' => 'agent',
                'label' => 'Agent',
                'description' => 'Manage listings, leads, referrals, and revenue.',
                'icon' => 'agent',
            ],
            [
                'value' => 'admin',
                'label' => 'Admin',
                'description' => 'Control users, properties, revenue, and analytics.',
                'icon' => 'admin',
            ],
            [
                'value' => 'staff',
                'label' => 'Staff',
                'description' => 'Handle assigned tasks, support, and operations.',
                'icon' => 'staff',
            ],
        ];
    }

    private function selectedWorkspace(Request $request, array $workspaces): string
    {
        $workspaceValues = array_column($workspaces, 'value');
        $selected = $request->old('role');

        if (! is_string($selected) || $selected === '') {
            $selected = $request->query('role')
                ?? $request->query('workspace')
                ?? $request->session()->get('selected_workspace');
        }

        if (is_string($selected) && in_array($selected, $workspaceValues, true)) {
            return $selected;
        }

        return count($workspaceValues) === 1 ? (string) $workspaceValues[0] : '';
    }

    private function rememberSelectedWorkspace(Request $request, string $workspace): void
    {
        if ($workspace !== '') {
            $request->session()->put('selected_workspace', $workspace);
        }
    }
}
