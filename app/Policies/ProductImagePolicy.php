<?php

namespace App\Policies;

use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProductImagePolicy
{
    public function viewAny(User $user): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductImagePolicy@viewAny', $user, null, $e);
            return false;
        }
    }

    public function view(User $user, ProductImage $image): bool
    {
        try {
            if (! $this->isStaff($user)) {
                return false;
            }

            // If image belongs to an internal product, only admins can view it (admin screens)
            // (Assumes ProductImage has product() relationship.)
            if ($image->relationLoaded('product') && $image->product) {
                if ($image->product->visibility === 'internal') {
                    return $this->isAdmin($user);
                }
            }

            return true;
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductImagePolicy@view', $user, $image, $e);
            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            // Uploading images modifies catalog presentation => admin-only by default
            // If you want staff designers to upload, switch to isStaff($user)
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductImagePolicy@create', $user, null, $e);
            return false;
        }
    }

    public function update(User $user, ProductImage $image): bool
    {
        try {
            // Changing featured/sort/alt text affects SEO/UX
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductImagePolicy@update', $user, $image, $e);
            return false;
        }
    }

    public function delete(User $user, ProductImage $image): bool
    {
        try {
            // Only admins can delete catalog media
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductImagePolicy@delete', $user, $image, $e);
            return false;
        }
    }

    /**
     * Future-proof: useful if you implement "set featured" endpoint.
     */
    public function setFeatured(User $user, ProductImage $image): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductImagePolicy@setFeatured', $user, $image, $e);
            return false;
        }
    }

    /**
     * Future-proof: sort updates via drag & drop.
     */
    public function reorder(User $user): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductImagePolicy@reorder', $user, null, $e);
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

    private function logPolicyError(string $where, ?User $user, ?ProductImage $image, \Throwable $e): void
    {
        Log::error($where.' policy error', [
            'user_id' => $user?->id,
            'product_image_id' => $image?->id,
            'product_id' => $image?->product_id,
            'error' => $e->getMessage(),
        ]);
    }
}

