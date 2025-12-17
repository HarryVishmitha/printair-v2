<?php

namespace App\Policies;

use App\Models\OptionGroup;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OptionGroupPolicy
{
    public function viewAny(User $user): bool
    {
        try {
            // Staff can view option groups (admin screens), but only admin can modify.
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('OptionGroupPolicy@viewAny', $user, null, $e);
            return false;
        }
    }

    public function view(User $user, OptionGroup $group): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('OptionGroupPolicy@view', $user, $group, $e);
            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            // Creating new option groups affects product configuration globally.
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('OptionGroupPolicy@create', $user, null, $e);
            return false;
        }
    }

    public function update(User $user, OptionGroup $group): bool
    {
        try {
            // Admin-only: renaming/changing a group can break UI and combos.
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('OptionGroupPolicy@update', $user, $group, $e);
            return false;
        }
    }

    public function delete(User $user, OptionGroup $group): bool
    {
        try {
            // You can soft-delete in DB if you add SoftDeletes later.
            // For now: admin-only. Also recommended: block deletion if used by products.
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('OptionGroupPolicy@delete', $user, $group, $e);
            return false;
        }
    }

    /**
     * Future-proof guardrail:
     * prevent risky operations (rename code) in production unless super admin.
     */
    public function mutateCode(User $user, OptionGroup $group): bool
    {
        try {
            // You can implement "super_admin" later. For now admin can do it.
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('OptionGroupPolicy@mutateCode', $user, $group, $e);
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

    private function logPolicyError(string $where, ?User $user, ?OptionGroup $group, \Throwable $e): void
    {
        Log::error($where.' policy error', [
            'user_id' => $user?->id,
            'option_group_id' => $group?->id,
            'error' => $e->getMessage(),
        ]);
    }
}

