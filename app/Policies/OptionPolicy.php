<?php

namespace App\Policies;

use App\Models\Option;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OptionPolicy
{
    public function viewAny(User $user): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('OptionPolicy@viewAny', $user, null, $e);
            return false;
        }
    }

    public function view(User $user, Option $option): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('OptionPolicy@view', $user, $option, $e);
            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('OptionPolicy@create', $user, null, $e);
            return false;
        }
    }

    public function update(User $user, Option $option): bool
    {
        try {
            // Admin-only because label/code changes can affect variant naming/logic
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('OptionPolicy@update', $user, $option, $e);
            return false;
        }
    }

    public function delete(User $user, Option $option): bool
    {
        try {
            if (! $this->isAdmin($user)) {
                return false;
            }

            // Fool-proof safety:
            // Only allow delete when option is not used anywhere critical.
            if ($this->isUsedInSystem($option)) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            $this->logPolicyError('OptionPolicy@delete', $user, $option, $e);
            return false;
        }
    }

    /**
     * Future-proof: restrict code changes (best practice).
     * In production, changing "code" can break references in UI/config.
     */
    public function mutateCode(User $user, Option $option): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('OptionPolicy@mutateCode', $user, $option, $e);
            return false;
        }
    }

    // -------------------------
    // helpers
    // -------------------------

    private function isUsedInSystem(Option $option): bool
    {
        // These are the critical relations where usage should block deletion.
        // Implement these relations in Option model (recommended):
        // - productOptions()
        // - variantSetItems()
        //
        // Even if you don't have them, the query builder will still work
        // by referencing tables directly.

        // 1) Attached to any product?
        $inProduct = \DB::table('product_options')->whereNull('deleted_at')->where('option_id', $option->id)->exists();

        // 2) Used in any variant set?
        $inVariantSet = \DB::table('product_variant_set_items')->where('option_id', $option->id)->exists();

        // If either is true, deletion is unsafe.
        return $inProduct || $inVariantSet;
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

    private function logPolicyError(string $where, ?User $user, ?Option $option, \Throwable $e): void
    {
        Log::error($where.' policy error', [
            'user_id' => $user?->id,
            'option_id' => $option?->id,
            'error' => $e->getMessage(),
        ]);
    }
}

