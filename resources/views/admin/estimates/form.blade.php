{{-- resources/views/admin/estimates/form.blade.php --}}

<x-app-layout>
    <x-slot name="sectionTitle">Sales</x-slot>
    <x-slot name="pageTitle">{{ $mode === 'create' ? 'New Estimate' : 'Edit Estimate' }}</x-slot>

    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.estimates.index') }}" class="text-slate-500 hover:text-slate-700">Estimates</a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">{{ $mode === 'create' ? 'Create' : 'Edit' }}</span>
    </x-slot>

    @php
        $locked = (bool) ($estimate?->locked_at);
        $canEdit = $estimate ? auth()->user()?->can('update', $estimate) : auth()->user()?->can('create', \App\Models\Estimate::class);

	        $currency = $estimate->currency ?? 'LKR';
	        $initial = [
	            'id' => $estimate->id ?? null,
	            'estimate_no' => $estimate->estimate_no ?? null,
	            'status' => $estimate->status ?? 'draft',
	            'working_group_id' => $estimate->working_group_id ?? null,
	            'currency' => $currency,
	            'valid_until' => $mode === 'create'
	                ? (optional($estimate?->valid_until)->format('Y-m-d') ?? now()->addDays(14)->format('Y-m-d'))
	                : optional($estimate?->valid_until)->format('Y-m-d'),
	            'tax_mode' => $estimate->tax_mode ?? 'none',
	            'discount_mode' => $estimate->discount_mode ?? 'none',
	            'discount_value' => (float) ($estimate->discount_value ?? 0),
	            'notes_internal' => $estimate->notes_internal ?? '',
            'notes_customer' => $estimate->notes_customer ?? '',
            'terms' => $estimate->terms ?? 'To start the project, a 50% deposit is required. The remaining balance is due upon completion unless otherwise agreed in writing.',
            'customer_id' => $estimate->customer_id ?? null,
            'customer_snapshot' => $estimate->customer_snapshot ?? [],
	            'items' => ($estimate?->items ?? collect())->map(fn($it) => [
	                'id' => $it->id,
	                'product_id' => $it->product_id,
	                'title' => $it->title,
	                'description' => $it->description,
	                'qty' => (int) $it->qty,
	                'width' => $it->width,
	                'height' => $it->height,
	                'unit' => $it->unit,
	                'area_sqft' => $it->area_sqft,
	                'offcut_sqft' => $it->offcut_sqft,
	                'roll_id' => $it->roll_id,
	                'pricing_snapshot' => $it->pricing_snapshot,
	                'unit_price' => (float) $it->unit_price,
	                'line_subtotal' => (float) $it->line_subtotal,
	                'discount_amount' => (float) ($it->discount_amount ?? 0),
	                'tax_amount' => (float) ($it->tax_amount ?? 0),
	                'line_total' => (float) $it->line_total,
	            ])->values(),
	        ];

        $customersForJs = ($customers ?? collect())->map(fn($c) => [
            'id' => $c->id,
            'full_name' => $c->full_name,
            'phone' => $c->phone,
            'email' => $c->email,
            'working_group_id' => $c->working_group_id,
            'type' => $c->type,
            'status' => $c->status,
        ])->values();

        $productsForJs = ($products ?? collect())->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'code' => $p->product_code,
        ])->values();
    @endphp

    <div
        x-data="estimateForm({
            mode: @js($mode),
            locked: @js($locked),
            canEdit: @js((bool) $canEdit),
            initial: @js($initial),
            customers: @js($customersForJs),
            products: @js([]),
            workingGroups: @js(($workingGroups ?? collect())->map(fn($wg)=>['id'=>$wg->id,'name'=>$wg->name])->values()),
            saveUrl: @js($mode==='create' ? route('admin.estimates.store') : route('admin.estimates.update', $estimate)),
            previewUrl: @js($estimate ? route('admin.estimates.show', $estimate) : null),
            productsUrl: @js(route('admin.estimates.products')),
            rollsUrlBase: @js(url('/admin/estimates/products')),
            quoteUrl: @js(route('admin.estimates.quote')),
            customerUsersUrl: @js(route('admin.estimates.customer-users')),
            customerCreateUrl: @js(route('admin.estimates.customers.store')),
            adminEstimatesBaseUrl: @js(url('/admin/estimates')),
        })"
        x-init="init()"
        class="space-y-6"
    >

        {{-- HERO BAND --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-5 sm:px-7 sm:py-6 text-white shadow-lg shadow-slate-900/20">
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2 max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium tracking-wide backdrop-blur">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        {{ $mode === 'create' ? 'Create Draft' : 'Edit Draft' }} · Estimates
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 shadow-inner shadow-black/20">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 7.5h6M9 12h6m-9 7.5h12A2.25 2.25 0 0020.25 17.25V6.75A2.25 2.25 0 0018 4.5H6A2.25 2.25 0 003.75 6.75v10.5A2.25 2.25 0 006 19.5z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold leading-tight">
                                <span x-text="mode==='create' ? 'New Estimate Draft' : (state.estimate_no ?? ('Estimate #' + state.id))"></span>
                            </h2>
                            <p class="text-xs sm:text-sm text-white/70">
                                Build quotation lines with live totals. Locked estimates are view-only.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs">
                            <span class="font-semibold">Status:</span>
                            <span class="font-bold" x-text="state.status"></span>
                        </span>

                        <template x-if="locked">
                            <span class="inline-flex items-center gap-2 rounded-full bg-rose-500/15 px-3 py-1 text-xs">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-300"></span>
                                Locked — editing disabled
                            </span>
                        </template>

                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs">
                            <span class="font-semibold">Items:</span>
                            <span class="font-bold" x-text="state.items.length"></span>
                        </span>
                    </div>
                </div>

                <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-end">
                    <button type="button" @click="addItem()" :disabled="isReadOnly()"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-md shadow-black/10 hover:shadow-lg hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:hover:translate-y-0">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Item
                    </button>

                    <button type="button" @click="save('stay')" :disabled="saving || isReadOnly()"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-600 disabled:opacity-50">
                        <span x-show="!saving">Save Draft</span>
                        <span x-show="saving">Saving…</span>
                    </button>

                    <button type="button" @click="save('preview')" :disabled="saving || isReadOnly()"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50">
                        Save & Preview
                    </button>

                    <button type="button" @click="save('publish')" :disabled="saving || isReadOnly()"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50">
                        Publish (Email + PDF)
                    </button>
                </div>
            </div>
        </section>

        {{-- KPI STRIP --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <template x-for="card in kpiCards()" :key="card.key">
                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400" x-text="card.label"></p>
                    <p class="mt-2 text-2xl font-black text-slate-900">
                        <span x-text="state.currency"></span>
                        <span x-text="formatMoney(card.value())"></span>
                    </p>
                    <p class="mt-1 text-xs text-slate-500" x-text="card.help"></p>
                </div>
            </template>
        </section>

        {{-- MAIN GRID --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            {{-- LEFT: CUSTOMER + META --}}
            <section class="space-y-6 xl:col-span-1">
                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900">Customer</h3>
                    <p class="mt-1 text-xs text-slate-500">Choose an existing customer or fill snapshot fields.</p>

                    <div class="mt-4">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Existing customer
                        </label>

                        <div class="flex items-center gap-2">
                            <select x-model="state.customer_id" :disabled="isReadOnly()"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                            <option value="">— Snapshot only —</option>
                            <template x-for="c in filteredCustomers()" :key="c.id">
                                <option :value="c.id" x-text="`${c.full_name} (${c.phone ?? '—'})`"></option>
                            </template>
                            </select>

                            <button type="button" @click="openCustomerModal('walk_in')" :disabled="isReadOnly()"
                                class="shrink-0 inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-50">
                                + Walk-in
                            </button>

                            <button type="button" @click="openCustomerModal('from_user')" :disabled="isReadOnly()"
                                class="shrink-0 inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-50">
                                + From User
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Name</label>
                            <input x-model="state.customer_snapshot.full_name" :disabled="isReadOnly()"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                placeholder="Customer name" />
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Phone</label>
                                <input x-model="state.customer_snapshot.phone" :disabled="isReadOnly()"
                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                    placeholder="07x..." />
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Email</label>
                                <input x-model="state.customer_snapshot.email" :disabled="isReadOnly()"
                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                    placeholder="email@..." />
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-600">
                        <p class="font-semibold text-slate-700">Audit snapshot</p>
                        <p class="mt-1">Even when a customer is selected, the snapshot is stored for consistency.</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900">Settings</h3>

                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Working Group <span class="text-rose-600">*</span>
                            </label>
                            <select x-model="state.working_group_id" :disabled="mode !== 'create' || isReadOnly()"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                                <option value="">Select…</option>
                                <template x-for="wg in workingGroups" :key="wg.id">
                                    <option :value="wg.id" x-text="wg.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Currency</label>
                            <input x-model="state.currency" :disabled="isReadOnly()"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                placeholder="LKR" />
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Valid Until</label>
                            <input type="date" x-model="state.valid_until" :disabled="isReadOnly()"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Discount Mode</label>
                            <select x-model="state.discount_mode" :disabled="isReadOnly()"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                                <option value="none">None</option>
                                <option value="percent">Percent</option>
                                <option value="amount">Amount</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Discount Value</label>
                            <input type="number" step="0.01" x-model.number="state.discount_value" :disabled="isReadOnly() || state.discount_mode==='none'"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                placeholder="0" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Tax Mode</label>
                        <select x-model="state.tax_mode" :disabled="isReadOnly()"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                            <option value="none">None</option>
                            <option value="inclusive">Inclusive</option>
                            <option value="exclusive">Exclusive</option>
                        </select>
                        <p class="mt-2 text-[11px] text-slate-500">Line-level tax amounts drive totals today; this field is stored for future pricing rules.</p>
                    </div>
                </div>
            </section>

            {{-- RIGHT: ITEMS --}}
            <section class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden xl:col-span-2">
                <div class="border-b border-slate-100 bg-slate-50/60 px-5 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Items</h3>
                            <p class="mt-1 text-xs text-slate-500">Add quotation lines. Totals update live.</p>
                        </div>

                        <button type="button" @click="addItem()" :disabled="isReadOnly()"
                            class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add item
                        </button>
                    </div>
                </div>

                <div class="p-5 sm:p-6 space-y-4">
                    <template x-if="state.items.length === 0">
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-8 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 13.5h6m-6 3h6M6 3h12a2.25 2.25 0 012.25 2.25v15A.75.75 0 0119.5 21H4.5A.75.75 0 013.75 20.25v-15A2.25 2.25 0 016 3z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-semibold text-slate-900">No items added</h4>
                            <p class="mt-1 text-sm text-slate-500">Add at least one item to create a meaningful estimate.</p>
                        </div>
                    </template>

                    <template x-for="(it, idx) in state.items" :key="it._key">
                        <div class="rounded-3xl border border-slate-200 p-4 sm:p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 w-full">
	                                    <div class="flex items-center gap-2">
	                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-2xl bg-slate-100 text-xs font-bold text-slate-600" x-text="idx+1"></span>

	                                        <div class="relative w-full" @click.away="it._product_open=false">
	                                            <button type="button" @click="openProductPicker(it)" :disabled="isReadOnly()"
	                                                class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-left text-sm text-slate-900 shadow-sm transition-all hover:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
	                                                <img :src="(productForItem(it)?.image_url || '{{ asset('assets/placeholders/product.png') }}')"
	                                                    class="h-9 w-9 rounded-xl border border-slate-200 bg-white object-cover" alt="">

	                                                <div class="min-w-0 flex-1">
	                                                    <template x-if="productForItem(it)">
	                                                        <div class="min-w-0">
	                                                            <div class="truncate font-semibold" x-text="productForItem(it)?.name"></div>
	                                                            <div class="mt-0.5 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
	                                                                <span class="truncate" x-text="productForItem(it)?.code ? `Code: ${productForItem(it)?.code}` : ''"></span>
	                                                                <span class="text-slate-300" x-show="productForItem(it)?.code">•</span>
	                                                                <span class="font-semibold text-slate-700" x-text="productForItem(it)?.price_label || '—'"></span>
	                                                            </div>
	                                                        </div>
	                                                    </template>

	                                                    <template x-if="!productForItem(it)">
	                                                        <div class="text-slate-500">Search product…</div>
	                                                    </template>
	                                                </div>

	                                                <div class="text-slate-400">
	                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
	                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15l3.75 3.75L15.75 15m-7.5-6l3.75-3.75L15.75 9" />
	                                                    </svg>
	                                                </div>
	                                            </button>

	                                            <div x-show="it._product_open" x-cloak
	                                                class="absolute z-40 mt-2 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
	                                                <div class="border-b border-slate-100 p-3">
	                                                    <div class="relative">
	                                                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
	                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
	                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
	                                                            </svg>
	                                                        </span>
	                                                        <input x-model="it._product_search" @input="searchProductsForItem(it)" :disabled="isReadOnly()"
	                                                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 py-2 pl-9 pr-3 text-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10"
	                                                            placeholder="Type product name or code…" />
	                                                    </div>
	                                                    <p class="mt-2 text-[11px] text-slate-500">
	                                                        Prices are based on the selected working group.
	                                                    </p>
	                                                </div>

	                                                <div class="max-h-80 overflow-auto divide-y divide-slate-100">
	                                                    <template x-if="it._product_results.length === 0">
	                                                        <div class="px-4 py-4 text-sm text-slate-500">No products found.</div>
	                                                    </template>
	                                                    <template x-for="p in it._product_results" :key="p.id">
	                                                        <button type="button" @click="selectProduct(it, p)"
	                                                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-slate-50">
	                                                            <img :src="p.image_url || '{{ asset('assets/placeholders/product.png') }}'"
	                                                                class="h-10 w-10 rounded-xl border border-slate-200 bg-white object-cover" alt="">
	                                                            <div class="min-w-0 flex-1">
	                                                                <div class="truncate text-sm font-semibold text-slate-900" x-text="p.name"></div>
	                                                                <div class="mt-0.5 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
	                                                                    <span class="truncate" x-text="p.code ? `(${p.code})` : ''"></span>
	                                                                    <span class="text-slate-300" x-show="p.code">•</span>
	                                                                    <span class="font-semibold text-slate-700" x-text="p.price_label || '—'"></span>
	                                                                    <span class="text-slate-300">•</span>
	                                                                    <span x-text="p.is_dimension_based ? 'Dimension-based' : 'Unit/Service'"></span>
	                                                                </div>
	                                                            </div>
	                                                        </button>
	                                                    </template>
	                                                </div>
	                                            </div>
	                                        </div>
	                                    </div>

                                    <template x-if="requiresDimensions(it)">
                                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                            <div>
                                                <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Width</label>
                                                <input type="number" min="0.01" step="0.01" x-model.number="it.width" @input="scheduleQuote(it)" :disabled="isReadOnly()"
                                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                                    placeholder="e.g. 24" />
                                            </div>
                                            <div>
                                                <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Height</label>
                                                <input type="number" min="0.01" step="0.01" x-model.number="it.height" @input="scheduleQuote(it)" :disabled="isReadOnly()"
                                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                                    placeholder="e.g. 60" />
                                            </div>
                                            <div>
                                                <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Unit</label>
                                                <select x-model="it.unit" @change="scheduleQuote(it)" :disabled="isReadOnly()"
                                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                                                    <option value="in">in</option>
                                                    <option value="ft">ft</option>
                                                    <option value="mm">mm</option>
                                                    <option value="cm">cm</option>
                                                    <option value="m">m</option>
                                                </select>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="requiresDimensions(it) && (it._available_rolls?.length)">
                                        <div class="mt-3">
                                            <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Material Roll</label>
                                            <select x-model="it.roll_choice" @change="onRollChoiceChanged(it)" :disabled="isReadOnly()"
                                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                                                <option value="">Auto</option>
                                                <template x-for="r in (it._available_rolls || [])" :key="r.roll_id">
                                                    <option :value="String(r.roll_id)" x-text="`${r.name} (${Number(r.width_in).toFixed(1)}in)`"></option>
                                                </template>
                                            </select>

                                            <template x-if="rollSummary(it)">
                                                <div class="mt-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span x-text="rollSummary(it)"></span>
                                                        <template x-if="it._roll_rotated">
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800">Rotated to fit</span>
                                                        </template>
                                                        <template x-if="it.area_sqft">
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">
                                                                Area: <span x-text="Number(it.area_sqft).toFixed(4)"></span> sqft
                                                            </span>
                                                        </template>
                                                        <template x-if="it.offcut_sqft && Number(it.offcut_sqft) > 0">
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">
                                                                Offcut: <span x-text="Number(it.offcut_sqft).toFixed(4)"></span> sqft
                                                            </span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <div class="mt-3">
                                        <input x-model="it.title" :disabled="isReadOnly()"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                            placeholder="Item title (e.g., Banner Printing)" />
                                    </div>

                                    <textarea x-model="it.description" :disabled="isReadOnly()" rows="2"
                                        class="mt-3 w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                        placeholder="Short description…"></textarea>

	                                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-5">
	                                        <div>
	                                            <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Qty</label>
	                                            <input type="number" min="1" step="1" x-model.number="it.qty" @input="scheduleQuote(it)" :disabled="isReadOnly()"
	                                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
	                                        </div>
	                                        <div>
	                                            <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Unit Price</label>
	                                            <input type="number" min="0" step="0.01" x-model.number="it.unit_price" @input="it._manual_price=true; it._use_quoted_subtotal=false; recalcItem(it)" :disabled="isReadOnly()"
	                                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
	                                            <p class="mt-1 text-[11px] text-slate-500" x-show="!it._manual_price">Auto-priced from WG pricing.</p>
	                                            <p class="mt-1 text-[11px] text-amber-700" x-show="it._manual_price">Manual override.</p>
	                                        </div>
	                                        <div>
	                                            <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Line Discount</label>
	                                            <input type="number" min="0" step="0.01" x-model.number="it.discount_amount" @input="recalcItem(it)" :disabled="isReadOnly()"
	                                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
	                                                placeholder="0.00" />
	                                        </div>
	                                        <div>
	                                            <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Line Total</label>
	                                            <div class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900">
	                                                <span x-text="state.currency"></span>
	                                                <span x-text="formatMoney(it.line_total)"></span>
	                                            </div>
	                                            <p class="mt-1 text-[11px] text-slate-500">
	                                                Sub: <span x-text="formatMoney(it.line_subtotal)"></span>
	                                            </p>
	                                        </div>
	                                        <div class="flex items-end justify-end">
	                                            <button type="button" @click="confirmRemove(idx)" :disabled="isReadOnly()"
	                                                class="inline-flex items-center gap-1.5 rounded-2xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 shadow-sm hover:bg-rose-50 disabled:opacity-50">
	                                                Delete
	                                            </button>
	                                        </div>
	                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500">
                                <span>Subtotal: <span class="font-semibold text-slate-700" x-text="formatMoney(it.line_subtotal)"></span></span>
                                <span>Discount: <span class="font-semibold text-slate-700" x-text="formatMoney(it.discount_amount)"></span></span>
                                <span>Tax: <span class="font-semibold text-slate-700" x-text="formatMoney(it.tax_amount)"></span></span>
                            </div>
                        </div>
                    </template>

	                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
	                        <div class="flex flex-col gap-3">
	                            <div class="flex items-center justify-between text-sm">
	                                <div class="text-slate-600">Subtotal</div>
	                                <div class="font-semibold text-slate-900">
	                                    <span x-text="state.currency"></span>
	                                    <span x-text="formatMoney(subtotal())"></span>
	                                </div>
	                            </div>

	                            <div class="flex items-center justify-between text-sm">
	                                <div class="text-slate-600">Line Discounts</div>
	                                <div class="font-semibold text-slate-900">
	                                    <span x-text="state.currency"></span>
	                                    <span x-text="formatMoney(lineDiscountTotal())"></span>
	                                </div>
	                            </div>

	                            <div class="flex items-center justify-between text-sm">
	                                <div class="text-slate-600">Estimate Discount</div>
	                                <div class="font-semibold text-slate-900">
	                                    <span x-text="state.currency"></span>
	                                    <span x-text="formatMoney(estimateDiscount())"></span>
	                                </div>
	                            </div>

	                            <div class="flex items-center justify-between text-sm">
	                                <div class="text-slate-600">Tax (line)</div>
	                                <div class="font-semibold text-slate-900">
	                                    <span x-text="state.currency"></span>
	                                    <span x-text="formatMoney(taxTotal())"></span>
	                                </div>
	                            </div>

	                            <div class="h-px bg-slate-200/70"></div>

	                            <div class="flex items-center justify-between">
	                                <div class="text-slate-700 font-semibold">Grand Total</div>
	                                <div class="text-xl font-black text-slate-900">
	                                    <span x-text="state.currency"></span>
	                                    <span x-text="formatMoney(grandTotal())"></span>
	                                </div>
	                            </div>
	                        </div>

	                        <p class="mt-3 text-xs text-slate-500">
	                            Grand total = subtotal - (line discounts + estimate discount) + tax.
	                        </p>
	                    </div>

                    {{-- Notes --}}
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Internal Notes
                            </label>
                            <textarea x-model="state.notes_internal" :disabled="isReadOnly()" rows="4"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                placeholder="Visible only to staff…"></textarea>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Customer Notes
                            </label>
                            <textarea x-model="state.notes_customer" :disabled="isReadOnly()" rows="4"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                placeholder="Appears on quotation…"></textarea>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Terms & Conditions
                        </label>
                        <textarea x-model="state.terms" :disabled="isReadOnly()" rows="4"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                            placeholder="Delivery, payment terms, validity…"></textarea>
                    </div>
                </div>
            </section>
        </div>

        {{-- Delete Item Modal --}}
        <div x-show="modals.remove.open" x-cloak>
            <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm" @click="closeRemove()"></div>
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl overflow-hidden" @click.away="closeRemove()">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-900">Remove item?</h3>
                        <p class="mt-1 text-sm text-slate-500">This will remove the line from the draft.</p>
                    </div>
                    <div class="px-6 py-5 text-sm text-slate-700">
                        Are you sure you want to delete this item?
                    </div>
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/60 flex justify-end gap-3">
                        <button class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            @click="closeRemove()">Cancel</button>
                        <button class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700"
                            @click="removeConfirmed()">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Add Customer Modal (Walk-in / From User) --}}
        <div x-show="modals.customer.open" x-cloak>
            <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm" @click="closeCustomerModal()"></div>
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden" @click.away="closeCustomerModal()">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-900" x-text="modals.customer.mode === 'walk_in' ? 'Add Walk-in Customer' : 'Create Customer From User'"></h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Working group will be auto-selected from the customer/user.
                        </p>
                    </div>

                    <div class="px-6 py-5 space-y-4">
                        <template x-if="modals.customer.mode === 'walk_in'">
                            <div class="space-y-3">
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Working Group</label>
                                    <select x-model="modals.customer.walkIn.working_group_id" :disabled="isReadOnly()"
                                        class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                                        <option value="">Select…</option>
                                        <template x-for="wg in workingGroups" :key="wg.id">
                                            <option :value="String(wg.id)" x-text="wg.name"></option>
                                        </template>
                                    </select>
                                    <p class="mt-2 text-[11px] text-slate-500">If you already selected a WG in the estimate, it will be used.</p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Full Name</label>
                                    <input x-model="modals.customer.walkIn.full_name" :disabled="isReadOnly()"
                                        class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                        placeholder="Customer name" />
                                </div>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Phone</label>
                                        <input x-model="modals.customer.walkIn.phone" :disabled="isReadOnly()"
                                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                            placeholder="07x..." />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Email (optional)</label>
                                        <input x-model="modals.customer.walkIn.email" :disabled="isReadOnly()"
                                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                            placeholder="email@..." />
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="modals.customer.mode === 'from_user'">
                            <div class="space-y-3">
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Search user</label>
                                    <input x-model="modals.customer.user.q" @input="searchCustomerUsers()"
                                        class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10"
                                        placeholder="Name, email, WhatsApp…" />
                                    <p class="mt-2 text-[11px] text-slate-500">Internal/staff roles are excluded automatically.</p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                                    <div class="max-h-56 overflow-auto divide-y divide-slate-100">
                                        <template x-if="modals.customer.user.results.length === 0">
                                            <div class="px-4 py-4 text-sm text-slate-500">No users found.</div>
                                        </template>
                                        <template x-for="u in modals.customer.user.results" :key="u.id">
                                            <button type="button" @click="selectCustomerUser(u)"
                                                class="w-full px-4 py-3 text-left hover:bg-slate-50">
                                                <div class="text-sm font-semibold text-slate-900" x-text="u.full_name"></div>
                                                <div class="mt-1 text-xs text-slate-500" x-text="`${u.email} · WG#${u.working_group_id ?? '—'}`"></div>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <template x-if="modals.customer.user.selected">
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700">
                                        Selected: <span class="font-semibold" x-text="modals.customer.user.selected.full_name"></span>
                                        <span class="text-slate-400">·</span>
                                        WG: <span class="font-semibold" x-text="modals.customer.user.selected.working_group_id ?? '—'"></span>
                                    </div>
                                </template>

                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Phone (required)</label>
                                    <input x-model="modals.customer.user.phone_override"
                                        class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10"
                                        placeholder="07x..." />
                                    <p class="mt-2 text-[11px] text-slate-500">If the user has WhatsApp saved, it will auto-fill here.</p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/60 flex justify-end gap-3">
                        <button class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            @click="closeCustomerModal()">Cancel</button>
                        <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 disabled:opacity-50"
                            :disabled="modals.customer.saving"
                            @click="createCustomerFromModal()">
                            <span x-show="!modals.customer.saving">Create & Use</span>
                            <span x-show="modals.customer.saving">Creating…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
	            function estimateForm({ mode, locked, canEdit, initial, customers, products, workingGroups, saveUrl, previewUrl, productsUrl, rollsUrlBase, quoteUrl, customerUsersUrl, customerCreateUrl, adminEstimatesBaseUrl }) {
	                const uuid = () => (globalThis.crypto?.randomUUID?.() ?? (Date.now().toString(16) + Math.random().toString(16).slice(2)));

	                return {
	                    mode, locked, canEdit,
	                    customers, products, workingGroups,
	                    productsUrl, rollsUrlBase, quoteUrl,
	                    customerUsersUrl, customerCreateUrl,
                        adminEstimatesBaseUrl,
	                    saving: false,
	                    productsById: {},
	                    rollsByProductId: {},

                    state: {
                        ...initial,
	                        items: (initial.items || []).map(i => ({
	                            ...i,
	                            _key: uuid(),
	                            _product: null,
	                            _product_open: false,
	                            _product_search: '',
	                            _product_search_t: null,
	                            _product_results: [],
	                            roll_choice: i.roll_id ? String(i.roll_id) : '',
	                            _pricing_snapshot: i.pricing_snapshot || null,
	                            _use_quoted_subtotal: false,
	                            _manual_price: false,
	                            _available_rolls: [],
	                            _roll_auto: false,
	                            _roll_rotated: false,
	                        })),
                        customer_snapshot: initial.customer_snapshot || {},
                    },

                    modals: {
                        remove: { open: false, idx: null },
                        customer: {
                            open: false,
                            mode: 'walk_in',
                            saving: false,
                            walkIn: { working_group_id: '', full_name: '', phone: '', email: '' },
                            user: { q: '', results: [], selected: null, phone_override: '' },
                        },
                    },

	                    init() {
	                        this.$watch('state.working_group_id', async (val) => {
	                            if (!val) {
	                                this.productsById = {};
	                                return;
	                            }
	                            await this.hydrateSelectedProducts();
	                            // Re-quote items (especially when WG changes)
	                            this.state.items.forEach(it => this.scheduleQuote(it));
	                        });

                        this.$watch('state.customer_id', (val) => {
                            if (!val) return;
                            const c = this.customers.find(x => String(x.id) === String(val));
                            if (!c) return;
                            if (c.working_group_id && String(this.state.working_group_id || '') !== String(c.working_group_id)) {
                                if (this.mode === 'create') {
                                    // WG auto-select from customer (enterprise rule)
                                    this.state.working_group_id = Number(c.working_group_id);
                                } else {
                                    // edit mode: WG is immutable; block cross-WG customer selection
                                    alert('This estimate belongs to a different working group. Please choose a customer from the same working group.');
                                    this.state.customer_id = '';
                                    return;
                                }
                            }
                            this.state.customer_snapshot = {
                                ...(this.state.customer_snapshot || {}),
                                full_name: c.full_name ?? '',
                                phone: c.phone ?? '',
                                email: c.email ?? '',
                            };
                        });

                        if (this.mode === 'create' && this.state.items.length === 0) {
                            this.addItem();
                        }

	                        // Load products if WG already selected (edit)
	                        if (this.state.working_group_id) {
	                            this.hydrateSelectedProducts().then(() => {
	                                this.state.items.forEach(it => {
	                                    this.loadRollsForItem(it);
	                                    this.scheduleQuote(it);
	                                });
	                            });
                        } else {
                            this.state.items.forEach(it => this.recalcItem(it));
                        }
                    },

                    isReadOnly() {
                        return this.locked || !this.canEdit;
                    },

                    filteredCustomers() {
                        const wgId = this.state.working_group_id ? Number(this.state.working_group_id) : null;
                        if (!wgId) return this.customers;
                        return this.customers.filter(c => Number(c.working_group_id || 0) === wgId);
                    },

                    openCustomerModal(mode) {
                        if (this.isReadOnly()) return;
                        this.modals.customer.open = true;
                        this.modals.customer.mode = mode;
                        this.modals.customer.saving = false;
                        document.body.classList.add('overflow-hidden');

                        // default WG from estimate selection
                        const currentWg = this.state.working_group_id ? String(this.state.working_group_id) : '';
                        this.modals.customer.walkIn = { working_group_id: currentWg, full_name: '', phone: '', email: '' };
                        this.modals.customer.user = { q: '', results: [], selected: null, phone_override: '' };
                    },

                    closeCustomerModal() {
                        this.modals.customer.open = false;
                        document.body.classList.remove('overflow-hidden');
                    },

                    async searchCustomerUsers() {
                        const q = (this.modals.customer.user.q || '').trim();
                        if (q.length < 2) {
                            this.modals.customer.user.results = [];
                            return;
                        }
                        const url = new URL(this.customerUsersUrl, window.location.origin);
                        url.searchParams.set('q', q);

                        const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            this.modals.customer.user.results = [];
                            return;
                        }
                        this.modals.customer.user.results = data.items || [];
                    },

                    selectCustomerUser(u) {
                        this.modals.customer.user.selected = u;
                        this.modals.customer.user.phone_override = u.whatsapp_number || this.modals.customer.user.phone_override || '';

                        if (u.working_group_id && this.mode === 'create') {
                            this.state.working_group_id = Number(u.working_group_id);
                        }
                    },

                    async createCustomerFromModal() {
                        if (this.isReadOnly()) return;
                        this.modals.customer.saving = true;

                        try {
                            let payload = null;
                            if (this.modals.customer.mode === 'walk_in') {
                                const wg = this.modals.customer.walkIn.working_group_id || this.state.working_group_id;
                                payload = {
                                    mode: 'walk_in',
                                    working_group_id: wg ? Number(wg) : null,
                                    full_name: (this.modals.customer.walkIn.full_name || '').trim(),
                                    phone: (this.modals.customer.walkIn.phone || '').trim(),
                                    email: (this.modals.customer.walkIn.email || '').trim() || null,
                                };
                            } else {
                                const u = this.modals.customer.user.selected;
                                if (!u) throw new Error('Please select a user.');
                                payload = {
                                    mode: 'from_user',
                                    user_id: Number(u.id),
                                    phone_override: (this.modals.customer.user.phone_override || '').trim(),
                                };
                            }

                            const res = await fetch(this.customerCreateUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(payload),
                            });

                            const data = await res.json().catch(() => ({}));
                            if (!res.ok || !data?.ok) {
                                throw new Error(data?.message || 'Failed to create customer.');
                            }

                            const c = data.customer;
                            if (!c) throw new Error('Invalid response.');

                            // Add to list and select
                            this.customers.unshift(c);
                            this.state.customer_id = c.id;

                            if (c.working_group_id && this.mode === 'create') {
                                this.state.working_group_id = Number(c.working_group_id);
                            }

                            this.state.customer_snapshot = {
                                ...(this.state.customer_snapshot || {}),
                                full_name: c.full_name ?? '',
                                phone: c.phone ?? '',
                                email: c.email ?? '',
                            };

                            this.closeCustomerModal();
                        } catch (e) {
                            alert(e?.message || 'Failed to create customer.');
                        } finally {
                            this.modals.customer.saving = false;
                        }
                    },

	                    onProductSelected(it) {
	                        const p = this.productForItem(it);
	                        if (!p) return;
	                        if (!it.title) it.title = p.name;
                        if (!this.requiresDimensions(it)) {
                            it.width = null;
                            it.height = null;
                            it.unit = null;
                            it.roll_choice = '';
                            it.roll_id = null;
                            it.area_sqft = null;
                            it.offcut_sqft = 0;
                        } else {
                            if (!it.unit) it.unit = 'in';
	                        }
	                        it._manual_price = false;
	                        it._use_quoted_subtotal = false;
	                        it._pricing_snapshot = null;
	                        this.loadRollsForItem(it);
	                        this.scheduleQuote(it);
	                    },

	                    productForItem(it) {
	                        if (it?._product) return it._product;
	                        const pid = it?.product_id ? String(it.product_id) : '';
	                        if (!pid) return null;
	                        return this.productsById[pid] || null;
	                    },

	                    async hydrateSelectedProducts() {
	                        const wgId = this.state.working_group_id ? Number(this.state.working_group_id) : null;
	                        if (!wgId) return;

	                        const ids = Array.from(new Set(
	                            (this.state.items || [])
	                                .map(i => Number(i.product_id || 0))
	                                .filter(v => v > 0)
	                        ));

	                        if (ids.length === 0) return;

	                        const url = new URL(this.productsUrl, window.location.origin);
	                        url.searchParams.set('working_group_id', wgId);
	                        url.searchParams.set('ids', ids.join(','));
	                        url.searchParams.set('limit', Math.min(50, ids.length));

	                        const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
	                        const data = await res.json().catch(() => ({}));
	                        if (!res.ok) {
	                            throw new Error(data?.message || 'Unable to load selected products');
	                        }

	                        const items = data.items || [];
	                        items.forEach(p => {
	                            this.productsById[String(p.id)] = p;
	                        });

	                        // Attach to items for convenience
	                        this.state.items.forEach(it => {
	                            const pid = it.product_id ? String(it.product_id) : '';
	                            if (pid && this.productsById[pid]) {
	                                it._product = this.productsById[pid];
	                            }
	                        });
	                    },

	                    openProductPicker(it) {
	                        if (this.isReadOnly()) return;
	                        if (!this.state.working_group_id) {
	                            alert('Please select a working group first.');
	                            return;
	                        }
	                        // close others
	                        this.state.items.forEach(x => { if (x !== it) x._product_open = false; });
	                        it._product_open = !it._product_open;
	                        if (it._product_open) {
	                            this.searchProductsForItem(it, true);
	                        }
	                    },

	                    searchProductsForItem(it, immediate = false) {
	                        const wgId = this.state.working_group_id ? Number(this.state.working_group_id) : null;
	                        if (!wgId) {
	                            it._product_results = [];
	                            return;
	                        }

	                        if (it._product_search_t) {
	                            clearTimeout(it._product_search_t);
	                        }

	                        const run = async () => {
	                            const url = new URL(this.productsUrl, window.location.origin);
	                            url.searchParams.set('working_group_id', wgId);
	                            url.searchParams.set('limit', 20);
	                            const q = (it._product_search || '').trim();
	                            if (q) url.searchParams.set('q', q);

	                            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
	                            const data = await res.json().catch(() => ({}));
	                            if (!res.ok) {
	                                throw new Error(data?.message || 'Unable to search products');
	                            }
	                            it._product_results = data.items || [];
	                            (data.items || []).forEach(p => {
	                                this.productsById[String(p.id)] = p;
	                            });
	                        };

	                        if (immediate) {
	                            run().catch(e => { console.warn(e); it._product_results = []; });
	                            return;
	                        }

	                        it._product_search_t = setTimeout(() => {
	                            run().catch(e => { console.warn(e); it._product_results = []; });
	                        }, 200);
	                    },

	                    selectProduct(it, p) {
	                        it.product_id = String(p.id);
	                        it._product = p;
	                        it._product_open = false;
	                        it._product_search = '';
	                        it._product_results = [];
	                        this.onProductSelected(it);
	                    },

	                    addItem() {
	                        if (this.isReadOnly()) return;
	                        const it = {
	                            id: null,
	                            _key: uuid(),
	                            product_id: '',
	                            _product: null,
	                            _product_open: false,
	                            _product_search: '',
	                            _product_search_t: null,
	                            _product_results: [],
	                            title: '',
	                            description: '',
	                            qty: 1,
	                            width: null,
	                            height: null,
	                            unit: 'in',
	                            area_sqft: null,
	                            offcut_sqft: 0,
	                            roll_id: null,
	                            roll_choice: '',
	                            pricing_snapshot: null,
	                            _pricing_snapshot: null,
	                            _use_quoted_subtotal: false,
	                            unit_price: 0,
	                            line_subtotal: 0,
	                            discount_amount: 0,
	                            tax_amount: 0,
	                            line_total: 0,
	                            _manual_price: false,
                            _quote_t: null,
	                            _available_rolls: [],
	                            _roll_auto: false,
	                            _roll_rotated: false,
	                        };
                        this.state.items.push(it);
                        this.recalcItem(it);
                    },

                    async loadRollsForItem(it) {
                        it._available_rolls = it._available_rolls || [];

                        if (!this.requiresDimensions(it) || !it.product_id) {
                            it._available_rolls = [];
                            return;
                        }

                        const pid = String(it.product_id);
                        if (this.rollsByProductId[pid]) {
                            it._available_rolls = this.rollsByProductId[pid];
                            if (!it.roll_choice && it.roll_id) {
                                it.roll_choice = String(it.roll_id);
                            }
                            return;
                        }

                        try {
                            const url = new URL(`${this.rollsUrlBase}/${pid}/rolls`, window.location.origin);
                            if (this.state.working_group_id) {
                                url.searchParams.set('working_group_id', Number(this.state.working_group_id));
                            }
                            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                            const data = await res.json().catch(() => ({}));
                            if (!res.ok) {
                                throw new Error(data?.message || 'Unable to load rolls');
                            }
                            const list = data.items || [];
                            this.rollsByProductId[pid] = list;
                            it._available_rolls = list;
                            if (!it.roll_choice && it.roll_id) {
                                it.roll_choice = String(it.roll_id);
                            }
                        } catch (e) {
                            console.warn(e);
                            it._available_rolls = [];
                        }
                    },

                    onRollChoiceChanged(it) {
                        // roll_choice: '' => Auto; else explicit roll
                        if (!it.roll_choice) {
                            // Keep persisted roll_id populated by quote for audit/production, but drive quoting with choice
                            this.scheduleQuote(it);
                            return;
                        }
                        this.scheduleQuote(it);
                    },

                    rollSummary(it) {
                        const rollId = it.roll_id ? Number(it.roll_id) : null;
                        if (!rollId) return '';

                        const list = it._available_rolls || [];
                        const r = list.find(x => Number(x.roll_id) === rollId);
                        const name = r ? r.name : `Roll #${rollId}`;
                        const w = r && r.width_in !== undefined ? ` (${Number(r.width_in).toFixed(1)}in)` : '';

                        const isAuto = !it.roll_choice || it._roll_auto;
                        return isAuto ? `Auto selected: ${name}${w}` : `Selected roll: ${name}${w}`;
                    },

	                    requiresDimensions(it) {
	                        const p = this.productForItem(it);
	                        return !!(p && p.is_dimension_based);
	                    },

	                    recalcItem(it) {
	                        const qty = Math.max(0, Number(it.qty || 0));
	                        const unit = Math.max(0, Number(it.unit_price || 0));
	                        const subtotal = it._use_quoted_subtotal && !it._manual_price
	                            ? Math.max(0, Number(it.line_subtotal || 0))
	                            : Math.max(0, qty * unit);

	                        const discount = Math.max(0, Math.min(Number(it.discount_amount || 0), subtotal));
	                        const tax = Math.max(0, Number(it.tax_amount || 0));

	                        it.line_subtotal = subtotal;
	                        it.discount_amount = discount;
	                        it.tax_amount = tax;
	                        it.line_total = Math.max(0, it.line_subtotal - discount + tax);
	                    },

	                    async loadProducts() {
	                        // Legacy method retained for backwards compatibility.
	                        // We now fetch products on-demand (search/lookup) for scale.
	                        await this.hydrateSelectedProducts();
	                    },

                    scheduleQuote(it) {
                        if (this.isReadOnly()) {
                            this.recalcItem(it);
                            return;
                        }

                        if (!it.product_id || !this.state.working_group_id) {
                            this.recalcItem(it);
                            return;
                        }

                        if (it._quote_t) {
                            clearTimeout(it._quote_t);
                        }

                        it._quote_t = setTimeout(() => this.quoteItem(it), 250);
                    },

                    async quoteItem(it) {
                        if (!it.product_id || !this.state.working_group_id) return;

                        // For dimension-based products, require dimensions before quoting
                        if (this.requiresDimensions(it)) {
                            if (!it.width || !it.height || !it.unit) {
                                return;
                            }
                        }

                        const payload = {
                            working_group_id: Number(this.state.working_group_id),
                            product_id: Number(it.product_id),
                            qty: Number(it.qty || 1),
                            width: this.requiresDimensions(it) ? Number(it.width || 0) : null,
                            height: this.requiresDimensions(it) ? Number(it.height || 0) : null,
                            unit: this.requiresDimensions(it) ? (it.unit || 'in') : null,
                            roll_id: this.requiresDimensions(it) ? (it.roll_choice ? Number(it.roll_choice) : null) : null,
                        };

                        try {
                            const res = await fetch(this.quoteUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(payload),
                            });
                            const data = await res.json().catch(() => ({}));
                            if (!res.ok || !data?.ok) {
                                throw new Error(data?.message || 'Quote failed');
                            }

	                            const q = data.data;
	                            if (!q) return;
	
	                            it._pricing_snapshot = q.pricing_snapshot || null;

		                            // Always sync qty-derived fields
		                            it.qty = q.qty;

                            // Dimension-related fields
                            it.width = q.width ?? it.width;
                            it.height = q.height ?? it.height;
                            it.unit = q.unit ?? it.unit;
                            it.area_sqft = q.area_sqft ?? it.area_sqft;
                            it.offcut_sqft = q.offcut_sqft ?? it.offcut_sqft;
                            it.roll_id = q.roll_id ?? it.roll_id; // persist chosen roll (even when choice is Auto)
	                            it._roll_auto = !!q.roll_auto;
	                            it._roll_rotated = !!q.roll_rotated;

	                            // Only set unit_price if admin has not overridden it
	                            if (!it._manual_price) {
	                                it.unit_price = Number(q.unit_price || 0);
	                                it.line_subtotal = Number(q.line_subtotal || 0);
	                                it._use_quoted_subtotal = true;
	                            }

	                            // Always recompute line_total using current discount/tax (don’t overwrite admin-entered discounts)
	                            this.recalcItem(it);
		                        } catch (e) {
	                            // Keep silent (avoid spam); admin can still edit manually
	                            console.warn(e);
	                        }
	                    },

                    subtotal() {
                        return this.state.items.reduce((s, it) => s + Number(it.line_subtotal || 0), 0);
                    },

	                    lineDiscountTotal() {
	                        return this.state.items.reduce((s, it) => s + Number(it.discount_amount || 0), 0);
	                    },

                    taxTotal() {
                        return this.state.items.reduce((s, it) => s + Number(it.tax_amount || 0), 0);
                    },

	                    estimateDiscount() {
	                        const mode = this.state.discount_mode || 'none';
	                        const v = Math.max(0, Number(this.state.discount_value || 0));
	                        if (mode === 'none') return 0;

	                        const base = Math.max(0, this.subtotal() - this.lineDiscountTotal());
	                        if (base <= 0) return 0;

	                        if (mode === 'percent') return Math.min(base, base * (v / 100));
	                        if (mode === 'amount') return Math.min(base, v);

	                        return 0;
	                    },

	                    discountTotal() {
	                        return this.lineDiscountTotal() + this.estimateDiscount();
	                    },

	                    grandTotal() {
	                        return Math.max(0, this.subtotal() - this.discountTotal() + this.taxTotal());
	                    },

	                    kpiCards() {
	                        return [
	                            { key: 'sub', label: 'Subtotal', help: 'Sum of line subtotals', value: () => this.subtotal() },
	                            { key: 'disc', label: 'Discount', help: 'Line + estimate discount', value: () => this.discountTotal() },
	                            { key: 'tax', label: 'Tax', help: 'Sum of line taxes', value: () => this.taxTotal() },
	                            { key: 'grand', label: 'Grand Total', help: 'Subtotal - discounts + tax', value: () => this.grandTotal() },
	                        ];
	                    },

                    formatMoney(v) {
                        const n = Number(v || 0);
                        return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },

                    confirmRemove(idx) {
                        this.modals.remove = { open: true, idx };
                        document.body.classList.add('overflow-hidden');
                    },

                    closeRemove() {
                        this.modals.remove.open = false;
                        this.modals.remove.idx = null;
                        document.body.classList.remove('overflow-hidden');
                    },

                    removeConfirmed() {
                        const idx = this.modals.remove.idx;
                        if (idx === null) return;
                        this.state.items.splice(idx, 1);
                        this.closeRemove();
                    },

                    async save(next) {
                        if (this.isReadOnly()) return;

                        if (this.mode === 'create' && !this.state.working_group_id) {
                            alert('Working group is required.');
                            return;
                        }

                        // Basic item guard (DB requires product_id)
                        const missingProduct = this.state.items.find(it => !it.product_id);
                        if (missingProduct) {
                            alert('Please select a product for every item.');
                            return;
                        }

                        this.saving = true;

                        const payload = {
                            ...(this.mode === 'create' ? { working_group_id: Number(this.state.working_group_id) } : {}),

                            customer_id: this.state.customer_id ? Number(this.state.customer_id) : null,
                            customer_snapshot: this.state.customer_snapshot || {},
                            currency: this.state.currency || 'LKR',
                            valid_until: this.state.valid_until || null,

                            tax_mode: this.state.tax_mode || 'none',
                            discount_mode: this.state.discount_mode || 'none',
                            discount_value: Number(this.state.discount_value || 0),

                            notes_internal: this.state.notes_internal || null,
                            notes_customer: this.state.notes_customer || null,
                            terms: this.state.terms || null,

	                            items: this.state.items.map(i => ({
	                                id: i.id || null,
	                                product_id: Number(i.product_id),
	                                title: i.title || '',
	                                description: i.description || null,
	                                qty: Number(i.qty || 0),
	                                width: this.requiresDimensions(i) ? (i.width === null ? null : Number(i.width)) : null,
	                                height: this.requiresDimensions(i) ? (i.height === null ? null : Number(i.height)) : null,
	                                unit: this.requiresDimensions(i) ? (i.unit || 'in') : null,
	                                area_sqft: i.area_sqft === null ? null : Number(i.area_sqft),
	                                offcut_sqft: Number(i.offcut_sqft || 0),
	                                roll_id: i.roll_id ? Number(i.roll_id) : null,
	                                pricing_snapshot: i._pricing_snapshot || i.pricing_snapshot || null,
	                                unit_price: Number(i.unit_price || 0),
	                                line_subtotal: Number(i.line_subtotal || 0),
	                                discount_amount: Number(i.discount_amount || 0),
	                                tax_amount: Number(i.tax_amount || 0),
	                                line_total: Number(i.line_total || 0),
	                            })),
	                        };

                        try {
                            const res = await fetch(saveUrl, {
                                method: this.mode === 'create' ? 'POST' : 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(payload),
                            });

                            const data = await res.json().catch(() => ({}));
                            if (!res.ok) {
                                throw new Error(data?.message || 'Save failed');
                            }

                            const id = data?.id ?? this.state.id;
                            if (id) this.state.id = id;

                            const showUrl = data?.redirect_url || (id ? `${this.adminEstimatesBaseUrl}/${id}` : null);
                            const editUrl = id ? `${this.adminEstimatesBaseUrl}/${id}/edit` : null;

                            if (next === 'stay' && editUrl) {
                                window.location.href = editUrl;
                                return;
                            }

                            if (next === 'publish' && id) {
                                const sendRes = await fetch(`${this.adminEstimatesBaseUrl}/${id}/send`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({ reason: null }),
                                });

                                const sendData = await sendRes.json().catch(() => ({}));
                                if (!sendRes.ok) {
                                    throw new Error(sendData?.message || 'Publish failed');
                                }

                                window.location.href = sendData?.redirect_url || showUrl || `${this.adminEstimatesBaseUrl}/${id}`;
                                return;
                            }

                            if (next === 'preview' && showUrl) {
                                window.location.href = showUrl;
                                return;
                            }

                            if (showUrl) {
                                window.location.href = showUrl;
                                return;
                            }
                        } catch (e) {
                            alert(e?.message || 'Save failed');
                        } finally {
                            this.saving = false;
                        }
                    },
                }
            }
        </script>
    @endpush
</x-app-layout>
