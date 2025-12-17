<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProductPolicy
{
    /**
     * Centralized "safe auth" checks with logging.
     * Policies shouldn't throw random exceptions; they should return boolean.
     * Controllers can call $this->authorize(...) and Laravel will throw AuthorizationException cleanly.
     */
    public function viewAny(User $user): bool
    {
        try {
            // Staff can see product listing in admin (incl. draft/hidden/internal, depending on role)
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPolicy@viewAny', $user, null, $e);

            return false;
        }
    }

    public function view(User $user, Product $product): bool
    {
        try {
            // Public storefront "view" should be handled by controllers/queries (visibility/status filters).
            // This policy is for admin-side viewing.
            if (! $this->isStaff($user)) {
                return false;
            }

            if ($product->visibility === 'internal') {
                return $this->isStaff($user);
            }

            return true;
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPolicy@view', $user, $product, $e);

            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            // Creating catalog items should be restricted to admin-level staff
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPolicy@create', $user, null, $e);

            return false;
        }
    }

    public function update(User $user, Product $product): bool
    {
        try {
            // Only admins can edit product definitions (prevents accidental catalog damage)
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPolicy@update', $user, $product, $e);

            return false;
        }
    }

    public function delete(User $user, Product $product): bool
    {
        try {
            // Soft-delete only; keep history safe
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPolicy@delete', $user, $product, $e);

            return false;
        }
    }

    public function restore(User $user, Product $product): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPolicy@restore', $user, $product, $e);

            return false;
        }
    }

    public function forceDelete(User $user, Product $product): bool
    {
        try {
            // Hard-delete is dangerous in print businesses due to references (estimates/orders later).
            // Keep it admin-only (or remove this ability entirely).
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPolicy@forceDelete', $user, $product, $e);

            return false;
        }
    }

    /**
     * Future-proof abilities (you'll use these soon)
     * - managePricing: pricing module access for this product
     * - publish: toggle status/visibility to active/public
     */
    public function managePricing(User $user, Product $product): bool
    {
        try {
            // Pricing changes affect revenue => admin-only by default
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPolicy@managePricing', $user, $product, $e);

            return false;
        }
    }

    public function publish(User $user, Product $product): bool
    {
        try {
            // Publishing makes it visible to customers; keep strict
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductPolicy@publish', $user, $product, $e);

            return false;
        }
    }

    public function manageRolls(User $user, Product $product): bool
    {
        return $user->hasRole('admin')
            || $user->hasPermissionTo('manage-products')
            || $user->hasPermissionTo('manage-product-rolls');
    }

    // -------------------------
    // Internal helpers
    // -------------------------

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

    private function isAdmin(User $user): bool
    {
        $role = $user->relationLoaded('role') ? $user->role : $user->role()->first();
        if (! $role) {
            return false;
        }

        $name = strtolower((string) ($role->name ?? ''));

        return in_array($name, ['admin', 'super_admin'], true);
    }

    private function logPolicyError(string $where, ?User $user, ?Product $product, \Throwable $e): void
    {
        Log::error($where.' policy error', [
            'user_id' => $user?->id,
            'product_id' => $product?->id,
            'error' => $e->getMessage(),
        ]);
    }
}
