<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        abort_unless($user, 401);

        return view('pages.account.profile', [
            'user' => $user,
            'meta' => [
                'title' => 'Profile & Account | OmniReferral',
                'description' => 'Update your personal information, profile photo, contact details, and security settings in one place.',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $changingPassword = $request->filled('password');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:120'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'avatar' => ['nullable', 'image', 'max:3072'],
            'social_facebook_url' => ['nullable', 'url', 'max:255'],
            'social_linkedin_url' => ['nullable', 'url', 'max:255'],
            'notify_email' => ['nullable', Rule::in(['0', '1'])],
            'notify_marketing' => ['nullable', Rule::in(['0', '1'])],
            'two_factor_enabled' => ['nullable', Rule::in(['0', '1'])],
        ];

        if ($changingPassword) {
            $rules['current_password'] = ['required', 'string'];
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules, [
            'password.confirmed' => 'Your new password confirmation does not match.',
            'email.unique' => 'That email address is already in use by another account.',
        ]);

        if ($changingPassword) {
            if (! $user->passwordMatches($validated['current_password'])) {
                return back()
                    ->withInput()
                    ->withErrors(['current_password' => 'Your current password is not correct.']);
            }
        }

        $payload = [
            'name' => $validated['name'],
            'display_name' => $validated['display_name'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address_line_1' => $validated['address_line_1'] ?? null,
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'zip_code' => $validated['zip_code'] ?? null,
            'social_facebook_url' => $validated['social_facebook_url'] ?? null,
            'social_linkedin_url' => $validated['social_linkedin_url'] ?? null,
            'notify_email' => ($validated['notify_email'] ?? '1') === '1',
            'notify_marketing' => ($validated['notify_marketing'] ?? '0') === '1',
            'two_factor_enabled' => ($validated['two_factor_enabled'] ?? '0') === '1',
        ];

        if ($request->hasFile('avatar')) {
            $this->deleteStoredAvatar($user->avatar);
            $payload['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($changingPassword) {
            $payload['password'] = $validated['password'];
            $payload['must_reset_password'] = false;
            $payload['password_set_at'] = now();
        }

        $user->fill($payload);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $message = $changingPassword
            ? 'Profile and password updated successfully.'
            : 'Profile updated successfully.';

        return back()->with('success', $message);
    }

    protected function deleteStoredAvatar(?string $path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/storage/', 'storage/', 'images/'])) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
