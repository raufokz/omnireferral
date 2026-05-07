<?php

namespace App\Support;

use App\Models\User;

/**
 * Temporary compatibility layer until spatie/laravel-permission is installed.
 *
 * - Keeps the application bootable even when the Spatie package isn't present yet.
 * - Provides minimal role checks based on the existing `users.role` column.
 *
 * Once the project runtime is upgraded (PHP 8.4+) and Composer can install Spatie,
 * swap this trait out for `Spatie\\Permission\\Traits\\HasRoles`.
 */
trait HasRolesShim
{
    public function hasRole(string $role, ?string $guard = null): bool
    {
        /** @var User $this */
        $role = strtolower(trim($role));

        if ($role === 'super admin' || $role === 'super_admin') {
            return method_exists($this, 'isSuperAdmin') ? $this->isSuperAdmin() : false;
        }

        return strtolower((string) ($this->role ?? '')) === $role;
    }
}

