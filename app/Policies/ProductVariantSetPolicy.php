<?php

namespace App\Policies;

use App\Models\ProductVariantSet;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductVariantSetPolicy
{
    public function viewAny(User $user): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductVariantSetPolicy@viewAny', $user, null, $e);
            return false;
        }
    }

    public function view(User $user, ProductVariantSet $set): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductVariantSetPolicy@view', $user, $set, $e);
            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            // Creating allowed combos should be admin-only (integrity critical)
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductVariantSetPolicy@create', $user, null, $e);
            return false;
        }
    }

    public function update(User $user, ProductVariantSet $set): bool
    {
        try {
            // Changing allowed combos affects pricing availability and customer UX.
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductVariantSetPolicy@update', $user, $set, $e);
            return false;
        }
    }

    public function delete(User $user, ProductVariantSet $set): bool
    {
        try {
            if (! $this->isAdmin($user)) {
                return false;
            }

            // Fool-proof:
            // If variant set is referenced by pricing or availability overrides, disallow delete.
            // (You can soft-delete or set is_active=0 instead.)
            if ($this->isReferenced($set)) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductVariantSetPolicy@delete', $user, $set, $e);
            return false;
        }
    }

    /**
     * Safer alternative to delete (you'll use in UI):
     * deactivate a variant set instead of deleting it.
     */
    public function deactivate(User $user, ProductVariantSet $set): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductVariantSetPolicy@deactivate', $user, $set, $e);
            return false;
        }
    }

    // -------------------------
    // helpers
    // -------------------------

    private function isReferenced(ProductVariantSet $set): bool
    {
        // 1) Any variant pricing exists referencing this set?
        $inVariantPricing = DB::table('product_variant_pricings')
            ->whereNull('deleted_at')
            ->where('variant_set_id', $set->id)
            ->exists();

        // 2) Any WG availability overrides for this variant?
        $inAvailabilityOverrides = DB::table('product_variant_availability_overrides')
            ->where('variant_set_id', $set->id)
            ->exists();

        // 3) Variant set has items (normal) - not a block by itself but sanity
        // If it has items, it's a real combo; if it doesn't, admin can delete safely.
        // (We won't use this as a blocker.)

        return $inVariantPricing || $inAvailabilityOverrides;
    }

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

    private function logPolicyError(string $where, ?User $user, ?ProductVariantSet $set, \Throwable $e): void
    {
        Log::error($where.' policy error', [
            'user_id' => $user?->id,
            'variant_set_id' => $set?->id,
            'product_id' => $set?->product_id,
            'error' => $e->getMessage(),
        ]);
    }
}

