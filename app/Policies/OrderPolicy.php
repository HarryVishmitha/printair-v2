<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $this->inSameWorkingGroup($user, $order->working_group_id);
    }

    public function confirm(User $user, Order $order): bool
    {
        if (! $this->inSameWorkingGroup($user, $order->working_group_id)) return false;

        // confirm only from draft
        return $order->status === 'draft'
            && $user->can('manage-orderFlow');
    }

    public function changeStatus(User $user, Order $order): bool
    {
        if (! $this->inSameWorkingGroup($user, $order->working_group_id)) return false;

        // Production and delivery statuses should be restricted
        return $user->can('manage-orderFlow');
    }

    public function createInvoice(User $user, Order $order): bool
    {
        if (! $this->inSameWorkingGroup($user, $order->working_group_id)) return false;

        // Typically only finance/admin
        return $user->can('manage-orderFlow');
    }

    private function inSameWorkingGroup(User $user, int $wgId): bool
    {
        return (int) $user->working_group_id === (int) $wgId || $user->isAdminOrSuperAdmin();
    }
}
