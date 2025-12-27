{{-- resources/views/admin/orders/create.blade.php --}}

<x-app-layout>
    <x-slot name="sectionTitle">Sales</x-slot>
    <x-slot name="pageTitle">New Order</x-slot>

    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.orders.index') }}" class="text-slate-500 hover:text-slate-700">Orders</a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Create</span>
    </x-slot>

    <div class="space-y-6">
        @if (session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <div class="text-sm font-black text-slate-900">Create order</div>
            <div class="mt-1 text-xs text-slate-500">Creates a draft order, then you can add items and finishings.</div>

            <form method="POST" action="{{ route('admin.orders.store') }}" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                @csrf

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Working group</label>
                    <select name="working_group_id"
                        class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                        @foreach (($workingGroups ?? collect()) as $wg)
                            <option value="{{ $wg->id }}" {{ (int) old('working_group_id', auth()->user()?->working_group_id) === (int) $wg->id ? 'selected' : '' }}>
                                {{ $wg->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('working_group_id')
                        <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Currency</label>
                    <input name="currency" value="{{ old('currency', 'LKR') }}"
                        class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                    @error('currency')
                        <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Customer (optional)</label>
                    <select name="customer_id"
                        class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                        <option value="">— Snapshot only —</option>
                        @foreach (($customers ?? collect()) as $c)
                            <option value="{{ $c->id }}" {{ (int) old('customer_id') === (int) $c->id ? 'selected' : '' }}>
                                {{ $c->full_name }} ({{ $c->phone ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="md:col-span-2 flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                        Create
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>

