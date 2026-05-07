<?php

namespace App\Policies;

use App\Models\User;
use App\Support\AuthorizesWithPermissions;

class UserPolicy
{
    use AuthorizesWithPermissions;

    public function viewAny(User $actor): bool
    {
        return $actor->can('users.view') || $actor->isStaff();
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->can('users.view') || $actor->isStaff();
    }

    public function update(User $actor, User $target): bool
    {
        return ($actor->can('users.update') || $actor->isAdmin())
            && (int) $actor->id !== (int) $target->id;
    }

    public function export(User $actor): bool
    {
        return $actor->can('users.export') || $actor->isAdmin();
    }

    public function suspend(User $actor, User $target): bool
    {
        return ($actor->can('users.suspend') || $actor->isAdmin())
            && $this->moderate($actor, $target);
    }

    public function delete(User $actor, User $target): bool
    {
        return ($actor->can('users.delete') || $actor->isAdmin())
            && $this->moderate($actor, $target);
    }

    public function moderate(User $actor, User $target): bool
    {
        if (! $actor->isStaff() && ! $actor->can('users.update')) {
            return false;
        }

        if ((int) $actor->id === (int) $target->id) {
            return false;
        }

        if ($target->role === 'admin') {
            return false;
        }

        if ($target->role === 'staff' && ! $actor->isAdmin()) {
            return false;
        }

        return true;
    }

    public function viewAuditLog(User $actor): bool
    {
        return $actor->can('audit.view') || $actor->isAdmin();
    }
}

