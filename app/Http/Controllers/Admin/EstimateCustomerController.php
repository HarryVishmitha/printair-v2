<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkingGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EstimateCustomerController extends Controller
{
    /**
     * Search eligible system users to create an "account" customer from.
     * Excludes internal/staff roles and users already linked to a customer.
     */
    public function userSearch(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $like = $q !== '' ? ('%'.$q.'%') : null;

        $users = User::query()
            ->with(['role:id,name,is_staff'])
            ->whereHas('role', fn ($rq) => $rq->where('is_staff', false))
            ->where('status', 'active')
            ->whereDoesntHave('customer')
            ->when($like, function ($uq) use ($like) {
                $uq->where(function ($sub) use ($like) {
                    $sub->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('whatsapp_number', 'like', $like);
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(20)
            ->get(['id', 'first_name', 'last_name', 'email', 'whatsapp_number', 'working_group_id', 'role_id']);

        return response()->json([
            'items' => $users->map(fn (User $u) => [
                'id' => $u->id,
                'full_name' => $u->full_name,
                'email' => $u->email,
                'whatsapp_number' => $u->whatsapp_number,
                'working_group_id' => $u->working_group_id,
                'role' => [
                    'id' => $u->role?->id,
                    'name' => $u->role?->name,
                    'is_staff' => (bool) ($u->role?->is_staff ?? false),
                ],
            ])->values(),
        ]);
    }

    /**
     * Create a new customer from estimate screen.
     * - walk_in: creates walk-in customer under selected WG
     * - from_user: creates account customer linked to an existing non-staff user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:walk_in,from_user'],
            'working_group_id' => ['nullable', 'integer', 'exists:working_groups,id'],

            // walk_in fields
            'full_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],

            // from_user fields
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'phone_override' => ['nullable', 'string', 'max:30'],
        ]);

        if ($validated['mode'] === 'walk_in') {
            $wgId = (int) ($validated['working_group_id'] ?? 0);
            if ($wgId <= 0) {
                throw ValidationException::withMessages([
                    'working_group_id' => 'Working group is required.',
                ]);
            }

            $fullName = trim((string) ($validated['full_name'] ?? ''));
            $phone = trim((string) ($validated['phone'] ?? ''));
            if ($fullName === '' || $phone === '') {
                throw ValidationException::withMessages([
                    'full_name' => 'Name is required.',
                    'phone' => 'Phone is required.',
                ]);
            }

            $customer = DB::transaction(function () use ($wgId, $fullName, $phone, $validated) {
                $this->assertWorkingGroupExists($wgId);

                return Customer::create([
                    'user_id' => null,
                    'working_group_id' => $wgId,
                    'customer_code' => $this->generateCustomerCode(),
                    'full_name' => $fullName,
                    'email' => $validated['email'] ?? null,
                    'phone' => $phone,
                    'whatsapp_number' => null,
                    'company_name' => null,
                    'company_phone' => null,
                    'company_reg_no' => null,
                    'type' => 'walk_in',
                    'status' => 'active',
                    'email_notifications' => true,
                    'sms_notifications' => false,
                    'notes' => null,
                ]);
            });

            return response()->json([
                'ok' => true,
                'customer' => $this->customerJson($customer),
            ]);
        }

        // from_user
        $userId = (int) ($validated['user_id'] ?? 0);
        if ($userId <= 0) {
            throw ValidationException::withMessages([
                'user_id' => 'User is required.',
            ]);
        }

        $customer = DB::transaction(function () use ($userId, $validated) {
            /** @var User $user */
            $user = User::query()->with('role')->whereKey($userId)->lockForUpdate()->firstOrFail();

            $role = $user->role;
            if (($role?->is_staff ?? false) === true) {
                throw ValidationException::withMessages([
                    'user_id' => 'Internal staff users cannot be added as customers.',
                ]);
            }

            if ($user->customer()->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => 'This user is already linked to a customer.',
                ]);
            }

            $wgId = (int) ($user->working_group_id ?? 0);
            if ($wgId <= 0) {
                throw ValidationException::withMessages([
                    'user_id' => 'Selected user does not have a working group.',
                ]);
            }

            $phone = trim((string) ($validated['phone_override'] ?? $user->whatsapp_number ?? ''));
            if ($phone === '') {
                throw ValidationException::withMessages([
                    'phone_override' => 'Phone is required for the customer profile.',
                ]);
            }

            return Customer::create([
                'user_id' => $user->id,
                'working_group_id' => $wgId,
                'customer_code' => $this->generateCustomerCode(),
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $phone,
                'whatsapp_number' => $user->whatsapp_number,
                'company_name' => null,
                'company_phone' => null,
                'company_reg_no' => null,
                'type' => 'account',
                'status' => 'active',
                'email_notifications' => true,
                'sms_notifications' => false,
                'notes' => 'Created from estimate (linked user_id '.$user->id.')',
            ]);
        });

        return response()->json([
            'ok' => true,
            'customer' => $this->customerJson($customer),
        ]);
    }

    private function customerJson(Customer $c): array
    {
        return [
            'id' => $c->id,
            'customer_code' => $c->customer_code,
            'full_name' => $c->full_name,
            'email' => $c->email,
            'phone' => $c->phone,
            'working_group_id' => $c->working_group_id,
            'type' => $c->type,
            'status' => $c->status,
            'user_id' => $c->user_id,
        ];
    }

    private function assertWorkingGroupExists(int $wgId): void
    {
        if (!WorkingGroup::query()->whereKey($wgId)->exists()) {
            throw ValidationException::withMessages([
                'working_group_id' => 'Invalid working group.',
            ]);
        }
    }

    private function generateCustomerCode(): string
    {
        do {
            $code = 'CUST-'.strtoupper(Str::random(6));
        } while (Customer::query()->where('customer_code', $code)->exists());

        return $code;
    }
}

