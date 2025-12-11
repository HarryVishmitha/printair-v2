<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkingGroup;

class WorkingGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage-working-groups')
            || $user->hasRole('admin');
    }

    public function view(User $user, WorkingGroup $group): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage-working-groups')
            || $user->hasRole('admin');
    }

    public function update(User $user, WorkingGroup $group): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, WorkingGroup $group): bool
    {
        // You can’t delete the public group
        if ($group->slug === WorkingGroup::PUBLIC_SLUG) {
            return false;
        }

        return $this->create($user);
    }
}
