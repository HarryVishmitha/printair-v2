<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $this->inSameWorkingGroup($user, $payment->working_group_id);
    }

    public function create(User $user): bool
    {
        return $user->can('manage-orderFlow');
    }

    public function confirm(User $user, Payment $payment): bool
    {
        if (! $this->inSameWorkingGroup($user, $payment->working_group_id)) return false;

        return $user->can('manage-orderFlow');
    }

    public function allocate(User $user, Payment $payment, Invoice $invoice): bool
    {
        // Must be same WG on both sides
        if (! $this->inSameWorkingGroup($user, $payment->working_group_id)) return false;
        if ((int) $payment->working_group_id !== (int) $invoice->working_group_id) return false;

        return $user->can('manage-orderFlow');
    }

    private function inSameWorkingGroup(User $user, int $wgId): bool
    {
        return (int) $user->working_group_id === (int) $wgId || $user->isAdminOrSuperAdmin();
    }
}
