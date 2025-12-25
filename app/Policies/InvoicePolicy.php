<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        return $this->inSameWorkingGroup($user, $invoice->working_group_id);
    }

    public function issue(User $user, Invoice $invoice): bool
    {
        if (! $this->inSameWorkingGroup($user, $invoice->working_group_id)) return false;

        // issue only from draft
        return $invoice->status === 'draft'
            && $user->can('manage-orderFlow');
    }

    public function void(User $user, Invoice $invoice): bool
    {
        if (! $this->inSameWorkingGroup($user, $invoice->working_group_id)) return false;

        // finance/admin only
        return $user->can('manage-orderFlow');
    }

    private function inSameWorkingGroup(User $user, int $wgId): bool
    {
        return (int) $user->working_group_id === (int) $wgId || $user->isAdminOrSuperAdmin();
    }
}
