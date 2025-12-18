<?php

namespace App\Policies;

use App\Models\ProductPricing;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProductPricingPolicy
{
    public function viewAny(User $user): bool
    {
        try {
            // Viewing pricing tables is still sensitive (profit margins etc.)
            // Keep admin-only by default.
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPricingPolicy@viewAny', $user, null, $e);

            return false;
        }
    }

    public function view(User $user, ProductPricing $pricing): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPricingPolicy@view', $user, $pricing, $e);

            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            // Creating public / WG price lists
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPricingPolicy@create', $user, null, $e);

            return false;
        }
    }

    public function update(User $user, ProductPricing $pricing): bool
    {
        try {
            // Any pricing update is admin-only
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPricingPolicy@update', $user, $pricing, $e);

            return false;
        }
    }

    public function delete(User $user, ProductPricing $pricing): bool
    {
        try {
            // Soft delete only; keep audit history
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPricingPolicy@delete', $user, $pricing, $e);

            return false;
        }
    }

    public function restore(User $user, ProductPricing $pricing): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPricingPolicy@restore', $user, $pricing, $e);

            return false;
        }
    }

    /**
     * Advanced, future-proof abilities you will 100% need.
     */
    public function manageWorkingGroupOverrides(User $user): bool
    {
        try {
            // WG overrides can affect special clients pricing => admin-only
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPricingPolicy@manageWorkingGroupOverrides', $user, null, $e);

            return false;
        }
    }

    public function manageVariantPricing(User $user, ProductPricing $pricing): bool
    {
        try {
            // Controls variant-level money
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPricingPolicy@manageVariantPricing', $user, $pricing, $e);

            return false;
        }
    }

    public function manageFinishingPricing(User $user, ProductPricing $pricing): bool
    {
        try {
            // Controls finishing add-ons money
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPricingPolicy@manageFinishingPricing', $user, $pricing, $e);

            return false;
        }
    }

    public function manageRollPricing(User $user, ProductPricing $pricing): bool
    {
        return $user->hasRole('admin')
            || $user->hasPermissionTo('manage-pricing')
            || $user->hasPermissionTo('manage-product-pricing');
    }

    public function publish(User $user, ProductPricing $pricing): bool
    {
        try {
            // Optional: "publish pricing" action in UI
            // Keep strict: publishing impacts live calculations
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPricingPolicy@publish', $user, $pricing, $e);

            return false;
        }
    }

    // -------------------------
    // Internal helpers
    // -------------------------

    private function isAdmin(User $user): bool
    {
        $role = $user->relationLoaded('role') ? $user->role : $user->role()->first();

        if (! $role) {
            return false;
        }

        $name = strtolower((string) ($role->name ?? ''));

        return in_array($name, ['admin', 'super_admin'], true);
    }

    private function logPolicyError(string $where, ?User $user, ?ProductPricing $pricing, \Throwable $e): void
    {
        Log::error($where.' policy error', [
            'user_id' => $user?->id,
            'product_pricing_id' => $pricing?->id,
            'product_id' => $pricing?->product_id,
            'context' => $pricing?->context,
            'working_group_id' => $pricing?->working_group_id,
            'error' => $e->getMessage(),
        ]);
    }

    private function isStaff(User $user): bool
    {
        // Defensive: role relationship might not be loaded; handle safely
        $role = $user->relationLoaded('role') ? $user->role : $user->role()->first();

        if (! $role) {
            return false;
        }

        // Your roles table includes is_staff boolean
        if ((bool) ($role->is_staff ?? false) === true) {
            return true;
        }

        // Fallback by name (future proof if is_staff is misconfigured)
        $name = strtolower((string) ($role->name ?? ''));

        return in_array($name, ['admin', 'super_admin', 'staff', 'manager'], true);
    }
}
