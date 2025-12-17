<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('CategoryPolicy@viewAny', $user, null, $e);
            return false;
        }
    }

    public function view(User $user, Category $category): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('CategoryPolicy@view', $user, $category, $e);
            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('CategoryPolicy@create', $user, null, $e);
            return false;
        }
    }

    public function update(User $user, Category $category): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('CategoryPolicy@update', $user, $category, $e);
            return false;
        }
    }

    public function delete(User $user, Category $category): bool
    {
        try {
            if (! $this->isAdmin($user)) {
                return false;
            }

            // Fool-proof delete rules:
            // 1) If category has children, don't delete (use deactivate or move them).
            $hasChildren = DB::table('categories')
                ->whereNull('deleted_at')
                ->where('parent_id', $category->id)
                ->exists();

            if ($hasChildren) {
                return false;
            }

            // 2) If any products exist in this category, don't delete.
            // (Assumes products table exists with category_id)
            $hasProducts = DB::table('products')
                ->whereNull('deleted_at')
                ->where('category_id', $category->id)
                ->exists();

            if ($hasProducts) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            $this->logPolicyError('CategoryPolicy@delete', $user, $category, $e);
            return false;
        }
    }

    // Future-proof toggles
    public function toggleFeatured(User $user, Category $category): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('CategoryPolicy@toggleFeatured', $user, $category, $e);
            return false;
        }
    }

    public function toggleMenuVisibility(User $user, Category $category): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('CategoryPolicy@toggleMenuVisibility', $user, $category, $e);
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

    private function logPolicyError(string $where, ?User $user, ?Category $category, \Throwable $e): void
    {
        Log::error($where.' policy error', [
            'user_id' => $user?->id,
            'category_id' => $category?->id,
            'working_group_id' => $category?->working_group_id,
            'error' => $e->getMessage(),
        ]);
    }
}

