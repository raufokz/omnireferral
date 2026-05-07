<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isStaff();
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->isStaff();
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->isAdmin() && (int) $actor->id !== (int) $target->id;
    }

    public function export(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function suspend(User $actor, User $target): bool
    {
        return $this->update($actor, $target);
    }

    public function delete(User $actor, User $target): bool
    {
        return $this->update($actor, $target);
    }

    public function moderate(User $actor, User $target): bool
    {
        if (! $actor->isStaff()) {
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
        return $actor->isAdmin();
    }
}

