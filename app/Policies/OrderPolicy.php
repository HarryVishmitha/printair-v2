<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function create(User $user): bool
    {
        return $user->can('manage-orderFlow');
    }

    public function view(User $user, Order $order): bool
    {
        return $this->inSameWorkingGroup($user, $order->working_group_id);
    }

    public function update(User $user, Order $order): bool
    {
        if (! $this->inSameWorkingGroup($user, $order->working_group_id)) return false;

        if (! $user->can('manage-orderFlow')) return false;

        // Order editing is allowed until an invoice is issued (draft invoices don't block).
        $hasIssuedInvoice = $order->invoices()
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['draft', 'void', 'refunded'])
            ->exists();

        return ! $hasIssuedInvoice;
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
