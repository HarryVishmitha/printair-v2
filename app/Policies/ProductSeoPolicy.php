<?php

namespace App\Policies;

use App\Models\ProductSeo;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProductSeoPolicy
{
    public function viewAny(User $user): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductSeoPolicy@viewAny', $user, null, $e);
            return false;
        }
    }

    public function view(User $user, ProductSeo $seo): bool
    {
        try {
            // Staff can view SEO values for admin preview
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductSeoPolicy@view', $user, $seo, $e);
            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            // Creating/editing SEO impacts public visibility & indexing
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductSeoPolicy@create', $user, null, $e);
            return false;
        }
    }

    public function update(User $user, ProductSeo $seo): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductSeoPolicy@update', $user, $seo, $e);
            return false;
        }
    }

    public function delete(User $user, ProductSeo $seo): bool
    {
        try {
            // Normally you won't delete SEO; you update it.
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductSeoPolicy@delete', $user, $seo, $e);
            return false;
        }
    }

    /**
     * Future-proof: special ability to change indexability (noindex).
     * This is a “safety” action but still admin-only.
     */
    public function toggleIndexing(User $user, ProductSeo $seo): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductSeoPolicy@toggleIndexing', $user, $seo, $e);
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

    private function logPolicyError(string $where, ?User $user, ?ProductSeo $seo, \Throwable $e): void
    {
        Log::error($where.' policy error', [
            'user_id' => $user?->id,
            'product_seo_id' => $seo?->id,
            'product_id' => $seo?->product_id,
            'error' => $e->getMessage(),
        ]);
    }
}

