<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        try {
            return $this->isAdminOrManager($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('CustomerPolicy@viewAny', $user, null, $e);
            return false;
        }
    }

    public function view(User $user, Customer $customer): bool
    {
        try {
            // Admin area only: restrict customer visibility
            if (! $this->isAdminOrManager($user)) {
                return false;
            }

            // Optional future-proof: if you want to restrict staff by WG
            // return $this->isAdmin($user) || $user->working_group_id === $customer->working_group_id;

            return true;
        } catch (\Throwable $e) {
            $this->logPolicyError('CustomerPolicy@view', $user, $customer, $e);
            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            // Creating customers affects billing pipeline
            return $this->isAdminOrManager($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('CustomerPolicy@create', $user, null, $e);
            return false;
        }
    }

    public function update(User $user, Customer $customer): bool
    {
        try {
            // Updating customer PII => admin/manager only
            return $this->isAdminOrManager($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('CustomerPolicy@update', $user, $customer, $e);
            return false;
        }
    }

    public function delete(User $user, Customer $customer): bool
    {
        try {
            if (! $this->isAdmin($user)) {
                // Keep delete stricter than update (safer)
                return false;
            }

            // Fool-proof: if customer is linked to an actual user account, do not delete
            // (soft-delete could still be allowed later, but avoid breaking auth history)
            if (! is_null($customer->user_id)) {
                return false;
            }

            // Future-proof: block deleting customers in restricted groups (if used)
            if ($customer->relationLoaded('workingGroup') && $customer->workingGroup) {
                if ((bool) $customer->workingGroup->is_restricted === true) {
                    return false;
                }
            }

            return true;
        } catch (\Throwable $e) {
            $this->logPolicyError('CustomerPolicy@delete', $user, $customer, $e);
            return false;
        }
    }

    /**
     * Future-proof: customer merge action (common in printing businesses).
     */
    public function merge(User $user): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('CustomerPolicy@merge', $user, null, $e);
            return false;
        }
    }

    // -------------------------
    // helpers
    // -------------------------

    private function isAdminOrManager(User $user): bool
    {
        $role = $user->relationLoaded('role') ? $user->role : $user->role()->first();
        if (! $role) {
            return false;
        }

        if ((bool) ($role->is_staff ?? false) !== true) {
            return false;
        }

        $name = strtolower((string) ($role->name ?? ''));
        return in_array($name, ['admin', 'super_admin', 'manager'], true);
    }

    private function isAdmin(User $user): bool
    {
        $role = $user->relationLoaded('role') ? $user->role : $user->role()->first();
        if (! $role) {
            return false;
        }

        $name = strtolower((string) ($role->name ?? ''));
        return in_array($name, ['admin', 'super_admin'], true);
    }

    private function logPolicyError(string $where, ?User $user, ?Customer $customer, \Throwable $e): void
    {
        Log::error($where.' policy error', [
            'user_id' => $user?->id,
            'customer_id' => $customer?->id,
            'customer_user_id' => $customer?->user_id,
            'working_group_id' => $customer?->working_group_id,
            'error' => $e->getMessage(),
        ]);
    }
}

