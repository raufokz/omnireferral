<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthLog;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginRequest',
    type: 'object',
    required: ['role', 'email', 'password'],
    properties: [
        new OA\Property(property: 'role', type: 'string', enum: ['agent', 'admin', 'staff'], description: 'Workspace role'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'password', type: 'string', format: 'password'),
        new OA\Property(property: 'remember', type: 'boolean', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'ForgotPasswordRequest',
    type: 'object',
    required: ['email'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email'),
    ]
)]
#[OA\Schema(
    schema: 'ResetPasswordRequest',
    type: 'object',
    required: ['token', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'token', type: 'string', description: 'Password reset token from email'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
    ]
)]
#[OA\Schema(
    schema: 'User',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'phone', type: 'string', nullable: true),
        new OA\Property(property: 'role', type: 'string', enum: ['agent', 'admin', 'staff', 'buyer', 'seller']),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'pending', 'suspended']),
        new OA\Property(property: 'ghl_contact_id', type: 'string', nullable: true),
        new OA\Property(property: 'onboarding_completed_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class AuthController extends Controller
{
    #[OA\Get(
        path: '/login',
        tags: ['Auth'],
        summary: 'Show login form',
        responses: [
            new OA\Response(response: 200, description: 'Login page')
        ]
    )]
    public function showLogin(Request $request): View
    {
        $workspaces = $this->loginWorkspaces();

        return view('pages.login', [
            'workspaces' => $workspaces,
            'selectedWorkspace' => $this->selectedWorkspace($request, $workspaces),
        ]);
    }

    #[OA\Post(
        path: '/login',
        tags: ['Auth'],
        summary: 'Authenticate user and start session',
        description: 'Logs in a user with workspace role, email, and password. Starts a web session.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
        ),
        responses: [
            new OA\Response(response: 302, description: 'Redirect to dashboard on success'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
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

                AuthLog::record('login_blocked_pending', 'failure', [
                    'user_id' => $user->id, 'email' => $user->email, 'request' => $request,
                ]);

                return back()->withErrors([
                    'email' => 'Your portal access is not active yet. Your profile may be listed publicly, but signing in requires an active plan and completed GoHighLevel onboarding.',
                ])->onlyInput('email', 'role');
            }

            if ($user && $user->status === 'suspended') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                AuthLog::record('login_blocked_suspended', 'failure', [
                    'user_id' => $user->id, 'email' => $user->email, 'request' => $request,
                ]);

                return back()->withErrors([
                    'email' => 'This account is not active. Contact OmniReferral support if you believe this is a mistake.',
                ])->onlyInput('email', 'role');
            }

            if ($user && $user->role !== $credentials['role']) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                AuthLog::record('login_role_mismatch', 'failure', [
                    'user_id' => $user->id, 'email' => $user->email, 'request' => $request,
                    'context' => ['chosen_role' => $credentials['role'], 'actual_role' => $user->role],
                ]);

                return back()->withErrors([
                    'role' => 'That account belongs to the ' . $user->roleLabel() . ' workspace. Please choose the correct role and try again.',
                ])->onlyInput('email', 'role');
            }

            AuthLog::record('login_success', 'success', [
                'user_id' => $user->id, 'email' => $user->email, 'request' => $request,
            ]);

            return $this->redirectBasedOnRole($user);
        }

        AuthLog::record('login_failed', 'failure', [
            'user_id' => $user?->id, 'email' => $credentials['email'], 'request' => $request,
            'error_message' => 'No matching account or wrong password.',
        ]);

        return back()->withErrors([
            'email' => 'We could not find a matching account with those details.',
        ])->onlyInput('email', 'role');
    }

    #[OA\Get(
        path: '/forgot-password',
        tags: ['Auth'],
        summary: 'Show forgot password form',
        responses: [
            new OA\Response(response: 200, description: 'Forgot password page')
        ]
    )]
    public function showForgotPassword(): View
    {
        return view('pages.auth.forgot-password');
    }

    #[OA\Post(
        path: '/forgot-password',
        tags: ['Auth'],
        summary: 'Send password reset link',
        description: 'Sends a password reset email to the given address.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ForgotPasswordRequest')
        ),
        responses: [
            new OA\Response(response: 302, description: 'Redirect with success or error message')
        ]
    )]
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Oops, looks like you missed your email!',
        ]);

        $email = $request->string('email')->value();

        AuthLog::record('forgot_password_requested', 'info', ['email' => $email, 'request' => $request]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            // A mailer/SMTP failure here would otherwise surface as a raw 500.
            Log::error('Forgot-password email failed to send.', ['email' => $email, 'error' => $e->getMessage()]);
            EmailLog::failed($email, $e->getMessage(), [
                'event_type' => 'password_reset_link',
                'subject'    => 'Reset Password Notification',
            ]);

            return back()->withErrors([
                'email' => 'We could not send the reset email right now. Please try again shortly or contact support.',
            ]);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'A password reset link has been sent. If mail is set to log in local development, check your Laravel log file.')
            : back()->withErrors(['email' => __($status)]);
    }

    #[OA\Get(
        path: '/reset-password/{token}',
        tags: ['Auth'],
        summary: 'Show password reset form',
        parameters: [
            new OA\Parameter(name: 'token', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'email', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'email'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Reset password page')
        ]
    )]
    public function showResetPassword(Request $request, string $token): View
    {
        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->value(),
        ]);
    }

    #[OA\Post(
        path: '/reset-password',
        tags: ['Auth'],
        summary: 'Reset password with token',
        description: 'Consumes a password reset token and sets a new password.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ResetPasswordRequest')
        ),
        responses: [
            new OA\Response(response: 302, description: 'Redirect to login on success')
        ]
    )]
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
            function (User $user, string $password) use ($request) {
                $user->forceFill([
                    'password'             => $password,
                    'remember_token'       => Str::random(60),
                    'must_reset_password'  => false,
                    'password_set_at'      => now(),
                ])->save();

                event(new PasswordReset($user));

                AuthLog::record('password_reset', 'success', [
                    'user_id' => $user->id, 'email' => $user->email, 'request' => $request,
                ]);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Your password has been updated. You can sign in now.')
            : back()->withErrors(['email' => __($status)]);
    }

    #[OA\Post(
        path: '/logout',
        tags: ['Auth'],
        summary: 'Log out and invalidate session',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 302, description: 'Redirect to home')
        ]
    )]
    public function logout(Request $request): RedirectResponse
    {
        if ($user = $request->user()) {
            AuthLog::record('logout', 'info', ['user_id' => $user->id, 'email' => $user->email, 'request' => $request]);
        }

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
