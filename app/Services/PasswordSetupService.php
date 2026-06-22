<?php

namespace App\Services;

use App\Models\PasswordSetupToken;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Issues and consumes secure, one-time, 24-hour password-setup tokens.
 *
 * The plaintext token is returned once (for the email link) and never stored;
 * only its SHA-256 digest is persisted so links can be looked up without ever
 * holding a usable secret at rest.
 */
class PasswordSetupService
{
    public const TTL_HOURS = 24;

    /**
     * Generate a fresh setup token for the user and return the plaintext value.
     * Any previously-issued unused tokens for the user are invalidated.
     */
    public function generate(User $user, string $via = 'ghl_onboarding'): string
    {
        PasswordSetupToken::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $plain = Str::random(64);

        PasswordSetupToken::create([
            'user_id'     => $user->id,
            'token'       => $this->hash($plain),
            'created_via' => $via,
            'expires_at'  => now()->addHours(self::TTL_HOURS),
        ]);

        return $plain;
    }

    /** Build the public, clickable setup URL for a plaintext token. */
    public function url(string $plain): string
    {
        return route('password.setup', ['token' => $plain]);
    }

    /** Resolve a plaintext token to a still-valid record, or null. */
    public function findValid(string $plain): ?PasswordSetupToken
    {
        return PasswordSetupToken::with('user')
            ->where('token', $this->hash($plain))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Consume the token: set the user's password, clear the reset flag, and
     * burn this token plus any other outstanding tokens for the user.
     */
    public function consume(PasswordSetupToken $token, string $newPassword): User
    {
        $user = $token->user;

        $user->password            = $newPassword; // hashed by the User model cast
        $user->must_reset_password = false;
        $user->password_set_at     = now();
        if (is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
        }
        $user->save();

        $token->used_at = now();
        $token->save();

        // Burn any other outstanding tokens (one-time use, defence in depth).
        PasswordSetupToken::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        return $user;
    }

    private function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }
}
