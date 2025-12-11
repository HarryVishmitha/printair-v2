<x-app-layout>
    <x-slot name="sectionTitle">Settings</x-slot>
    <x-slot name="pageTitle">Edit User</x-slot>

    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Settings</span>
        <span class="mx-1 opacity-60">/</span>
        <a href="{{ route('admin.users.index') }}" class="text-slate-500 hover:text-slate-700">Users</a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">{{ $user->full_name ?? $user->name }}</span>
    </x-slot>

    @php
        $publicGroupId = $workingGroups->firstWhere('slug', \App\Models\WorkingGroup::PUBLIC_SLUG)?->id;
    @endphp

    <div class="space-y-6">
        {{-- Global feedback (backend errors / success) --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => (show = false), 4500)"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 via-emerald-50/90 to-white px-4 py-3.5 text-sm text-emerald-800 shadow-sm">
                <span
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </span>
                <span class="font-medium">{{ session('success') }}</span>
                <button @click="show = false" class="ml-auto text-emerald-500 hover:text-emerald-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                class="flex items-center gap-3 rounded-xl border border-rose-200 bg-gradient-to-r from-rose-50 via-rose-50/90 to-white px-4 py-3.5 text-sm text-rose-800 shadow-sm">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m0 3h.008v.008H12V15.75zm9-.75a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <span class="font-medium">{{ session('error') }}</span>
                <button @click="show = false" class="ml-auto text-rose-500 hover:text-rose-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                <div class="flex gap-2">
                    <div class="mt-0.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m0 3h.008v.008H12V15.75zm9-.75a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold">Please fix the errors below before continuing.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Hero --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-5 sm:px-7 sm:py-6 text-white shadow-lg shadow-slate-900/40">
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2 max-w-xl">
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium tracking-wide backdrop-blur">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Update account
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold leading-tight">
                        {{ $user->full_name ?? $user->name }}
                    </h2>
                    <p class="text-xs sm:text-sm text-white/80">
                        Adjust role, working group and status. Password remains unchanged unless you trigger a
                        reset flow later.
                    </p>
                </div>
            </div>
        </section>

        {{-- Form --}}
        <section
            x-data="{
                roleId: '{{ old('role_id', $user->role_id) }}',
                publicGroupId: '{{ $publicGroupId }}',
                isStaffRole(id) {
                    const staffRoles = @json($roles->where('is_staff', true)->pluck('id')->values());
                    return staffRoles.includes(Number(id));
                }
            }"
            class="rounded-3xl border border-slate-200/80 bg-white px-5 py-6 shadow-sm sm:px-6">

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Basic info --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="first_name"
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                            First name
                        </label>
                        <input type="text" id="first_name" name="first_name"
                            value="{{ old('first_name', $user->first_name) }}"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                        @error('first_name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name"
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Last name
                        </label>
                        <input type="text" id="last_name" name="last_name"
                            value="{{ old('last_name', $user->last_name) }}"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                        @error('last_name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="email"
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Email (login)
                        </label>
                        <input type="email" id="email" name="email"
                            value="{{ old('email', $user->email) }}"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                        @error('email')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="whatsapp_number"
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                            WhatsApp number
                        </label>
                        <input type="text" id="whatsapp_number" name="whatsapp_number"
                            value="{{ old('whatsapp_number', $user->whatsapp_number) }}"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                        @error('whatsapp_number')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Role & group --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="role_id"
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Role
                        </label>
                        <select id="role_id" name="role_id" x-model="roleId"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                            <option value="">Select role…</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>
                                    {{ $role->name }}
                                    @if ($role->is_staff)
                                        (Staff)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="working_group_id"
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Working group
                        </label>
                        <select id="working_group_id" name="working_group_id"
                            :disabled="isStaffRole(roleId)"
                            :class="isStaffRole(roleId) ? 'bg-slate-100 cursor-not-allowed' : 'bg-slate-50/60'"
                            class="block w-full rounded-2xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                            <option value="">Default: Public</option>
                            @foreach ($workingGroups as $wg)
                                <option value="{{ $wg->id }}"
                                    @selected(old('working_group_id', $user->working_group_id ?? $publicGroupId) == $wg->id)>
                                    {{ $wg->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('working_group_id')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-400" x-show="isStaffRole(roleId)">
                            Staff roles are always assigned to the <span class="font-semibold">Public</span> working
                            group.
                        </p>
                    </div>
                </div>

                {{-- Status & notifications --}}
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="status"
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Status
                        </label>
                        <select id="status" name="status"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                            <option value="active" @selected(old('status', $user->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Inactive</option>
                            <option value="suspended" @selected(old('status', $user->status) === 'suspended')>Suspended</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label
                            class="block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Email notifications
                        </label>
                        <label
                            class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-xs font-medium text-slate-700 cursor-pointer">
                            <input type="checkbox" name="email_notifications" value="1"
                                class="rounded border-slate-300 text-slate-900 focus:ring-slate-900/20"
                                @checked(old('email_notifications', $user->email_notifications ?? true))>
                            Enable system emails for this user
                        </label>
                    </div>

                    <div class="space-y-2">
                        <label
                            class="block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                            System notifications
                        </label>
                        <label
                            class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-xs font-medium text-slate-700 cursor-pointer">
                            <input type="checkbox" name="system_notifications" value="1"
                                class="rounded border-slate-300 text-slate-900 focus:ring-slate-900/20"
                                @checked(old('system_notifications', $user->system_notifications ?? true))>
                            Show in-app alerts for this user
                        </label>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.users.index') }}"
                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#ff4b5c] to-[#ff7a45] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#ff4b5c]/30 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Save changes
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
