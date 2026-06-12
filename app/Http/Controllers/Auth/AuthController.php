<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SyncUserToGoHighLevel;
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
            'role' => ['required', 'string', 'in:buyer,seller,agent,admin,staff'],
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
                    'email' => 'Your account is waiting for administrator approval. You will be able to sign in once an admin activates your workspace.',
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

    public function showRegister(Request $request): View
    {
        $workspaces = $this->registrationWorkspaces();

        return view('pages.register', [
            'workspaces' => $workspaces,
            'selectedWorkspace' => $this->selectedWorkspace($request, $workspaces),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:buyer,seller'],
            'phone' => ['required', 'string', 'max:20'],
            'profile_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],
            'zip_code' => ['required', 'string', 'max:10'],
            'terms_accepted' => ['accepted'],
            'communication_accepted' => ['accepted'],
        ], [
            'role.required' => 'Choose the workspace you want us to create.',
            'role.in' => 'Choose a valid signup workspace.',
            'name.required' => 'Tell us your name so we can personalize your setup.',
            'email.required' => 'Oops, looks like you missed your email!',
            'email.unique' => 'That email is already connected to an OmniReferral account.',
            'password.min' => 'Use at least 8 characters so your account stays secure.',
            'password.confirmed' => 'Your password confirmation does not match yet.',
            'phone.required' => 'Add your phone number so your account is ready for follow-up.',
            'profile_image.required' => 'Upload a profile image so your account looks complete from day one.',
            'profile_image.image' => 'Please upload a valid profile photo.',
            'address_line_1.required' => 'Add your address so we can complete your profile.',
            'state.size' => 'Use the 2-letter state code, like TX or FL.',
            'terms_accepted.accepted' => 'Please accept the Terms and Privacy Policy before continuing.',
            'communication_accepted.accepted' => 'Please agree to onboarding communication so we can activate your account.',
        ]);

        $this->rememberSelectedWorkspace($request, $request->string('role')->value());

        $avatarPath = $request->hasFile('profile_image')
            ? $request->file('profile_image')->store('avatars', 'public')
            : null;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'phone' => $request->phone,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'city' => $request->city,
            'state' => strtoupper($request->state),
            'zip_code' => $request->zip_code,
            'status' => 'pending',
            'avatar' => $avatarPath,
            'affiliate_code' => strtoupper(Str::random(8)),
        ]);

        if ($request->hasCookie('omnireferral_affiliate')) {
            $affiliateProfile = \App\Models\AffiliateProfile::where('referral_code', $request->cookie('omnireferral_affiliate'))->first();
            if ($affiliateProfile) {
                $user->update(['referred_by_user_id' => $affiliateProfile->user_id]);
                $affiliateProfile->increment('conversion_count');
            }
        }

        SyncUserToGoHighLevel::dispatch($user->id);

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Thanks for registering. An administrator will review and activate your account. You can sign in from this page once your workspace is approved.'
            );
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
                'value' => 'buyer',
                'label' => 'Buyer',
                'description' => 'Track favorites, saved homes, and enquiries.',
                'icon' => 'buyer',
            ],
            [
                'value' => 'seller',
                'label' => 'Seller',
                'description' => 'Review your properties, enquiries, and performance.',
                'icon' => 'seller',
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

    private function registrationWorkspaces(): array
    {
        return [
            [
                'value' => 'buyer',
                'label' => 'Buyer',
                'description' => 'Save homes and send property enquiries faster.',
                'icon' => 'buyer',
            ],
            [
                'value' => 'seller',
                'label' => 'Seller',
                'description' => 'List properties and monitor inbound buyer activity.',
                'icon' => 'seller',
            ],
        ];
    }

    private function selectedWorkspace(Request $request, array $workspaces): string
    {
        $workspaceValues = array_column($workspaces, 'value');
        $selected = $request->old('role', $request->session()->get('selected_workspace'));

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
