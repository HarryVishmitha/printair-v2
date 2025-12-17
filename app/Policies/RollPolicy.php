<?php

namespace App\Policies;

use App\Models\Roll;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RollPolicy
{
    public function viewAny(User $user): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('RollPolicy@viewAny', $user, null, $e);
            return false;
        }
    }

    public function view(User $user, Roll $roll): bool
    {
        try {
            return $this->viewAny($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('RollPolicy@view', $user, $roll, $e);
            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            // Allow staff roles (incl. admin/managers) to create rolls
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('RollPolicy@create', $user, null, $e);
            return false;
        }
    }

    public function update(User $user, Roll $roll): bool
    {
        try {
            // Same level as create
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('RollPolicy@update', $user, $roll, $e);
            return false;
        }
    }

    public function delete(User $user, Roll $roll): bool
    {
        try {
            // Allow staff-level roles to attempt delete;
            // DB usage checks below will still prevent unsafe deletions.
            if (! $this->isStaff($user)) {
                return false;
            }

            // Future-proof: block deleting if used anywhere
            $inUse = DB::table('product_rolls')
                    ->where('roll_id', $roll->id)
                    ->whereNull('deleted_at')
                    ->exists()
                || DB::table('product_roll_pricings')
                    ->where('roll_id', $roll->id)
                    ->whereNull('deleted_at')
                    ->exists();

            return ! $inUse;
        } catch (\Throwable $e) {
            $this->logPolicyError('RollPolicy@delete', $user, $roll, $e);
            return false;
        }
    }

    // -------------------------
    // helpers (mirroring ProductPolicy)
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

    private function logPolicyError(string $where, ?User $user, ?Roll $roll, \Throwable $e): void
    {
        Log::error($where.' policy error', [
            'user_id' => $user?->id,
            'roll_id' => $roll?->id,
            'error' => $e->getMessage(),
        ]);
    }
}
