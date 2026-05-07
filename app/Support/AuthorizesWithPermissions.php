<?php

namespace App\Support;

use App\Models\User;

/**
 * Bridges legacy role helpers (isStaff, isAgent, …) with Spatie permissions during rollout.
 * Prefer replacing callers with $user->can('permission.name') over time.
 */
trait AuthorizesWithPermissions
{
    protected function canStaff(?User $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can($permission) || $user->isStaff();
    }

    protected function canAdmin(?User $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can($permission) || $user->isAdmin();
    }
}
