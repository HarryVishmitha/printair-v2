<?php

namespace App\Policies;

use App\Models\Estimate;
use App\Models\User;

class EstimatePolicy
{
    public function view(User $user, Estimate $estimate): bool
    {
        return $this->inSameWorkingGroup($user, $estimate->working_group_id);
    }

    public function create(User $user): bool
    {
        // Uses existing Gate ability from AppServiceProvider.
        return $user->can('manage-orderFlow');
    }

    public function update(User $user, Estimate $estimate): bool
    {
        if (! $this->inSameWorkingGroup($user, $estimate->working_group_id)) return false;

        // Locked: no edits
        if ($estimate->locked_at) return false;

        // Allowed update statuses
        return in_array($estimate->status, ['draft', 'viewed'], true)
            && $user->can('manage-orderFlow');
    }

    public function send(User $user, Estimate $estimate): bool
    {
        if (! $this->inSameWorkingGroup($user, $estimate->working_group_id)) return false;
        if ($estimate->locked_at) return false;

        return in_array($estimate->status, ['draft', 'viewed'], true)
            && $user->can('manage-orderFlow');
    }

    public function resend(User $user, Estimate $estimate): bool
    {
        if (! $this->inSameWorkingGroup($user, $estimate->working_group_id)) return false;

        return in_array($estimate->status, ['sent', 'viewed', 'accepted', 'converted'], true)
            && $user->can('manage-orderFlow');
    }

    public function accept(User $user, Estimate $estimate): bool
    {
        if (! $this->inSameWorkingGroup($user, $estimate->working_group_id)) return false;

        return in_array($estimate->status, ['sent', 'viewed'], true)
            && $user->can('manage-orderFlow');
    }

    public function reject(User $user, Estimate $estimate): bool
    {
        if (! $this->inSameWorkingGroup($user, $estimate->working_group_id)) return false;

        return in_array($estimate->status, ['sent', 'viewed'], true)
            && $user->can('manage-orderFlow');
    }

    public function convertToOrder(User $user, Estimate $estimate): bool
    {
        if (! $this->inSameWorkingGroup($user, $estimate->working_group_id)) return false;

        return $estimate->status === 'accepted'
            && $user->can('manage-orderFlow');
    }

    private function inSameWorkingGroup(User $user, int $wgId): bool
    {
        // Replace with your exact WG-scope logic if you have multi-WG access.
        return (int) $user->working_group_id === (int) $wgId || $user->isAdminOrSuperAdmin();
    }
}
