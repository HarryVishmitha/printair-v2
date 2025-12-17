<?php

namespace App\Policies;

use App\Models\ProductFile;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProductFilePolicy
{
    public function viewAny(User $user): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFilePolicy@viewAny', $user, null, $e);
            return false;
        }
    }

    public function view(User $user, ProductFile $file): bool
    {
        try {
            return $this->isStaff($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFilePolicy@view', $user, $file, $e);
            return false;
        }
    }

    public function create(User $user): bool
    {
        try {
            // Uploading attachments affects production & IP
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFilePolicy@create', $user, null, $e);
            return false;
        }
    }

    public function update(User $user, ProductFile $file): bool
    {
        try {
            // Changing labels, metadata, etc.
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFilePolicy@update', $user, $file, $e);
            return false;
        }
    }

    public function delete(User $user, ProductFile $file): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFilePolicy@delete', $user, $file, $e);
            return false;
        }
    }

    /**
     * Future-proof: separate ability for "download file" endpoints.
     * Default: admin-only (safer). If you want staff designers to download, switch to isStaff().
     */
    public function download(User $user, ProductFile $file): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFilePolicy@download', $user, $file, $e);
            return false;
        }
    }

    /**
     * Future-proof: replace the file content but keep DB row/history.
     */
    public function replace(User $user, ProductFile $file): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFilePolicy@replace', $user, $file, $e);
            return false;
        }
    }

    /**
     * Future-proof: reordering attachments in UI
     */
    public function reorder(User $user): bool
    {
        try {
            return $this->isAdmin($user);
        } catch (\Throwable $e) {
            $this->logPolicyError('ProductFilePolicy@reorder', $user, null, $e);
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

    private function logPolicyError(string $where, ?User $user, ?ProductFile $file, \Throwable $e): void
    {
        Log::error($where.' policy error', [
            'user_id' => $user?->id,
            'product_file_id' => $file?->id,
            'product_id' => $file?->product_id,
            'error' => $e->getMessage(),
        ]);
    }
}

