<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SyncUserToGoHighLevel;
use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('pages.login');
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
            'email.required' => 'Oops, looks like you missed your email!',
            'password.required' => 'Enter your password to continue.',
        ]);

        if (Auth::attempt($request->only('email', 'password'), (bool) $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

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

    public function showRegister(): View
    {
        return view('pages.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:buyer,seller,agent'],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_image' => ['nullable', 'image', 'max:4096'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'zip_code' => ['nullable', 'string', 'max:10'],
        ], [
            'name.required' => 'Tell us your name so we can personalize your setup.',
            'email.required' => 'Oops, looks like you missed your email!',
            'email.unique' => 'That email is already connected to an OmniReferral account.',
            'password.min' => 'Use at least 8 characters so your account stays secure.',
            'profile_image.image' => 'Please upload a valid profile photo.',
        ]);

        $avatarPath = $request->hasFile('profile_image')
            ? $request->file('profile_image')->store('avatars', 'public')
            : null;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'status' => 'active',
            'avatar' => $avatarPath,
            'affiliate_code' => strtoupper(Str::random(8)),
        ]);

        if ($user->isAgent()) {
            RealtorProfile::create([
                'user_id' => $user->id,
                'slug' => Str::slug($user->name . '-' . Str::lower(Str::random(6))),
                'brokerage_name' => 'OmniReferral Partner',
                'city' => $request->input('city', 'Dallas'),
                'state' => strtoupper($request->input('state', 'TX')),
                'zip_code' => $request->input('zip_code', '75201'),
                'specialties' => 'Buyer Representation, Seller Strategy, Referral Conversion',
                'bio' => 'New OmniReferral partner profile created through the onboarding funnel.',
                'headshot' => $avatarPath ? 'storage/' . $avatarPath : 'images/realtors/3.png',
            ]);
        }

        if ($request->hasCookie('omnireferral_affiliate')) {
            $affiliateProfile = \App\Models\AffiliateProfile::where('referral_code', $request->cookie('omnireferral_affiliate'))->first();
            if ($affiliateProfile) {
                $user->update(['referred_by_user_id' => $affiliateProfile->user_id]);
                $affiliateProfile->increment('conversion_count');
            }
        }

        SyncUserToGoHighLevel::dispatch($user->id);

        Auth::login($user);

        return redirect()
            ->route('onboarding', $user->role)
            ->with('success', 'Welcome aboard! Your account is ready and onboarding is the next step.');
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
                    'password' => Hash::make($password),
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
}
