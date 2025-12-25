{{-- resources/views/admin/estimates/create.blade.php --}}

<x-app-layout>
    <x-slot name="sectionTitle">Sales</x-slot>
    <x-slot name="pageTitle">New Estimate</x-slot>

    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.estimates.index') }}" class="text-slate-500 hover:text-slate-700">Estimates</a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Create</span>
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-slate-900">Create a draft estimate</h2>
                <p class="mt-1 text-sm text-slate-500">A draft is editable until you send it (sending locks it).</p>
            </div>

            <form method="POST" action="{{ route('admin.estimates.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Working Group <span class="text-rose-600">*</span>
                        </label>
                        <select name="working_group_id" required
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                            <option value="">Select…</option>
                            @foreach (($workingGroups ?? []) as $wg)
                                <option value="{{ $wg->id }}" {{ old('working_group_id') == $wg->id ? 'selected' : '' }}>
                                    {{ $wg->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('working_group_id')
                            <div class="mt-2 text-sm text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Customer ID (optional)
                        </label>
                        <input type="number" name="customer_id" value="{{ old('customer_id') }}"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10"
                            placeholder="e.g. 123" />
                        @error('customer_id')
                            <div class="mt-2 text-sm text-rose-600">{{ $message }}</div>
                        @enderror
                        <p class="mt-2 text-xs text-slate-500">You can attach a customer later; this only links if you know the ID.</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Currency
                        </label>
                        <input type="text" name="currency" value="{{ old('currency', 'LKR') }}"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10"
                            placeholder="LKR" />
                        @error('currency')
                            <div class="mt-2 text-sm text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Valid Until (optional)
                        </label>
                        <input type="date" name="valid_until" value="{{ old('valid_until') }}"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10" />
                        @error('valid_until')
                            <div class="mt-2 text-sm text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                        Internal Notes (optional)
                    </label>
                    <textarea name="notes_internal" rows="3"
                        class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10"
                        placeholder="Notes for staff only…">{{ old('notes_internal') }}</textarea>
                    @error('notes_internal')
                        <div class="mt-2 text-sm text-rose-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('admin.estimates.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                        Create Draft
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>

