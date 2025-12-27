<x-app-layout>
    <x-slot name="sectionTitle">Sales</x-slot>
    <x-slot name="pageTitle">Edit Invoice</x-slot>

    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.invoices.index') }}" class="text-slate-500 hover:text-slate-700">Invoices</a>
        <span class="mx-1 opacity-60">/</span>
        <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-slate-500 hover:text-slate-700">
            {{ $invoice->invoice_no ?? ('INV-' . $invoice->id) }}
        </a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Edit</span>
    </x-slot>

    @php
        $currency = $invoice->currency ?? 'LKR';
        $status = (string) ($invoice->status ?? 'draft');
        $locked = (bool) ($invoice->locked_at);

        $snap = is_array($invoice->customer_snapshot) ? $invoice->customer_snapshot : [];
        if (!array_key_exists('full_name', $snap)) $snap['full_name'] = $snap['name'] ?? null;
        if (!array_key_exists('phone', $snap)) $snap['phone'] = $snap['whatsapp_number'] ?? ($snap['whatsapp'] ?? null);

        $initial = [
            'id' => $invoice->id,
            'invoice_no' => $invoice->invoice_no,
            'status' => $status,
            'working_group_id' => $invoice->working_group_id,
            'currency' => $currency,
            'due_at' => optional($invoice->due_at)->format('Y-m-d\\TH:i') ?? now()->addDays(14)->format('Y-m-d'),
            'shipping_fee' => (float) ($invoice->shipping_fee ?? 0),
            'other_fee' => (float) ($invoice->other_fee ?? 0),
            'customer_snapshot' => $snap,
            'items' => ($invoice->items ?? collect())->map(fn($it) => [
                'id' => (int) $it->id,
                'product_id' => (int) $it->product_id,
                'variant_set_item_id' => $it->variant_set_item_id ? (int) $it->variant_set_item_id : null,
                'options' => is_array($it->pricing_snapshot)
                    ? (($it->pricing_snapshot['options'] ?? null) ?? (data_get($it->pricing_snapshot, 'input.options') ?? []))
                    : [],
                'roll_id' => $it->roll_id ? (int) $it->roll_id : null,
                'title' => (string) ($it->title ?? ''),
                'description' => $it->description,
                'qty' => (int) ($it->qty ?? 1),
                'width' => $it->width,
                'height' => $it->height,
                'unit' => $it->unit,
                'area_sqft' => $it->area_sqft,
                'offcut_sqft' => $it->offcut_sqft,
                'pricing_snapshot' => $it->pricing_snapshot,
                'unit_price' => (float) ($it->unit_price ?? 0),
                'line_subtotal' => (float) ($it->line_subtotal ?? 0),
                'discount_amount' => (float) ($it->discount_amount ?? 0),
                'tax_amount' => (float) ($it->tax_amount ?? 0),
                'line_total' => (float) ($it->line_total ?? 0),
                'finishings' => ($it->finishings ?? collect())->map(fn($f) => [
                    'id' => (int) $f->id,
                    'finishing_product_id' => $f->finishing_product_id ? (int) $f->finishing_product_id : null,
                    'option_id' => $f->option_id ? (int) $f->option_id : null,
                    'label' => $f->label ?? $f->option?->label ?? $f->finishingProduct?->name ?? ('Finishing #' . $f->id),
                    'qty' => (int) ($f->qty ?? 1),
                    'unit_price' => (float) ($f->unit_price ?? 0),
                    'total' => (float) ($f->total ?? 0),
                    'pricing_snapshot' => $f->pricing_snapshot,
                    'selected' => true,
                ])->values()->all(),
            ])->values()->all(),
        ];
    @endphp

    <div
        x-data="invoiceForm({
            locked: @js($locked),
            initial: @js($initial),
            saveUrl: @js(route('admin.invoices.update', $invoice)),
            previewUrl: @js(route('admin.invoices.show', $invoice)),
            productsUrl: @js(route('admin.estimates.products')),
            rollsUrlBase: @js(url('/admin/estimates/products')),
            quoteUrl: @js(route('admin.estimates.quote')),
            adminInvoicesBaseUrl: @js(url('/admin/invoices')),
        })"
        x-init="init()"
        class="space-y-6"
    >

        {{-- HERO BAND --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-5 sm:px-7 sm:py-6 text-white shadow-lg shadow-slate-900/20">
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-16 right-10 h-56 w-56 rounded-full border border-white/10"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2 max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium tracking-wide backdrop-blur">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Billing · Invoice editor
                    </div>

                    <div class="flex items-start gap-3 pt-1">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 shadow-inner shadow-black/20">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-xl sm:text-2xl font-black leading-tight truncate" x-text="state.invoice_no || ('INV-' + state.id)"></h2>
                                <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    <span x-text="state.status || 'draft'"></span>
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide">
                                    WG: <span x-text="state.working_group_id"></span>
                                </span>
                            </div>
                            <p class="mt-1 text-xs sm:text-sm text-white/80">
                                Works like your quotation editor: add/remove items, auto-quote, and adjust pricing. Draft invoices are editable; issued invoices are locked.
                            </p>

                            <template x-if="locked">
                                <div class="mt-3 rounded-2xl border border-amber-200/40 bg-amber-500/10 px-4 py-3 text-xs text-amber-50">
                                    This invoice is locked. Editing is disabled.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a :href="previewUrl"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur hover:bg-white/15 transition-all">
                        Back
                    </a>

                    <button type="button" @click="addItem()" :disabled="saving || isReadOnly()"
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
                    <h3 class="text-sm font-bold text-slate-900">Customer snapshot</h3>
                    <p class="mt-1 text-xs text-slate-500">This will appear on the invoice emails/PDF.</p>

                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Full name</label>
                            <input type="text" x-model="state.customer_snapshot.full_name"
                                :disabled="isReadOnly()"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Phone</label>
                            <input type="text" x-model="state.customer_snapshot.phone"
                                :disabled="isReadOnly()"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Email</label>
                            <input type="email" x-model="state.customer_snapshot.email"
                                :disabled="isReadOnly()"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900">Invoice meta</h3>
                    <p class="mt-1 text-xs text-slate-500">Due date and header fees.</p>

                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Currency</label>
                            <input type="text" x-model="state.currency" :disabled="isReadOnly()"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Due at</label>
                            <input type="datetime-local" x-model="state.due_at"
                                :disabled="isReadOnly()"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Shipping fee</label>
                            <input type="number" step="0.01" min="0" x-model.number="state.shipping_fee"
                                :disabled="isReadOnly()"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Other fee</label>
                            <input type="number" step="0.01" min="0" x-model.number="state.other_fee"
                                :disabled="isReadOnly()"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <div class="text-sm font-bold text-amber-900">Note</div>
                    <div class="mt-2 text-xs text-amber-900/90">
                        Draft totals are editable. After you issue the invoice, it becomes locked and payments can be recorded.
                    </div>
                </div>
            </section>

            {{-- RIGHT: ITEMS --}}
            <section class="space-y-6 xl:col-span-2">
                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Invoice items</h3>
                            <p class="mt-1 text-xs text-slate-500">Add products, auto-quote, then adjust pricing if needed.</p>
                        </div>

                        <div class="text-xs text-slate-500">
                            <span class="font-semibold text-slate-900" x-text="state.items.length"></span> item(s)
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        <template x-for="(it, idx) in state.items" :key="it._key">
                            <div class="rounded-3xl border border-slate-200 bg-white p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="text-sm font-black text-slate-900 truncate" x-text="productForItem(it)?.name || (it.product_id ? ('Product #' + it.product_id) : 'Select a product')"></div>
                                            <template x-if="productForItem(it)?.code">
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700"
                                                    x-text="productForItem(it).code"></span>
                                            </template>
                                            <template x-if="it._manual_price">
                                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800">
                                                    Manual price
                                                </span>
                                            </template>
                                        </div>

                                        <div class="mt-2 flex flex-col sm:flex-row sm:items-center gap-2">
                                            <button type="button"
                                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-60"
                                                :disabled="isReadOnly()"
                                                @click="toggleProductPicker(it)">
                                                Change product
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>

                                            <button type="button"
                                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-60"
                                                :disabled="isReadOnly() || !it.product_id"
                                                @click="it._manual_price=false; scheduleQuote(it)">
                                                Re-quote
                                            </button>

                                            <button type="button"
                                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 disabled:opacity-60"
                                                :disabled="isReadOnly()"
                                                @click="confirmRemove(idx)">
                                                Remove item
                                            </button>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <div class="text-[11px] text-slate-500">Line total</div>
                                        <div class="text-lg font-black text-slate-900 whitespace-nowrap">
                                            <span x-text="state.currency"></span>
                                            <span x-text="formatMoney(lineTotalWithFinishings(it))"></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Product picker --}}
                                <template x-if="it._product_open">
                                    <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex items-center gap-2">
                                            <input type="text"
                                                class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                                placeholder="Search products…"
                                                x-model="it._product_search"
                                                @input.debounce.250ms="searchProducts(it)">
                                            <button type="button"
                                                class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                                @click="it._product_open=false">
                                                Close
                                            </button>
                                        </div>

                                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-72 overflow-auto">
                                            <template x-for="p in (it._product_results || [])" :key="p.id">
                                                <button type="button"
                                                    class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 text-left hover:bg-slate-50"
                                                    @click="selectProduct(it, p)">
                                                    <img :src="p.image_url" class="h-10 w-10 rounded-xl object-cover border border-slate-200" alt="">
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-bold text-slate-900 truncate" x-text="p.name"></div>
                                                        <div class="text-[11px] text-slate-500 truncate" x-text="p.price_label || p.code || ('#' + p.id)"></div>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Inputs --}}
                                <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-12">
                                    <div class="lg:col-span-4">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Title</label>
                                        <input type="text" x-model="it.title" :disabled="isReadOnly()"
                                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                                    </div>

                                    <div class="lg:col-span-4">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Description</label>
                                        <input type="text" x-model="it.description" :disabled="isReadOnly()"
                                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                                    </div>

                                    <div class="lg:col-span-2">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Qty</label>
                                        <input type="number" min="1" step="1" x-model.number="it.qty" :disabled="isReadOnly()"
                                            @input="recalcItem(it); scheduleQuote(it)"
                                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                                    </div>

                                    <div class="lg:col-span-2">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Unit price</label>
                                        <input type="number" min="0" step="0.01" x-model.number="it.unit_price" :disabled="isReadOnly()"
                                            @input="it._manual_price=true; recalcItem(it)"
                                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                                    </div>
                                </div>

                                {{-- Variants (Option Groups + Valid Combinations) --}}
                                <template x-if="(it._option_groups || []).length">
                                    <div class="mt-5 rounded-3xl border border-slate-200 bg-white p-4">
                                        <div class="flex items-center justify-between">
                                            <div class="text-xs font-black text-slate-900">Variants</div>
                                            <button type="button"
                                                class="text-[11px] font-semibold text-slate-600 hover:text-slate-900 disabled:opacity-60"
                                                :disabled="isReadOnly()"
                                                @click="it.options = {}; scheduleQuote(it)">
                                                Clear
                                            </button>
                                        </div>

                                        <div class="mt-4 space-y-4">
                                            <template x-for="(g, gIndex) in (it._option_groups || [])" :key="g.id">
                                                <div class="rounded-2xl border border-slate-200 p-4">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div class="font-semibold text-slate-900" x-text="g.name"></div>
                                                        <template x-if="g.is_required">
                                                            <span class="text-[11px] rounded-full bg-red-50 text-red-700 px-2.5 py-1 font-semibold">Required</span>
                                                        </template>
                                                        <template x-if="!g.is_required">
                                                            <span class="text-[11px] rounded-full bg-slate-100 text-slate-700 px-2.5 py-1 font-semibold">Optional</span>
                                                        </template>
                                                    </div>

                                                    <div class="mt-3">
                                                        <select
                                                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60"
                                                            :disabled="isReadOnly()"
                                                            :required="!!g.is_required"
                                                            :value="(it.options || {})[g.id] || ''"
                                                            @change="it.options = (it.options && typeof it.options === 'object') ? it.options : {}; it.options[g.id] = $event.target.value ? Number($event.target.value) : ''; syncDependentSelectionsForItem(it); scheduleQuote(it)">
                                                            <option value="">Select…</option>
                                                            <template x-for="op in getGroupOptionsForItem(it, g, gIndex)" :key="op.id">
                                                                <option :value="op.id" x-text="op.name"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Dimensions --}}
                                <template x-if="requiresDimensions(it)">
                                    <div class="mt-5 rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="text-xs font-black text-slate-900">Dimensions</div>
                                                <div class="mt-1 text-[11px] text-slate-500">Width/height required for this product.</div>
                                            </div>
                                            <div class="text-[11px] text-slate-500" x-text="rollSummary(it)"></div>
                                        </div>

                                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                            <div>
                                                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Width</label>
                                                <input type="number" min="0.01" step="0.01" x-model.number="it.width"
                                                    :disabled="isReadOnly()"
                                                    @input="scheduleQuote(it)"
                                                    class="block w-full rounded-2xl border border-slate-200 bg-white py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                                            </div>
                                            <div>
                                                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Height</label>
                                                <input type="number" min="0.01" step="0.01" x-model.number="it.height"
                                                    :disabled="isReadOnly()"
                                                    @input="scheduleQuote(it)"
                                                    class="block w-full rounded-2xl border border-slate-200 bg-white py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                                            </div>
                                            <div>
                                                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Unit</label>
                                                <select x-model="it.unit" :disabled="isReadOnly()"
                                                    @change="scheduleQuote(it)"
                                                    class="block w-full rounded-2xl border border-slate-200 bg-white py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                                                    <option value="in">in</option>
                                                    <option value="ft">ft</option>
                                                    <option value="mm">mm</option>
                                                    <option value="cm">cm</option>
                                                    <option value="m">m</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Roll</label>
                                                <select x-model="it.roll_choice" :disabled="isReadOnly()"
                                                    @change="onRollChoiceChanged(it)"
                                                    class="block w-full rounded-2xl border border-slate-200 bg-white py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                                                    <option value="">Auto</option>
                                                    <template x-for="r in (it._available_rolls || [])" :key="r.roll_id">
                                                        <option :value="String(r.roll_id)" x-text="`${r.name} (${Number(r.width_in).toFixed(1)}in)`"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Discount/Tax --}}
                                <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-12">
                                    <div class="lg:col-span-3">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Line subtotal</label>
                                        <input type="number" min="0" step="0.01" x-model.number="it.line_subtotal" :disabled="true"
                                            class="block w-full rounded-2xl border border-slate-200 bg-slate-100 py-2.5 px-3 text-sm text-slate-900 shadow-sm opacity-70" />
                                    </div>
                                    <div class="lg:col-span-3">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Discount</label>
                                        <input type="number" min="0" step="0.01" x-model.number="it.discount_amount" :disabled="isReadOnly()"
                                            @input="recalcItem(it)"
                                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                                    </div>
                                    <div class="lg:col-span-3">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Tax</label>
                                        <input type="number" min="0" step="0.01" x-model.number="it.tax_amount" :disabled="isReadOnly()"
                                            @input="recalcItem(it)"
                                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                                    </div>
                                    <div class="lg:col-span-3">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Line total</label>
                                        <input type="number" min="0" step="0.01" x-model.number="it.line_total" :disabled="true"
                                            class="block w-full rounded-2xl border border-slate-200 bg-slate-100 py-2.5 px-3 text-sm text-slate-900 shadow-sm opacity-70" />
                                    </div>
                                </div>

                                {{-- Finishings (optional) --}}
                                <template x-if="(it.finishings || []).length">
                                    <div class="mt-5 rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="text-xs font-black text-slate-900">Finishings</div>
                                                <div class="mt-1 text-[11px] text-slate-500">
                                                    Select required finishings and quantities. Pricing is auto-estimated; you can override unit prices manually.
                                                </div>
                                            </div>
                                            <div class="text-[11px] text-slate-500">
                                                Total: <span class="font-extrabold text-slate-900" x-text="state.currency + ' ' + formatMoney(finishingsTotal(it))"></span>
                                            </div>
                                        </div>

                                        <div class="mt-4 space-y-2">
                                            <template x-for="f in (it.finishings || [])" :key="String(f.finishing_product_id || f.id)">
                                                <div class="rounded-2xl border border-slate-200 bg-white p-4"
                                                    :class="!f.selected ? 'opacity-60' : ''">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0">
                                                            <div class="flex flex-wrap items-center gap-2">
                                                                <div class="text-sm font-extrabold text-slate-900 truncate" x-text="f.label || ('Finishing #' + (f.finishing_product_id || f.id))"></div>
                                                                <template x-if="f.is_required">
                                                                    <span class="text-[11px] rounded-full bg-red-50 text-red-700 px-2.5 py-1 font-semibold">Required</span>
                                                                </template>
                                                            </div>
                                                            <div class="mt-1 text-[11px] text-slate-500">
                                                                Total:
                                                                <span class="font-bold text-slate-900">
                                                                    <span x-text="state.currency"></span>
                                                                    <span x-text="formatMoney(finishingTotalInline(f))"></span>
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700">
                                                            <input type="checkbox" class="rounded border-slate-300" x-model="f.selected"
                                                                :disabled="isReadOnly() || f.is_required"
                                                                @change="onFinishingToggle(it, f)">
                                                            Include
                                                        </label>
                                                    </div>

                                                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                                        <div>
                                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Qty</label>
                                                            <input type="number" min="1" step="1" x-model.number="f.qty" :disabled="isReadOnly() || !f.selected"
                                                                @input="onFinishingQtyInput(it, f)"
                                                                class="block w-full rounded-2xl border border-slate-200 bg-white py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                                                        </div>
                                                        <div>
                                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Unit price</label>
                                                            <input type="number" min="0" step="0.01" x-model.number="f.unit_price" :disabled="isReadOnly() || !f.selected"
                                                                @input="onFinishingUnitPriceInput(it, f)"
                                                                class="block w-full rounded-2xl border border-slate-200 bg-white py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                                                            <p class="mt-1 text-[11px] text-slate-500" x-show="!f._manual_price">Auto-priced.</p>
                                                            <p class="mt-1 text-[11px] text-amber-700" x-show="f._manual_price">Manual override.</p>
                                                        </div>
                                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                                            <div class="text-[11px] text-slate-500">Total</div>
                                                            <div class="mt-1 text-sm font-extrabold text-slate-900">
                                                                <span x-text="state.currency"></span>
                                                                <span x-text="formatMoney(finishingTotalInline(f))"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </section>
        </div>

        {{-- Remove modal --}}
        <template x-if="modals.remove.open">
            <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-xl">
                    <div class="text-lg font-black text-slate-900">Remove item?</div>
                    <div class="mt-2 text-sm text-slate-600">This will delete the invoice item when you save.</div>

                    <div class="mt-5 flex items-center justify-end gap-2">
                        <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            @click="closeRemove()">
                            Cancel
                        </button>
                        <button type="button" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700"
                            @click="removeConfirmed()">
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @push('scripts')
        <script>
            function invoiceForm({ locked, initial, saveUrl, previewUrl, productsUrl, rollsUrlBase, quoteUrl, adminInvoicesBaseUrl }) {
                const uuid = () => (globalThis.crypto?.randomUUID?.() ?? (Date.now().toString(16) + Math.random().toString(16).slice(2)));

                return {
                    locked,
                    saveUrl,
                    previewUrl,
                    productsUrl,
                    rollsUrlBase,
                    quoteUrl,
                    adminInvoicesBaseUrl,
                    saving: false,
                    productsById: {},
                    variantsByProductId: {},
                    finishingsCatalogByProductId: {},

                    state: {
                        ...initial,
                        items: (initial.items || []).map(i => ({
                            ...i,
                            _key: uuid(),
                            _product_open: false,
                            _product_search: '',
                            _product_results: [],
                            options: (i.options && typeof i.options === 'object') ? i.options : {},
                            _option_groups: [],
                            _variant_matrix: [],
                            _finishings_catalog: [],
                            roll_choice: i.roll_id ? String(i.roll_id) : '',
                            _pricing_snapshot: i.pricing_snapshot || null,
                            _use_quoted_subtotal: false,
                            _manual_price: false,
                            _available_rolls: [],
                            _roll_auto: false,
                            _roll_rotated: false,
                            finishings: (i.finishings || []).map(f => ({
                                ...f,
                                selected: f.selected !== undefined ? !!f.selected : true,
                                is_required: false,
                                min_qty: null,
                                max_qty: null,
                                _manual_price: false,
                            })),
                        })),
                        customer_snapshot: initial.customer_snapshot || {},
                    },

                    modals: {
                        remove: { open: false, idx: null },
                    },

                    init() {
                        this.hydrateSelectedProducts().then(() => {
                            this.state.items.forEach(it => {
                                this.loadRollsForItem(it);
                                this.loadVariantsForItem(it);
                                this.loadFinishingsForItem(it);
                                this.scheduleQuote(it);
                                this.recalcItem(it);
                            });
                        });
                    },

                    isReadOnly() {
                        return !!this.locked || String(this.state.status || 'draft') !== 'draft';
                    },

                    productForItem(it) {
                        const id = it?.product_id ? Number(it.product_id) : null;
                        return id ? (this.productsById[id] || null) : null;
                    },

                    requiresDimensions(it) {
                        const p = this.productForItem(it);
                        return !!(p && p.is_dimension_based);
                    },

                    async hydrateSelectedProducts() {
                        const ids = Array.from(new Set((this.state.items || []).map(x => Number(x.product_id || 0)).filter(Boolean)));
                        if (!ids.length || !this.state.working_group_id) return;

                        const url = new URL(this.productsUrl, window.location.origin);
                        url.searchParams.set('working_group_id', String(this.state.working_group_id));
                        url.searchParams.set('ids', ids.join(','));
                        url.searchParams.set('limit', '50');

                        const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) return;

                        for (const p of (data.items || [])) {
                            this.productsById[Number(p.id)] = p;
                        }
                    },

                    toggleProductPicker(it) {
                        if (this.isReadOnly()) return;
                        it._product_open = !it._product_open;
                        if (it._product_open && !it._product_results?.length) {
                            it._product_search = '';
                            this.searchProducts(it);
                        }
                    },

                    async searchProducts(it) {
                        if (!this.state.working_group_id) return;
                        const q = (it._product_search || '').trim();

                        const url = new URL(this.productsUrl, window.location.origin);
                        url.searchParams.set('working_group_id', String(this.state.working_group_id));
                        if (q) url.searchParams.set('q', q);
                        url.searchParams.set('limit', '20');

                        const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            it._product_results = [];
                            return;
                        }
                        it._product_results = data.items || [];
                    },

                    selectProduct(it, p) {
                        if (this.isReadOnly()) return;
                        it.product_id = Number(p.id);
                        this.productsById[Number(p.id)] = p;

                        // basic defaults
                        it.title = it.title || p.name || '';
                        it.description = it.description || null;
                        it.qty = Math.max(1, Number(it.qty || 1));

                        // reset dimension fields if not required
                        if (!p.is_dimension_based) {
                            it.width = null;
                            it.height = null;
                            it.unit = null;
                            it.area_sqft = null;
                            it.offcut_sqft = 0;
                            it.roll_id = null;
                            it.roll_choice = '';
                            it._available_rolls = [];
                        } else {
                            it.unit = it.unit || 'in';
                        }

                        it._product_open = false;
                        it._product_search = '';
                        it._product_results = [];

                        this.onProductSelected(it);
                    },

                    onProductSelected(it) {
                        const p = this.productForItem(it);
                        if (!p) return;

                        it.options = {};
                        it.variant_set_item_id = null;
                        it._option_groups = [];
                        it._variant_matrix = [];

                        it._finishings_catalog = [];
                        it.finishings = [];

                        it._manual_price = false;
                        it._use_quoted_subtotal = false;
                        it._pricing_snapshot = null;

                        this.loadRollsForItem(it);
                        this.loadVariantsForItem(it);
                        this.loadFinishingsForItem(it);
                        this.scheduleQuote(it);
                    },

                    addItem() {
                        if (this.isReadOnly()) return;
                        this.state.items.push({
                            id: null,
                            product_id: null,
                            variant_set_item_id: null,
                            options: {},
                            _option_groups: [],
                            _variant_matrix: [],
                            roll_id: null,
                            title: '',
                            description: null,
                            qty: 1,
                            width: null,
                            height: null,
                            unit: null,
                            area_sqft: null,
                            offcut_sqft: 0,
                            pricing_snapshot: null,
                            unit_price: 0,
                            line_subtotal: 0,
                            discount_amount: 0,
                            tax_amount: 0,
                            line_total: 0,
                            _finishings_catalog: [],
                            finishings: [],

                            _key: uuid(),
                            _product_open: true,
                            _product_search: '',
                            _product_results: [],
                            roll_choice: '',
                            _pricing_snapshot: null,
                            _use_quoted_subtotal: false,
                            _manual_price: false,
                            _available_rolls: [],
                            _roll_auto: false,
                            _roll_rotated: false,
                        });
                    },

                    async loadRollsForItem(it) {
                        if (!it.product_id) return;
                        const p = this.productForItem(it);
                        if (!p || !p.is_dimension_based) return;

                        try {
                            const url = `${this.rollsUrlBase}/${Number(it.product_id)}/rolls?working_group_id=${encodeURIComponent(this.state.working_group_id || '')}`;
                            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                            const data = await res.json().catch(() => ({}));
                            if (!res.ok) throw new Error(data?.message || 'Rolls load failed');
                            it._available_rolls = data.items || [];
                        } catch (e) {
                            console.warn(e);
                            it._available_rolls = [];
                        }
                    },

                    async loadVariantsForItem(it) {
                        it._option_groups = it._option_groups || [];
                        it._variant_matrix = it._variant_matrix || [];
                        it.options = (it.options && typeof it.options === 'object') ? it.options : {};

                        if (!it.product_id || !this.state.working_group_id) {
                            it._option_groups = [];
                            it._variant_matrix = [];
                            it.options = {};
                            return;
                        }

                        const pid = String(it.product_id);
                        if (this.variantsByProductId[pid]) {
                            const cached = this.variantsByProductId[pid];
                            it._option_groups = cached.option_groups || [];
                            it._variant_matrix = cached.variant_matrix || [];
                            this.syncDependentSelectionsForItem(it);
                            return;
                        }

                        try {
                            const url = new URL(`${this.rollsUrlBase}/${pid}/variants`, window.location.origin);
                            url.searchParams.set('working_group_id', Number(this.state.working_group_id));
                            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                            const data = await res.json().catch(() => ({}));
                            if (!res.ok) throw new Error(data?.message || 'Unable to load variants');

                            const optionGroups = data.option_groups || [];
                            const variantMatrix = data.variant_matrix || [];

                            this.variantsByProductId[pid] = { option_groups: optionGroups, variant_matrix: variantMatrix };
                            it._option_groups = optionGroups;
                            it._variant_matrix = variantMatrix;

                            // Clear selections that no longer exist
                            const allowedByGroup = {};
                            (it._option_groups || []).forEach(g => {
                                allowedByGroup[String(g.id)] = new Set((g.options || []).map(o => String(o.id)));
                            });
                            Object.keys(it.options || {}).forEach(gid => {
                                const oid = it.options[gid];
                                const set = allowedByGroup[String(gid)];
                                if (!set || !set.has(String(oid))) delete it.options[gid];
                            });

                            this.syncDependentSelectionsForItem(it);
                        } catch (e) {
                            console.warn(e);
                            it._option_groups = [];
                            it._variant_matrix = [];
                            it.options = {};
                        }
                    },

                    getSelectedOptionsMapForItem(it) {
                        const map = {};
                        const raw = (it && typeof it.options === 'object' && it.options) ? it.options : {};
                        for (const [gid, oid] of Object.entries(raw)) {
                            if (oid) map[Number(gid)] = Number(oid);
                        }
                        return map;
                    },

                    getGroupOptionsForItem(it, group, groupIndex) {
                        const groups = Array.isArray(it?._option_groups) ? it._option_groups : [];
                        const matrix = Array.isArray(it?._variant_matrix) ? it._variant_matrix : null;
                        if (!matrix?.length) return group?.options || [];

                        const gid = Number(group?.id);
                        const idx = Number.isFinite(groupIndex) ? groupIndex : groups.findIndex(g => Number(g.id) === gid);
                        if (idx < 0) return group?.options || [];

                        const selected = this.getSelectedOptionsMapForItem(it);
                        const beforeGroupIds = groups
                            .slice(0, Math.max(0, idx))
                            .map(g => Number(g.id))
                            .filter(gidBefore => selected[gidBefore]);

                        const validRows = matrix.filter(row => {
                            const rowMap = row?.options || row || {};
                            return beforeGroupIds.every(gidBefore => Number(rowMap[gidBefore]) === Number(selected[gidBefore]));
                        });

                        const validOptionIds = new Set(
                            validRows
                                .map(row => Number((row?.options || row || {})[gid]))
                                .filter(v => Number.isFinite(v))
                        );

                        if (validOptionIds.size === 0) return group?.options || [];

                        return (group?.options || []).filter(op => validOptionIds.has(Number(op.id)));
                    },

                    syncDependentSelectionsForItem(it) {
                        const groups = Array.isArray(it?._option_groups) ? it._option_groups : [];
                        const matrix = Array.isArray(it?._variant_matrix) ? it._variant_matrix : null;
                        if (!matrix?.length || groups.length === 0) return;

                        it.options = (it.options && typeof it.options === 'object') ? it.options : {};
                        const selected = this.getSelectedOptionsMapForItem(it);

                        for (let i = 0; i < groups.length; i++) {
                            const g = groups[i];
                            const gid = Number(g.id);
                            const current = selected[gid];
                            if (!current) continue;

                            const validIds = this.getGroupOptionsForItem(it, g, i).map(o => Number(o.id));
                            if (!validIds.includes(Number(current))) {
                                for (let j = i; j < groups.length; j++) {
                                    const gid2 = Number(groups[j].id);
                                    delete it.options[gid2];
                                }
                                break;
                            }
                        }
                    },

                    async loadFinishingsForItem(it) {
                        it._finishings_catalog = it._finishings_catalog || [];
                        it.finishings = it.finishings || [];

                        if (!it.product_id || !this.state.working_group_id) {
                            it._finishings_catalog = [];
                            it.finishings = [];
                            return;
                        }

                        const pid = String(it.product_id);
                        let catalog = this.finishingsCatalogByProductId[pid] || null;

                        if (!catalog) {
                            try {
                                const url = new URL(`${this.rollsUrlBase}/${pid}/finishings`, window.location.origin);
                                url.searchParams.set('working_group_id', Number(this.state.working_group_id));
                                const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                                const data = await res.json().catch(() => ({}));
                                if (!res.ok) throw new Error(data?.message || 'Unable to load finishings');
                                catalog = data.items || [];
                                this.finishingsCatalogByProductId[pid] = catalog;
                            } catch (e) {
                                console.warn(e);
                                it._finishings_catalog = [];
                                return;
                            }
                        }

                        it._finishings_catalog = catalog;

                        const existingById = {};
                        (it.finishings || []).forEach(f => {
                            const fid = Number(f.finishing_product_id || 0);
                            if (fid > 0) existingById[String(fid)] = f;
                        });

                        const merged = [];
                        for (const c of (catalog || [])) {
                            const fid = Number(c.finishing_product_id || 0);
                            if (!fid) continue;
                            const existing = existingById[String(fid)] || null;

                            const isRequired = !!c.is_required;
                            const selected = existing ? (existing.selected !== undefined ? !!existing.selected : true) : isRequired;

                            const qty = existing ? Number(existing.qty || 1) : Number(c.default_qty || 1);
                            const unitPrice = existing ? Number(existing.unit_price || 0) : Number(c.unit_price || 0);
                            const total = Math.max(0, Math.max(1, qty) * Math.max(0, unitPrice));

                            merged.push({
                                id: existing?.id || null,
                                finishing_product_id: fid,
                                option_id: existing?.option_id || null,
                                label: existing?.label || c.name || (`Finishing #${fid}`),
                                selected,
                                qty: Math.max(1, qty),
                                unit_price: unitPrice,
                                total: existing ? Number(existing.total || 0) : (selected ? total : 0),
                                pricing_snapshot: existing?.pricing_snapshot || null,
                                is_required: isRequired,
                                min_qty: c.min_qty === null || c.min_qty === undefined ? null : Number(c.min_qty),
                                max_qty: c.max_qty === null || c.max_qty === undefined ? null : Number(c.max_qty),
                                _manual_price: existing?._manual_price || false,
                            });
                        }

                        // Keep any already-saved finishings not in catalog
                        for (const k of Object.keys(existingById)) {
                            const f = existingById[k];
                            if (!merged.some(x => String(x.finishing_product_id) === String(f.finishing_product_id))) {
                                merged.push({
                                    id: f.id || null,
                                    finishing_product_id: Number(f.finishing_product_id),
                                    option_id: f.option_id || null,
                                    label: f.label || (`Finishing #${f.finishing_product_id}`),
                                    selected: f.selected !== undefined ? !!f.selected : true,
                                    qty: Math.max(1, Number(f.qty || 1)),
                                    unit_price: Number(f.unit_price || 0),
                                    total: Number(f.total || 0),
                                    pricing_snapshot: f.pricing_snapshot || null,
                                    is_required: false,
                                    min_qty: null,
                                    max_qty: null,
                                    _manual_price: f._manual_price || false,
                                });
                            }
                        }

                        it.finishings = merged;
                        this.scheduleQuote(it);
                    },

                    onRollChoiceChanged(it) {
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

                    scheduleQuote(it) {
                        if (this.isReadOnly()) {
                            this.recalcItem(it);
                            return;
                        }

                        if (!it.product_id || !this.state.working_group_id) {
                            this.recalcItem(it);
                            return;
                        }

                        if (it._quote_t) clearTimeout(it._quote_t);
                        it._quote_t = setTimeout(() => this.quoteItem(it), 250);
                    },

                    async quoteItem(it) {
                        if (!it.product_id || !this.state.working_group_id) return;

                        // For dimension-based products, require dimensions before quoting
                        if (this.requiresDimensions(it)) {
                            if (!it.width || !it.height || !it.unit) return;
                        }

                        const normalizedDim = (v) => {
                            if (v === null || v === undefined || v === '') return null;
                            const n = Number(v);
                            return Number.isFinite(n) && n > 0 ? n : null;
                        };
                        const normalizedUnit = (v) => {
                            if (typeof v !== 'string') return 'in';
                            const s = v.trim();
                            return ['in', 'ft', 'mm', 'cm', 'm'].includes(s) ? s : 'in';
                        };

                        const payload = {
                            working_group_id: Number(this.state.working_group_id),
                            product_id: Number(it.product_id),
                            qty: Number(it.qty || 1),
                            roll_id: this.requiresDimensions(it) ? (it.roll_choice ? Number(it.roll_choice) : null) : null,
                            width: this.requiresDimensions(it) ? normalizedDim(it.width) : null,
                            height: this.requiresDimensions(it) ? normalizedDim(it.height) : null,
                            unit: this.requiresDimensions(it) ? normalizedUnit(it.unit) : null,
                            options: (it.options && typeof it.options === 'object') ? it.options : {},
                            finishings: (() => {
                                const out = {};
                                (it.finishings || []).forEach(f => {
                                    if (!f?.selected) return;
                                    const fid = Number(f.finishing_product_id || 0);
                                    const qty = Number(f.qty || 0);
                                    if (fid > 0 && qty > 0) out[String(fid)] = qty;
                                });
                                return out;
                            })(),
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
                            if (!res.ok || !data?.ok) throw new Error(data?.message || 'Quote failed');

                            const q = data.data;
                            if (!q) return;

                            it._pricing_snapshot = q.pricing_snapshot || null;
                            it.qty = q.qty;

                            it.width = q.width ?? it.width;
                            it.height = q.height ?? it.height;
                            it.unit = q.unit ?? it.unit;
                            it.area_sqft = q.area_sqft ?? it.area_sqft;
                            it.offcut_sqft = q.offcut_sqft ?? it.offcut_sqft;
                            it.roll_id = q.roll_id ?? it.roll_id;
                            it._roll_auto = !!q.roll_auto;
                            it._roll_rotated = !!q.roll_rotated;

                            // Variant selection (echo-back)
                            if (q.options && typeof q.options === 'object') {
                                it.options = q.options;
                                this.syncDependentSelectionsForItem(it);
                            }

                            // Finishings pricing lines
                            if (Array.isArray(q.finishings)) {
                                it.finishings = it.finishings || [];
                                for (const fr of q.finishings) {
                                    const fid = Number(fr.finishing_product_id || 0);
                                    if (!fid) continue;
                                    const idx = it.finishings.findIndex(x => Number(x.finishing_product_id) === fid);

                                    const prev = idx >= 0 ? it.finishings[idx] : null;
                                    const manual = !!prev?._manual_price;

                                    const manualUnit = Number(prev?.unit_price || 0);
                                    const serverQty = Number(fr.qty || 1);

                                    const next = {
                                        id: prev?.id || null,
                                        finishing_product_id: fid,
                                        option_id: prev?.option_id || null,
                                        label: fr.label || prev?.label || (`Finishing #${fid}`),
                                        selected: true,
                                        qty: serverQty,
                                        unit_price: manual ? manualUnit : Number(fr.unit_price || 0),
                                        total: manual ? Math.max(0, serverQty * manualUnit) : Number(fr.total || 0),
                                        pricing_snapshot: fr.pricing_snapshot || prev?.pricing_snapshot || null,
                                        is_required: prev?.is_required || false,
                                        min_qty: prev?.min_qty ?? null,
                                        max_qty: prev?.max_qty ?? null,
                                        _manual_price: manual,
                                    };

                                    if (idx >= 0) it.finishings[idx] = { ...it.finishings[idx], ...next };
                                    else it.finishings.push(next);
                                }
                            }

                            if (!it._manual_price) {
                                it.unit_price = Number(q.unit_price || 0);
                                it.line_subtotal = Number(q.line_subtotal || 0);
                                it._use_quoted_subtotal = true;
                            }

                            this.recalcItem(it);
                        } catch (e) {
                            console.warn(e);
                        }
                    },

                    clampFinishingQty(f) {
                        let qty = Number(f?.qty || 0);
                        qty = Number.isFinite(qty) ? qty : 0;
                        qty = Math.max(0, qty);

                        const min = f?.min_qty === null || f?.min_qty === undefined ? null : Number(f.min_qty);
                        const max = f?.max_qty === null || f?.max_qty === undefined ? null : Number(f.max_qty);

                        if (min !== null && Number.isFinite(min)) qty = Math.max(qty, min);
                        if (max !== null && Number.isFinite(max)) qty = Math.min(qty, max);

                        qty = Math.max(1, qty);
                        f.qty = qty;
                    },

                    recalcFinishingLine(f) {
                        const qty = Math.max(0, Number(f?.qty || 0));
                        const unit = Math.max(0, Number(f?.unit_price || 0));
                        f.total = Math.max(0, qty * unit);
                    },

                    onFinishingToggle(it, f) {
                        if (!f) return;
                        if (f.selected) {
                            this.clampFinishingQty(f);
                            this.recalcFinishingLine(f);
                        } else {
                            f.total = 0;
                        }
                        this.scheduleQuote(it);
                    },

                    onFinishingQtyInput(it, f) {
                        if (!f) return;
                        this.clampFinishingQty(f);
                        this.recalcFinishingLine(f);
                        this.scheduleQuote(it);
                    },

                    onFinishingUnitPriceInput(_it, f) {
                        if (!f) return;
                        f._manual_price = true;
                        this.clampFinishingQty(f);
                        this.recalcFinishingLine(f);
                    },

                    finishingTotalInline(f) {
                        if (!f || !f.selected) return 0;
                        const qty = Math.max(0, Number(f.qty || 0));
                        const unit = Math.max(0, Number(f.unit_price || 0));
                        if (f.total !== null && f.total !== undefined) {
                            return Math.max(0, Number(f.total || 0));
                        }
                        return Math.max(0, qty * unit);
                    },

                    finishingsTotal(it) {
                        return (it.finishings || []).reduce((s, f) => s + this.finishingTotalInline(f), 0);
                    },

                    lineTotalWithFinishings(it) {
                        return Math.max(0, Number(it.line_total || 0)) + this.finishingsTotal(it);
                    },

                    subtotal() {
                        // mirror backend: item subtotal + finishings totals
                        return this.state.items.reduce((s, it) => s + Number(it.line_subtotal || 0) + this.finishingsTotal(it), 0);
                    },

                    lineDiscountTotal() {
                        return this.state.items.reduce((s, it) => s + Number(it.discount_amount || 0), 0);
                    },

                    taxTotal() {
                        return this.state.items.reduce((s, it) => s + Number(it.tax_amount || 0), 0);
                    },

                    grandTotal() {
                        const base = Math.max(0, this.subtotal() - this.lineDiscountTotal() + this.taxTotal());
                        const shipping = Math.max(0, Number(this.state.shipping_fee || 0));
                        const other = Math.max(0, Number(this.state.other_fee || 0));
                        return base + shipping + other;
                    },

                    kpiCards() {
                        return [
                            { key: 'sub', label: 'Subtotal', help: 'Items + finishings', value: () => this.subtotal() },
                            { key: 'disc', label: 'Discount', help: 'Sum of line discounts', value: () => this.lineDiscountTotal() },
                            { key: 'tax', label: 'Tax', help: 'Sum of line taxes', value: () => this.taxTotal() },
                            { key: 'grand', label: 'Grand Total', help: 'Subtotal - discounts + tax + fees', value: () => this.grandTotal() },
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

                        if (!this.state.working_group_id) {
                            alert('Working group is required.');
                            return;
                        }

                        const missingProduct = this.state.items.find(it => !it.product_id);
                        if (missingProduct) {
                            alert('Please select a product for every item.');
                            return;
                        }

                        this.saving = true;

                        const payload = {
                            customer_snapshot: this.state.customer_snapshot || {},
                            currency: this.state.currency || 'LKR',
                            due_at: this.state.due_at || null,
                            shipping_fee: Number(this.state.shipping_fee || 0),
                            other_fee: Number(this.state.other_fee || 0),

                            items: this.state.items.map(i => ({
                                id: i.id || null,
                                product_id: Number(i.product_id),
                                variant_set_item_id: i.variant_set_item_id ? Number(i.variant_set_item_id) : null,
                                options: (i.options && typeof i.options === 'object') ? i.options : {},
                                roll_id: i.roll_id ? Number(i.roll_id) : null,
                                title: i.title || '',
                                description: i.description || null,
                                qty: Number(i.qty || 0),
                                width: this.requiresDimensions(i) ? (i.width === null ? null : Number(i.width)) : null,
                                height: this.requiresDimensions(i) ? (i.height === null ? null : Number(i.height)) : null,
                                unit: this.requiresDimensions(i) ? (i.unit || 'in') : null,
                                area_sqft: i.area_sqft === null ? null : Number(i.area_sqft),
                                offcut_sqft: Number(i.offcut_sqft || 0),
                                pricing_snapshot: i._pricing_snapshot || i.pricing_snapshot || null,
                                unit_price: Number(i.unit_price || 0),
                                line_subtotal: Number(i.line_subtotal || 0),
                                discount_amount: Number(i.discount_amount || 0),
                                tax_amount: Number(i.tax_amount || 0),
                                line_total: Number(i.line_total || 0),
                                finishings: (i.finishings || [])
                                    .filter(f => !!f?.selected)
                                    .map(f => ({
                                        id: f.id || null,
                                        finishing_product_id: f.finishing_product_id ? Number(f.finishing_product_id) : null,
                                        option_id: f.option_id ? Number(f.option_id) : null,
                                        label: f.label || '',
                                        selected: true,
                                        qty: Number(f.qty || 1),
                                        unit_price: Number(f.unit_price || 0),
                                        total: Number(this.finishingTotalInline(f)),
                                        pricing_snapshot: f.pricing_snapshot || null,
                                    })),
                            })),
                        };

                        try {
                            const res = await fetch(this.saveUrl, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(payload),
                            });

                            const data = await res.json().catch(() => ({}));
                            if (!res.ok || !data?.ok) throw new Error(data?.message || 'Save failed');

                            const id = data?.id ?? this.state.id;
                            if (id) this.state.id = id;

                            if (next === 'stay' && data?.edit_url) {
                                window.location.href = data.edit_url;
                                return;
                            }

                            window.location.href = data?.redirect_url || this.previewUrl || `${this.adminInvoicesBaseUrl}/${id}`;
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
