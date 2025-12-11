<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Customer;
use App\Models\WorkingGroup;
use App\Services\ActivityLogger;
use App\Notifications\NewUserCredentialsNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UserCustomerController extends Controller
{
    use AuthorizesRequests;

    /* =========================================================
     | USERS SECTION
     | Separate pages & URLs for system users (staff + portal)
     * =======================================================*/

    /**
     * List system users with filters.
     */
    public function usersIndex(Request $request)
    {
        $this->authorize('manage-users'); // Gate: Super Admin / Admin / Manager

        $search          = $request->string('search')->toString();
        $roleId          = $request->integer('role_id') ?: null;
        $workingGroupId  = $request->integer('working_group_id') ?: null;
        $status          = $request->string('status')->toString() ?: null;

        $usersQuery = User::query()
            ->with(['role', 'workingGroup'])
            ->when($search, function ($q) use ($search) {
                $like = '%' . $search . '%';

                $q->where(function ($sub) use ($like) {
                    $sub->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('whatsapp_number', 'like', $like);
                });
            })
            ->when($roleId, fn ($q) => $q->where('role_id', $roleId))
            ->when($workingGroupId, fn ($q) => $q->where('working_group_id', $workingGroupId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('first_name')
            ->orderBy('last_name');

        $users = $usersQuery->paginate(15)->withQueryString();

        $roles         = Role::orderBy('name')->get();
        $workingGroups = WorkingGroup::orderBy('name')->get();

        return view('admin.users.index', [
            'users'          => $users,
            'roles'          => $roles,
            'workingGroups'  => $workingGroups,
            'filters'        => [
                'search'          => $search,
                'role_id'         => $roleId,
                'working_group_id'=> $workingGroupId,
                'status'          => $status,
            ],
        ]);
    }

    /**
     * Show create-user form.
     */
    public function usersCreate()
    {
        $this->authorize('manage-users');

        $roles         = Role::orderBy('name')->get();
        $workingGroups = WorkingGroup::orderBy('name')->get();

        return view('admin.users.create', [
            'roles'         => $roles,
            'workingGroups' => $workingGroups,
        ]);
    }

    /**
     * Store a new system user (staff or customer portal user).
     * - Generates a random password
     * - Emails credentials
     * - Enforces working-group rules
     */
    public function usersStore(Request $request)
    {
        $this->authorize('manage-users');
    
        $request->validate([
            'first_name'        => ['required', 'string', 'max:100'],
            'last_name'         => ['nullable', 'string', 'max:100'],
            'email'             => ['required', 'email', 'max:255', 'unique:users,email'],
            'whatsapp_number'   => ['nullable', 'string', 'max:50'],
            'role_id'           => ['required', 'exists:roles,id'],
            'working_group_id'  => ['nullable', 'exists:working_groups,id'],
            'status'            => ['required', 'in:active,inactive,suspended'],
        ]);
    
        $actor = $request->user();
    
        try {
            $plainPassword = Str::random(12);
    
            $user = DB::transaction(function () use ($request, $plainPassword) {
                $data = $request->only([
                    'first_name',
                    'last_name',
                    'email',
                    'whatsapp_number',
                    'role_id',
                    'working_group_id',
                    'status',
                ]);
    
                // 🔹 IMPORTANT: set Laravel's default "name" column
                $data['name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
    
                // 🔹 Working group rules
                $role = Role::find($data['role_id']);
    
                if ($role && $role->is_staff) {
                    $publicGroup = WorkingGroup::where('slug', WorkingGroup::PUBLIC_SLUG)->first();
                    $data['working_group_id'] = $publicGroup?->id;
                } else {
                    if (empty($data['working_group_id'])) {
                        $publicGroup = WorkingGroup::where('slug', WorkingGroup::PUBLIC_SLUG)->first();
                        $data['working_group_id'] = $publicGroup?->id;
                    }
                }
    
                // 🔹 Auth fields
                $data['password']            = bcrypt($plainPassword);
                $data['login_status']        = false;
                $data['force_password_change'] = true; 
    
                return User::create($data);
            });
    
            if ($user) {
                $user->notify(new NewUserCredentialsNotification($plainPassword));
            }
    
            ActivityLogger::log(
                $actor,
                'users.create',
                'Created user account',
                [
                    'user_id'          => $user->id,
                    'email'            => $user->email,
                    'role_id'          => $user->role_id,
                    'working_group_id' => $user->working_group_id,
                    'status'           => $user->status,
                ]
            );
    
            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User created successfully and credentials email has been sent.');
    
        } catch (\Throwable $e) {
            Log::error('Error creating user', [
                'error' => $e->getMessage(),
            ]);
    
            return back()
                ->withInput()
                ->with('error', 'Failed to create user. Please try again or contact the system administrator.');
        }
    }

    /**
     * Show edit-user form.
     */
    public function usersEdit(User $user)
    {
        $this->authorize('manage-users');

        $roles         = Role::orderBy('name')->get();
        $workingGroups = WorkingGroup::orderBy('name')->get();

        return view('admin.users.edit', [
            'user'          => $user,
            'roles'         => $roles,
            'workingGroups' => $workingGroups,
        ]);
    }

    /**
     * Update an existing user.
     */
    public function usersUpdate(Request $request, User $user)
    {
        $this->authorize('manage-users');

        // Prevent locking yourself out (optional but safer)
        if ($user->id === Auth::id() && $request->input('status') === 'suspended') {
            return back()
                ->withInput()
                ->with('error', 'You cannot suspend your own account.');
        }

        $validated = $request->validate([
            'first_name'        => ['required', 'string', 'max:255'],
            'last_name'         => ['nullable', 'string', 'max:255'],
            'email'             => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'whatsapp_number'   => ['nullable', 'string', 'max:30'],
            'role_id'           => ['required', 'exists:roles,id'],
            'working_group_id'  => ['nullable', 'exists:working_groups,id'],
            'status'            => ['required', 'in:active,inactive,suspended'],
            'email_notifications'  => ['sometimes', 'boolean'],
            'system_notifications' => ['sometimes', 'boolean'],
        ]);

        $data = $this->normalizeUserData($validated);
        $data['email_notifications']  = $request->boolean('email_notifications', true);
        $data['system_notifications'] = $request->boolean('system_notifications', true);

        $before = $user->replicate();

        try {
            DB::transaction(function () use ($user, $data) {
                $user->update($data);
            });

            ActivityLogger::log(
                Auth::user(),
                'users.update',
                'Updated user account',
                [
                    'user_id' => $user->id,
                    'before'  => [
                        'email'            => $before->email,
                        'role_id'          => $before->role_id,
                        'working_group_id' => $before->working_group_id,
                        'status'           => $before->status,
                    ],
                    'after'   => [
                        'email'            => $user->email,
                        'role_id'          => $user->role_id,
                        'working_group_id' => $user->working_group_id,
                        'status'           => $user->status,
                    ],
                ]
            );

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User updated successfully.');

        } catch (Throwable $e) {
            Log::error('Error updating user', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update user. Please try again.');
        }
    }

    /**
     * Soft-delete / deactivate a user.
     * For now we just mark as inactive instead of hard delete.
     */
    public function usersDestroy(User $user)
    {
        $this->authorize('manage-users');

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot remove your own account.');
        }

        try {
            $before = $user->toArray();

            DB::transaction(function () use ($user) {
                // Safer than delete: keep history
                $user->status = 'inactive';
                $user->save();
            });

            ActivityLogger::log(
                Auth::user(),
                'users.deactivate',
                'Deactivated user account',
                $before
            );

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User marked as inactive.');

        } catch (Throwable $e) {
            Log::error('Error deactivating user', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Failed to deactivate user. Please try again.');
        }
    }

    /* =========================================================
     | CUSTOMERS SECTION
     | Separate pages & URLs for walk-in / business customers
     * =======================================================*/

    /**
     * List customers (walk-in / account / corporate) with filters.
     */
    public function customersIndex(Request $request)
    {
        $this->authorize('manage-customers'); // define gate similarly if you want

        $search         = $request->string('search')->toString();
        $type           = $request->string('type')->toString() ?: null;
        $status         = $request->string('status')->toString() ?: null;
        $workingGroupId = $request->integer('working_group_id') ?: null;

        $customersQuery = Customer::query()
            ->with(['user', 'workingGroup'])
            ->when($search, function ($q) use ($search) {
                $like = '%' . $search . '%';

                $q->where(function ($sub) use ($like) {
                    $sub->where('full_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('company_name', 'like', $like);
                });
            })
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($workingGroupId, fn ($q) => $q->where('working_group_id', $workingGroupId))
            ->orderBy('full_name');

        $customers     = $customersQuery->paginate(15)->withQueryString();
        $workingGroups = WorkingGroup::orderBy('name')->get();

        return view('admin.customers.index', [
            'customers'      => $customers,
            'workingGroups'  => $workingGroups,
            'filters'        => [
                'search'          => $search,
                'type'            => $type,
                'status'          => $status,
                'working_group_id'=> $workingGroupId,
            ],
        ]);
    }

    /**
     * Show create-customer form.
     */
    public function customersCreate()
    {
        $this->authorize('manage-customers');

        $workingGroups = WorkingGroup::orderBy('name')->get();

        return view('admin.customers.create', [
            'workingGroups' => $workingGroups,
        ]);
    }

    /**
     * Store a new customer (walk-in / account / corporate).
     */
    public function customersStore(Request $request)
    {
        $this->authorize('manage-customers');

        $validated = $request->validate([
            'full_name'        => ['required', 'string', 'max:255'],
            'email'            => ['nullable', 'email', 'max:255'],
            'phone'            => ['required', 'string', 'max:30'],
            'whatsapp_number'  => ['nullable', 'string', 'max:30'],
            'company_name'     => ['nullable', 'string', 'max:255'],
            'company_phone'    => ['nullable', 'string', 'max:30'],
            'company_reg_no'   => ['nullable', 'string', 'max:50'],
            'type'             => ['required', 'in:walk_in,account,corporate'],
            'status'           => ['required', 'in:active,inactive'],
            'working_group_id' => ['nullable', 'exists:working_groups,id'],
            'email_notifications' => ['sometimes', 'boolean'],
            'sms_notifications'   => ['sometimes', 'boolean'],
            'notes'            => ['nullable', 'string'],
        ]);

        $data = $this->normalizeCustomerData($validated);
        $data['email_notifications'] = $request->boolean('email_notifications', true);
        $data['sms_notifications']   = $request->boolean('sms_notifications', false);

        try {
            $customer = DB::transaction(function () use ($data) {
                $data['customer_code'] = $this->generateCustomerCode();
                return Customer::create($data);
            });

            ActivityLogger::log(
                Auth::user(),
                'customers.create',
                'Created customer',
                [
                    'customer_id'   => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'full_name'     => $customer->full_name,
                    'type'          => $customer->type,
                ]
            );

            return redirect()
                ->route('admin.customers.index')
                ->with('success', 'Customer created successfully.');

        } catch (Throwable $e) {
            Log::error('Error creating customer', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create customer. Please try again.');
        }
    }

    /**
     * Show edit-customer form.
     */
    public function customersEdit(Customer $customer)
    {
        $this->authorize('manage-customers');

        $workingGroups = WorkingGroup::orderBy('name')->get();

        return view('admin.customers.edit', [
            'customer'      => $customer,
            'workingGroups' => $workingGroups,
        ]);
    }

    /**
     * Update a customer.
     */
    public function customersUpdate(Request $request, Customer $customer)
    {
        $this->authorize('manage-customers');

        $validated = $request->validate([
            'full_name'        => ['required', 'string', 'max:255'],
            'email'            => ['nullable', 'email', 'max:255'],
            'phone'            => ['required', 'string', 'max:30'],
            'whatsapp_number'  => ['nullable', 'string', 'max:30'],
            'company_name'     => ['nullable', 'string', 'max:255'],
            'company_phone'    => ['nullable', 'string', 'max:30'],
            'company_reg_no'   => ['nullable', 'string', 'max:50'],
            'type'             => ['required', 'in:walk_in,account,corporate'],
            'status'           => ['required', 'in:active,inactive'],
            'working_group_id' => ['nullable', 'exists:working_groups,id'],
            'email_notifications' => ['sometimes', 'boolean'],
            'sms_notifications'   => ['sometimes', 'boolean'],
            'notes'            => ['nullable', 'string'],
        ]);

        $data = $this->normalizeCustomerData($validated);
        $data['email_notifications'] = $request->boolean('email_notifications', true);
        $data['sms_notifications']   = $request->boolean('sms_notifications', false);

        $before = $customer->replicate();

        try {
            DB::transaction(function () use ($customer, $data) {
                $customer->update($data);
            });

            ActivityLogger::log(
                Auth::user(),
                'customers.update',
                'Updated customer',
                [
                    'customer_id' => $customer->id,
                    'before'      => [
                        'full_name'   => $before->full_name,
                        'type'        => $before->type,
                        'status'      => $before->status,
                    ],
                    'after'       => [
                        'full_name'   => $customer->full_name,
                        'type'        => $customer->type,
                        'status'      => $customer->status,
                    ],
                ]
            );

            return redirect()
                ->route('admin.customers.index')
                ->with('success', 'Customer updated successfully.');

        } catch (Throwable $e) {
            Log::error('Error updating customer', [
                'customer_id' => $customer->id,
                'error'       => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update customer. Please try again.');
        }
    }

    /**
     * Soft-delete a customer.
     */
    public function customersDestroy(Customer $customer)
    {
        $this->authorize('manage-customers');

        try {
            $snapshot = $customer->toArray();

            DB::transaction(function () use ($customer) {
                $customer->delete();
            });

            ActivityLogger::log(
                Auth::user(),
                'customers.delete',
                'Deleted customer',
                $snapshot
            );

            return redirect()
                ->route('admin.customers.index')
                ->with('success', 'Customer deleted successfully.');

        } catch (Throwable $e) {
            Log::error('Error deleting customer', [
                'customer_id' => $customer->id,
                'error'       => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Failed to delete customer. Please try again.');
        }
    }

    /* =========================================================
     | INTERNAL HELPERS
     * =======================================================*/

    /**
     * Enforce working-group rules & staff behaviour for users.
     *
     * - Everybody must have a working group (default: public)
     * - Staff roles (Super Admin / Admin / Manager) are always in Public group.
     */
    protected function normalizeUserData(array $data): array
    {
        $publicGroupId = WorkingGroup::where('slug', WorkingGroup::PUBLIC_SLUG)->value('id');

        $role    = isset($data['role_id']) ? Role::find($data['role_id']) : null;
        $isStaff = $role?->is_staff ?? false;

        if (empty($data['working_group_id'])) {
            $data['working_group_id'] = $publicGroupId;
        }

        if ($isStaff) {
            $data['working_group_id'] = $publicGroupId;
        }

        return $data;
    }

    /**
     * Enforce working-group default for customers.
     */
    protected function normalizeCustomerData(array $data): array
    {
        $publicGroupId = WorkingGroup::where('slug', WorkingGroup::PUBLIC_SLUG)->value('id');

        if (empty($data['working_group_id'])) {
            $data['working_group_id'] = $publicGroupId;
        }

        return $data;
    }

    /**
     * Generate a unique, human-friendly customer code.
     */
    protected function generateCustomerCode(): string
    {
        do {
            $code = 'CUST-' . strtoupper(Str::random(6));
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }
}
