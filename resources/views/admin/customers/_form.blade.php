{{-- resources/views/admin/customers/_form.blade.php --}}

@php
    /** @var \App\Models\Customer|null $customer */
    $isEdit = isset($customer) && $customer?->id;
@endphp

<form method="POST"
    action="{{ $isEdit ? route('admin.customers.update', $customer) : route('admin.customers.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        {{-- Full Name --}}
        <div>
            <label for="full_name" class="block text-xs font-medium uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Full Name <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="full_name" id="full_name"
                value="{{ old('full_name', $customer->full_name ?? '') }}"
                required
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
            @error('full_name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-xs font-medium uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Email
            </label>
            <input type="email" name="email" id="email"
                value="{{ old('email', $customer->email ?? '') }}"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
            @error('email')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Phone (Primary Contact) --}}
        <div>
            <label for="phone"
                class="block text-xs font-medium uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Phone <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="phone" id="phone"
                value="{{ old('phone', $customer->phone ?? '') }}"
                placeholder="+9477XXXXXXX"
                required
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
            @error('phone')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- WhatsApp Number --}}
        <div>
            <label for="whatsapp_number"
                class="block text-xs font-medium uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                WhatsApp Number
            </label>
            <input type="text" name="whatsapp_number" id="whatsapp_number"
                value="{{ old('whatsapp_number', $customer->whatsapp_number ?? '') }}"
                placeholder="+9477XXXXXXX"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
            @error('whatsapp_number')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Customer Type --}}
        <div>
            <label for="type"
                class="block text-xs font-medium uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Customer Type
            </label>
            <select name="type" id="type"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                @php
                    $customerType = old('type', $customer->type ?? 'walk_in');
                @endphp
                <option value="walk_in" {{ $customerType === 'walk_in' ? 'selected' : '' }}>Walk-in</option>
                <option value="account" {{ $customerType === 'account' ? 'selected' : '' }}>Account / Corporate</option>
                <option value="corporate" {{ $customerType === 'corporate' ? 'selected' : '' }}>Corporate</option>
            </select>
            @error('type')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Working Group --}}
        <div>
            <label for="working_group_id"
                class="block text-xs font-medium uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Working Group
            </label>
            <select name="working_group_id" id="working_group_id"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                <option value="">Default: Public</option>
                @foreach ($workingGroups as $wg)
                    <option value="{{ $wg->id }}"
                        {{ (string) old('working_group_id', $customer->working_group_id ?? '') === (string) $wg->id ? 'selected' : '' }}>
                        {{ $wg->name }}
                    </option>
                @endforeach
            </select>
            @error('working_group_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Status --}}
        <div>
            <label for="status"
                class="block text-xs font-medium uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Status
            </label>
            @php
                $status = old('status', $customer->status ?? 'active');
            @endphp
            <select name="status" id="status"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Company Name --}}
        <div class="md:col-span-2">
            <label for="company_name"
                class="block text-xs font-medium uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Company / Organisation
            </label>
            <input type="text" name="company_name" id="company_name"
                value="{{ old('company_name', $customer->company_name ?? '') }}"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
            @error('company_name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Company Phone --}}
        <div>
            <label for="company_phone"
                class="block text-xs font-medium uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Company Phone
            </label>
            <input type="text" name="company_phone" id="company_phone"
                value="{{ old('company_phone', $customer->company_phone ?? '') }}"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
            @error('company_phone')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Notes --}}
        <div class="md:col-span-2">
            <label for="notes"
                class="block text-xs font-medium uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Internal Notes
            </label>
            <textarea name="notes" id="notes" rows="3"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20"
                placeholder="Eg: Repeat client from Kelaniya, prefers matte lamination for business cards.">{{ old('notes', $customer->notes ?? '') }}</textarea>
            @error('notes')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mt-6 flex items-center justify-end gap-3">
        <a href="{{ route('admin.customers.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition">
            Cancel
        </a>
        <button type="submit"
            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#ff4b5c] to-[#ff7a45] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#ff4b5c]/30 hover:from-[#ff4b5c] hover:to-[#ff6a30] hover:shadow-lg transition-all">
            @if ($isEdit)
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                </svg>
                Save Changes
            @else
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Create Customer
            @endif
        </button>
    </div>
</form>
