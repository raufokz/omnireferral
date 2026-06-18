<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class PasswordProvisioningService
{
    /**
     * Provision a temporary password onto $user if appropriate.
     *
     * Returns the plaintext password (for emailing) or null when the user
     * already has a real password and should not be overwritten.
     *
     * Rule: if the user has a password and has already reset it from the temp
     * one (must_reset_password = false), we leave it alone.
     */
    public function provision(User $user): ?string
    {
        if ($user->exists && $user->password && ! $user->must_reset_password) {
            return null;
        }

        $password = Str::password(16, true, true, false, false);
        $user->password        = $password; // hashed by User model cast
        $user->must_reset_password = true;

        return $password;
    }

    /**
     * Always generate and set a fresh temporary password regardless of existing state.
     * Used when an explicit reset is configured.
     */
    public function forceProvision(User $user): string
    {
        $password = Str::password(16, true, true, false, false);
        $user->password        = $password;
        $user->must_reset_password = true;

        return $password;
    }
}
