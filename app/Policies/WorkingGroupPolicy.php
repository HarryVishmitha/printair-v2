<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkingGroup;
use Illuminate\Support\Facades\Log;

class WorkingGroupPolicy
{
    public function viewAny(User $user): bool
    {
        try {
            return $this->isAdminOrManager($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('WorkingGroupPolicy@viewAny', $user, null, $e);
            return false;
        }
    }

    public function view(User $user, WorkingGroup $group): bool
    {
        try {
            return $this->viewAny($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('WorkingGroupPolicy@view', $user, $group, $e);
            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            // Creating working groups changes tenant-like structure => admin-only by default
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('WorkingGroupPolicy@create', $user, null, $e);
            return false;
        }
    }

    public function update(User $user, WorkingGroup $group): bool
    {
        try {
            // Renaming/editing WG affects pricing overrides, customers, users => admin-only
            // If you want "manager" to edit later, change to isAdminOrManager()
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('WorkingGroupPolicy@update', $user, $group, $e);
            return false;
        }
    }

    public function delete(User $user, WorkingGroup $group): bool
    {
        try {
            // Fool-proof: never delete public group
            if ($group->slug === WorkingGroup::PUBLIC_SLUG) {
                return false;
            }

            // Also safer: block deleting staff groups (if you use it)
            if ((bool) $group->is_staff_group === true) {
                return false;
            }

            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('WorkingGroupPolicy@delete', $user, $group, $e);
            return false;
        }
    }

    /**
     * Future-proof abilities for pricing module.
     */
    public function managePricing(User $user, WorkingGroup $group): bool
    {
        try {
            // Pricing per WG is revenue-sensitive => admin-only
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('WorkingGroupPolicy@managePricing', $user, $group, $e);
            return false;
        }
    }

    public function manageVariantAvailability(User $user, WorkingGroup $group): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('WorkingGroupPolicy@manageVariantAvailability', $user, $group, $e);
            return false;
        }
    }

    // -------------------------
    // helpers (DB-based RBAC)
    // -------------------------

    private function isAdminOrManager(User $user): bool
    {
        $role = $user->relationLoaded('role') ? $user->role : $user->role()->first();
        if (! $role) {
            return false;
        }

        // Prefer the is_staff flag, then role name fallback
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

    private function logPolicyError(string $where, ?User $user, ?WorkingGroup $group, \Throwable $e): void
    {
        Log::error($where . ' policy error', [
            'user_id' => $user?->id,
            'working_group_id' => $group?->id,
            'working_group_slug' => $group?->slug,
            'error' => $e->getMessage(),
        ]);
    }
}
