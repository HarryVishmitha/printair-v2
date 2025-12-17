<?php

namespace App\Policies;

use App\Models\ProductFinishingLink;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProductFinishingLinkPolicy
{
    public function viewAny(User $user): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFinishingLinkPolicy@viewAny', $user, null, $e);
            return false;
        }
    }

    public function view(User $user, ProductFinishingLink $link): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFinishingLinkPolicy@view', $user, $link, $e);
            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFinishingLinkPolicy@create', $user, null, $e);
            return false;
        }
    }

    public function update(User $user, ProductFinishingLink $link): bool
    {
        try {
            // Changing min/max/default qty is a pricing-impact change
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFinishingLinkPolicy@update', $user, $link, $e);
            return false;
        }
    }

    public function delete(User $user, ProductFinishingLink $link): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFinishingLinkPolicy@delete', $user, $link, $e);
            return false;
        }
    }

    /**
     * Extra fool-proof guard:
     * Only allow “activate/deactivate finishing on product” for admins.
     */
    public function toggle(User $user, ProductFinishingLink $link): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFinishingLinkPolicy@toggle', $user, $link, $e);
            return false;
        }
    }

    /**
     * Strong correctness check (optional usage in controller/service):
     * Ensure linked finishing is actually a finishing product.
     */
    public function attachFinishingProduct(User $user, ProductFinishingLink $link): bool
    {
        try {
            if (! $this->isAdmin($user)) {
                return false;
            }

            // If relationships are loaded, we can enforce correctness.
            // If not loaded, allow and validate in FormRequest/service layer.
            $fin = $link->relationLoaded('finishingProduct') ? $link->finishingProduct : null;

            if ($fin) {
                return $fin->product_type === 'finishing';
            }

            return true;
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFinishingLinkPolicy@attachFinishingProduct', $user, $link, $e);
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

    private function logPolicyError(string $where, ?User $user, ?ProductFinishingLink $link, \Throwable $e): void
    {
        Log::error($where.' policy error', [
            'user_id' => $user?->id,
            'link_id' => $link?->id,
            'product_id' => $link?->product_id,
            'finishing_product_id' => $link?->finishing_product_id,
            'error' => $e->getMessage(),
        ]);
    }
}

