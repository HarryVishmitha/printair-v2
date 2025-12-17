<?php

namespace App\Policies;

use App\Models\ProductVariantAvailabilityOverride;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProductVariantAvailabilityOverridePolicy
{
    public function viewAny(User $user): bool
    {
        try {
            // Staff can view the availability matrix in admin
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductVariantAvailabilityOverridePolicy@viewAny', $user, null, $e);
            return false;
        }
    }

    public function view(User $user, ProductVariantAvailabilityOverride $override): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductVariantAvailabilityOverridePolicy@view', $user, $override, $e);
            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            // Toggling product variants is catalog control => admin-only
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductVariantAvailabilityOverridePolicy@create', $user, null, $e);
            return false;
        }
    }

    public function update(User $user, ProductVariantAvailabilityOverride $override): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductVariantAvailabilityOverridePolicy@update', $user, $override, $e);
            return false;
        }
    }

    public function delete(User $user, ProductVariantAvailabilityOverride $override): bool
    {
        try {
            // Deleting an override means falling back to public behavior.
            // Still admin-only because it changes what a WG can order.
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductVariantAvailabilityOverridePolicy@delete', $user, $override, $e);
            return false;
        }
    }

    /**
     * Future-proof: your UI will likely have a grid of toggles.
     * Bulk update should be admin-only.
     */
    public function bulkUpdate(User $user): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductVariantAvailabilityOverridePolicy@bulkUpdate', $user, null, $e);
            return false;
        }
    }

    // -------------------------
    // helpers
    // -------------------------

    private function isStaff(User $user): bool
    {
        $role = $user->relationLoaded('role') ? $user->role : $user->role()->first();
        if (! $role) {
            return false;
        }

        if ((bool) ($role->is_staff ?? false) === true) {
            return true;
        }

        $name = strtolower((string) ($role->name ?? ''));
        return in_array($name, ['admin', 'super_admin', 'staff', 'manager'], true);
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

    private function logPolicyError(string $where, ?User $user, ?ProductVariantAvailabilityOverride $override, \Throwable $e): void
    {
        Log::error($where.' policy error', [
            'user_id' => $user?->id,
            'override_id' => $override?->id,
            'variant_set_id' => $override?->variant_set_id,
            'working_group_id' => $override?->working_group_id,
            'error' => $e->getMessage(),
        ]);
    }
}

